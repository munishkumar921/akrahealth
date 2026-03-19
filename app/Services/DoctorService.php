<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSpeciality;
use App\Models\HospitalTiming;
use App\Models\Speciality;
use App\Traits\SMSTrait;
use App\Traits\UploadFileTrait;
use Carbon\Carbon;

class DoctorService
{
    use SMSTrait, UploadFileTrait;

    /**
     * list
     *
     * @param  mixed  $request
     * @return void
     */
    public function list($request)
    {
        $user = auth()->user();
        $hospitalId = $user->doctor?->hospital?->id;

        if (! $hospitalId) {
            // Return empty collection if user has no doctor or doctor has no hospital
            return collect([
                'data' => [],
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 10,
                'total' => 0,
            ]);
        }

        $doctors = Doctor::where('hospital_id', $hospitalId)
            ->with('user', 'hospital.timings', 'specialities')
            ->when($request->search, function ($query) use ($request) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%")
                        ->orWhere('email', 'like', "%{$request->search}%")
                        ->orWhere('mobile', 'like', "%{$request->search}%");
                });
            })
            ->when(
                $request->filled('filterActive') || $request->filled('filterInactive'),
                function ($q) use ($request) {
                    $filterActive = $request->boolean('filterActive');
                    $filterInactive = $request->boolean('filterInactive');

                    // If both are checked or both are unchecked, show all
                    if ($filterActive && $filterInactive) {
                        return $q;
                    }

                    // If only Active is checked
                    if ($filterActive) {
                        return $q->where('is_active', 1);
                    }

                    // If only Inactive is checked
                    if ($filterInactive) {
                        return $q->where('is_active', 0);
                    }

                    return $q;
                }
            )
            ->when(
                $request->date,
                fn ($q) => $q->whereDate('created_at', $request->date)
            )
            ->orderBy('created_at', 'desc')
            ->paginate(request('per_page', paginateLimit()))->withQueryString();
        $doctors->getCollection()->transform(function ($doctor) {
            $doctor->user->profile_photo_url = $doctor->user->profile_photo_url;
            $doctor->user->created = dateFormat($doctor->user->created_at);

            return $doctor;
        });

        return $doctors;
    }

    /**
     * Save Doctor, Hospital, HospitalTiming, DoctorSpeciality data
     *
     * @param  array  $data
     * @param  User  $user
     * @return void
     */
    public function saveDoctor($data, $user = null)
    {
        // $lat_lng = getLatLong($data['street_address1']['en'] ?? null);

        // /* upsert a hospital */
        // $hospital = Hospital::updateOrCreate(
        //     [
        //         'phone' => $data['mobile'],
        //     ],
        //     [
        //         'name' => $data['name']?? $user['name'],
        //         'street_address1' => $data['street_address1']??'',
        //         'street_address2' => $data['street_address2'] ?? null,
        //         'state' => $data['state'] ?? null,
        //         'country' => $data['country'] ?? null,
        //         'zip' => $data['zip'] ?? null,
        //         'latitude' => $lat_lng['lat']??'',
        //         'longitude' => $lat_lng['lng']??'',
        //     ]
        // );

        /* upsert hospital's timings */
        $hospitalId = $data['hospitalId'] ?? $user->doctor?->hospital_id;
        $hospital_timings = $hospitalId ? HospitalTiming::find($hospitalId) : null;

        /* upsert the doctor */
        $updateData = [
            'user_id' => $user->id ?? $data['user_id'] ?? null,
            'hospital_id' => $hospitalId,
            'name' => 'Dr'.' '.$data['first_name'].' '.$data['last_name'] ?? null,
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'registration_number' => $data['registration_number'] ?? '',
            'qualification' => $data['qualification'] ?? '',
            'experience' => $data['experience'] ?? '',
            'consultation_fee' => $data['consultation_fee'] ?? '',
            'appointment_slot_duration' => $data['appointment_slot_duration'] ?? '0',
            'dea' => $data['dea'] ?? null,
            'about' => $data['about'] ?? '',
            'description' => $data['description'] ?? null,
            'certification' => '',
            'government_id_proof' => '',
            'is_active' => $data['is_active'] ?? false,
            'is_verified' => $data['is_verified'] ?? false,
            'speciality_id' => $data['speciality_id'] ?? null,
            'in_person_consultation' => $data['in_person_consultation'] ?? false,
            'video_consultation' => $data['video_consultation'] ?? false,
            'whatsapp_consultation' => $data['whatsapp_consultation'] ?? false,
        ];

        if (request()->hasFile('certification') && request()->file('certification')->isValid()) {
            $updateData['certification'] = $this->uploadPublic(request()->file('certification'));
        } else {
            $updateData['certification'] = $data['old_certification'] ?? '';
        }

        if (request()->hasFile('government_id_proof') && request()->file('government_id_proof')->isValid()) {
            $updateData['government_id_proof'] = $this->uploadPublic(request()->file('government_id_proof'));
        } else {
            $updateData['government_id_proof'] = $data['old_government_id_proof'] ?? '';
        }

        $is_available = Doctor::where('user_id', $user->id ?? null)->first();
        if (isset($is_available->is_verified) && $is_available->is_verified == false && $data['is_verified'] == true) {
            $this->sendSMS($user->mobile, 'KYC_Approved', [
                'var1' => 'Doctor',
            ]);
        }
        $doctor = Doctor::updateOrCreate(
            ['id' => $data['doctorId'] ?? null],
            ['user_id' => $data['user_id'] ?? $user->id ?? null] + $updateData
        );

        $doctor->user()->update([
            'is_active' => $data['is_active'] ?? false,
        ]);

        //     /* Update doctor's slots */
        //     $slots = [];
        //     if(!empty($data) && !empty($data['appointment_slot_duration'])   && $hospital_timings){
        //     if ($hospital_timings->morning_slot) {
        //         $morning_slots = $this->generateTimeSlots('09:00', '13:00', (int) $data['appointment_slot_duration'], $doctor->id, $hospital_timings);
        //         $slots = array_merge($slots, $morning_slots);
        //     }

        //     if ($hospital_timings->afternoon_slot) {
        //         $afternoon_slot = $this->generateTimeSlots('14:00', '17:00', (int) $data['appointment_slot_duration'], $doctor->id, $hospital_timings);
        //         $slots = array_merge($slots, $afternoon_slot);
        //     }

        //     if ($hospital_timings->evening_slot) {
        //         $evening_slot = $this->generateTimeSlots('17:00', '20:00', (int) $data['appointment_slot_duration'], $doctor->id, $hospital_timings);
        //         $slots = array_merge($slots, $evening_slot);
        //     }

        //     if ($hospital_timings->night_slot) {
        //         $night_slot = $this->generateTimeSlots('20:00', '22:00', (int) $data['appointment_slot_duration'], $doctor->id, $hospital_timings);
        //         $slots = array_merge($slots, $night_slot);
        //     }

        //     DoctorSlot::where('doctor_id', $doctor->id)->forceDelete();
        //     foreach ($slots as $slot) {
        //         DoctorSlot::create($slot);
        //     }
        // }
        /* create doctor's specialities */
        DoctorSpeciality::where('doctor_id', $doctor->id)->delete();
        if (isset($data['specialities'])) {

            foreach ($data['specialities'] as $speciality) {
                $speciality = is_array($speciality) ? $speciality : $speciality;
                $specialityId = Speciality::where('name', $speciality)->first()->id ?? null;
                if ($specialityId) {
                    try {
                        DoctorSpeciality::create([
                            'doctor_id' => $doctor->id,
                            'speciality_id' => $specialityId,
                        ]);
                    } catch (\Illuminate\Database\QueryException $e) {
                        // Skip if foreign key constraint fails
                    }
                }
            }
        }
    }

    /**
     * Get a doctor by UUID
     *
     * @return Doctor|null
     */
    public function getDoctor(string $id)
    {
        return Doctor::with(['user.address', 'hospital.timings', 'specialities'])
            ->where('id', $id)
            ->firstOrFail();
    }

    /**
     * generateTimeSlots
     *
     * @param  mixed  $startTime
     * @param  mixed  $endTime
     * @param  mixed  $slotDuration
     * @return void
     */
    public function generateTimeSlots($startTime, $endTime, $slotDuration, $doctor_id, $hospital_timings)
    {
        $slots = [];

        $start = Carbon::createFromTimeString($startTime);
        $end = Carbon::createFromTimeString($endTime);

        while ($start->lt($end)) {
            $slotStart = $start->format('H:i');
            $slotEnd = $start->copy()->addMinutes($slotDuration)->format('H:i');

            if ($start->copy()->addMinutes($slotDuration)->gt($end)) {
                /* do not create a slot that goes past end time */
                break;
            }

            foreach (
                [
                    'sunday',
                    'monday',
                    'tuesday',
                    'wednesday',
                    'thursday',
                    'friday',
                    'saturday',
                ] as $day
            ) {
                if ($hospital_timings[$day]) {
                    $slots[] = [
                        'doctor_id' => $doctor_id,
                        'day_of_week' => $day,
                        'start_time' => $slotStart,
                        'end_time' => $slotEnd,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ];
                }
            }

            $start->addMinutes($slotDuration);
        }

        return $slots;
    }

    /**
     * getDoctorSlots
     *
     * @param  mixed  $doctor_id
     * @param  mixed  $date
     * @param  int  $duration
     * @return array
     */
    public function getDoctorSlots($doctor_id, $date, $duration = 30)
    {
        // Ensure duration is an integer
        $duration = 30;

        // Keep original date for appointment queries
        $dateString = Carbon::parse($date)->format('Y-m-d');
        $dayOfWeek = ucfirst(strtolower(Carbon::parse($date)->format('l')));

        $doctor = Doctor::with(['providerException' => function ($q) use ($dateString) {
            $q->where('exception_date', $dateString);
        }])->where('id', $doctor_id)
            ->first();

        if (! $doctor || ! $doctor->hospital_id) {
            return [];
        }

        /*
        |--------------------------------------------------------------------------
        | Get Timings (Exception OR Hospital Default)
        |--------------------------------------------------------------------------
        */

        $slots = collect();

        // Check if doctor has exception for selected date
        $exception = $doctor->providerException->first();
        if (! empty($exception)) {
            $slots = collect([
                (object) [
                    'open_time' => Carbon::parse($exception->start_time)->format('H:i'),
                    'close_time' => Carbon::parse($exception->end_time)->format('H:i'),
                ],
            ]);

        } else {

            $slots = HospitalTiming::where('hospital_id', $doctor->hospital_id)
                ->where('day_of_week', $dayOfWeek)
                ->orderBy('open_time')
                ->get();
        }

        if ($slots->isEmpty()) {
            return [];
        }

        /*
        |--------------------------------------------------------------------------
        | Get Booked Appointments
        |--------------------------------------------------------------------------
        */

        $bookedAppointments = Appointment::where('doctor_id', $doctor_id)
            ->where('appointment_date', $dateString)
            ->pluck('appointment_time')
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Generate Slots
        |--------------------------------------------------------------------------
        */

        $result = [];
        foreach ($slots as $slot) {

            $slotStart = Carbon::parse($slot->open_time);
            $slotEnd = Carbon::parse($slot->close_time);
            $current = $slotStart->copy();

            while ($current->copy()->addMinutes($duration)->lte($slotEnd)) {

                $startTime = $current->format('H:i');
                $endTime = $current->copy()->addMinutes($duration)->format('H:i');

                $isBooked = in_array($startTime, $bookedAppointments);

                $result[] = [
                    'value' => $startTime,
                    'slot' => "{$startTime} - {$endTime}",
                    'status' => $isBooked ? 'booked' : 'available',
                ];

                $current->addMinutes($duration);
            }
        }

        return $result;
    }

    public function profileUpdate($data, $request = null)
    {
        $user = auth()->user();
        $doctor = $user->doctor;
        // Update User
        $user->update([
            'mobile' => $data['mobile'] ?? $user->mobile,
            'sex' => $data['sex'] ?? $user->sex,
        ]);

        // Update Address
        $addressData = [
            'address_1' => $data['street_address1'] ?? $data['address_1'] ?? null,
            'address_2' => $data['street_address2'] ?? $data['address_2'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'zip' => $data['zip'] ?? null,
            'country' => $data['country'] ?? null,
        ];
        $user->address()->updateOrCreate(['user_id' => $user->id], array_filter($addressData));

        // Update Doctor
        if ($doctor) {
            $doctorData = [
                'name' => $data['first_name'].$data['last_name'] ?? $doctor->name,
                'first_name' => $data['first_name'] ?? $doctor->first_name,
                'last_name' => $data['last_name'] ?? $doctor->last_name,
                'qualification' => $data['qualification'] ?? $doctor->qualification,
                'experience' => $data['experience'] ?? $doctor->experience,
                'consultation_fee' => $data['consultation_fee'] ?? $doctor->consultation_fee,
                'appointment_slot_duration' => $data['appointment_slot_duration'] ?? $doctor->appointment_slot_duration,
                'about' => $data['about'] ?? $doctor->about,
                'registration_number' => $data['registration_number'] ?? $doctor->registration_number,
                'in_person_consultation' => isset($data['in_person_consultation']) ? $data['in_person_consultation'] : $doctor->in_person_consultation,
                'video_consultation' => isset($data['video_consultation']) ? $data['video_consultation'] : $doctor->video_consultation,
                'whatsapp_consultation' => isset($data['whatsapp_consultation']) ? $data['whatsapp_consultation'] : $doctor->whatsapp_consultation,
                'profile_photo_path' => ($request && $request->file('profile_photo_path'))
                    ? $this->uploadPublic($request->file('profile_photo_path'))
                    : $doctor->profile_photo_path,
            ];

            if (request()->hasFile('certification') && request()->file('certification')->isValid()) {
                $doctorData['certification'] = $this->uploadPublic(request()->file('certification'));
            }

            $doctor->update($doctorData);

            // Update Specialities
            if (isset($data['specialities'])) {
                DoctorSpeciality::where('doctor_id', $doctor->id)->delete();
                foreach ($data['specialities'] as $speciality) {
                    $specialityId = is_array($speciality) ? $speciality : $speciality;

                    $specialityId = Speciality::where('name', $specialityId)->value('id');

                    if ($specialityId) {
                        try {
                            DoctorSpeciality::create([
                                'doctor_id' => $doctor->id,
                                'speciality_id' => $specialityId,
                            ]);
                        } catch (\Illuminate\Database\QueryException $e) {
                            // Skip if foreign key constraint fails
                        }
                    }
                }
            }
        }
    }
}

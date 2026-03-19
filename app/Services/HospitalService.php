<?php

namespace App\Services;

use App\Models\Hospital;
use App\Models\HospitalTiming;
use App\Models\User;
use App\Traits\UploadFileTrait;
use Illuminate\Support\Facades\DB;

class HospitalService
{
    use UploadFileTrait;

    /**
     * Store or update hospital
     */
    public function saveHospital(array $input, $user = null): Hospital
    {
        return DB::transaction(function () use ($input, $user) {

            // Handle practice_logo file upload
            $practiceLogoPath = null;
            if (isset($input['practice_logo']) && $input['practice_logo'] instanceof \Illuminate\Http\UploadedFile && $input['practice_logo']->isValid()) {
                $practiceLogoPath = $this->uploadPublic($input['practice_logo'], 'practice-logos');
            } elseif (isset($input['practice_logo']) && is_string($input['practice_logo'])) {
                // Keep existing logo path if it's a string
                $practiceLogoPath = $input['practice_logo'];
            } elseif (isset($input['old_practice_logo'])) {
                // Keep old logo if no new one uploaded
                $practiceLogoPath = $input['old_practice_logo'];
            }

            // Handle profile_photo_path file upload
            $profilePhotoPath = null;
            if (isset($input['profile_photo_path']) && $input['profile_photo_path'] instanceof \Illuminate\Http\UploadedFile && $input['profile_photo_path']->isValid()) {
                $profilePhotoPath = $this->uploadPublic($input['profile_photo_path'], 'profile-photos');
            } elseif (isset($input['profile_photo_path']) && is_string($input['profile_photo_path'])) {
                // Keep existing profile photo path if it's a string
                $profilePhotoPath = $input['profile_photo_path'];
            } elseif (isset($input['old_profile_photo_path'])) {
                // Keep old profile photo if no new one uploaded
                $profilePhotoPath = $input['old_profile_photo_path'];
            }
            $hospital = Hospital::updateOrCreate(
                [
                    'id' => $input['hospitalId'] ?? $input['id'] ?? null,
                    'user_id' => $user->id ?? null,

                ],
                ['name' => $input['practice_name'] ?? $input['name'] ?? null,
                    'email' => $input['practice_email'] ?? $input['email'] ?? null,
                    'phone' => $input['practice_mobile'] ?? $input['phone'] ?? null,

                    'street_address1' => $input['street_address1'] ?? $input['practice_street_address1'] ?? null,
                    'street_address2' => $input['street_address2'] ?? $input['practice_street_address2'] ?? null,

                    'city' => $input['city'] ?? $input['practice_city'] ?? null,
                    'state' => $input['state'] ?? $input['practice_state'] ?? null,
                    'zip' => $input['zip'] ?? $input['practice_zip'] ?? null,
                    'country' => $input['country'] ?? $input['practice_country'] ?? null,

                    'website' => $input['website'] ?? null,
                    'primary_contact' => $input['primary_contact'] ?? $input['practice_primary_contact'] ?? null,

                    'practice_logo' => $practiceLogoPath ?? $input['old_practice_logo'] ?? null,

                    'npi' => $input['npi'] ?? null,
                    'medicare' => $input['medicare'] ?? null,
                    'tax_id' => $input['tax_id'] ?? null,

                    'default_pos_id' => $input['default_pos_id'] ?? 0,
                    'documents_dir' => $input['documents_dir'] ?? null,

                    'weight_unit' => $input['weight_unit'] ?? null,
                    'height_unit' => $input['height_unit'] ?? null,
                    'height_unit' => $input['temp_unit'] ?? null,
                    'hc_unit' => $input['hc_unit'] ?? null,

                    'is_active' => isset($input['is_active'])
                        ? (bool) $input['is_active']
                        : true,
                    'main_branch_id' => $input['main_branch_id'] ?? null,
                ]
            );

            // Update user profile photo if uploaded
            if ($profilePhotoPath && $user) {
                $userModel = User::find($user->id);
                if ($userModel) {
                    $userModel->profile_photo_path = $profilePhotoPath;
                    $userModel->save();
                }
            }
            if ($hospital->main_branch_id == null) {
                Hospital::where('main_branch_id', $hospital->id)->update([
                    'practice_logo' => $practiceLogoPath ?? $input['old_practice_logo'] ?? null,
                ]);

            }

            // ✅ Hospital Timings
            if (! empty($input['timings']) && is_array($input['timings'])) {

                // Convert time values to 24-hour format for MySQL TIME column
                $convertTime = function ($time) {
                    if (empty($time)) {
                        return null;
                    }
                    // Check if already in 24-hour format
                    if (preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $time)) {
                        return $time;
                    }
                    try {
                        return \Carbon\Carbon::createFromFormat('h:i A', $time)->format('H:i:s');
                    } catch (\Exception $e) {
                        try {
                            return \Carbon\Carbon::createFromFormat('h:i:s A', $time)->format('H:i:s');
                        } catch (\Exception $e) {
                            return $time; // Return as is if parsing fails
                        }
                    }
                };

                foreach ($input['timings'] as $timing) {
                    $isClosed = (bool) ($timing['is_closed'] ?? false);

                    HospitalTiming::updateOrCreate(
                        [
                            'hospital_id' => $hospital->id,
                            'day_of_week' => $timing['day_of_week'] ?? null,
                        ],
                        [
                            // `is_closed` is derived from whether open/close times are null.
                            'open_time' => $isClosed ? null : $convertTime($timing['open_time'] ?? null),
                            'close_time' => $isClosed ? null : $convertTime($timing['close_time'] ?? null),
                            'weekends' => $timing['weekends'] ?? null,
                            'time_zone' => $timing['time_zone'] ?? null,
                        ]
                    );
                }
            }

            return $hospital;
        });
    }
}

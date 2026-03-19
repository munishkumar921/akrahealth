<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Helper function to add column only if it doesn't exist
        $addColumnIfNotExists = function (Blueprint $table, string $column, string $type, ?string $after = null) {
            if (! Schema::hasColumn('patients', $column)) {
                $columnDef = $table->$type($column);
                if ($after) {
                    $columnDef->after($after);
                }
                $columnDef->nullable();
            }
        };

        Schema::table('patients', function (Blueprint $table) use (&$addColumnIfNotExists) {
            $addColumnIfNotExists($table, 'last_name', 'string', 'user_id');
            $addColumnIfNotExists($table, 'first_name', 'string', 'last_name');
            $addColumnIfNotExists($table, 'title', 'string', 'first_name');
            $addColumnIfNotExists($table, 'sex', 'string', 'title');
            $addColumnIfNotExists($table, 'dob', 'date', 'sex');
            $addColumnIfNotExists($table, 'race', 'string', 'dob');
            $addColumnIfNotExists($table, 'ethnicity', 'string', 'race');
            $addColumnIfNotExists($table, 'language', 'string', 'ethnicity');
            $addColumnIfNotExists($table, 'address_1', 'string', 'language');
            $addColumnIfNotExists($table, 'address_2', 'string', 'address_1');
            $addColumnIfNotExists($table, 'country', 'string', 'address_2');
            $addColumnIfNotExists($table, 'city', 'string', 'country');
            $addColumnIfNotExists($table, 'state', 'string', 'city');
            $addColumnIfNotExists($table, 'zip', 'string', 'state');
            $addColumnIfNotExists($table, 'mobile', 'string', 'zip');
            $addColumnIfNotExists($table, 'email', 'string', 'mobile');
            $addColumnIfNotExists($table, 'marital_status', 'string', 'email');
            $addColumnIfNotExists($table, 'partner_name', 'string', 'marital_status');
            $addColumnIfNotExists($table, 'employer', 'string', 'partner_name');
            $addColumnIfNotExists($table, 'emergency_contact', 'string', 'employer');
            $addColumnIfNotExists($table, 'emergency_phone', 'string', 'emergency_contact');
            $addColumnIfNotExists($table, 'photo', 'string', 'emergency_phone');
            $addColumnIfNotExists($table, 'comments', 'longText', 'photo');
            $addColumnIfNotExists($table, 'registration_code', 'string', 'comments');
            $addColumnIfNotExists($table, 'other1', 'string', 'registration_code');
            $addColumnIfNotExists($table, 'other2', 'string', 'other1');
            $addColumnIfNotExists($table, 'billing_notes', 'longText', 'other2');
            $addColumnIfNotExists($table, 'imm_notes', 'longText', 'billing_notes');
            $addColumnIfNotExists($table, 'tobacco', 'string', 'imm_notes');
            $addColumnIfNotExists($table, 'sexually_active', 'string', 'tobacco');
            $addColumnIfNotExists($table, 'pregnant', 'string', 'sexually_active');
            $addColumnIfNotExists($table, 'caregiver', 'string', 'pregnant');
            $addColumnIfNotExists($table, 'referred_by', 'string', 'caregiver');
            $addColumnIfNotExists($table, 'guardian_other1', 'string', 'referred_by');
            $addColumnIfNotExists($table, 'guardian_firstname', 'string', 'guardian_other1');
            $addColumnIfNotExists($table, 'guardian_lastname', 'string', 'guardian_firstname');
            $addColumnIfNotExists($table, 'guardian_code', 'string', 'guardian_lastname');
            $addColumnIfNotExists($table, 'guardian_address', 'string', 'guardian_code');
            $addColumnIfNotExists($table, 'guardian_city', 'string', 'guardian_address');
            $addColumnIfNotExists($table, 'guardian_state', 'string', 'guardian_city');
            $addColumnIfNotExists($table, 'guardian_zip', 'string', 'guardian_state');
            $addColumnIfNotExists($table, 'guardian_phone_home', 'string', 'guardian_zip');
            $addColumnIfNotExists($table, 'guardian_phone_work', 'string', 'guardian_phone_home');
            $addColumnIfNotExists($table, 'guardian_phone_cell', 'string', 'guardian_phone_work');
            $addColumnIfNotExists($table, 'guardian_email', 'string', 'guardian_phone_cell');
            $addColumnIfNotExists($table, 'guardian_relationship', 'string', 'guardian_email');
            $addColumnIfNotExists($table, 'rcopia_sync', 'string', 'guardian_relationship');
            $addColumnIfNotExists($table, 'rcopia_update_medications', 'string', 'rcopia_sync');
            $addColumnIfNotExists($table, 'rcopia_update_medications_date', 'string', 'rcopia_update_medications');
            $addColumnIfNotExists($table, 'rcopia_update_allergies', 'string', 'rcopia_update_medications_date');
            $addColumnIfNotExists($table, 'rcopia_update_allergies_date', 'string', 'rcopia_update_allergies');
            $addColumnIfNotExists($table, 'rcopia_update_prescription', 'string', 'rcopia_update_allergies_date');
            $addColumnIfNotExists($table, 'rcopia_update_prescription_date', 'string', 'rcopia_update_prescription');
            $addColumnIfNotExists($table, 'race_code', 'string', 'rcopia_update_prescription_date');
            $addColumnIfNotExists($table, 'ethnicity_code', 'string', 'race_code');
            $addColumnIfNotExists($table, 'language_code', 'string', 'ethnicity_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};

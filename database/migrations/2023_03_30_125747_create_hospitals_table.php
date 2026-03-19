<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hospitals', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Basic details
            $table->string('name', 255)->nullable();
            $table->string('street_address1', 150)->nullable();
            $table->string('street_address2', 150)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('zip', 20)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('fax', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('website', 150)->nullable();
            $table->string('primary_contact', 100)->nullable();
            $table->string('npi', 100)->nullable();
            $table->string('medicare', 100)->nullable();
            $table->string('tax_id', 50)->nullable();

            // Measurement units
            $table->string('weight_unit', 20)->nullable();
            $table->string('height_unit', 20)->nullable();
            $table->string('temp_unit', 20)->nullable();
            $table->string('hc_unit', 20)->nullable();

            // Coordinates & timings
            $table->string('latitude', 50)->nullable();
            $table->string('longitude', 50)->nullable();
            $table->json('timings')->nullable();
            $table->string('min_time', 20)->nullable();
            $table->string('max_time', 20)->nullable();
            $table->boolean('weekends')->default(true);
            $table->boolean('default_pos_id')->default(true);

            // Paths
            $table->string('documents_dir', 150)->nullable();
            $table->string('document_dir', 150)->nullable();

            // Billing info
            $table->string('billing_street_address1', 150)->nullable();
            $table->string('billing_street_address2', 150)->nullable();
            $table->string('billing_city', 100)->nullable();
            $table->string('billing_state', 100)->nullable();
            $table->string('billing_zip', 20)->nullable();

            // Fax / email configs
            $table->string('fax_type', 50)->nullable();
            $table->text('fax_email')->nullable();
            $table->text('fax_email_password')->nullable();
            $table->text('fax_email_hostname')->nullable();

            // Integrations (moved to text where appropriate)
            $table->string('patient_portal', 100)->nullable();
            $table->string('rcopia_extension', 100)->nullable();
            $table->string('rcopia_apiVendor', 100)->nullable();
            $table->text('rcopia_apiPass')->nullable();
            $table->string('rcopia_apiPractice', 100)->nullable();
            $table->string('rcopia_apiSystem', 100)->nullable();
            $table->string('rcopia_update_notification_last_update', 100)->nullable();
            $table->string('updox_extension', 100)->nullable();
            $table->string('version', 50)->nullable();
            $table->string('mtm_extension', 100)->nullable();
            $table->string('practice_logo', 150)->nullable();
            $table->string('mtm_logo', 150)->nullable();
            $table->string('mtm_alert_users', 150)->nullable();

            // Misc settings
            $table->text('additional_message')->nullable();
            $table->string('snomed_extension', 100)->nullable();
            $table->string('vivacare', 100)->nullable();
            $table->string('sales_tax', 20)->nullable();
            $table->string('practicehandle', 100)->nullable();
            $table->string('peacehealth_id', 100)->nullable();

            // Clinical templates
            $table->string('icd', 100)->nullable();
            $table->string('supplements_menu_item', 100)->nullable();
            $table->string('immunizations_menu_item', 100)->nullable();
            $table->string('encounter_template', 100)->nullable();

            // More integrations / API creds
            $table->string('fax_email_smtp', 150)->nullable();
            $table->string('phaxio_api_key', 150)->nullable();
            $table->text('phaxio_api_secret')->nullable();
            $table->string('birthday_extension', 100)->nullable();
            $table->string('birthday_sent_date', 50)->nullable();
            $table->text('birthday_message')->nullable();

            // Practice settings
            $table->string('opennotes', 100)->nullable();
            $table->string('patient_centric', 100)->nullable();
            $table->text('practice_api_key')->nullable();
            $table->text('practice_registration_key')->nullable();
            $table->string('practice_registration_timeout', 50)->nullable();

            // OAuth / Tokens
            $table->string('openidconnect_client_id', 150)->nullable();
            $table->text('openidconnect_client_secret')->nullable();
            $table->string('uma_client_id', 150)->nullable();
            $table->text('uma_client_secret')->nullable();
            $table->text('uma_refresh_token')->nullable();
            $table->text('google_refresh_token')->nullable();

            // URLs and reminders
            $table->text('practice_api_url')->nullable();
            $table->string('sms_url', 150)->nullable();
            $table->string('reminder_interval', 50)->nullable();
            $table->string('openepic_client_id', 150)->nullable();
            $table->string('openepic_sandbox_client_id', 150)->nullable();

            // Branding / appointments
            $table->string('logo', 150)->nullable();
            $table->string('appointment_extension', 100)->nullable();
            $table->string('appointment_sent_date', 50)->nullable();
            $table->string('appointment_message', 150)->nullable();
            $table->string('appointment_interval', 50)->nullable();

            // General
            $table->string('timezone', 64)->default('utc');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement('ALTER TABLE hospitals ROW_FORMAT = DYNAMIC');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospitals');
    }
};

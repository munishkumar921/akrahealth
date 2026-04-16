<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $defaults = [
            'supported_languages' => json_encode(['en', 'ar', 'de', 'es'], JSON_UNESCAPED_UNICODE),
            'default_language' => 'en',
            'language_catalog' => json_encode([
                ['name' => 'English', 'code' => 'en', 'enabled' => true],
                ['name' => 'العربية', 'code' => 'ar', 'enabled' => true],
                ['name' => 'Deutsch', 'code' => 'de', 'enabled' => true],
                ['name' => 'Español', 'code' => 'es', 'enabled' => true],
            ], JSON_UNESCAPED_UNICODE),
        ];

        foreach ($defaults as $key => $value) {
            if (DB::table('settings')->where('key', $key)->exists()) {
                continue;
            }

            DB::table('settings')->insert([
                'id' => (string) Str::uuid(),
                'key' => $key,
                'value' => $value,
                'type' => 'string',
                'description' => ucfirst(str_replace('_', ' ', $key)),
                'group' => 'language',
                'is_encrypted' => false,
                'is_active' => true,
                'created_by' => null,
                'updated_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('group', 'language')
            ->whereIn('key', ['supported_languages', 'default_language', 'language_catalog'])
            ->delete();
    }
};

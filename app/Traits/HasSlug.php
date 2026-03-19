<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait HasSlug
{
    /**
     * bootHasSlug
     *
     * @return void
     */
    protected static function bootHasSlug()
    {
        static::creating(function ($model) {
            $model->slug = self::getSlug($model->table);
        });
    }

    /**
     * getSlug
     *
     * @param  string  $table
     */
    protected static function getSlug($table): string
    {
        $slug = strtoupper(Str::random(15));
        $row = DB::table($table)->where('slug', $slug)->first();

        if ($row) {
            self::getSlug($table);
        }

        return $slug;
    }
}

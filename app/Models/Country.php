<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'countries';

    protected $keyType = 'string';  // ✅ UUID is string

    public $incrementing = false;
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class icd9 extends Model
{
    /**
     * fill
     *
     * @var array
     */
    protected $fill = [
        'icd9_id',
        'icd9',
        'icd9_description',
        'icd9_common',
    ];
}

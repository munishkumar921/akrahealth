<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class icd9 extends Model
{
    use SoftDeletes;

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

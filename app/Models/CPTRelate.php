<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CPTRelate extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'hospital_id',
        'cpt',
        'cpt_description',
        'cpt_charge',
        'favorite',
        'unit',
    ];

    /**
     * hospital
     *
     * @return void
     */
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }
}

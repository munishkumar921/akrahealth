<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DemoRequest extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'demo_requests';

    protected $fillable = ['name', 'email', 'phone', 'project'];
}

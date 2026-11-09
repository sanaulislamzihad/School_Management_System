<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicSession extends Model
{
    public $timestamps = false;

    protected $table = 'academic_sessions';

    protected $guarded = ['id'];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherSubject extends Model
{
    public $timestamps = false;

    protected $table = 'teacher_subjects';

    protected $guarded = ['id'];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    public $timestamps = false;

    protected $table = 'shifts';

    protected $guarded = ['id'];
}

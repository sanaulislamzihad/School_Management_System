<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

#[Fillable(['name', 'login', 'email', 'password', 'role', 'active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    protected function casts(): array
    {
        return ['password' => 'hashed', 'active' => 'boolean'];
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    /** Dashboard route for this user's role. */
    public function homeRoute(): string
    {
        return ['admin' => 'admin.dashboard', 'teacher' => 'teacher.dashboard', 'student' => 'portal.dashboard'][$this->role];
    }
}

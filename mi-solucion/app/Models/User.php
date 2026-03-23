<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'role',
        'email',
        'phone',
        'password',
    ];

    public function courses()
    {
        return $this->belongsToMany(Course::class)->withPivot('current_lesson')->withTimestamps();
    }

    public function teacherSchedules()
    {
        return $this->hasMany(Schedule::class, 'teacher_id');
    }

    public function classRegistrations()
    {
        return $this->hasMany(ClassRegistration::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function isMaestro(): bool
    {
        return $this->role === 'maestro';
    }

    public function isAlumno(): bool
    {
        return $this->role === 'alumno';
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}

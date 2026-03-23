<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'classroom_id', 'course_id', 'teacher_id',
        'day_of_week', 'start_time', 'end_time', 'active',
    ];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function classRegistrations()
    {
        return $this->hasMany(ClassRegistration::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'class_registration_id', 'user_id', 'lesson_id',
        'schedule_id', 'class_date', 'attended', 'notes',
        'marked_via', 'marked_by', 'marked_at', 'marked_at_tz',
    ];

    protected function casts(): array
    {
        return [
            'class_date'  => 'date',
            'attended'    => 'boolean',
            'marked_by'   => 'integer',
            'marked_at'   => 'datetime',
            'marked_at_tz' => 'string',
        ];
    }

    public function classRegistration()
    {
        return $this->belongsTo(ClassRegistration::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}

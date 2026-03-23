<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassRegistration extends Model
{
    protected $fillable = ['schedule_id', 'user_id', 'class_date', 'status'];

    protected function casts(): array
    {
        return [
            'class_date' => 'date',
        ];
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->hasOne(Attendance::class);
    }
}

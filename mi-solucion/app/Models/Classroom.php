<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    protected $fillable = ['name', 'capacity', 'location'];

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}

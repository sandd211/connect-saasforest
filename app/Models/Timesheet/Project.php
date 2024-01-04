<?php

namespace App\Models\Timesheet;

use App\Models\Employee\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'team_id',
        'description',
        'start_date',
        'end_date',
        'status',
    ];
    public function users()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function teams()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($project) {
            if (Auth::check()) {
                $project->user_id = Auth::id();
            }
        });

       
    }
}

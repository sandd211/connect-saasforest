<?php

namespace App\Models\Timesheet;

use App\Models\User;
use Database\Seeders\Employee\UsersTableSeeder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'description',
        
    ];


    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function users()
    {
        return $this->belongsTo(User::class,'user_id');
    }
  
    public function timesheets()
    {
        return $this->hasMany(Timesheet::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($task) {
            if (Auth::check()) {
                $task->user_id = Auth::id();
            }
        });

       
    }
}

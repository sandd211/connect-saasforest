<?php

namespace App\Models\Offboarding;

use App\Models\User;
use App\Models\Offboarding\OffboardingList;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeOffboarding extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'offboarding_list_id',
        'comment',
    ];

    public function offboardingList()
    {
        return $this->belongsTo(OffboardingList::class, 'offboarding_list_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}

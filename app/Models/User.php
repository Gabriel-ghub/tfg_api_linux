<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Role;
use App\Models\Work;
use App\Models\Order;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'surname',
        'email',
        'password',
        'username',
        'role_id',
        'course_id'
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return ["user" => ["name" => $this->name, "role_id" => $this->role_id]];
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function works()
    {
        return $this->belongsToMany(Work::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }



    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}

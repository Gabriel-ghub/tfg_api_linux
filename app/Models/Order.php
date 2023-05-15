<?php

namespace App\Models;

use App\Models\Car;
use App\Models\User;
use App\Models\Work;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{


    protected $fillable = [
        'date_in',
        'date_out',
        'kilometres',
        'state',
        'total',
        'creator_user_id',
        'car_id',
        'name',
        'surname',
        'email',
        'phone'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function anomalies()
    {
        return $this->hasMany(Anomaly::class);
    }

    public function works()
    {
        return $this->hasMany(Work::class);
    }

    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'order_user', 'order_id', 'user_id')->withTimestamps();
    }

    public function materials()
    {
        return $this->hasMany(Material::class);
    }
}

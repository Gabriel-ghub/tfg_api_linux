<?php

namespace App\Models;

use App\Models\Car;
use App\Models\User;
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
        'user_id',
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
}

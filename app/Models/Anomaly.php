<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anomaly extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'order_id'
    ];


    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

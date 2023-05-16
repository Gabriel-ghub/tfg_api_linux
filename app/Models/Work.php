<?php

namespace App\Models;

use App\Models\User;
use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Work extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'order_id',
        'user_id'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

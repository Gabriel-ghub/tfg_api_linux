<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Work extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'hours',
        'state',
        'order_id'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}

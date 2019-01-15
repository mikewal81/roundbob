<?php

namespace app\Models;

use Illuminate\Database\Eloquent\Model;

class Booking_type extends Model
{
    protected $fillable = [
        'id',
        'title',
        'description',
        'disabled',
        'user_id'
    ];
}
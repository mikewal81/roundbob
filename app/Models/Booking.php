<?php

namespace app\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'id',
        'customer_id',
        'agent_id',
        'booking_type',
        'booking_information',
        'custom_request_id',
        'disabled',
        'booking_date',
    ];
}
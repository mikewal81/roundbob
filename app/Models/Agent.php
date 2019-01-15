<?php

namespace app\Models;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    protected $fillable = [
        'id',
        'wallet_id',
        'rating',
        'company_id',
        'verified',
        'disabled',
        'agent_type',
        'user_id'
    ];
}
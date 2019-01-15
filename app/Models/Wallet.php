<?php

namespace app\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
        'balance',
        'bonus_balance',
        'activated ',
        'disabled',
    ];
}
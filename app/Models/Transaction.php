<?php

namespace app\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions';
    
    protected $fillable = [
        'ref_no',
        'wallet_id',
        'user_id',
        'amount',
        'currency',
        'status',
        'tx_type',
        'meta',
        'ip_address',
        'disabled'
    ];
}
<?php

namespace app\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'user_id',
        'wallet_id',
        'company_name',
        'company_address',
        'company_city',
        'company_country_code_id',
        'company_website',
        'disabled',
    ];
}
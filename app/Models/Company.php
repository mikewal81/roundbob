<?php

namespace app\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'companies';
    protected $fillable = [
        'id',
        'name',
        'description',
        'address',
        'city',
        'country',
        'email_address',
        'phone_number',
        'phone_number_code',
        'logo_url',
        'website_url',
        'disabled'
    ];
}
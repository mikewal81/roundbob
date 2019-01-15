<?php

namespace app\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'users';
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'phone_number',
        'phone_number_code',
        'email_address',
        'dob',
        'gender',
        'profile_picture',
        'address',
        'city',
        'country',
        'verified',
        'disabled',
        'is_old',
        'user_type'
    ];
}
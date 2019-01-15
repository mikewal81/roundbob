<?php

namespace app\Models;

use Illuminate\Database\Eloquent\Model;

class Login extends Model
{
    protected $table = 'logins';

    protected $fillable = [
        'email_address',
        'phone_number',
        'password',
        'user_id',
    ];

    public function generatePassword($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()-+';
        $charactersLength = strlen($characters);
        $randomString = '';
        for($i = 0; $i < $length; $i++)
        {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function hashed_pwd($password)
    {
        $pwd = password_hash($password, PASSWORD_DEFAULT);

        return $pwd;
    }
}
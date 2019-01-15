<?php

namespace app\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $fillable = [
        'id',
        'user_id',
        'role',
        'user_id'
    ];
}
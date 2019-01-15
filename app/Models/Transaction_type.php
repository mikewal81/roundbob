<?php

namespace app\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction_type extends Model
{
    protected $fillable = [
        'name',
        'type',
        'description',
        'disabled',
    ];
}
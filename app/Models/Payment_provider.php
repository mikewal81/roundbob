<?php

namespace app\Models;

use Illuminate\Database\Eloquent\Model;

class Payment_provider extends Model
{
    protected $fillable = [
        'name',
        'description',
        'disabled'
    ];
}
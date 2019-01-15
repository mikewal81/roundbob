<?php

namespace app\Models;

use Illuminate\Database\Eloquent\Model;

class Package_category extends Model
{
    protected $table = 'package_categories';
    protected $fillable = [
        'id',
        'name',
        'description',
        'user_id',
        'disabled',
    ];
}
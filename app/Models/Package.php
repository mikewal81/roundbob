<?php

namespace app\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'id',
        'name',
        'agent_id',
        'description',
        'valid_from',
        'valid_to',
        'images',
        'display_image',
        'category_id',
        'location_country',
        'location_city',
        'price',
        'disabled'
    ];
}
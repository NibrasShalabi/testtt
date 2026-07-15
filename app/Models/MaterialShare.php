<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialShare extends Model
{
    protected $fillable = ['resource_id', 'shared_with_email', 'access_level'];
}

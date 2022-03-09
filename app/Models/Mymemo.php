<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mymemo extends Model
{
    use HasFactory;
    protected $fillable = [
        'mymemo',
        'mymemotitle',
    ];
}

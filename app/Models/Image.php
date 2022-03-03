<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{

    protected $fillable = [
        'filename',
        'url',
        'free_boards_id'
    ];

    use HasFactory;
}

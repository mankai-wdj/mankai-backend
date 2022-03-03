<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FreeBoardImage extends Model
{

    protected $fillable = [
        'url',
        'free_boards_id'
    ];

    use HasFactory;

}

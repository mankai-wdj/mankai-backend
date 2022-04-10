<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FreeBoard extends Model
{

    protected $fillable = [
        'memo_title',
        'user_id',
        'content_text',
    ];


    use HasFactory;
}

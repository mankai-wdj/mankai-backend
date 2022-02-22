<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Noti extends Model
{
    use HasFactory;


    protected $table = 'Noti';
    protected $fillable = [
        'noti_title',
        'noti_message',
        'noti_link',
        'read'
    ];

}

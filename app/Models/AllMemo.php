<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllMemo extends Model
{
    use HasFactory;

    protected $fillable = [
        'memo_title',
        'user_id',
        'content_text',
    ];

    public function users()
    {
        return $this->belongsTo(User::class);
    }
}

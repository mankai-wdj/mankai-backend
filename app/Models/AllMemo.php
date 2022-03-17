<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllMemo extends Model
{
    use HasFactory;

    protected $fillable = [
        'memo_user_id',
        'memo_title',
        'content_text',
    ];

    public function users() {
        return $this->belongsTo(User::class);
    }
}

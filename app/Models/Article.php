<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasUuids, HasFactory;
    
    protected $fillable = [
        'type',
        'source',
        'author',
        'title',
        'description',
        'url',
        'urlToImage',
        'content',
        'publishedAt',
        'category',
        'platform',
    ];

    protected $casts = [
        'publishedAt' => 'datetime',
    ];
}

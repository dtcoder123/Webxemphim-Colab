<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    protected $table = 'movies';

    protected $fillable = [
        'title',
        'genre',
        'rating',
        'year',
        'duration',
        'poster',
        'tagline',
        'description',
        'director',
        'cast',
        'video_url',
        'trailer_url',
        'featured',
        'status',
    ];

    protected $casts = [
        'cast' => 'array',
        'rating' => 'float',
        'featured' => 'boolean',
        'status' => 'boolean',
    ];
}
?>
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovieComment extends Model
{
    protected $table = 'movie_comments';

    const UPDATED_AT = null;

    protected $fillable = [
        'movie_id',
        'user_id',
        'rating',
        'comment',
    ];

    public function movie()
    {
        return $this->belongsTo(Movie::class, 'movie_id');
    }
}
?>
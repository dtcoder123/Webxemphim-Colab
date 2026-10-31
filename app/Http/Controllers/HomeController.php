<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Genre;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $genres = collect(['Tất cả'])
            ->merge(Genre::orderBy('name')->pluck('name'))
            ->unique()
            ->values();

        $activeGenre = trim($request->query('genre', 'Tất cả'));

        if (!$genres->contains($activeGenre)) {
            $activeGenre = 'Tất cả';
        }

        $movies = Movie::where('status', 1)
            ->orderByDesc('id')
            ->get();

        $featured = $movies->firstWhere('featured', 1);

        if (!$featured) {
            $featured = $movies->sortBy('id')->first();
        }

        $movieShelves = $movies->groupBy(function ($movie) {
            return trim((string) $movie->genre) ?: 'Khác';
        });

        $featuredRailMovies = $movies->take(5)->values();

        if ($featured && !$featuredRailMovies->contains('id', $featured->id)) {
            if ($featuredRailMovies->count() >= 5) {
                $featuredRailMovies->pop();
            }

            $featuredRailMovies->push($featured);
        }

        $movieCatalog = $movies->map(function ($movie) {
            $cast = $movie->cast;

            if (is_string($cast)) {
                $cast = json_decode($cast, true) ?: [];
            }

            if (!is_array($cast)) {
                $cast = [];
            }

            return [
                'id' => (int) $movie->id,
                'title' => (string) $movie->title,
                'genre' => (string) $movie->genre,
                'tagline' => (string) $movie->tagline,
                'poster' => (string) $movie->poster,
                'keywords' => mb_strtolower(
                    $movie->title . ' ' .
                    $movie->genre . ' ' .
                    $movie->tagline . ' ' .
                    implode(' ', $cast)
                ),
                'watch_url' => route('movies.show', $movie->id),
            ];
        })->values();

        return view('home', compact(
            'genres',
            'activeGenre',
            'featured',
            'movies',
            'movieShelves',
            'featuredRailMovies',
            'movieCatalog'
        ));
    }
}

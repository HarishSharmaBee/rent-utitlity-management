<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Movie\Models\Movie;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $movies = Movie::where('is_live',1)->paginate(8);
        if ($request->ajax()) {
            return view('_partials.movie_list', compact('movies'))->render();
        }
        return view('frontend.opportunities',['movies' => $movies]);
    }

    public function movieFunding(Request $request)
    {
        $movie = Movie::with('movieCasts', 'movieCrews', 'documents', 'videos', 'budget')
        ->where('id', $request->movie_id)
        ->where('is_live', 1)
        ->firstOrFail();
        return view('frontend.movie_funding',compact('movie'));
    }

    public function movieProject(Request $request)
    {
        try {
            $movie = Movie::with('movieCasts', 'movieCrews', 'documents', 'videos', 'budget')
                ->where('id', $request->movie_id)
                ->where('is_live', 1)
                ->firstOrFail();
            $stages = Movie::stages();

        }catch (ModelNotFoundException $e) {
            abort(404, 'Movie not found or not live');
        }
        return view('frontend.movie_project',['movie' => $movie,'stages' =>$stages]);
    }

    public function distributionStrategy(Request $request)
    {
        $movie = Movie::with('movieCasts', 'movieCrews', 'documents', 'videos', 'budget')
        ->where('id', $request->movie_id)
        ->where('is_live', 1)
        ->firstOrFail();
        return view('frontend.distribution_strategy',compact('movie'));
    }
}

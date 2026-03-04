<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MovieApiController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $genre = trim((string) $request->query('genre', ''));

        $rows = DB::table('filme')
            ->leftJoin('film_actor', 'filme.id_film', '=', 'film_actor.id_film')
            ->leftJoin('actori', 'film_actor.id_actor', '=', 'actori.id_actor')
            ->when($q !== '', function ($query) use ($q) {
                $like = '%' . $q . '%';
                $query->where(function ($w) use ($like) {
                    $w->where('filme.titlu', 'like', $like)
                      ->orWhere('actori.nume', 'like', $like)
                      ->orWhere('actori.prenume', 'like', $like)
                      ->orWhereRaw("concat(actori.prenume,' ',actori.nume) like ?", [$like])
                      ->orWhereRaw("concat(actori.nume,' ',actori.prenume) like ?", [$like]);
                });
            })
            ->when($genre !== '', fn($query) => $query->where('filme.gen', $genre))
            ->select([
                'filme.id_film',
                'filme.titlu',
                'filme.durata',
                'filme.gen',
                'filme.data_lansare',
                'filme.poster_path', 
            ])
            ->distinct()
            ->orderBy('filme.titlu')
            ->get();

        // transformăm poster_path -> poster_url public
        $rows->transform(function ($m) {
            $m->poster_url = $m->poster_path ? asset('storage/' . $m->poster_path) : null;
            return $m;
        });

        return response()->json(['movies' => $rows], 200);
    }
}

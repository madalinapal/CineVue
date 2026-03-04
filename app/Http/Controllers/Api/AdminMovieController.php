<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminMovieController extends Controller
{
    
    public function index()
    {
        $movies = DB::table('filme')->orderBy('id_film', 'desc')->get();
        return response()->json(['movies' => $movies]);
    }

    // util pentru dropdown în Admin UI
    public function sali()
    {
        $sali = DB::table('sali')
            ->orderBy('nume')
            ->select(['id_sala', 'nume', 'capacitate'])
            ->get();

        return response()->json(['sali' => $sali], 200);
    }

    // GET /api/admin/movies/{id}/projections (pentru edit)
    public function projections($id)
    {
        $rows = DB::table('proiectii')
            ->where('id_film', $id)
            ->orderBy('data_proiectie')
            ->orderBy('ora_proiectie')
            ->get();

        return response()->json(['projections' => $rows], 200);
    }



        public function store(Request $request)
        {
            $v = $request->validate([
                'titlu' => 'required|string|max:100',
                'durata' => 'required|integer|min:1',
                'gen' => 'required|string|max:50',
                'data_lansare' => 'required|date',
                'director' => 'required|string|max:100',
                'regizor' => 'required|string|max:100',
                'poster' => 'required|file|mimes:jpg,jpeg,png,webp,avif,jfif|max:4096',
                'projections' => 'nullable|string',
            ]);

            $path = $request->file('poster')->store('postere', 'public');

            if (!$path) {
                return response()->json([
                    'message' => 'Poster upload failed (path empty). Verifica permisiuni / disk public.',
                ], 500);
            }

            $id = DB::table('filme')->insertGetId([
                'titlu' => $v['titlu'],
                'durata' => $v['durata'],
                'gen' => $v['gen'],
                'data_lansare' => $v['data_lansare'],
                'director' => $v['director'],
                'regizor' => $v['regizor'],
                'poster_path' => $path,
            ]);

            return response()->json([
                'message' => 'Movie created',
                'id_film' => $id,
                'poster_url' => asset('storage/' . $path),
            ], 201);
        }

        public function update(Request $request, $id)
        {
            $v = $request->validate([
                'titlu' => 'required|string|max:100',
                'durata' => 'required|integer|min:1',
                'gen' => 'required|string|max:50',
                'data_lansare' => 'required|date',
                'director' => 'required|string|max:100',
                'regizor' => 'required|string|max:100',

                // poster optional la edit
                'poster' => 'nullable|file|mimes:jpg,jpeg,png,webp,avif,jfif|max:4096',

                // IMPORTANT: vine ca string în multipart
                'projections' => 'nullable|string',
            ]);

            $projections = [];
            if ($request->filled('projections')) {
                $projections = json_decode($request->input('projections'), true) ?? [];
            }

            // validează structura proiecțiilor
            Validator::make(['projections' => $projections], [
                'projections' => 'array',
                'projections.*.id_sala' => 'required|integer|exists:sali,id_sala',
                'projections.*.data' => 'required|date',
                'projections.*.ora' => 'required|date_format:H:i',
            ])->validate();

            $update = [
                'titlu' => $v['titlu'],
                'durata' => $v['durata'],
                'gen' => $v['gen'],
                'data_lansare' => $v['data_lansare'],
                'director' => $v['director'],
                'regizor' => $v['regizor'],
            ];

            if ($request->hasFile('poster')) {
                $old = DB::table('filme')->where('id_film', $id)->value('poster_path');
                if ($old) Storage::disk('public')->delete($old);

                $path = $request->file('poster')->store('postere', 'public');
                $update['poster_path'] = $path;
            }

            DB::table('filme')->where('id_film', $id)->update($update);

            // aici actualizezi proiecțiile: ștergi + reinserare
            DB::table('proiectii')->where('id_film', $id)->delete();
            foreach ($projections as $p) {
                DB::table('proiectii')->insert([
                    'id_film' => $id,
                    'id_sala' => $p['id_sala'],
                    'data_proiectie' => $p['data'],
                    'ora_proiectie' => $p['ora'],
                ]);
            }

            return response()->json(['message' => 'Movie updated']);
        }


            public function destroy($id)
{
    DB::transaction(function () use ($id) {

        // ia toate proiecțiile filmului
        $projIds = DB::table('proiectii')
            ->where('id_film', $id)
            ->pluck('id_proiectie');

        // șterge biletele pentru proiecțiile filmului
        if ($projIds->count() > 0) {
            DB::table('bilete')->whereIn('id_proiectie', $projIds)->delete();
        }

        // șterge proiecțiile
        DB::table('proiectii')->where('id_film', $id)->delete();

        // șterge legăturile film-actor (dacă ai)
        DB::table('film_actor')->where('id_film', $id)->delete();

        // (opțional) șterge posterul din storage, cum aveai deja
        $old = DB::table('filme')->where('id_film', $id)->value('poster_path');
        if ($old) \Storage::disk('public')->delete($old);

        // șterge filmul
        DB::table('filme')->where('id_film', $id)->delete();
    });

    return response()->json(['message' => 'Movie deleted']);
}


}

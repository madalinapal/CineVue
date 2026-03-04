<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminProjectionController extends Controller
{
    public function index()
    {
        $rows = DB::table('proiectii as p')
            ->join('filme as f', 'f.id_film', '=', 'p.id_film')
            ->join('sali as s', 's.id_sala', '=', 'p.id_sala')
            ->orderByDesc('p.data_proiectie')
            ->orderByDesc('p.ora_proiectie')
            ->select([
                'p.id_proiectie','p.id_film','p.id_sala','p.data_proiectie','p.ora_proiectie',
                'f.titlu as film','s.nume as sala'
            ])
            ->get();

        return response()->json(['projections' => $rows]);
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'id_film' => 'required|integer|exists:filme,id_film',
            'id_sala' => 'required|integer|exists:sali,id_sala',
            'data_proiectie' => 'required|date',
            'ora_proiectie' => 'required', // time
        ]);

        $id = DB::table('proiectii')->insertGetId($v);

        return response()->json(['message' => 'Projection created', 'id_proiectie' => $id], 201);
    }

    public function update(Request $request, $id)
    {
        $v = $request->validate([
            'id_film' => 'required|integer|exists:filme,id_film',
            'id_sala' => 'required|integer|exists:sali,id_sala',
            'data_proiectie' => 'required|date',
            'ora_proiectie' => 'required',
        ]);

        DB::table('proiectii')->where('id_proiectie', $id)->update($v);

        return response()->json(['message' => 'Projection updated']);
    }

    public function destroy($id)
    {
        DB::table('proiectii')->where('id_proiectie', $id)->delete();
        return response()->json(['message' => 'Projection deleted']);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProjectionApiController extends Controller
{
    // GET /api/projections?date=YYYY-MM-DD
    public function index(Request $request)
{
    $date  = $request->query('date');   // pentru "programul de azi"
    $movie = trim((string) $request->query('movie', '')); // pentru BuyTicket

    $query = DB::table('proiectii as p')
        ->join('filme as f', 'f.id_film', '=', 'p.id_film')
        ->join('sali as s', 's.id_sala', '=', 'p.id_sala')
        ->leftJoin('bilete as b', 'b.id_proiectie', '=', 'p.id_proiectie')
        ->selectRaw("
            p.id_proiectie,
            p.data_proiectie,
            p.ora_proiectie,
            f.id_film,
            f.titlu,
            s.id_sala,
            s.nume as sala,
            s.capacitate,
            COUNT(b.id_bilet) as ocupate,
            (s.capacitate - COUNT(b.id_bilet)) as libere
        ")
        ->groupBy(
            'p.id_proiectie','p.data_proiectie','p.ora_proiectie',
            'f.id_film','f.titlu',
            's.id_sala','s.nume','s.capacitate'
        );

    // dacă vine date -> filtrezi strict pe ziua aia (homepage)
    if ($date) {
        $day = Carbon::parse($date)->toDateString();
        $query->whereDate('p.data_proiectie', $day);
    }

    // dacă vine movie -> filtrezi pe film (BuyTicket)
    if ($movie !== '') {
        $query->where('f.titlu', $movie);
    }

    $rows = $query
        ->orderBy('p.data_proiectie')
        ->orderBy('p.ora_proiectie')
        ->get();

    return response()->json([
        'projections' => $rows,
    ], 200);
}

public function occupiedSeats($id)
{
    // lista locurilor ocupate (loc_sala) pentru proiecția respectivă
    $seats = DB::table('bilete')
        ->where('id_proiectie', $id)
        ->orderBy('loc_sala')
        ->pluck('loc_sala')
        ->values();

    return response()->json([
        'id_proiectie' => (int) $id,
        'occupied_seats' => $seats,  // ex: [1,2,5,17]
    ], 200);
}



}

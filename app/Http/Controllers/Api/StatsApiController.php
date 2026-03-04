<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class StatsApiController extends Controller
{
    // GET /api/stats/top-movies
    public function topMovies()
    {
        $rows = DB::select("
            WITH per_tip AS (
                SELECT
                    f.id_film,
                    f.titlu,
                    COALESCE(c.tip, 'Niciun tip') AS tip,
                    COUNT(*) AS cnt
                FROM filme f
                JOIN proiectii p ON p.id_film = f.id_film
                JOIN bilete b ON b.id_proiectie = p.id_proiectie
                JOIN clienti c ON c.id_client = b.id_client
                GROUP BY f.id_film, f.titlu, tip
            ),
            totals AS (
                SELECT id_film, titlu, SUM(cnt) AS total_bilete
                FROM per_tip
                GROUP BY id_film, titlu
            ),
            ranked AS (
                SELECT
                    pt.id_film,
                    pt.titlu,
                    t.total_bilete,
                    pt.tip,
                    pt.cnt,
                    ROW_NUMBER() OVER (
                        PARTITION BY pt.id_film
                        ORDER BY
                            pt.cnt DESC,
                            FIELD(pt.tip, 'pensionar', 'student', 'elev', 'Niciun tip') ASC,
                            pt.tip ASC
                    ) AS rn
                FROM per_tip pt
                JOIN totals t ON t.id_film = pt.id_film
            )
            SELECT
                id_film,
                titlu,
                total_bilete,
                tip AS categorie_majoritara,
                cnt AS bilete_in_categoria_majoritara
            FROM ranked
            WHERE rn = 1
            ORDER BY total_bilete DESC
            LIMIT 5
        ");

        return response()->json(['top_movies' => $rows], 200);
    }
    public function ticketsByClientType()
    {
        $rows = DB::table('bilete as b')
            ->join('clienti as c', 'c.id_client', '=', 'b.id_client')
            ->selectRaw("COALESCE(NULLIF(TRIM(c.tip), ''), 'Niciun tip') AS tip, COUNT(*) AS total_bilete")
            ->groupBy('tip')
            ->orderByDesc('total_bilete')
            ->get();

        $total = (int) $rows->sum('total_bilete');

        $data = $rows->map(function ($r) use ($total) {
            $count = (int) $r->total_bilete;
            return [
                'tip' => $r->tip,
                'total_bilete' => $count,
                'procent' => $total > 0 ? round(($count / $total) * 100, 1) : 0,
            ];
        })->values();

        return response()->json([
            'total_bilete' => $total,
            'by_type' => $data,
        ], 200);
    }
public function soldOutMovies(Request $request)
{
    $from = $request->query('from');
    $to   = $request->query('to');

    $fromDate = $from ? Carbon::parse($from)->toDateString() : Carbon::now()->subDays(30)->toDateString();
    $toDate   = $to   ? Carbon::parse($to)->toDateString()   : Carbon::now()->toDateString();

    $rows = DB::select("
        SELECT
          f.id_film,
          f.titlu,
          SUM(CASE WHEN x.vandute >= x.capacitate THEN 1 ELSE 0 END) AS nr_proiectii_soldout,
          COUNT(*) AS total_proiectii_in_perioada
        FROM (
          SELECT
            p.id_proiectie,
            p.id_film,
            s.capacitate,
            (SELECT COUNT(*) FROM bilete b WHERE b.id_proiectie = p.id_proiectie) AS vandute
          FROM proiectii p
          JOIN sali s ON s.id_sala = p.id_sala
          WHERE p.data_proiectie BETWEEN ? AND ?
            AND s.capacitate IS NOT NULL
            AND s.capacitate > 0
        ) x
        JOIN filme f ON f.id_film = x.id_film
        GROUP BY f.id_film, f.titlu
        HAVING SUM(CASE WHEN x.vandute >= x.capacitate THEN 1 ELSE 0 END) > 0
        ORDER BY nr_proiectii_soldout DESC, total_proiectii_in_perioada DESC, f.titlu
        LIMIT 10
    ", [$fromDate, $toDate]);

    return response()->json([
        'from' => $fromDate,
        'to' => $toDate,
        'soldout_movies' => $rows
    ], 200);
}

    
}

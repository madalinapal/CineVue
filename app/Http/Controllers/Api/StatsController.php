<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatsController extends Controller
{
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

    //  GET /api/stats/top-halls-occupancy?start=YYYY-MM-DD&end=YYYY-MM-DD
    public function topHallsOccupancy(Request $request)
    {
        $request->validate([
            'start' => 'nullable|date',
            'end'   => 'nullable|date',
        ]);

        // default: luna curentă
        $start = $request->query('start')
            ? Carbon::parse($request->query('start'))->toDateString()
            : Carbon::now()->startOfMonth()->toDateString();

        $end = $request->query('end')
            ? Carbon::parse($request->query('end'))->toDateString()
            : Carbon::now()->endOfMonth()->toDateString();

        // IMPORTANT: locuri_total = nr_proiecții * capacitate (fără să “explodeze” la join cu bilete)
        $rows = DB::select("
            SELECT
                s.id_sala,
                s.nume,
                s.capacitate,
                x.nr_proiectii,
                x.vandute,
                x.locuri_total,
                ROUND(100 * x.vandute / NULLIF(x.locuri_total, 0), 1) AS ocupare_proc
            FROM sali s
            JOIN (
                SELECT
                    p.id_sala,
                    COUNT(DISTINCT p.id_proiectie) AS nr_proiectii,
                    COUNT(b.id_bilet) AS vandute,
                    COUNT(DISTINCT p.id_proiectie) * MAX(s2.capacitate) AS locuri_total
                FROM proiectii p
                JOIN sali s2 ON s2.id_sala = p.id_sala
                LEFT JOIN bilete b ON b.id_proiectie = p.id_proiectie
                WHERE p.data_proiectie BETWEEN ? AND ?
                GROUP BY p.id_sala
            ) x ON x.id_sala = s.id_sala
            ORDER BY ocupare_proc DESC, vandute DESC
            LIMIT 3
        ", [$start, $end]);

        return response()->json([
            'start' => $start,
            'end' => $end,
            'top_halls' => $rows,
        ], 200);
    }

    /**
     * NOU: Top 5 utilizatori după nr bilete și încasări (cu subquery)
     * GET /api/stats/top-users?from=YYYY-MM-DD&to=YYYY-MM-DD
     *
     * NOTE:
     * - dacă ai coloană b.pret -> folosim SUM(b.pret)
     * - altfel -> calculăm prețul cu CASE pe tip (modifici valorile după regulile tale)
     */

    
public function topUsers(Request $request)
{
    $from = $request->query('from');
    $to   = $request->query('to');

    $fromDate = $from ? Carbon::parse($from)->toDateString() : Carbon::now()->subDays(30)->toDateString();
    $toDate   = $to   ? Carbon::parse($to)->toDateString()   : Carbon::now()->toDateString();

    // ai coloană pret în bilete?
    $hasPret = DB::getSchemaBuilder()->hasColumn('bilete', 'pret');

    // expresie de încasări
    if ($hasPret) {
        $incasariExpr = "IFNULL(SUM(b.pret), 0)";
        $pretMode = 'bilete.pret';
    } else {
        $incasariExpr = "IFNULL(SUM(
            CASE
                WHEN c.tip = 'student' THEN 15
                WHEN c.tip = 'elev' THEN 12
                WHEN c.tip = 'pensionar' THEN 10
                ELSE 20
            END
        ), 0)";
        $pretMode = 'CASE tip';
    }

    // IMPORTANT: filtrăm după data cumpărării
    // dacă nu ai data_cumparare, spune-mi și schimb pe p.data_proiectie
    $rows = DB::table('users as u')
        ->join('clienti as c', 'c.user_id', '=', 'u.id')
        ->join('bilete as b', 'b.id_client', '=', 'c.id_client')
        ->whereBetween(DB::raw('DATE(b.data_cumparare)'), [$fromDate, $toDate])
        ->groupBy('u.id', 'u.username', 'u.email')
        ->selectRaw("
            u.id as user_id,
            u.username,
            u.email,
            COUNT(b.id_bilet) as total_bilete,
            ROUND($incasariExpr, 2) as incasari
        ")
        ->orderByDesc('total_bilete')
        ->orderByDesc('incasari')
        ->limit(5)
        ->get();

    return response()->json([
        'from' => $fromDate,
        'to' => $toDate,
        'top_users' => $rows,
        'pret_mode' => $pretMode,
    ], 200);
}

    /**
     * ✅ NOU: Proiecții cu ocupare > 80% într-o perioadă
     * GET /api/stats/projections-high-occupancy?from=YYYY-MM-DD&to=YYYY-MM-DD&min=80
     */
    public function projectionsHighOccupancy(Request $request)
    {
        $min = (int) $request->query('min', 80); // procent (ex 80)
        if ($min < 0) $min = 0;
        if ($min > 100) $min = 100;

        // perioadă default: ultimele 30 zile
        $from = $request->query('from');
        $to   = $request->query('to');

        $fromDate = $from ? Carbon::parse($from)->toDateString() : Carbon::now()->subDays(30)->toDateString();
        $toDate   = $to ? Carbon::parse($to)->toDateString() : Carbon::now()->toDateString();

        // subquery COUNT(*) bilete + procent ocupare
        // folosim HAVING cu alias (ok în MySQL)
        $rows = DB::select("
            SELECT
                p.id_proiectie,
                f.titlu,
                s.nume AS sala,
                s.capacitate,
                p.data_proiectie,
                p.ora_proiectie,

                (SELECT COUNT(*) FROM bilete b WHERE b.id_proiectie = p.id_proiectie) AS bilete_vandute,

                ROUND(
                    (
                        (SELECT COUNT(*) FROM bilete b WHERE b.id_proiectie = p.id_proiectie)
                        / NULLIF(s.capacitate, 0)
                    ) * 100, 2
                ) AS ocupare_proc

            FROM proiectii p
            JOIN filme f ON f.id_film = p.id_film
            JOIN sali s ON s.id_sala = p.id_sala

            WHERE p.data_proiectie BETWEEN ? AND ?

            HAVING ocupare_proc > ?

            ORDER BY ocupare_proc DESC, bilete_vandute DESC
            LIMIT 10
        ", [$fromDate, $toDate, $min]);

        return response()->json([
            'from' => $fromDate,
            'to' => $toDate,
            'min' => $min,
            'projections' => $rows
        ], 200);
    }

}




<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;

use App\Models\Bilet;
use App\Models\Client;

class TicketApiController extends Controller
{
    // POST /api/tickets
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_proiectie' => 'required|integer|exists:proiectii,id_proiectie',
            'loc_sala'     => 'required|string|max:10',
            'pret'         => 'required|numeric|min:0',
        ]);

        $userId = Auth::id(); // user curent (trebuie să fie logat)
        if (!$userId) {
            return response()->json(['message' => 'Neautentificat.'], 401);
        }

        $client = Client::where('user_id', $userId)->first();

        if (!$client) {
            return response()->json([
                'message' => 'Nu ai date de client completate în cont.'
            ], 422);
        }

        // REGULA 1: nu cumperi de 2 ori la aceeași proiecție (pentru același client)
        $already = Bilet::where('id_client', $client->id_client)
            ->where('id_proiectie', $validated['id_proiectie'])
            ->exists();

        if ($already) {
            return response()->json([
                'message' => 'Ai cumpărat deja bilet pentru această proiecție.'
            ], 409);
        }

        // REGULA 2: locul nu poate fi luat deja la aceeași proiecție (GLOBAL)
        $seatTaken = Bilet::where('id_proiectie', $validated['id_proiectie'])
            ->where('loc_sala', $validated['loc_sala'])
            ->exists();

        if ($seatTaken) {
            return response()->json([
                'message' => 'Locul este deja ocupat pentru această proiecție.'
            ], 409);
        }

        try {
            $bilet = new Bilet();
            $bilet->id_client = $client->id_client;
            $bilet->id_proiectie = $validated['id_proiectie'];
            $bilet->loc_sala = $validated['loc_sala'];
            $bilet->pret = $validated['pret'];
            $bilet->data_cumparare = now();
            $bilet->save();
        } catch (QueryException $e) {
            // dacă ai UNIQUE(id_proiectie, loc_sala), MySQL aruncă 1062 la conflict
            if (isset($e->errorInfo[1]) && (int)$e->errorInfo[1] === 1062) {
                return response()->json([
                    'message' => 'Locul este deja ocupat pentru această proiecție.'
                ], 409);
            }

            // altă eroare DB
            return response()->json([
                'message' => 'Eroare la salvarea biletului.',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Bilet cumpărat',
            'bilet' => $bilet
        ], 201);
    }
}

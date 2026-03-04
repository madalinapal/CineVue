<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bilet;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Client;

class AccountApiController extends Controller
{
    // GET /api/account/{id}
    public function show($id)
{
    $user = User::findOrFail($id);
    $client = Client::where('user_id', $id)->first();

    $tickets = [];
    if ($client) {

        // cu detalii despre proiecție + sală + film (JOIN)
        $tickets = DB::table('bilete as b')
            ->join('proiectii as p', 'p.id_proiectie', '=', 'b.id_proiectie')
            ->join('sali as s', 's.id_sala', '=', 'p.id_sala')
            ->join('filme as f', 'f.id_film', '=', 'p.id_film')
            ->where('b.id_client', $client->id_client)
            ->orderByDesc('b.data_cumparare')
            ->select([
                'b.id_bilet',
                'b.loc_sala',
                'b.pret',
                'b.data_cumparare',
                'p.id_proiectie',
                'p.data_proiectie',
                'p.ora_proiectie',
                's.nume as sala',
                'f.titlu as film',
            ])
            ->get();
    }

    return response()->json([
        'user' => $user,
        'client' => $client,
        'tickets' => $tickets,
    ], 200);
}


    // PUT /api/account/{id}
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nume' => 'required|string|max:50',
            'prenume' => 'required|string|max:50',
            'data_nasterii' => 'nullable|date',
            'nr_telefon' => 'nullable|string|max:15',
            'email' => 'required|email|max:100',
            'tip' => 'nullable|in:elev,student,pensionar',
        ]);

        $client = Client::where('user_id', $id)->first();

        if (!$client) {
            $client = new Client();
            $client->user_id = $id;
        }

        $client->nume = $validated['nume'];
        $client->prenume = $validated['prenume'];
        $client->data_nasterii = $validated['data_nasterii'] ?? null;
        $client->nr_telefon = $validated['nr_telefon'] ?? null;
        $client->email = $validated['email'];
        $client->tip = $validated['tip'] ?? null;

        $client->save();

        return response()->json(['message' => 'Account updated'], 200);
    }
}

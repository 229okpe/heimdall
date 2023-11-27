<?php

namespace App\Http\Controllers;

use App\Models\abonnements;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class AbonnementsController extends Controller
{
    public function index(Request $request){


        $abonnements = abonnements::with('produits:id,nom')
        ->orderBy('id', 'desc')
        ->get();

        return response()->json(['abonnements' => $abonnements], 200);
    }

    public function store(Request $request)
    { 

        $validator = Validator::make($request->all(), [
            'details' => 'required',
            'produit_id' => 'nullable|exists:produits,id',
            'nomClient' => 'required|string',
            'emailClient' => 'required|email|email:rfc,dns',
            'dateExpiration' => 'required|date',
           
        ]);
        
            if ($validator->fails()) {
            return response(['errors' => $validator->errors(), ], 422); 
        } 

        // Création d'un nouvel abonnement
        $abonnement = new abonnements();
        $abonnement->details = $request->input('details');
        $abonnement->produit_id = $request->input('produit_id');
        $abonnement->nomClient = $request->input('nomClient');
        $abonnement->emailClient = $request->input('emailClient');
        $abonnement->dateExpiration = $request->input('dateExpiration');
      
        
        // Sauvegarde de l'abonnement
        $abonnement->save();

             return response()->json(['abonnement' => $abonnement], 200);
   
    }

}

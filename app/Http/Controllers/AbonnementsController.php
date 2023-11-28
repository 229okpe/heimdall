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

        foreach ($abonnements as $abonnement) {
            $dateExpiration = $abonnement->dateExpiration;
        
            // Comparer la date d'expiration avec la date actuelle
            $maintenant = now();
            if ($dateExpiration < $maintenant) {
                $abonnement->statut = 'Expiré';
            } else {
                $abonnement->statut = 'Actif';
            }
        }

        return response()->json(['abonnements' => $abonnements], 200);
    }

    public function show($id)
    {
        // Récupérer un abonnement spécifique par son ID avec la relation 'produits'
        $abonnement = Abonnements::with('produits:id,nom')->find($id);
    
        // Vérifier si l'abonnement existe
        if (!$abonnement) {
            return response()->json(['message' => 'Abonnement non trouvé'], 404);
        }
    
        // Comparer la date d'expiration avec la date actuelle
        $dateExpiration = $abonnement->dateExpiration;
        $maintenant = now();
    
        if ($dateExpiration < $maintenant) {
            $abonnement->statut = 'Expiré';
        } else {
            $abonnement->statut = 'Actif';
        }
    
        return response()->json(['abonnement' => $abonnement], 200);
    }
    

    public function store(Request $request)
    { 

        $validator = Validator::make($request->all(), [
            'details' => 'required',
            'produit_id' => 'nullable|exists:produits,id',
            'dateExpiration' => 'required|date',
           
        ]);
        
            if ($validator->fails()) {
            return response(['errors' => $validator->errors(), ], 422); 
        } 

        // Création d'un nouvel abonnement
        $abonnement = new abonnements();
        $abonnement->details = $request->input('details');
        $abonnement->produit_id = $request->input('produit_id'); 
        $abonnement->dateExpiration = $request->input('dateExpiration');
      
        
        // Sauvegarde de l'abonnement
        $abonnement->save();

             return response()->json(['abonnement' => $abonnement], 200);
   
    }


    public function update(Request $request, $id)
    {
        // Validation des données de la requête
        $validator = Validator::make($request->all(), [
            'details' => 'required',
            'produit_id' => 'nullable|exists:produits,id', 
            'dateExpiration' => 'required|date',
        ]);
    
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()], 422);
        }
    
        // Recherche de l'abonnement à mettre à jour
        $abonnement = Abonnements::find($id);
    
        if (!$abonnement) {
            return response()->json(['error' => 'Abonnement non trouvé'], 404);
        }
    
        // Mise à jour des données de l'abonnement
        $abonnement->details = $request->input('details');
        $abonnement->produit_id = $request->input('produit_id'); 
        $abonnement->dateExpiration = $request->input('dateExpiration');
    
        // Sauvegarde des modifications
        $abonnement->save();
    
        return response()->json(['abonnement' => $abonnement], 200);
    }

    public function affecterAbonnement(Request $request, $id){

        // Validation des données de la requête
        $validator = Validator::make($request->all(), [
            'nomClient' => 'required',
            'emailClient' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()], 422);
        }
    
        // Recherche de l'abonnement à mettre à jour
        $abonnement = Abonnements::find($id);
    
        if (!$abonnement) {
            return response()->json(['error' => 'Abonnement non trouvé'], 404);
        }
    
        // Mise à jour des données de l'abonnement
        $abonnement->nomClient = $request->input('nomClient');
        $abonnement->emailClient = $request->input('emailClient');  
    
        // Sauvegarde des modifications
        $abonnement->save();
    
        return response()->json(['message' => "Abonnemment affecté à un client"], 200);
                
    }


    
}

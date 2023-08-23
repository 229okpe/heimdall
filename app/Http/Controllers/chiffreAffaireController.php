<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class chiffreAffaireController extends Controller
{
    public function calculerChiffreAffaires()
    {
        $chiffreAffaire = Commande::sum('prix_total');

        return response(["chiffre daffaire"=>$chiffreAffaire ], 200);
 
    }


    public function calculerChiffreAffairesMoisEnCours()
    {
        $moisEnCours = Carbon::now()->month;

        $chiffreAffaires = Commande::whereMonth('created_at', $moisEnCours)->sum('prix_total');

        return response(["chiffre daffaire"=>$chiffreAffaires ], 200); 
    }
}
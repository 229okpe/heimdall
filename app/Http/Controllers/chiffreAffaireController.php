<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class chiffreAffaireController extends Controller
{
    public function calculerChiffreAffaires()
    {
        $chiffreAffaire = Commande::where('status', '!=', 'Unpaid')->sum('prix_total');


        return response(["ca"=>$chiffreAffaire ], 200);
 
    }


    public function calculerChiffreAffairesMoisEnCours()
    {
        $moisEnCours = Carbon::now()->month;

        $chiffreAffaires = Commande::whereMonth('created_at', $moisEnCours)->where('status', '!=', 'Unpaid')->sum('prix_total');

        return response(["ca"=>$chiffreAffaires ], 200); 
    }
}

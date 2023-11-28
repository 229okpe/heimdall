<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class automatisationController extends Controller
{
    static public function getnbreProduitsManuel(array $idsDansLePanier)
{
    // Récupérer les produits avec les ID spécifiés dans le panier
   
    $nbreProduitsManuel = Produit::whereIn('id', $idsDansLePanier)
                                    ->where('traitement','=','Manuelle') 
                                    ->count();
   


    return $nbreProduitsManuel;
}

 

}

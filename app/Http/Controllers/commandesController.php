<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Produit;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;


class commandesController extends Controller
{

    public function addcart($id) {
        $produit=Produit::find($id);
        
            if($produit){
                Cart::add($produit, '1', 1, 9.99);
                Cart::save();
                return response(["message"=>"Produit ajouté"], 200);
            } else {
                return response(["message"=>"Produit non trouvé"], 404);
            }

    }

    public function recupererContenuPanier()
    {
        $contenuPanier = Cart::content();
       
        dd($contenuPanier);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function nombreCommandesEnAttente()
    {
        $nombreEnAttente = Commande::where('statut', 'en_attente')->count();
        
        return "Le nombre de commande en attente est : " .  $nombreEnAttente
        ;
    }
    
}


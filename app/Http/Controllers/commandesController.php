<?php

namespace App\Http\Controllers;
session_start();
use Carbon\Carbon;
use App\Models\Produit;
use App\Models\Commande;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Gloudemans\Shoppingcart\Facades\Cart;


class commandesController extends Controller
{
    public function addcart($id) {
        $produit=Produit::find($id);
        
            if($produit){
                Cart::instance("a")->add($produit->id, $produit->nom, 1,$produit->prix);
                
                return response(["message"=>"Produit ajouté"], 200);
            } else {
                return response(["message"=>"Produit non trouvé"], 404);
            }

    }

    public function removeCart($rowId) {
       
                Cart::remove($rowId);
                return response(["message"=>"Produit ajouté"], 200);
         
    }

    public function recupererContenuPanier()
    {    
        $contenuPanier = Cart::instance('a')->content();;
        return response(["message"=>$contenuPanier], 200);
   
    }

    public function totalPanier()
    {    
        $totalPanier = Cart::instance('a')->total();;
        return response(["message"=>$totalPanier], 200);
   
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $commandes=Commande::all();

        return response(["commandes"=>$commandes], 200);
    }



    public function payerAbonnement($idProduit ){
        $produit=Produit::findorfail($idProduit);
        $prefix = 'HEIMDALL_ORDER-';
        $randomNumber = mt_rand(1000, 9999); 
        $orderID = $prefix . $randomNumber;

        $vente=Commande::create([
            'order_id' => $orderID,
            'prix_total' =>$produit->prix 
          ]);
          $_SESSION[app('currentUser')->nom]=$vente->order_id; 
         /* Rempacez VOTRE_CLE_API par votre véritable clé API */
         \FedaPay\FedaPay::setApiKey("sk_sandbox_mGVNXupMPNzgS08eH8BGsJlo");
         //  \FedaPay\FedaPay::setApiKey("sk_live_i8hnQzQKe-Ez_gY6Hq6VC27D");
       /* Précisez si vous souhaitez exécuter votre requête en mode test ou live */
           \FedaPay\FedaPay::setEnvironment('sandbox'); //ou setEnvironment('live');

    
           /* Créer la transaction */ 
          $transaction = \FedaPay\Transaction::create(array(
           "description" =>   app('currentUser')->nom." ".$produit->prix,
           "amount" => $produit->prix,
           "currency" => ["iso" => "XOF"],
           "callback_url" => "http://uppersoftgroup.com/",
           "customer" => [
               "firstname" =>app('currentUser')->nom,
               "lastname" => app('currentUser')->prenoms,
               "email" => app('currentUser')->email,
               "phone_number" => [
                   "number" => "22996199507",
                   "country" => "bj"
               ]
           ]
           ));
           
          
           $token = $transaction->generateToken(); 
           
           return response()->json(['url' => $token->url], 200);
        
          
    }

    public function savePayment(Request $request) {
         
        $vente = Commande::where('order_id', $_SESSION[app('currentUser')->nom])->first();
        
        if($vente && $request->statut == 'success'){
            $vente->produit_id = $request->produit_id;
            $vente->user_id = app('currentUser')->id;
            $vente->date_created = Carbon::now();
            $vente->save();
            Session::forget(app('currentUser')->nom);
            //Mail de Paiement
            return response(['success' => 'Achat effectue avec succes'], 200);
            
          
        
        }
        else{
            return response(['error' => 'Produit non trouvé'], 404);
        }


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
        // Récupérez le produit par son ID
        $commande = Commande::find($id);

        if ($commande) {
            // Multipliez le prix du produit par la devise donnée
            
            return response()->json(['message' => $commande], 200);
        } else{
            return response()->json(['message' => 'Commande non trouvé'], 404);
        }
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
        $nombreEnAttente = Commande::where('statut', 'En attente')->count();

        return response(["message"=>$nombreEnAttente], 200);
         
    }

    public function nbrTotalCommandes(){

        $total = Commande::count();

        return response()->json(['message' =>  $total], 200); 

    } 
    
}


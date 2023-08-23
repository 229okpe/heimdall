<?php

namespace App\Http\Controllers;
session_start();
use Carbon\Carbon;
use App\Models\Produit;
use App\Models\Commande;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Validator;


class commandesController extends Controller
{
    public function addcart(Request $request, $id) {
        $produit=Produit::find($id);
        $token = $request->header('Authorization');
       
            if($produit){
                Cart::instance($token)->add($produit->id, $produit->nom, 1,$produit->prix);
                
                return response(["message"=>"Produit ajouté"], 200);
            } else {
                return response(["message"=>"Produit non trouvé"], 404);
            }

    }

    public function removeCart(Request $request, $rowId) {
            $token = $request->header('Authorization');
              Cart::instance($token)->remove($rowId);
                return response(["message"=>"Produit supprimé du panier"], 200);
         
    }

    public function recupererContenuPanier(Request $request)
    {     $token = $request->header('Authorization');
        $contenuPanier = Cart::instance($token)->content(); 
        $tauxDeChange = app('currentUser')->valeurDevise;

            foreach ($contenuPanier as $item) {
                $item->subtotal = $item->subtotal * $tauxDeChange;
            }
        return response(["message"=>$contenuPanier], 200);
   
    }

    public function totalPanier(Request $request)
    {     $token = $request->header('Authorization');
        $totalPanier = Cart::instance($token)->total();
        $totalPanier=$totalPanier *app('currentUser')->valeurDevise;

        return response(["message"=>$totalPanier], 200);
   
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    { 
        
        $commandes = Commande::selectRaw('*, prix_total / :devise as prix_converti', ['devise' => app('currentUser')->valeurDevise])->get();
     
        return response(["commandes"=>$commandes], 200);
    }

    public function mesCommandes()
    {
                $commandes = Commande::where('user_id', app('currentUser')->id) ->get();

                foreach($commandes as $commande){
                    $commande->prix_converti = $commande->prix_total / app('currentUser')->valeurDevise;
                }
                           
                         
        return response(["commandes"=>$commandes], 200);
    }
     

    public function payerAbonnement(Request $request, $idProduit = 02513 ){
       

        if($idProduit = 02513 ) {
            $token = $request->header('Authorization');
           $prix = (int)Cart::instance($token)->total(); 
        }
        else {
            $produit=Produit::findorfail($idProduit);
            $prix =$produit->prix;  
        }
       
        $prefix = 'HEIMDALL_ORDER-';
        $randomNumber = mt_rand(1000, 9999); 
        $orderID = $prefix . $randomNumber;

        $vente=Commande::create([
            'order_id' => $orderID,
            'prix_total' =>$prix 
          ]);
          $_SESSION[app('currentUser')->nom]=$vente->order_id; 
          
 
         /* Rempacez VOTRE_CLE_API par votre véritable clé API */
         \FedaPay\FedaPay::setApiKey("sk_sandbox_mGVNXupMPNzgS08eH8BGsJlo");
         //  \FedaPay\FedaPay::setApiKey("sk_live_i8hnQzQKe-Ez_gY6Hq6VC27D");
       /* Précisez si vous souhaitez exécuter votre requête en mode test ou live */
           \FedaPay\FedaPay::setEnvironment('sandbox'); //ou setEnvironment('live');

    
           /* Créer la transaction */ 
          $transaction = \FedaPay\Transaction::create(array(
           "description" =>   app('currentUser')->nom." ".$prix,
           "amount" => $prix,
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
  
        $validator = Validator::make($request->all(), [
            'statut' => 'required', 
           ]);
           
            if ($validator->fails()) {
              return response([
                     'errors' => $validator->errors(),
              ], 422); // Code de r&eacute;ponse HTTP 422 Unprocessable Entity
          }
          
        if(empty($request->produit_id)){
            $token = $request->header('Authorization');
            $contenuPanier = Cart::instance($token)->content();
            $idsDansLePanier = [];

            foreach ($contenuPanier as $item) {
                $idsDansLePanier[] = $item->id;
            }
            $produits=$idsDansLePanier;
        }
        else{
            $produits =$request->produit_id;
        } 
         
        $vente = Commande::where('order_id', $_SESSION[app('currentUser')->nom])->first();
        
        if($vente && $request->statut == 'success'){
            $vente->produit_id =  $produits;
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
        $tab=json_decode($commande->produit_id); 
        if ($commande) {

            foreach($tab as $id) 
                {
                     
                $produits[] = Produit::find($id);
                
                }
            $commande->produits = $produits;
            $commande->prix_converti = $commande->prix_total / app('currentUser')->valeurDevise;
            
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


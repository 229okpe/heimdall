<?php

namespace App\Http\Controllers;
session_start();

use Carbon\Carbon;
use App\Models\User;
use App\Models\Produit;
use App\Models\Commande;
use Illuminate\Http\Request;
use App\Mail\orderDetailsMail;
use App\Mail\orderMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;


class commandesController extends Controller
{
    public function addcart(Request $request, $id) {
        $produit=Produit::find($id);
        $token = $request->header('Authorization');
       
            if($produit){
                Cart::instance($token)->add($produit->id, $produit->nom, 1,$produit->prix, ['image' => $produit->image]);
                
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
        $cartWithoutRowIds = [];
        $tauxDeChange = app('currentUser')->valeurDevise;
     
        $totalPanier = Cart::instance($token)->total();
       $totalPanier=$totalPanier / app('currentUser')->valeurDevise;
       foreach ($contenuPanier as $item) {
        $cartWithoutRowIds[] = [
            'id' => $item->id,
            'name' => $item->name,
            'qty' => $item->qty,
            'price' => round($item->price /$tauxDeChange,2)  ,
            'options' => $item->options,
            'tax' => $item->tax,
            'subtotal' => $item->subtotal,
        ];
    }
        return response(["message"=>$cartWithoutRowIds, "prixTotal" =>round($totalPanier,2)], 200);
   
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
       

        if($idProduit == 02513 ) {
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
            $nomProduits= "" ;

            foreach ($contenuPanier as $item) {
                $idsDansLePanier[] = $item->id;
                $nomProduits .= $item->name.' \ ';
            }
            $produits=$idsDansLePanier;
            $listeProduit = $nomProduits;
        }
        else{

            $produits =$request->produit_id;

            $listeProduit = Produit::find($request->produit_id)->nom;
        } 
         
        $vente = Commande::where('order_id', $_SESSION[app('currentUser')->nom])->first();
        
        if($vente && $request->statut == 'success'){
            $vente->produit_id =  $produits;
            $vente->box = $request->box;
            
            $vente->user_id = app('currentUser')->id;
            $vente->user_name = app('currentUser')->nom.' '.app('currentUser')->prenoms;
            $vente->date_created = Carbon::now();
            $vente->save();
           // Session::forget(app('currentUser')->nom); 
            session()->forget(app('currentUser')->nom);
            if(Mail::to(app('currentUser')->email)->send(new orderMail( $vente,$listeProduit)))
                {
                return response(['success' => 'Achat effectue avec succes'], 200);
                 } else {dd("error");}}
     
        
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
            $tab=json_decode($commande->produit_id); 
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

    public function validateOrder(Request $request){
        $validator = Validator::make($request->all(), [
        
            'order_id' => 'required'
          ]);

           
            if ($validator->fails()) {
              return response(['errors' => $validator->errors(), ], 422); 
          } 
          else {
            $commande = Commande::where('order_id', $request->order_id)->first();
            if($commande){ 
                    
                if ($request->statut =="Annulé") {
                    $commande->status = $request->statut;
                    $commande->save();
                }
                 else {
                        $data = $request->details; 
                        $commande->status = $request->statut;
                        $commande->details = $data;
                        $commande->save();
                        $user= User::where('id', $commande->user_id)->first();
                    
                        if(Mail::to($user->email)->send(new orderDetailsMail( $user,$data, $commande))){
                                return response(['message' => "Details envoyé"], 200);
                        }
            
                      }
        
            }
                else {
                    return response(['message' => 'Commande non trouvé'], 404);
                }

           
        }
    }

    
}


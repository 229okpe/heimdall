<?php

namespace App\Http\Controllers;
session_start();

use Carbon\Carbon;
use App\Models\User;
use App\Models\Panier;
use App\Mail\orderMail;
use App\Models\Produit;
use App\Models\Commande;
use Illuminate\Http\Request;
use App\Mail\orderDetailsMail;
use Feexpay\FeexpayPhp\FeexpayClass;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Validator;


class commandesController extends Controller
{
          

    public function addcart(Request $request, $id) {
        $token = $request->header('Authorization');
        $paniers = Panier::where('token', $token)->get(); 
        $ids = Panier::where('token', $token)->pluck('idProduit')->toArray();

        $product=Produit::find($id);
        
        if(count($paniers)!==0){
                    if($product)
                        { 
                            if(in_array($product->id,$ids)) {
                                $pdt = Panier::where('idProduit', $product->id)->first();
                                $pdt->qty+=1;
                                $pdt->save();
                                return response(["message"=> "Produit ajouté" ], 200);
                             
                            }
                            else {
                                Panier::create([
                                    'token' => $token,
                                    'idProduit' => $product->id,
                                    'nomProduit'=>$product->nom,
                                    'qty'=>1,
                                    'image'=>$product->image,
                                    'prix'=>$product->prix ]);
                                    return response(["message"=> "Produit ajouté" ], 200);
                                   
                            }

                    
                                return response(["message"=> "Produit ajouté" ], 200);
                        
                            } 
                        
                            else {
                                return response(["message"=>"Produit non trouvé"], 404);
                            }
                        }
                        else{
                             
                            Panier::create([
                                'token' => $token,
                                'idProduit' => $product->id,
                                'nomProduit'=>$product->nom,
                                'qty'=>1,
                                'image'=>$product->image,
                                'prix'=>$product->prix ]);

                              return response(["message"=> "Produit ajouté" ], 200);
                        }
         }
            

    public function removeCart(Request $request, $id) {
       // Récupérez le jeton ou l'ID de l'utilisateur, selon votre système d'authentification
    $token = $request->header('Authorization'); // Vous devrez peut-être ajuster ceci.

    // Recherchez le panier de l'utilisateur en fonction du jeton ou de l'ID utilisateur.
    $produit = Panier::where('token', $token)->where('idProduit', $id)->first();

    if ($produit) {
        // Utilisez la méthode `where` pour trouver l'élément spécifique du panier en fonction de l'ID du produit et du panier.
        $produit->delete();

        return response(["message" => "Produit supprimé du panier"], 200);
    } else {
        return response(["message" => "Produit introuvable"], 404);
    }
} 

          public function modifierQuantite(Request $request, $id)
    {
        $token = $request->header('Authorization'); 
        $produit = Panier::where('token', $token)->where('idProduit', $id)->first();

        if ($produit) {
            // Utilisez la méthode `where` pour trouver l'élément spécifique du panier en fonction de l'ID du produit et du panier.
            $produit->qte = $request->qte;
             $produit->save();
    
            return response(["message" => "quantité mis a jour"], 200);
        } else {
            return response(["message" => "Produit introuvable"], 404);
        }
        $panier = Panier::findOrFail($id);
      

        // Redirigez l'utilisateur vers la page du panier ou une autre page appropriée
        return redirect()->route('panier.index')->with('success', 'Quantité mise à jour avec succès');
    }

  

    public function recupererContenuPanier(Request $request)
    {      $token = $request->header('Authorization');  
          $paniers = Panier::where('token', $token)->get();

         $prixTotal = 0;

        // Parcourez tous les paniers de l'utilisateur
        
            // Parcourez les produits dans chaque panier et ajoutez leur prix à la somme
            foreach ($paniers  as $produit) {
                $produit->prix_converti= round($produit->prix /app('currentUser')->valeurDevise,2) ;
                $prixTotal += $produit->prix * $produit->qty;
            }
            $prixTotal =round($prixTotal /  app('currentUser')->valeurDevise,2);
        // Répondez avec le contenu du panier
        return response()->json(['message' =>$paniers, "prixTotal" => $prixTotal ]);
     
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
            $paniers = Panier::where('token', $token)->get();
            $prix = 0;
           foreach ($paniers  as $produit) {
               $prix += $produit->prix;
             }
  
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
         // \FedaPay\FedaPay::setApiKey("sk_live_HvgQ1tCMXjY9zKqWEvAhonDO");
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
             "idTransaction" => 'required' 
           ]);
           
            if ($validator->fails()) {
              return response([
                     'errors' => $validator->errors(),
              ], 422); // Code de r&eacute;ponse HTTP 422 Unprocessable Entity
          }

          $transaction = \FedaPay\Transaction::retrieve($request->idTransaction);
          if ($transaction->status !== "approved") {
            return response(['error' => 'Transaction echouée'], 404);
        }
        if(empty($request->produit_id)){
            $token = $request->header('Authorization');
            $paniers = Panier::where('token', $token)->get();
          
            $idsDansLePanier = [];
            $nomProduits= "" ;
           foreach ($paniers  as $item) {
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
        
        if($vente && $transaction->status == 'approved'){
            $vente->produit_id =  $produits;
            $vente->box = $request->box;
            
            $vente->user_id = app('currentUser')->id;
            $vente->user_name = app('currentUser')->nom.' '.app('currentUser')->prenoms;
            $vente->date_created = Carbon::now();
            $vente->save();
           // Session::forget(app('currentUser')->nom); 
           $paniers = Panier::where('token', $token)->delete();
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
        $nombreEnAttente = Commande::where('status', 'En attente')->count();

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


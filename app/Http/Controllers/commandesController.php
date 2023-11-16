<?php

namespace App\Http\Controllers;
session_start();

use Carbon\Carbon;
use App\Models\User;
use App\Models\Panier;
use App\Mail\orderMail;
use App\Models\Produit;
use App\Models\Commande;
use App\Models\codePromo;
use Illuminate\Http\Request;
use App\Mail\orderDetailsMail; 
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
 


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
            $produit->qty = $request->qte;
             $produit->save();
    
            return response(["message" => "quantité mis a jour"], 200);
        } else {
            return response(["erreur" => "Produit introuvable"], 404);
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
        
        $commandes =Commande::selectRaw('*, prix_total / :devise as prix', ['devise' => app('currentUser')->valeurDevise])
    ->orderBy('id', 'desc')
    ->get();

     
        return response(["commandes"=>$commandes], 200);
    }

    public function mesCommandes()
    {
              $commandes = Commande::where('user_id', app('currentUser')->id)
                                    ->orderBy('id', 'desc')
                                    ->get();

                foreach($commandes as $commande){
                    $commande->prix= $commande->prix_total / app('currentUser')->valeurDevise;
                }
                           
                         
        return response(["commandes"=>$commandes], 200);
    }
     

    public function payerAbonnement(Request $request ){
  
        if($request->codepromo !== "UNDEFINED"){
           
            $promo = codePromo::where('intitule', $request->input('codepromo'))->first();
            
                if($promo && $promo->nombreUtilisation > 0){
                    $codePromo =$request->codepromo ;
                    $promo->nombreUtilisation =  $promo->nombreUtilisation - 1;
                    $promo ->save();
                 
                    if(!$request->idProduit) {
                           $url="https://heimdall-store.com/panier";
            $token = $request->header('Authorization');
            $paniers = Panier::where('token', $token)->get();
            $prix = 0;
             $qty= 0;
             $idsDansLePanier = [];
            
           foreach ($paniers  as $produit) {
              
                $qty += $produit->qty;
                
                 $prix += $produit->prix *  $qty;
                $idsDansLePanier[] =["id" =>$produit->idProduit, "qty" =>$produit->qty];
             }
              $produits=$idsDansLePanier;
           
              $prix = $prix - $promo->valeur * $prix /100;
                      
                    }
                    else {
                           $url="https://heimdall-store.com/payer-abonnement";
            $produit=Produit::findorfail($request->idProduit);
            $produits[]=["id" =>$produit->id, "qty" =>$produit->quantite];
            $prix =$produit->prix; 
            $qty = 1;
                       
                        $prix = $prix - $promo->valeur * $prix /100;
                    }
                }
                else 
                {
                    return response()->json(['erreur' => "Code promo errone ou code épuisé "]);
                }
        } 
        else {
       
       $codePromo=null;
        if(!$request->idProduit ) {
            $url="https://heimdall-store.com/panier";
            $token = $request->header('Authorization');
            $paniers = Panier::where('token', $token)->get();
            $prix = 0;
             $qty= 0;
             $idsDansLePanier = [];
           foreach ($paniers  as $produit) {
                $qty += $produit->qty;
                 $prix += $produit->prix *  $qty;
                $idsDansLePanier[] =["id" =>$produit->idProduit, "qty" =>$produit->qty];
             }
              $produits=$idsDansLePanier;
   
        }
        else {
            
                $url="https://heimdall-store.com/payer-abonnement";
            $produit=Produit::findorfail($request->idProduit);
            $produits[]=["id" =>$produit->id, "qty" =>$produit->quantite];
            $prix =$produit->prix; 
            $qty = 1;
            }
 
        }
      
       
        $prefix = 'HEIMDALL_ORDER-';
        $randomNumber = mt_rand(1000, 9999); 
        $orderID = $prefix . $randomNumber;
       $produits_serialized = json_encode($produits);

      
        $vente=Commande::create([
            'order_id' => $orderID,
            'codePromo' => $codePromo ? $codePromo : null,
            'produit_id'=> $produits_serialized ,
            'prix_total' => $prix ,
            'status' => "Unpaid",
            'quantite' => $qty ,
              'user_id' => app('currentUser')->id,
            'user_name' => app('currentUser')->nom.' '.app('currentUser')->prenoms,
             'date_created'=> Carbon::now()
          ]);
         
        //  $_SESSION[app('currentUser')->nom]=$vente->order_id; 
          

 
         /* Rempacez VOTRE_CLE_API par votre véritable clé API */
        \FedaPay\FedaPay::setApiKey("sk_live_E8o6Spu7rdpm4pVU_prsTEKf");
         // \FedaPay\FedaPay::setApiKey("sk_live_HvgQ1tCMXjY9zKqWEvAhonDO");
       /* Précisez si vous souhaitez exécuter votre requête en mode test ou live */
           \FedaPay\FedaPay::setEnvironment('live'); //ou setEnvironment('live');
 
           /* Créer la transaction */ 
          $transaction = \FedaPay\Transaction::create(array(
           "description" =>   app('currentUser')->nom." ".$prix,
           "amount" => $prix,
           "currency" => ["iso" => "XOF"],
           "callback_url" => $url,
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
           
           return response()->json(['url' => $token->url, 'commande'=> $vente], 200);
        
          
    }

    public function savePayment(Request $request) {
       
        $validator = Validator::make($request->all(), [
             "idTransaction" => 'required', 
             "order_id" => 'required'
           ]);
           
            if ($validator->fails()) {
              return response([
                     'errors' => $validator->errors(),
              ], 422); // Code de r&eacute;ponse HTTP 422 Unprocessable Entity
          }
          
          
          try {
    \FedaPay\FedaPay::setApiKey("sk_live_E8o6Spu7rdpm4pVU_prsTEKf");
    \FedaPay\FedaPay::setEnvironment('live');

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
         
        $vente = Commande::where('order_id', $request->order_id)->first();
        
        if($vente && $transaction->status == 'approved'){
             
            $vente->box = $request->box;
            $vente->status = "En attente";
            
            $vente->save();
           // Session::forget(app('currentUser')->nom); 
           $paniers = Panier::where('token', $token)->delete();
            if(Mail::to(app('currentUser')->email)->send(new orderMail( $vente,$listeProduit)))
                {
                return response(['success' => 'Achat effectue avec succes', 'id'=>$vente->id ], 200);
                 } else {dd("error");}}
     
        
        else{
            return response(['error' => 'Produit non trouvé'], 404);
        }


} catch (\FedaPay\Error\Base $e) {
      return response(['error' => 'Transaction erronée'], 500);

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
                  $customer = User::find($commande->user_id);
                 $tab=json_decode($commande->produit_id,true); 
 
        
            foreach($tab as $item) 
                {
                     
                 $id = $item['id'];
                $quantite = $item['qty']; // Supposons que la quantité soit également dans l'objet JSON
            
                $produit = Produit::find($id);
                
                // Ajouter la propriété quantité à l'objet $produit
                $produit->quantite = $quantite;
            
                $produits[] = $produit;
              
                
                }
            $commande->produits = $produits;
            $commande->prix_converti = $commande->prix_total / app('currentUser')->valeurDevise;
       
             $commande->customer_telephone =  $customer->numTelephone;
             $commande->customer_email =  $customer->email;
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

        $total = Commande::where('status', '!=', 'Unpaid')->count();

        return response()->json(['message' =>  $total], 200); 

    } 

    public function validercommande(Request $request){
     
       $validator = Validator::make($request->all(), [
        
            'order_id' => 'required',
               'status' => 'required|in:Annulee,Livree',
          ]);

           
  if ($validator->fails()) {
              return response(['errors' => $validator->errors(), ], 422); 
          } 
         else {
               
            $commande = Commande::where('order_id', $request->order_id)->first();
            if($commande){ 
                    
                if ($request->status =="Annulee") {
                    $commande->status = $request->status;
                    $commande->save();
                       return response(['message' => "Commande annulée"], 200);
                }
                elseif($request->status =="Livree"){
                        $data = $request->details; 
                        $commande->status = $request->status;
                        $commande->details = $data;
                        $commande->save();
                        $user= User::where('id', $commande->user_id)->first();
                       
                       if(Mail::to($user->email)->send(new orderDetailsMail( $user,$data, $commande))){
                                return response(['message' => "Details envoyé ".$user->email], 200);
                        } 
            
                      }
        
            }
                else {
                    return response(['message' => 'Commande non trouvé'], 404);
                }

           
        }
    }

 
    
}


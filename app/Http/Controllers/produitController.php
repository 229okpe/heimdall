<?php
 
namespace App\Http\Controllers;

use App\Models\Categorie; 
use App\Models\Favoris;
use App\Models\Produit;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Validator;
// App\Http\Controllers\nbrTotalProduits;

class produitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    { 
     //   $produits=Produit::all();
     $produits = Produit::selectRaw('*, prix * :devise as prix_converti', ['devise' => app('currentUser')->valeurDevise])->with('categorie:id,nom')->get();

        return response()->json(['produits' => $produits], 200);
    }

    public function indexwithoutlog()
    { 
     //   $produits=Produit::all();
     $produits = Produit::with('categorie:id,nom')->get();

        return response()->json(['produits' => $produits], 200);
    }

    
    public function showWithoutlog(string $id)
    {
      // Récupérez le produit par son ID
      $produit = Produit::with('categorie:id,nom')->find($id);

    if ($produit) {
      
        return response()->json(['produit' => $produit], 200);
    } else{
        return response()->json(['message' => 'Produit non trouvé'], 404);
    }
 
        
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
        
            'nom' => 'required',
            'description' => 'required',
            'prix' => 'required',
            'image' => 'required|file',
            'categorie_id' => 'required',
            'statut' => 'required'
           ]);
           
            if ($validator->fails()) {
              return response(['errors' => $validator->errors(), ], 422); 
          } 
          $images = $request->file('image');
          $filename = uniqid() . '.' . $images->getClientOriginalExtension();
       //  $images->storeAs('public/images/images_produits', $filename);
         $images->move("storage/images/images_produits", $filename);
          $image='public/storage/images/images_produits/'.$filename;
          $request->merge(['image' => $image]);
       
          $produit=Produit::create([
            'nom' => $request->nom,
            'description' => $request->description,
            'prix' => $request->prix,
            'statut' => $request->statut,
            'image' => $image,
            'categorie_id' => $request->categorie_id
          ]);
 
        return response()->json(['produit' => $produit], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
      // Récupérez le produit par son ID
      $produit = Produit::with('categorie:id,nom')->find($id);

    if ($produit) {
        // Multipliez le prix du produit par la devise donnée
        $produit->prix_converti = $produit->prix * app('currentUser')->valeurDevise;

 
        return response()->json(['produit' => $produit], 200);
    } else{
        return response()->json(['message' => 'Produit non trouvé'], 404);
    }
 
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required',
            'description' => 'required',
            'prix' => 'required',
            'image' => 'nullable|file', // Le champ 'image' est facultatif pour la mise à jour
            'categorie_id' => 'required'
        ]);
    
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()], 422);
        }
    
        // Récupérer le produit par son ID pour la mise à jour
        $produit = Produit::find($id);
    
        if (!$produit) {
            return response(['error' => 'Produit non trouvé'], 404);
        }
    
        // Gérer le téléchargement de la nouvelle image, le cas échéant
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = uniqid() . '.' . $image->getClientOriginalExtension();
             $image->move("storage/images/images_produits", $filename);
            $imagePath = 'public/storage/images/images_produits/' . $filename;
            $produit->image = $imagePath;
        }
    
        // Mettre à jour les attributs du produit avec les nouvelles valeurs
        $produit->nom = $request->nom;
        $produit->statut = $request->statut;
        $produit->description = $request->description;
        $produit->prix = $request->prix;
        $produit->categorie_id = $request->categorie_id;
    
        // Sauvegarder le produit mis à jour dans la base de données
        $produit->save();
    
        return response()->json(['produit' => $produit], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(string $id)
    {
        $produit = Produit::find($id);

        if ($produit) {
            $produit->delete();
            return response()->json(['message' => 'Produit supprimé'], 200);
            
        }
    }

    public function liste_produits_par_categorie($idCategorie)
    {
        $categorie = Categorie::find($idCategorie);

        $produits = $categorie->produits()->get();

        return response()->json($produits);
    }

    public function ajouterAuxFavoris($id)
    {
        $produit = Produit::find($id);

        if (!$produit) {
            return response()->json(['message' => 'Produit non trouvé'], 404);
        }

        $currentUser = app('currentUser');

        // Vérifier si le produit existe déjà dans les favoris de l'utilisateur
        $favorisExistant = Favoris::where('produit_id', $produit->id)
                                ->where('user_id', $currentUser->id)
                                ->first();

        if ($favorisExistant) {
            return response()->json(['message' => 'Ce produit est déjà dans les favoris.']);
        }

        // Ajouter le produit aux favoris de l'utilisateur
        $favoris = new Favoris();
        $favoris->produit_id = $produit->id;
        $favoris->user_id = $currentUser->id;
        $favoris->save();

        return response()->json(['message' => 'Ce produit a été ajouté aux favoris.'] , 200);
    }

    public function showFavoris()
    {
        $currentUser = app('currentUser');

        // Récupérer la liste des produits favoris de l'utilisateur
        $produitsFavoris = Favoris::where('user_id', $currentUser->id)
                                ->with('produit') // Charger les détails des produits associés
                                ->get();

        return response()->json(['produits_favoris' => $produitsFavoris] , 200);
    }


    public function supprimerUnFavoris($id){

        $produit = Produit::find($id);

        if($produit){
            
            // trouver un enregistrement dans la table des favoris de la bdd

            $favoris = Favoris::where('produit_id' , $produit->id)->where('user_id' , app('currentUser')->id);

            if ($favoris) {
                $favoris->delete();

                return response()->json(['message' => 'Produit supprimé'], 200);
            }else{
                return response()->json(['message' => 'Produit non trouver'] , 404);

            }
        }    
    } 

    public function nbrTotalProduits(){

        $total = Produit::count();

        return response()->json(['message' =>  $total], 200); 

    }  

    public function nbrTotalCatgories(){

        $total = Categorie::count();
        return response()->json(['message' =>  $total], 200); 
        

    } 

   

    public function rechercherProduits(Request $request)
    {
        $nomRecherche = $request->nom;

        if ($nomRecherche) {
            $produits = Produit::with('categorie:id,nom')
                ->where('nom', 'like', '%' . $nomRecherche . '%')
                ->get();

            if ($produits->isEmpty()) {
                return response()->json(['message' => 'Aucun produit trouvé'], 404);
            }

            return response()->json(['produits' => $produits], 200);
        } else {
            return response()->json(['message' => 'Nom de produit non spécifié'], 400);
        }
    }

}


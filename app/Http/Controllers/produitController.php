<?php
 
namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\Produit;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Validator;

class produitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    { 
     //   $produits=Produit::all();
     $produits = Produit::selectRaw('*, prix * :devise as prix_converti', ['devise' => app('currentUser')->devise])->get();

        return response()->json(['produits' => $produits], 200);
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
            'categorie_id' => 'required'
           ]);
           
            if ($validator->fails()) {
              return response(['errors' => $validator->errors(), ], 422); 
          } 
          $images = $request->file('image');
          $filename = uniqid() . '.' . $images->getClientOriginalExtension();
          $images->storeAs('public/images/images_produits', $filename);
          $image='public/storage/images/photos_profil/'.$filename;
          $request->merge(['image' => $image]);
       
          $produit=Produit::create([
            'nom' => $request->nom,
            'description' => $request->description,
            'prix' => $request->prix,
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
        $produit = Produit::find($id);

    if ($produit) {
        // Multipliez le prix du produit par la devise donnée
        $produit->prix_converti = $produit->prix * app('currentUser')->devise;

 
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
            $image->storeAs('public/images/images_produits', $filename);
            $imagePath = 'public/storage/images/images_produits/' . $filename;
            $produit->image = $imagePath;
        }
    
        // Mettre à jour les attributs du produit avec les nouvelles valeurs
        $produit->nom = $request->nom;
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
        // $produits ;
        
        return response()->json($produits);
        

        // foreach($produits as $produit){
        //     echo $produit->nom.",";
        // }
    }

}


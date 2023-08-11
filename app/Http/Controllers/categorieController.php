<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class categorieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Categorie::all();

        return response()->json(['categories' => $categories], 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
        
            'nom' => 'required',
            'description' => 'required',
           ]);
           
            if ($validator->fails()) {
              return response(['errors' => $validator->errors(), ], 422); 
            }
          $categorie=Categorie::create([
            'nom' => $request->nom,
            'description' => $request->description,
          ]);
 
        return response()->json(['categorie' => $categorie], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $categorie = Categorie::find($id);

        if (!$categorie) {
            return response()->json(['error' => 'Catégorie non trouvée'], 404);
        }

        return response()->json(['categorie' => $categorie], 200);
    }

    

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required',
            'description' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()], 422);
        }
    
        $categorie = Categorie::find($id);
    
        if (!$categorie) {
            return response()->json(['error' => 'Catégorie non trouvée'], 404);
        }
    
        $categorie->update([
            'nom' => $request->nom,
            'description' => $request->description,
        ]);
    
        return response()->json(['categorie' => $categorie], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(string $id)
    {
        $categorie = Categorie::find($id);

        if ($categorie) {
            $categorie->delete();
            return response()->json(['message' => 'Categorie supprimé'], 200);
            
        }
    }   

    
}

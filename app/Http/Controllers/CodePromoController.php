<?php

namespace App\Http\Controllers;

use App\Models\codePromo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CodePromoController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
        
            'intitule' => 'required',
            'valeur' => 'required|integer|max:100',
           ]);
           
            if ($validator->fails()) {
              return response(['errors' => $validator->errors(), ], 422); 
            }
          $codePromo=codePromo::create([
            'intitule' => $request->intitule,
            'valeur' => $request->valeur,
          ]);
 
        return response()->json(['codePromo' => $codePromo], 200);
    }
    
      public function index()
    {
        $codepromos = codePromo::all();

        return response()->json(['codepromos' => $codepromos], 200);
    }

    public function show(string $id)
    {
        $codePromo = codePromo::find($id);

        if (!$codePromo) {
            return response()->json(['error' => 'Code Promo non trouvée'], 404);
        }

        return response()->json(['CodePromo' => $codePromo], 200);
    }

    public function delete(string $id)
    {
        $codePromo = codePromo::find($id);

        if (!$codePromo) {

            return response()->json(['error' => 'Code Promo non trouvée'], 404);
        }
        $codePromo ->delete();
        return response()->json(['messsage' => "Code promo supprimé"], 200);
    }

    public function checkValidity(Request $request)
    {
    
  $validator = Validator::make($request->all(), [
            'codePromo' => 'required|string',
           ]);
           
            if ($validator->fails()) {
              return response(['errors' => $validator->errors(), ], 422); 
            }
        // Récupération du code promo depuis la base de données
        $promo = codePromo::where('intitule', $request->input('codePromo'))->first();

        // Vérification de la validité du code promo
        if ($promo) {
            // Le code promo est valide, vous pouvez ajouter des actions supplémentaires ici
            return response()->json(['value' => $promo->valeur/100]);
        } else {
            // Le code promo n'est pas valide
            return response()->json(['erreur' => "Code promo errone"]);
        }
    }
} 






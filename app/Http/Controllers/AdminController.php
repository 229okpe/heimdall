<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function ajouterAdmin(Request $request) 
    { 
        $validator = Validator::make($request->all(), [
            'nom' =>"required|string|max:255",
            'prenoms' =>"required|string",
            'type' => "required",
            'devise' => "required",
           'email' =>"required|string|email|max:255|unique:".User::class,
           'password' => 'required' ]);
         
          if ($validator->fails()) {return response(["error" =>  $validator->errors()], 200);  
        } else { 
    
        $admin = User::create([
            'nom' => $request->nom,
            'prenoms' => $request->prenoms,
            'type' => $request->type,
            'devise' => $request->devise,
            'valeurDevise' => $request->valeurDevise,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        return response()->json(['admin' => $admin], 200);


    //  //   $this->notify(new VerifyEmail);
    //    Notification::send($user, new VerifyEmail($user));

    //    // Mail::send(new inscriptionMail($user));

    //     $token = $user->createToken('api_token')->plainTextToken;
    //         $this->login($request);
    //     return response([
    //         'user' => $user,
    //         'token' => $token,
    //     ], 201);
    }
        
    }

    public function delete(string $id)
    {
        $user = User::find($id);

        if ($user) {
            $user->delete();
            return response()->json(['message' => 'Administrateur supprimé'], 200);
            
        }
    }

    public function index()
    {
        $user = User::all();

        return response()->json(['user' => $user], 200);
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'nom' =>"required|string|max:255",
            'prenoms' =>"required|string",
            'type' => "required",
            'devise' => "required",
           'email' =>"required|string|email|max:255|unique:".User::class,
           'password' => 'required' 
        ]);
    
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()], 422);
        }
    
        $user = User::find($id);
    
        if (!$user) {
            return response()->json(['error' => 'Administrateur non trouvée'], 404);
        }
    
        $user->update([

            $user->nom = $request->nom,
            $user->prenoms = $request->prenoms,
            $user->type = $request->type,
            $user->devise = $request->devise,
            $user->valeurDevise = $request->valeurDevise,
            $user->email = $request->email,
            $user->password = $request->password,

        ]);
    
        return response()->json(['user' => $user], 200);


    }

}

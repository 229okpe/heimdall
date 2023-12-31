<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\contactMail;
use Illuminate\Http\Request;
use App\Models\ResetCodePassword;
use App\Mail\SendCodeResetPassword;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;

class Authentification extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */

   
    public function register(Request $request) 
    { 
        $validator = Validator::make($request->all(), [
            'nom' =>"required|string|max:255",
            'prenoms' =>"required|string",
            'numeroTelephone'=>'required|',
            'type' => "required",
            'devise' => "required|in:EUR,CFA,USD",
           'email' =>"required|string|email:rfc,dns|max:255|unique:".User::class,
           'password' => 'required' ]);
         
          if ($validator->fails()) {return 
            response(["error" =>  $validator->errors()->all()], 200);  }
else { 
        if($request->devise =="USD"){
            $valeurDevise="650";
        }
        elseif($request->devise =="EUR"){
            $valeurDevise="700";
        }
        elseif($request->devise =="CFA"){
            $valeurDevise="1";
        }
        $user = User::create([
            'nom' => $request->nom,
            'numTelephone'=>$request->numeroTelephone,
            'prenoms' => $request->prenoms,
            'type' => $request->type,
            'devise' => $request->devise,
            'valeurDevise' => $valeurDevise,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

       
       Notification::send($user, new VerifyEmail($user));
 
        $token = $user->createToken('api_token')->plainTextToken;
            $this->login($request);
        return response([
            'user' => $user,
            'token' => $token,
        ], 201);
    }
        
    }

 
public function update(Request $request, $id)
{
    $user = User::find($id);

    if (!$user) {
        return response(['error' => 'Utilisateur introuvable'], 404);
    }

    $validator = Validator::make($request->all(), [
        'nom' => 'required|string|max:255',
        'prenoms' => 'required|string',
        'numeroTelephone' => 'required',
        'type' => 'required',
        'devise' => 'required' 
       
    ]);

    if ($validator->fails()) {
        return response(["error" => $validator->errors()], 200);
    } else {
        if ($request->devise == "USD") {
            $valeurDevise = "650";
        } elseif ($request->devise == "EUR") {
            $valeurDevise = "700";
        } elseif ($request->devise == "CFA") {
            $valeurDevise = "1";
        }

        $user->update([
            'nom' => $request->nom,
            'numTelephone' => $request->numeroTelephone,
            'prenoms' => $request->prenoms,
            'type' => $request->type,
            'devise' => $request->devise,
            'valeurDevise' => $valeurDevise 
        ]);

        return response([
            'message' => 'Utilisateur mis à jour avec succès',
            'user' => $user,
        ], 200);
    }
}

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'type'=> 'required'
        ]);

       if (Auth::attempt($credentials)) {
    $user = Auth::user();
    if ($user) {
        $token = $user->createToken('authToken')->plainTextToken;

        return response([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    } else {
        return response(['message' => 'Identifiants incorrects ! Veuillez réessayer'], 401);
    }
} else {
    return response(['message' => 'Identifiants incorrects ! Veuillez réessayer'], 401);
}

    }
    
    public function logout(Request $request)
    {
        $bearerToken = $request->bearerToken();
        
        $tokens = \Laravel\Sanctum\PersonalAccessToken::findToken($bearerToken);
            $user = $tokens->tokenable;
            if($user){
          $user->tokens()->delete();
        return [
          'Message'=>'Utilisateur D&eacute;connect&eacute;'
        ];
    }
    else {
        return ['error'=>"Cet utilisateur n'existe pas"];
    }
    }

    public function currentUser(Request $request)
    {      $user = auth('sanctum')->user()  ;
       
       if($user){
        return response($user,200);

         } else {
            return response(['error' => 'Aucun utilisateur trouv&eacute;',],200);

         }
    }

    public function modifyPassword(Request $request)
    {      $user = auth('sanctum')->user()  ;

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required', ]);
         
       if ($validator->fails()) {
            return response(["error" =>  $validator->errors()], 200);  
            }
                else {
             
            // Vérifier si le mot de passe actuel est correct
            if (!Hash::check($request->current_password, $user->password)) {
                return response(["error" => "Le mot de passe actuel est incorrect."], 422); 
            }

            // Mettre à jour le mot de passe
            $user->password = Hash::make($request->new_password);
            $user->save();

           return response(['success' => 'Mot de passe modifi&eacute; avec succ&egrave;s.']);
                
            }
    }
    public function verify($user_id, Request $request)
    {
       
        $user = User::findOrFail($user_id);
    
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            
        }
    
       // return response(['success' => 'Email vérifié avec succ&egrave;s.']);
           return redirect('https://heimdall-store.com/compte-valide'); 
    }

    public function resendEmailVerification() {
     
        $user = auth('sanctum')->user()  ;
        if ($user) {
            if ($user->hasVerifiedEmail()) {
                return response(["msg" => "Email already verified."], 400);
            } else {
         //      Notification::send($user, new VerifyEmail($user));
                return response(["msg" => "Un lien de v&eacute;rification a &eacute;t&eacute; envoy&eacute; &agrave; votre a²resse mail."]);
            }
        } else {
            return response(["msg" => "Utilisateur introuvable ! Veuillez v&eacute;rifier votre adresse mail"], 401);
        }
        

}

public function sendMailPasswordForgot(Request $request)
{ 
    
    $validator = Validator::make($request->all(), [
        
       'email' =>"required|string|email|max:255",  ]);
     
      if ($validator->fails()) {
        response(["msg" =>  $validator->errors()], 200);
    }
            else {
                        if(User::firstWhere('email', $request->email)){
                    // Delete all old code that user send before.
                    ResetCodePassword::where('email', $request->email)->delete();

                    // Generate random code
                    $code = mt_rand(100000, 999999);

                    // Create a new code
                    $codeData = ResetCodePassword::create([
                        'code'=>$code,
                        'email'=>$request->email,
                    ]);

                    // Send email to user
                    if(Mail::to($request->email)->send(new SendCodeResetPassword($codeData->code,User::firstWhere('email', $request->email)->type))){
 
                         return response(['message' => trans('passwords.sent')], 200);
                    } else {dd("error");}
                }
                else {
                    return response(["msg" => "Utilisateur introuvable ! Veuillez v&eacute;rifier votre adresse mail"], 404);
                }
        }
}

public function passwordReset(Request $request)
{ 
     
    $validator = Validator::make($request->all(), [
        
       'code' => 'required|string|exists:reset_code_passwords',
        'password' => 'required|string|',
      ]);
      
       if ($validator->fails()) {
         return response([
                'errors' => $validator->errors(),
         ], 422); // Code de r&eacute;ponse HTTP 422 Unprocessable Entity
     }
     else {
            // find the code
            $passwordReset = ResetCodePassword::firstWhere('code', $request->code);

                if($passwordReset){
            // check if it does not expired: the time is one hour
                if ($passwordReset->created_at > now()->addHour()) {
                    $passwordReset->delete();
                    return response(['message' => trans('passwords.code_is_expire')], 422);
                }
            }
            else {
                return response(['message' => trans('passwords.code_is_not_valid')], 422);
            }

            // find user's email 
            $user = User::firstWhere('email', $passwordReset->email);

            // update user password
            $user->update([
                'password' => Hash::make($request->password)
            ]);

            // delete current code 
            $passwordReset->delete();

            return response(['message' =>'Le mot de passe a &eacute;t&eacute; r&eacute;initialis&eacute; avec succ&egrave;s'], 200);
        }

    } 

    public function sendform(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required',
            'prenom' =>'required',
            'email' => 'required|email',
            'message' => 'required',
            'numeroTelephone'=>'required|'
           ]);
           
            if ($validator->fails()) {
              return response([
                     'errors' => $validator->errors(),
              ], 422); // Code de r&eacute;ponse HTTP 422 Unprocessable Entity
          }
          $data = $request->all();
          Mail::to('hello@heimdall-store.com')->send(new contactMail($data));

        return response()->json(['message' => 'Message sent successfully'], 200);
    }

}

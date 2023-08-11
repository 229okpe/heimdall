<?php

namespace App\Http\Controllers;

use App\Models\User;
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
            'superAdmin' => "required",
            'devise' => "required",
           'email' =>"required|string|email|max:255|unique:".User::class,
           'password' => 'required' ]);
         
          if ($validator->fails()) {return response(["error" =>  $validator->errors()], 200);  }
else { 
    
        $user = User::create([
            'nom' => $request->nom,
            'prenoms' => $request->prenoms,
            'superAdmin' => $request->superAdmin,
            'devise' => $request->devise,
            'valeurDevise' => $request->valeurDevise,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

     //   $this->notify(new VerifyEmail);
       Notification::send($user, new VerifyEmail($user));

       // Mail::send(new inscriptionMail($user));

        $token = $user->createToken('api_token')->plainTextToken;
            $this->login($request);
        return response([
            'user' => $user,
            'token' => $token,
        ], 201);
    }
        
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

            if (Auth::attempt($credentials)) {
                
                    $user = Auth::user();
                    if($user){
                    $token = $user->createToken('authToken')->plainTextToken;

                    return response([
                        'access_token' => $token,
                        'token_type' => 'Bearer', ]);

                    } else {
                    return response(['message' => 'Identifiants incorrects ! Veuillez r&eacute;ssayer'], 401);
                }
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
    
        return response(['success' => 'Email vérifié avec succ&egrave;s.']);
          // return redirect('http://82.165.107.148/compte-valide'); 
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
                    if(Mail::to($request->email)->send(new SendCodeResetPassword($codeData->code))){
 
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

}

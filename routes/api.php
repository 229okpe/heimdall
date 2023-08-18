<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Authentification;
use App\Http\Controllers\produitController;
use App\Http\Controllers\categorieController;
use App\Http\Controllers\chiffreAffaireController;
use App\Http\Controllers\commandesController;
use App\Http\Middleware\CheckAccess;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/register', [Authentification::class, 'register']); 

Route::post('/login', [Authentification::class, 'login']); 

Route::get('/logout', [Authentification::class, 'logout']);

Route::post('/sendMailPasswordForgot', [Authentification::class, 'sendMailPasswordForgot']);

Route::get('/verify-email/{id}/', [Authentification::class, 'verify'])->name('verification.verify');

Route::post('/contact', [Authentification::class, 'sendform'] );


Route::middleware(['auth'])->group(function () {   

            //PRODUITS
    Route::get('/produits', [produitController::class, 'index']); 

    Route::post('/ajouter-produit', [produitController::class, 'store']); 

    Route::get('/produit/{id}', [produitController::class, 'show']); 

    Route::post('/modifier-produit', [produitController::class, 'update']); 

    Route::get  ('/supprimer-produit/{id}', [produitController::class, 'delete']); 
    
    Route::get('/{categorie}/produits', [produitController::class, 'indexe']);

    Route::post('/rechercher-produit', [produitController::class, 'rechercherProduits']);

    
            //CATEGORIES
    Route::get('/categories', [categorieController::class, 'index']);

    Route::post('/ajouter-categorie', [categorieController::class, 'store']); 

    Route::post('/modifier-categorie/{id}', [categorieController::class, 'update']);

    Route::get('/categories/{id}', [categorieController::class, 'show']);

    Route::get('/supprimer-categorie/{id}', [categorieController::class, 'delete']);


            ///COMMANDES
    Route::get('/ajouter-panier/{id}', [commandesController::class, 'addCart']);

    Route::get('/panier', [commandesController::class, 'recupererContenuPanier']);

    Route::get('/nombre-commandes-en-attente', [commandesController::class, 'nombreCommandesEnAttente']);

    Route::get('/{idCategorie}/produits', [produitController::class, 'liste_produits_par_categorie']);
   
    Route::get('/total-produit', [produitController::class, 'nbrTotalProduits']);

    Route::get('/total-categorie', [produitController::class, 'nbrTotalCatgories']);

    Route::get('/total-categorie', [produitController::class, 'nbrTotalCommandes']);
    
                    //FAVORIS

    Route::get('/ajouter-favoris/{id}', [produitController::class, 'ajouterAuxFavoris']);

    Route::get('/supprimer-favoris/{id}', [produitController::class, 'supprimerUnFavoris']);

    Route::get('/favoris', [produitController::class, 'showFavoris']);
           
    

                    //CHIFFRES DAFFAIRES
    Route::get('/chiffre-affaires', [chiffreAffaireController::class, 'calculerChiffreAffaires']);

    Route::get('/chiffre-affaires/mois-en-cours', [chiffreAffaireController::class, 'calculerChiffreAffairesMoisEnCours']);

           
           //
  //   Route::get('/ajouter-panier/{id}', [commandesController::class, 'addCart']);

     
 //    Route::get('/panier', [commandesController::class, 'recupererContenuPanier']);
});

 


// Route::middleware(['CheckAccess::class'])->group(function () { 

    // Route::get('/admins', [AdminController::class, 'index']); 

    // Route::post('/ajouter-admin', [AdminController::class, 'ajouterAdmin']); 

    // Route::get  ('/supprimer-admin/{id}', [AdminController::class, 'delete']); 

    // Route::post('/modifier-admin/{id}', [AdminController::class, 'update']);

// });

// Route::get('/supprimer-admin/{id}' , function(){

// })->middleware(CheckAccess::class);




                                    //CHIFFRE D'AFFAIRE


Route::group(['middleware' => ['auth', 'superadmin']], function () {

    Route::get('/admins', [AdminController::class, 'index']); 

    Route::post('/ajouter-admin', [AdminController::class, 'ajouterAdmin']); 

    Route::get  ('/supprimer-admin/{id}', [AdminController::class, 'delete']); 

    Route::post('/modifier-admin/{id}', [AdminController::class, 'update']);
 });


















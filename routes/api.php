<?php

use App\Http\Controllers\Authentification;
use App\Http\Controllers\produitController;
use App\Http\Controllers\categorieController;
use App\Http\Controllers\commandesController;
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

Route::post('sendMailPasswordForgot', [Authentification::class, 'sendMailPasswordForgot']);

Route::get('/verify-email/{id}/', [Authentification::class, 'verify'])->name('verification.verify');
// Route::get('/logout', function(){ echo "e";});

Route::middleware(['auth:sanctum'])->group(function () {   

            //PRODUITS
    Route::get('/produits', [produitController::class, 'index']); 

    Route::post('/ajouter-produit', [produitController::class, 'store']); 

    Route::get('/produit/{id}', [produitController::class, 'show']); 

    Route::post('/modifier-produit', [produitController::class, 'update']); 

    Route::get  ('/supprimer-produit/{id}', [produitController::class, 'delete']); 
    
    Route::get('/{categorie}/produits', [produitController::class, 'indexe']);

    
            //CATEGORIES
    Route::get('/categories', [categorieController::class, 'index']);

    Route::post('/ajouter-categorie', [categorieController::class, 'store']); 

    Route::post('/modifier-categorie/{id}', [categorieController::class, 'update']);

    Route::get('/categories/{id}', [categorieController::class, 'show']);

    Route::get('/supprimer-categorie/{id}', [categorieController::class, 'delete']);


            ///COMMANDES
     Route::get('/ajouter-panier/{id}', [commandesController::class, 'addCart']);

     
     Route::get('/panier', [commandesController::class, 'recupererContenuPanier']);
});

Route::middleware(['auth:sanctum'])->group(function () { 

    Route::get('/{idCategorie}/produits', [produitController::class, 'liste_produits_par_categorie']);

    Route::get('/ajouter-favoris/{id}', [produitController::class, 'ajouterAuxFavoris']);

    Route::get('/supprimer-favoris/{id}', [produitController::class, 'supprimerUnFavoris']);

    Route::get('/favoris', [produitController::class, 'showFavoris']);

});












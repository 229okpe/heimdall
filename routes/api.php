<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Authentification;
use App\Http\Controllers\produitController;
use App\Http\Controllers\categorieController;
use App\Http\Controllers\chiffreAffaireController;
use App\Http\Controllers\CodePromoController;
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
Route::get('/payer', [commandesController::class, 'payer']);

Route::post('/register', [Authentification::class, 'register']); 

Route::post('/login', [Authentification::class, 'login']); 

Route::get('/logout', [Authentification::class, 'logout']);

Route::post('/sendMailPasswordForgot', [Authentification::class, 'sendMailPasswordForgot']);

Route::get('/verify-email/{id}/', [Authentification::class, 'verify'])->name('verification.verify');

Route::post('/contact', [Authentification::class, 'sendform'] );

Route::get('/produits-sansconnexion', [produitController::class, 'indexwithoutlog']); 

Route::post('/rechercher-produit', [produitController::class, 'rechercherProduits']);

Route::get('/produit-sansconnexion/{id}', [produitController::class, 'showWithoutlog']); 

Route::post('/update-password', [Authentification::class, 'passwordReset']);

Route::middleware(['auth'])->group(function () {   
                //USER
    Route::get('/current-user', [Authentification::class, 'currentUser']);          

    Route::post('/update-user/{id}', [Authentification::class, 'update']);


    Route::post('/update-password', [Authentification::class, 'modifyPassword']);
            //PRODUITS
    Route::get('/produits', [produitController::class, 'index']); 

    Route::post('/ajouter-produit', [produitController::class, 'store']); 

    Route::get('/produit/{id}', [produitController::class, 'show']); 

    Route::post('/modifier-produit/{id}', [produitController::class, 'update']); 

    Route::get  ('/supprimer-produit/{id}', [produitController::class, 'delete']); 
    
    Route::get('/{categorie}/produits', [produitController::class, 'indexe']);

  
    Route::get('/total-produit', [produitController::class, 'nbrTotalProduits']);

    
            //CATEGORIES
    Route::get('/categories', [categorieController::class, 'index']);

    Route::post('/ajouter-categorie', [categorieController::class, 'store']); 

    Route::post('/modifier-categorie/{id}', [categorieController::class, 'update']);

    Route::get('/categories/{id}', [categorieController::class, 'show']);

    Route::get('/supprimer-categorie/{id}', [categorieController::class, 'delete']);

    Route::get('/total-categorie', [categorieController::class, 'nbrTotalCatgories']);


            ///COMMANDES
    Route::get('/ajouter-panier/{id}', [commandesController::class, 'addCart']);

    Route::get('/supprimer-panier/{rowId}', [commandesController::class, 'removeCart']);

    Route::get('/contenu-panier', [commandesController::class, 'recupererContenuPanier']);

 
    //Route::get('/total-panier', [commandesController::class, 'totalPanier']);

    Route::get('/payer-abonnement/{idProduit?}', [commandesController::class, 'payerAbonnement']);
    
    Route::post('/enregistrer-paiement', [commandesController::class, 'savePayment']);

    Route::get('/nombre-commandes-en-attente', [commandesController::class, 'nombreCommandesEnAttente']);

    Route::get('/{idCategorie}/produits', [produitController::class, 'liste_produits_par_categorie']);
    
    Route::get('/commandes', [commandesController::class, 'index']);

    Route::get('/mes-commandes', [commandesController::class, 'mesCommandes']);

    Route::get('/commande/{id}', [commandesController::class, 'show']);

    Route::get('/commandes-en-attente', [commandesController::class, 'nombreCommandesEnAttente']);
   
    Route::get('/total-commandes', [commandesController::class, 'nbrTotalCommandes']);

    Route::post('/valider-commande', [commandesController::class, 'validateOrder']);

    Route::post('/modifier-quantite-panier/{id}', 'PanierController@modifierQuantite')->name('panier.modifier-quantite');

    
                    //FAVORIS

    Route::get('/ajouter-favoris/{id}', [produitController::class, 'ajouterAuxFavoris']);

    Route::get('/supprimer-favoris/{id}', [produitController::class, 'supprimerUnFavoris']);

    Route::get('/favoris', [produitController::class, 'showFavoris']);
           

                        //CODES PROMOS
 Route::post('/ajouter-codepromo', [CodePromoController::class, 'store']);
    
 Route::get('/codepromo/{id}', [CodePromoController::class, 'show']);

 Route::get('/supprimer-codepromo/{id}', [CodePromoController::class, 'delete']);
                    //CHIFFRES DAFFAIRES
    Route::get('/chiffre-affaires', [chiffreAffaireController::class, 'calculerChiffreAffaires']);

    Route::get('/chiffre-affaires/mois-en-cours', [chiffreAffaireController::class, 'calculerChiffreAffairesMoisEnCours']);

           
           //
  //   Route::get('/ajouter-panier/{id}', [commandesController::class, 'addCart']);

     
 //    Route::get('/panier', [commandesController::class, 'recupererContenuPanier']);
});

 


Route::middleware(['auth, superAdmin'])->group(function () { 

     Route::get('/admins', [AdminController::class, 'index']); 

     Route::post('/ajouter-admin', [AdminController::class, 'ajouterAdmin']); 

     Route::get  ('/supprimer-admin/{id}', [AdminController::class, 'delete']); 

     Route::post('/modifier-admin/{id}', [AdminController::class, 'update']);

});




                                    //CHIFFRE D'AFFAIRE


Route::group(['middleware' => ['auth', 'superadmin']], function () {

    Route::get('/admins', [AdminController::class, 'index']); 

    Route::post('/ajouter-admin', [AdminController::class, 'ajouterAdmin']); 

    Route::get  ('/supprimer-admin/{id}', [AdminController::class, 'delete']); 

    Route::post('/modifier-admin/{id}', [AdminController::class, 'update']);
 });


















<?php

namespace App\Models;

use App\Models\abonnements;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Produit extends Model
{
    use HasFactory;

    protected $table = 'produits';

    protected $fillable = [
        'nom',
        'description',
        'prix',
        'categorie_id',
        'image',
        'statut'
       
    ]; 

    public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'categorie_id', 'id');
    }

    public function abonnements()
    {
        return $this->hasMany(abonnements::class,'abonnement_id');
    }
    
}

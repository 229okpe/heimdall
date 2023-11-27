<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class abonnements extends Model
{
    use HasFactory;

    
    protected $fillable = [
        'details',
        'produit_id',
        'nomClient',
        'emailClient',
        'dateExpiration',
        'attribue'
       
    ]; 

    public function produits()
    {
        return $this->belongsTo(Produit::class, 'produit_id', 'id');
    }

}

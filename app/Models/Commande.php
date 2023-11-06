<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'produit_id',
        'order_id',
        'date_created',
        'status',
        'box',
        'codePromo',
        'quantite',
        'prix_total',
        'user_name'
    ];
}

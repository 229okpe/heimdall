<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favoris extends Model
{
    use HasFactory;

    protected $table = 'favoris';
    
    protected $fillable = [
        'user_id',
        'produit_id',
    ];


    public function produit(){

        return $this->belongsTo(Produit::class, 'produit_id', 'id');
    }
}

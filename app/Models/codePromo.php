<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class codePromo extends Model
{
    use HasFactory;
    protected $fillable = [
        'intitule',
        'valeur',
        'nombreUtilisation'
    ];
}

<?php

namespace Database\Seeders;

use App\Models\Categorie;
use App\Models\Produit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProduitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
     {   
                $cat =Categorie::create([
                'nom' => 'Chaussure ',
                'description' => 'Description ',
            ]);

                for ($i = 1; $i <= 10; $i++) {
                    Produit::create([
                        
                        'nom' => 'Chaussure ' . $i,
                        'description' => 'Description ' . $i,
                        'prix' => 200 * $i,
                        'statut' =>"Disponible",
                        'categorie_id' => $cat->id,
                        'image' => 'https://www.google.com/url?sa=i&url=https%3A%2F%2Fwww.shutterstock.com%2Ffr%2Fcategory%2Fnature&psig=AOvVaw1FVCJsazZT3VTWU_-pIRN8&ust=1691492161624000&source=images&cd=vfe&opi=89978449&ved=0CBEQjRxqFwoTCPjH8L2xyoADFQAAAAAdAAAAABAE'
                    ]);
                } 
    } 
}


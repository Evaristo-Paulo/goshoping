<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    /* Agrupar produtos com suas respectivas fotos (por ID produto)*/
    public function stocksGroupByID($dados)
    {

        $main = [];
        foreach ($dados as $dado) {
            $aux = [];
            $qty = 0;  // acúmulo de stock de cada produto 
            foreach ($dados as $copy) {
                if ($dado->product_id == $copy->product_id) {
                    /* Adicionar todos os fornecedores pertencente ao mesmo produto (Mesmo ID)*/
                    array_push($aux, $copy->collaborator);
                    $qty += $copy->qty;
                }
            }

            $product = [
                'id' => $dado->id,
                'collaborator_id' => $dado->collaborator_id,
                'collaborator' => $dado->collaborator,
                'product_id' => $dado->product_id,
                'product' => $dado->product,
                'category' => $dado->category,
                'qty' => $qty,
                'collaborators' => $aux, // Adicionar todas as fotos salvas 
            ];
            array_push($main, $product);
        }

        $products = array();

        if (count($main) == 0) {
            return $products;
        }


        $function = new Product();
        do {
            $first_element = $main[$function->get_first_occurence($main)];

            foreach ($main as $key => $dado) {
                if ($first_element != null) {
                    if ($first_element['product_id'] == $dado['product_id']) {
                        unset($main[$key]);
                    }
                }
            }
            Array_push($products, $first_element);
        } while (count($main) > 0);

        return $products;
    }

    public function stocks (){
        $stockq = \DB::table('stocks')
                ->join('products', function ($join) {
                    $join->on('products.id', '=', 'stocks.product_id')
                        ->where([['products.active', '=', 1]]);
                })
                ->join('categories', function ($join) {
                    $join->on('categories.id', '=', 'products.category_id')
                        ->where([['categories.active', '=', 1]]);
                })
                ->join('collaborators', function ($join) {
                    $join->on('collaborators.id', '=', 'stocks.collaborator_id')
                        ->where([['collaborators.active', '=', 1]]);
                })
                ->select('stocks.*', 'products.name as product', 'categories.name as category', 'products.id as product_id', 'collaborators.name as collaborator')
                ->get();

            $stocks = $this->stocksGroupByID($stockq);

            return $stocks;
    }
}

<?php
namespace App\Repositories;

interface ProductRepositoryInterface{
    public function all();

    public function myProducts();

    public function searchByCategory($categoryName);

    public function findById($id);

    public function findByProductName($name);

    public function buy($request, $id);

    public function update($request, $productId);

    public function destroy($productId);
}

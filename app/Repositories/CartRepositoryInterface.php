<?php
namespace App\Repositories;

interface CartRepositoryInterface{
    public function index();

    public function findById($itemId);

    public function addToCart($request, $productId);

    public function buyCartItem($itemId);

    public function buyCart();

    public function update($request, $itemId);

    public function deleteItem($itemId);

    public function emptyCart();
}

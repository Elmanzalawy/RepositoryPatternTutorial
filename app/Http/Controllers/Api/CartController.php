<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\CartRepositoryInterface;


class CartController extends Controller
{

    private $cartRepository;

    public function __construct(CartRepositoryInterface $cartRepository){
        return $this->cartRepository = $cartRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $cartItems = $this->cartRepository->index();
        return $cartItems;
        //get user's cart items
        // $user = User::find(auth()->user()->id);
        // $cartItems = User::find($user->id)->carts->toArray();
        // if(!$cartItems){    //if no cart items were found
        //     return response()->json(['message'=>__('messages.noCartItems')], 404);
        // }
        // return response()->json(['cart_items'=>$cartItems], 200);

    }

    public function show($itemId){
        return $this->cartRepository->findById($itemId);
    }

    public function addToCart(Request $request, $productId){
        return $this->cartRepository->addToCart($request, $productId);
    }

    public function update(Request $request, $id){

    }

    public function destroy($itemId){
        return $this->cartRepository->deleteItem($itemId);
        
    }


    public function buyCartItem($itemId){
        return $this->cartRepository->buyCartItem($itemId);
    }


    public function buyCart(){
        return $this->cartRepository->buyCart();
    }

    public function emptyCart(){
        return $this->cartRepository->emptyCart();
    }



}

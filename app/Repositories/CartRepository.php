<?php
namespace App\Repositories;
use App\History;
use App\Cart;
use App\Product;
use App\User;
use Illuminate\Http\Request;
use App\Repositories\CartRepositoryInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CartRepository implements CartRepositoryInterface{
    
    //get all cart items
    public function index(){
        $cartItems = Cart::where('user_id', auth()->user()->id)
        ->orderBy('created_at')
        ->get();
        if(count($cartItems) == 0){
            return response()->json(['message'=>__('messages.cartItemsNotFound')], 404);
        }
        return $cartItems;

        // return Cart::where('user_id', auth()->user()->id)
        // ->orderBy('created_at')
        // ->get() ?? response()->json(['message'=>__('messages.cartItemsNotFound')], 404);
        
        
    }


    public function findById($itemId){
        $cartItem =  Cart::where('id', $itemId)->first();
        if(!$cartItem){
            return response()->json(['message'=> __('messages.cartItemNotFound')], 404);
        }
        if($cartItem->user_id != auth()->user()->id){
            return response()->json(['message'=>__('messages.unauthorized')], 403);
        }
        return $cartItem;

    }

    public function addToCart($request, $productId){
        $product = Product::find($productId);
        if(!$product){
            return response()->json(['message'=>__('messages.productNotFound')], 404);
        }
        if($product->quantity == 0){
            return response()->json(['message'=>__('messages.productNotAvailable')], 404);
        }
        $validator = Validator::make($request->all(), [
            'quantity' => "integer|required|min:1|max:$product->quantity",
        ]);
        if($validator->fails()){
            return response(['errors'=>$validator->errors()->all()], 422);
        }
        $cartItem = new Cart;
        $cartItem->user_id = auth()->user()->id;
        $cartItem->product_id = $product->id;
        $cartItem->price = $product->price;
        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        $product->quantity -= $request->quantity;
        $product->save();

        return response()->json(['message'=>__('messages.cartItemAdded'), 'cart-item'=>$cartItem], 200);
    }



    public function buyCartItem($itemId){
        $cartItem = Cart::where('id', $itemId)->first();
        
        if(!$cartItem){
            return response()->json(['message'=> __('messages.cartItemNotFound')], 404);
        }
        $product = Product::find($cartItem->product_id);

        if(!$product){
            return response()->json(['message'=> __('messages.productNotFound')], 404);
        }
        $user = User::find(auth()->user()->id);
        $seller = $product->user;
        $orderPrice = 0;

        if($product->quantity == 0){
            return response()->json(['message'=>__('messages.productNotAvailable')], 404);
        }
        $orderPrice = $cartItem->quantity * $product->price;
        if($user->wallet < $orderPrice){
            return response()->json(['message'=>"not enough wallet credits ($user->wallet) to complete purchase ($orderPrice needed)."], 404);
        }

        $product->quantity -= $cartItem->quantity;
        $user->wallet -= $orderPrice;
        $seller->wallet += $orderPrice;
        $product->save();
        $user->save();
        $seller->save();
        
        $history = new History();
        $history->user_id = $user->id;
        $history->product_id = $product->id;
        $history->quantity = $cartItem->quantity;
        $history->unit_price = $product->price;
        $history->total_price = $orderPrice;
        $history->product_name = $product->name;
        $history->save();

        $cartItem->delete();
        return response()->json(['message'=>"Successfully purchased $cartItem->quantity units of $product->name for $orderPrice", 'history'=>$history], 200);
        
    }

    public function buyCart(){
        $cartItems = Cart::where('user_id', auth()->user()->id)->get();
        if(!$cartItems){
            return response()->json(['message'=>__('messages.cartItemsNotFound')], 404);
        }

        $user = User::find(auth()->user()->id);
        $totalPrice = 0;
        foreach($cartItems as $cartItem){
            $product = Product::find($cartItem->product_id);
            if(!$product){
                return response()->json(['message'=>__('messages.productNotFound')], 404);
            }
            $seller = $product->user;

            if($product->quantity == 0){
                return response()->json(['message'=>__('messages.productNotAvailable')], 404);
            }
            $orderPrice = $cartItem->quantity * $product->price;
            if($user->wallet < $orderPrice){
                return response()->json(['message'=>"not enough wallet credits ($user->wallet) to complete purchase ($orderPrice needed)."], 404);
            }

            $totalPrice += $product->price * $cartItem->quantity;

        }
        
        if($user->wallet < $totalPrice){
            return response()->json(['message'=>"not enough wallet credits ($user->wallet) to complete purchase ($totalPrice needed)."], 404);
        }

        $historyArray = array();
        foreach($cartItems as $cartItem){

            $product = Product::find($cartItem->product_id);
            $orderPrice = $cartItem->quantity * $product->price;

            $product->quantity -= $cartItem->quantity;
            $user->wallet -= $orderPrice;
            $seller->wallet += $orderPrice;
            $product->save();
            $user->save();
            $seller->save();

            $history = new History();
            $history->user_id = $user->id;
            $history->product_id = $product->id;
            $history->quantity = $cartItem->quantity;
            $history->unit_price = $product->price;
            $history->total_price = $orderPrice;
            $history->product_name = $product->name;
            $history->save();
            // array_push();
            array_push($historyArray, $history->toarray());

            $cartItem->delete();
        }
        return response()->json(['message'=>"Successfully purchased cart items for $totalPrice", "history"=>$historyArray], 200);


    }

    public function update($request, $productId){
        $product = Product::find($productId);
        if(!$product){
            return response()->json(['message'=> __('messages.productNotFound')], 404);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'string|unique:products|max:255',
            'price' => 'integer|min:1|',
            'quantity' => 'integer|min:1|',
            // 'category' => Rule::in(['Electronics','Fashion','Home Appliances','Jewelry','Health and Beauty','Sports and Fitness']),
        ]);
        if($validator->fails()){
            return response (['errors'=>$validator->errors()->all()], 422);
        }

        $product->update(request()->only(['name','quantity','price']));
        return response()->json(['message'=>__('messages.productUpdated'), 'product'=>$product], 200);
        
    }

    public function deleteItem($itemId){
        $cartItem = Cart::find($itemId);
        if(!$cartItem){
            return response()->json(['message'=>__('messages.cartItemNotFound')], 404);
        }
        $product = Product::find($cartItem->product_id);
        if(!$product){
            return response()->json(['message'=>__('messages.productNotFound')], 404);
        }
        if($cartItem->user_id != auth()->user()->id){
            return response()->json(['messages'=>__('messages.unauthorized')]);
        }
        $product->quantity += $cartItem->quantity;
        $product->save();
        $cartItem->delete();
        return response()->json(['message'=>__('messages.cartItemDeleted')], 200);  
      }

      public function emptyCart(){
        // User::find(auth()->user()->id)->carts;
        $cartItems =User::find(auth()->user()->id)->carts;
        if(!$cartItems){
            return response()->json(['message'=>__('messages.cartItemsNotFound')], 404);
        }
        foreach($cartItems as $cartItem){
            $product = Product::where('id', $cartItem->product_id)->first();
            $product->quantity += $cartItem->quantity;
            $product->save();
            $cartItem->delete();
        }
        return response()->json(['message'=>__('messages.cartEmptySuccess')], 200);
      }
}
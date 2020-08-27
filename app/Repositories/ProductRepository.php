<?php
namespace App\Repositories;
use App\Product;
use App\User;
use Illuminate\Http\Request;
use App\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductRepository implements ProductRepositoryInterface{
    
    //get all products
    public function all(){
        $products = Product::orderBy('name')
        ->orderBy('created_at')
        ->with('user')
        ->get();
        if(count($products)==0){
            return response()->json(['message'=>__('messages.productsNotFound')], 404);
        }
        return $products;
        
    }

    //get all products from this user
    public function myProducts(){
        return Product::where('seller_id', auth()->user()->id)
        ->orderBy('name')
        ->orderBy('created_at')
        -> get() ?? response()->json(['message'=>__('messages.productsNotFound')], 404);
    }

    public function findById($productId){
        return Product::where('id', $productId)
        ->with('user')
        ->first() ?? response()->json(['message'=> __('messages.productNotFound')], 404);

    }

    public function searchByCategory($categoryName){
        return Product::where('category',$categoryName)
        ->get() ?? response()->json(['message'=>__('messages.productsNotFound')], 404);
    }

    public function findByProductName($productName){
        return Product::where('name', $productName)
        ->with('user')
        ->first() ?? response()->json(['message'=> __('messages.productNotFound')], 404);
    }


    public function buy($request, $productId){
        $product = Product::where('id', $productId)->first();
        
        if(!$product){
            return response()->json(['message'=> __('messages.productNotFound')], 404);
        }
        
        $user = User::find(auth()->user()->id);
        $seller = $product->user;
        $orderPrice = 0;

        if($product->quantity == 0){
            return response()->json(['message'=>__('messages.productNotAvailable')], 404);
        }
        $validator = Validator::make($request->all(), [
            'quantity' => 'integer|required|min:1|max:'.$product->quantity,
        ]);
        if($validator->fails()){
            return response (['errors'=>$validator->errors()->all()], 422);
        }
        $orderPrice = $request->quantity * $product->price;
        if($user->wallet < $orderPrice){
            return response()->json(['message'=>"not enough wallet credits ($user->wallet) to complete purchase ($orderPrice needed)."], 404);
        }

        $product->quantity -= $request->quantity;
        $user->wallet -= $orderPrice;
        $seller->wallet += $orderPrice;
        $product->save();
        $user->save();
        $seller->save();
        return response()->json(['message'=>"Successfully purchased $request->quantity units of $product->name for $orderPrice"], 200);
        
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

    public function destroy($productId){
        $product = Product::find($productId);
        if(!$product){
            return response()->json(['message'=>__('messages.productNotFound')], 404);
        }
        if($product->seller_id == auth()->user()->id || auth()->user()->role==1){
            $product->delete();
            return response()->json(['message'=>__('messages.productDeleted')]);
        }
        //if user is unauthorized
        return response()->json(['messages'=>__('messages.unauthorized')]);
    }
}
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //

    //allowing mass assignment
    protected $guarded =[];

    public function user(){
        return $this->belongsTo('App\User', 'seller_id');
    }
    public function category(){
        return $this->belongsTo('App\Category','product_id');
    }

    public function cart(){
        return $this->belongsTo('App\Cart');
    }
    public function history(){
        return $this->belongsTo('App\History');
    }

    public function format(){
        return [
            'name'=>$this->name,
            'id' => $this->id,
            'seller_id'=>$this->seller_id,
            'seller_name'=>$this->seller_name,
            'quantity'=>$this->quantity,
            'price' => $this->price,
            'category'=>$this->category,
            'last_updated'=>$this->updated_at->diffForHumans(),
        ];
    }
}

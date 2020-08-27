<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{

    // allow all the fields from to be mass-assigned
    protected $guarded = [];

    public function format(){
        return [
            'customer_id'=>$this->id,
            'name'=>$this->name,
            'created_by'=>$this->user->email,
            'last_updated'=>$this->updated_at->diffForHumans(),
        ];
    }

    public function user(){
       return $this->belongsTo(User::class);
    }
}

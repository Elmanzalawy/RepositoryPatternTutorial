<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use App\Customer;
use App\User;
use Faker\Generator as Faker;

$factory->define(Customer::class, function (Faker $faker) {
    $usersCount = count(User::get());
    $randUser = User::find(rand(1,$usersCount));
    return [
        'user_id'=> $randUser->id,
        'name'=>$randUser->name,
        'active' => random_int(0,1),
    ];
});

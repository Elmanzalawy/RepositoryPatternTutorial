<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->integer('seller_id')->unsigned();
            $table->string('seller_name');
            $table->string('name')->unique();
            // $table->text('description');
            $table->integer('quantity')->unsigned();
            // $table->string('image');
            $table->integer('price');
            $table->string('category');
            // $table->float('rating')->default(0);
            $table->timestamps();

            // $table->foreign('seller_id')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('seller_name')->references('name')->on('users')->onDelete('cascade');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}

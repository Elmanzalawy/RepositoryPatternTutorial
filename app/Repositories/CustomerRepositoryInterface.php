<?php
namespace App\Repositories;

interface CustomerRepositoryInterface{
    public function all();

    public function findById($customerId);

    public function findByUsername();

    public function update($customerId);

    public function destroy($customerId);
}

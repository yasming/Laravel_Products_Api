<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Product;
class ProductService
{
    private $repository;

    public function __construct(ProductRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAll() : Collection
    {
        return $this->repository->all();
    }

    public function delete(Product $product) : bool
    {
        return $this->repository->delete($product);
    }
}
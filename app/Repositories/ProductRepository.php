<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository
{
    private $model;

    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    public function all() : Collection
    {
        return $this->model->with('category')->get();
    }

    public function create(array $data) : Product
    {
        return $this->model->create($data);
    }

    public function update(Product $model, array $data) : bool
    {
        return $model->update($data);
    }

    public function delete(Product $product) : bool
    {
        return $product->delete();
    }
}
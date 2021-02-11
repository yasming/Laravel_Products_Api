<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\Product\ProductCollection;
use App\Http\Resources\Product\ProductResource;
use App\Services\ProductService;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use App\Models\Product;

class ProductController extends Controller
{
    private $service;

    public function __construct(ProductService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return response()->json(['products' => new ProductCollection($this->service->getAll())], HttpResponse::HTTP_OK);
    }

    public function store(ProductRequest $request)
    {
        return response()->json(new ProductResource($this->service->create($request)), HttpResponse::HTTP_CREATED);
    }

    public function update(ProductRequest $request, Product $product)
    {
        if($this->service->update($product, $request)) {
            return response()->json(new ProductResource($product),HttpResponse::HTTP_OK);
        }
        return response()->json(['message' => __('messages.error_updating_record')], HttpResponse::HTTP_INTERNAL_SERVER_ERROR); 
    }

    public function destroy(Product $product)
    {
        if($this->service->delete($product)) {
            return response()->json([], HttpResponse::HTTP_NO_CONTENT);
        };
        return response()->json(['message' => __('messages.error_deleting_record')], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
    }
}

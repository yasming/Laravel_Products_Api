<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Product\ProductCollection;
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

    public function destroy(Product $product)
    {
        $this->service->delete($product);
        return response()->json([], HttpResponse::HTTP_NO_CONTENT);
    }
}

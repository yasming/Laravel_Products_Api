<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Product\ProductCollection;
use App\Services\ProductService;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

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
}

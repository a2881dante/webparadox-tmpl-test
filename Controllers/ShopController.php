<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Repositories\Shop\{ProductModelRepository, ProductRepository, PropertyRepository};

class ShopController extends Controller
{
    protected $productRepo;
    protected $propertyRepo;
    protected $modelRepo;

    public function __construct(
        ProductRepository $productRepo,
        PropertyRepository $propertyRepo,
        ProductModelRepository $modelRepo
    ) {
        $this->productRepo = $productRepo;
        $this->propertyRepo = $propertyRepo;
        $this->modelRepo = $modelRepo;
    }
    
    public function index(Request $request)
    {
        $products = $this->productRepo->getByFilter(
            $request->get('categories') ?? [],
            $request->get('properties') ?? []
        );
        $properties = $this->propertyRepo->getAllWithValues();
        return view('front.shop.items', [
            'products' => $products,
            'properties' => $properties,
        ]);
    }

    public function view(string $uri)
    {
        $product = $this->productRepo->findByUri($uri);
        return view('front.shop.item', [
            'product' => $product,
        ]);
    }

    public function getModel(Request $request, $uriProduct)
    {
        $product = $this->productRepo->findByUri($uriProduct);
        return $this->modelRepo
            ->findByProperties($product->id, $request->all());
    }

    public function getProperties($uriProduct)
    {
        $product = $this->productRepo->findByUri($uriProduct);
        return response()
            ->json($this->propertyRepo->getByProduct($product->id));
    }
}

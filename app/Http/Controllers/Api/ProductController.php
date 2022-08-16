<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Resources\ProductResource;

class ProductController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::all();

        return $this->sendResponse(ProductResource::collection($products), 'Products retrieved successfully.');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProductRequest $request)
    {
        $products = $request->all();

        // if (isset($request->validator) && $request->validator->fails()) {
        //     return $this->sendError('Validation Error.', $request->validator->errors()->all());
        // }

        $error = $request->validator->errors()->all();

        $createdProducts = 0;
        foreach($products as $key => $product) {
            if (empty($product['name']) || empty($product['image_url']) || empty($product['size_available']) || empty($product['other_image_url'])) {
                continue;
            }
            $data = [];
            $data['name'] = $product['name'];
            $data['image_url'] = $product['image_url'];
            $data['size_available'] = $product['size_available'];
            $data['other_image_url'] = json_encode($product['other_image_url'],JSON_UNESCAPED_SLASHES);

            $createdProducts++;
            Product::create($data);
        }

        return $this->sendResponse($createdProducts.' Products created successfully.', 'Error: '.implode(',',$error), count($error) > 0 ? true : false);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $products
     * @return \Illuminate\Http\Response
     */
    public function show(Product $products)
    {
        $product = Product::find($id);
  
        if (is_null($product)) {
            return $this->sendError('Product not found.');
        }
   
        return $this->sendResponse(new ProductResource($product), 'Product retrieved successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $products
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $products)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $products
     * @return \Illuminate\Http\Response
     */
    public function update(StoreProductRequest $request, Product $products)
    {
        $products->update($request->all());

        if ($request->failed()) {
            return $this->sendError('Validation Error.', $request->errors()->all());
        }

        return $this->sendResponse(new ProductResource($product), 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $products
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $products)
    {
        $products->delete();

        return $this->sendResponse([], 'Product deleted successfully.');
    }
}

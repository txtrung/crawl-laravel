<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Http;
use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::paginate(10);
      
        return view('products.index',compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'image_url' => 'required',
            'name' => 'required',
            'size_available' => 'required',
            'other_image_url' => 'required',
        ]);
      
        Product::create($request->all());
       
        return redirect()->route('products.index')
                        ->with('message','Product created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return view('products.show',compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        return view('products.edit',compact('product'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'image_url' => 'required',
            'name' => 'required',
            'size_available' => 'required',
            'other_image_url' => 'required',
        ]);
      
        $product->update($request->all());
      
        return redirect()->route('products.index')
                        ->with('message','Product updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $product->delete();
       
        return redirect()->route('products.index')
                        ->with('message','Product deleted successfully');
    }

    /**
     * Send products to other sites.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendToSite(Request $request)
    {
        $site = $request->input('site');
        $woocommerce = new Client(
            $site,
            'ck_42b2dcef4556386b591e23a9c74bdb76fae11f2c',
            'cs_ffcb777f77784fe325677c833cd641a00370e5e4',
            [
              'version' => 'wc/v3',
            ]
          );
        $woocommerce->http->setCustomCurlOptions([CURLOPT_TIMEOUT => 0]);
        $woocommerce->http->setCustomCurlOptions([CURLOPT_CONNECTTIMEOUT => 0]);

        $chunkItems = [];
        DB::table('products')->orderBy('id')->chunk(120, function ($products) use($woocommerce) {
            foreach ($products as $product) {
                $images = [];
                foreach (json_decode($product->other_image_url) as $image) {
                    array_push($images, [
                        "src" => $image
                    ]);
                }
                
                // var_dump($images);
                // die();

                $sizes = explode(" ",$product->size_available);

                $chunkItems[] = [
                    "name" => $product->name,
                    "type" => "variable",
                    "description" => "1111",
                    "short_description" => "22222",
                    'categories' => [
                        [
                            'id' => 9
                        ],
                        [
                            'id' => 14
                        ]
                    ],
                    'images' => $images,
                    'attributes' => [
                        [
                            'name' => 'Size',
                            'position' => 0,
                            'visible' => true,
                            'variation' => true,
                            'options' => $sizes
                        ]
                    ]
                ];
            }

            // $response = $woocommerce->post('products', $chunkItems);
            // echo '<pre><code>' . print_r($response, true) . '</code><pre>';
            // var_dump($response);

            try {
                // Array of response results.
                $results = $woocommerce->post('products/batch', ["create" => $chunkItems]);
                // Example: ['customers' => [[ 'id' => 8, 'created_at' => '2015-05-06T17:43:51Z', 'email' => ...
                echo '<pre><code>' . print_r($results, true) . '</code><pre>'; // JSON output.
              
                // Last request data.
                $lastRequest = $woocommerce->http->getRequest();
                echo '<pre><code>' . print_r($lastRequest->getUrl(), true) . '</code><pre>'; // Requested URL (string).
                echo '<pre><code>' .
                  print_r($lastRequest->getMethod(), true) .
                  '</code><pre>'; // Request method (string).
                echo '<pre><code>' .
                  print_r($lastRequest->getParameters(), true) .
                  '</code><pre>'; // Request parameters (array).
                echo '<pre><code>' .
                  print_r($lastRequest->getHeaders(), true) .
                  '</code><pre>'; // Request headers (array).
                echo '<pre><code>' . print_r($lastRequest->getBody(), true) . '</code><pre>'; // Request body (JSON).
              
                // Last response data.
                $lastResponse = $woocommerce->http->getResponse();
                echo '<pre><code>' . print_r($lastResponse->getCode(), true) . '</code><pre>'; // Response code (int).
                echo '<pre><code>' .
                  print_r($lastResponse->getHeaders(), true) .
                  '</code><pre>'; // Response headers (array).
                echo '<pre><code>' . print_r($lastResponse->getBody(), true) . '</code><pre>'; // Response body (JSON).
              } catch (HttpClientException $e) {
                echo '<pre><code>' . print_r($e->getMessage(), true) . '</code><pre>'; // Error message.
                echo '<pre><code>' . print_r($e->getRequest(), true) . '</code><pre>'; // Last request data.
                echo '<pre><code>' . print_r($e->getResponse(), true) . '</code><pre>'; // Last response data.
              }


            return false;
        });

        // var_dump(count($product));
        // die('');

        return redirect()->route('products.index')
                        ->with('message','Product sent successfully');
    }
}

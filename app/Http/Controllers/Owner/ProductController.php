<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Image;
use App\Models\Product;
use App\Models\Owner;
use App\Models\Stock;
use App\Models\Shop;
use Illuminate\Support\Facades\Log;
use Throwable;
use Illuminate\Support\Facades\DB; //queryBuilder


use App\Models\PrimaryCategory;


class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:owners');

        $this->middleware(function($request, $next){
            $id = $request->route()->parameter('product');
            if(!is_null($id)){
            $productsOwnerId = Product::findOrFail($id)->shop->owner->id;
            $productId = (int)$productsOwnerId;
            if($productId !== Auth::id()){
            abort(404);
            }
            }
            return $next($request);
            });

        }


    public function index()
    {
    //    $products = Owner::findOrFail(Auth::id())->shop->product;

        $ownerInfo = Owner::with('shop.product.imageFirst')
        ->where('id', Auth::id())->get();
        // foreach($ownerInfo as $owner){
            // dd($owner->shop->product);
        //     foreach ($owner->shop->product as $product){
        //         dd($product->imageFirst->filename);
        //     }
        // }
        return view ('owner.products.index' , compact('ownerInfo'));
    }

    public function create()
    {
        $shops = Shop::where('owner_id', Auth::id())
        ->select('id', 'name')
        ->get();

        $images = Image::where('owner_id', Auth::id())
        ->select('id','title','filename')
        ->orderBy('updated_at', 'desc')
        ->get();

        $categories = PrimaryCategory::with('secondary')
        ->get();

        return view ('owner.products.create' , compact('shops', 'images', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'information' => 'required|string|max:1000',
            'price' => 'required|integer',
            'sort_order' => 'nullable|integer',
            'quantity' => 'required|integer',
            'shop_id' => 'required|exists:shops,id',
            'category' => 'required|exists:secondary_categories,id',
            // 数あるカテゴリーIDのうちの一つが存在しているかを確認するため、こちらは複数形とする
            'image1' => 'nullable|exists:images,id',
            'image2' => 'nullable|exists:images,id',
            'image3' => 'nullable|exists:images,id',
            'image4' => 'nullable|exists:images,id',
            'is_selling' => 'required'
        ]);

        try {
            DB::transaction(function()use($request){
                // Transactionメソッドを使用している理由として、
                // Productテーブルの中にある情報の入力と、
                // Stockテーブルの中にあるQuantity情報を同時保存する必要があり、
                // それぞれ別テーブルのため、Transactionメソッドを使用している
                $product = Product::create([
                    // （左辺＝キー名）＝MG（マイグレーション）ファイルで定めたカラムを挿入
                    // （右辺＝値）＝上のページのValidationで定めたカラム名を挿入する
                    'name' => $request->name,
                    'information'=>$request->information,
                    'price'=>$request->price,
                    'sort_order'=>$request->sort_order,
                    'shop_id'=>$request->shop_id,
                    'secondary_category_id'=>$request->category,
                    // Key＝MGファイルでは'secondary_category_id'
                    // Value＝Validationでは'category'
                    'image1'=>$request->image1,
                    'image2'=>$request->image2,
                    'image3'=>$request->image3,
                    'image4'=>$request->image4,
                    'is_selling'=>$request->is_selling
        ]);
            Stock::create([
                'product_id'=>$product->id,
                'type'=>1,
                // 入庫在庫を増やすカラムとしてtypeを入れる
                'quantity'=>$request->quantity
            ]);
            }, 2);
        } catch (Throwable $e) {
            Log::error($e);
            throw $e;
        }



        return redirect()
        ->route('owner.products.index')
        ->with(['message'=> '商品登録が完了しました。' , 'status'=>'info']);

        // $product = Product::create([
        //     'name' => $request->name,
        //     'information'=>$request->information,
        //     'price'=>$request->price,
        //     'sort_order'=>$request->sort_order,
        //     'quantity'=>$request->quantity,
        //     'shop_id'=>$request->name,
        //     'image1'=>$request->image1,
        //     'image2'=>$request->image2,
        //     'image3'=>$request->image3,
        //     'image4'=>$request->image4
        // ]);
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $quantity = Stock::where('product_id', $product->product_id)
                ->sum('quantity');

        $shops = Shop::where('owner_id', Auth::id())
        ->select('id', 'name')
        ->get();

        $images = Image::where('owner_id', Auth::id())
        ->select('id','title','filename')
        ->orderBy('updated_at', 'desc')
        ->get();

        $categories = PrimaryCategory::with('secondary')
        ->get();


        return view ('owner.products.edit' , compact('product', 'quantity', 'shops', 'images', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

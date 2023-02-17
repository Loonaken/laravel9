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
use App\Http\Requests\ProductRequest;


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

    public function store(ProductRequest $request)
    // Validation を別ファイルで管理しているため、それを使用するために
    // 冒頭に use でPath指定をし、
    // このStoreメソッドでProductRequestで用いることを宣言する
    {

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

    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $quantity = Stock::where('product_id', $product->id)
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
    public function update(ProductRequest  $request, $id)
    {
        $request->validate([
            'current_quantity'=>'required|integer'
        ]);

        $product = Product::findOrFail($id);
        $quantity = Stock::where('product_id', $product->id)
        // Where の引数で$product->product_idと絶対書かない
                ->sum('quantity');

        if($request->current_quantity !== $quantity){
            $id = $request->route()->parameter('product');
            return
            redirect()
            ->route('owner.products.edit' , ['product'=> $id])
            ->with(['message'=> '在庫数が変更されています。再度確認してください。' , 'status'=>'error']);

            // Else文を書いているにも関わらず、ずっとIf文内の処理しか
            // 実行されない場合、If文の条件内の変数に問題があることを確認しなければならない
            // そして使われている変数がどこから派生しているのかを辿る！
            // このIf文であれば、$quantityに当初問題が発生しており、
            // $quantityの変数を定義していたIf文前の
            // $quantity = Stock::where('product_id', $product->id)
            // メソッド定義が当初おかしかった

        } else {
            try {
                DB::transaction(function()use($request, $product){
                    // 変数の＄productをUse引数に追加する

                    $product->name = $request->name;
                    $product->information = $request->information;
                    $product->price = $request->price;
                    $product->sort_order = $request->sort_order;
                    $product->shop_id = $request->shop_id;
                    $product->secondary_category_id = $request->category;
                    // ↑の行の挿入する変数は絶対にCategoryと間違えないこと
                    $product->image1 = $request->image1;
                    $product->image2 = $request->image2;
                    $product->image3 = $request->image3;
                    $product->image4 = $request->image4;
                    $product->is_selling = $request->is_selling;

                    $product->save();

                    if($request->type === \Constant::PRODUCT_LIST['add']){
                        $newQuantity = $request->quantity;
                    }
                    if($request->type === \Constant::PRODUCT_LIST['reduce']){
                        $newQuantity = $request->quantity * -1;
                    }

                Stock::create([
                    // 毎回新しくデータ作成をする
                    'product_id' => $product->id,
                    'type'=> $request->type,
                    'quantity'=> $newQuantity
                ]);
                }, 2);
            } catch (Throwable $e) {
                Log::error($e);
                throw $e;
            }



            return redirect()
            ->route('owner.products.index')
            ->with(['message'=> '商品情報を更新しました。' , 'status'=>'info']);
        }
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id)->delete();

            return redirect()
            ->route('owner.products.index')
            ->with(['message'=>'商品を削除しました。' , 'status'=>'error'],);
    }
}

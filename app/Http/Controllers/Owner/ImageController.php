<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Image;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UploadImageRequest;
use App\Services\ImageService;
use Ramsey\Uuid\Rfc4122\NilUuid;

class ImageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:owners');

        $this->middleware(function($request, $next){
            $id = $request->route()->parameter('image');
            if(!is_null($id)){
            $imagesOwnerId = Image::findOrFail($id)->owner->id;
            $imageId = (int)$imagesOwnerId;
            if($imageId !== Auth::id()){
            abort(404);
            }
            }
            return $next($request);
            });

        }

    public function index()
    {
        $images = Image::where('owner_id', Auth::id())
        // ログインしているオーナーIDを取得したい
        ->orderBy('updated_at','desc')
        ->paginate(20);

        return view('owner.images.index', compact('images'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view ('owner.images.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UploadImageRequest $request)
    {
        $imageFiles = $request->file('files');
        if(!is_null($imageFiles)){
            foreach($imageFiles as $imageFile){
            $fileNameToStore = ImageService::upload($imageFile, 'products');
            Image::create([
                'owner_id' => Auth::id(),
                'filename' => $fileNameToStore
            ]);

            }
        }
        return redirect()->route('owner.images.index')
            ->with(['message'=> '画像登録が実施しました。' , 'status'=>'info']);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $image = Image::findOrFail($id);
            return view('owner.images.edit', compact('image'));
    }

    public function update(UploadImageRequest $request, $id)
    {

        $request->validate([
            'title'=>'string|max:50',
        ]);

        $image = Image::findOrFail($id);
        $image->title = $request->title;
        $image->save();

        return redirect()->route('owner.images.index')
        ->with(['message'=> '画像情報を更新しました。' , 'status'=>'info']);

        }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        {
            $image = Image::findOrFail($id);

            $imageInProducts = Product::where('image1', $image->id)
            ->orWhere('image2', $image->id)
            ->orWhere('image3', $image->id)
            ->orWhere('image4', $image->id)
            ->get();

            if($imageInProducts){
                $imageInProducts->each(function($product) use($image){
                    if($product->image1 === $image->id){
                        $product->image1 = null;
                        $product->save();
                    }
                    if($product->image2 === $image->id){
                        $product->image2 = null;
                        $product->save();
                    }
                    if($product->image3 === $image->id){
                        $product->image3 = null;
                        $product->save();
                    }
                    if($product->image4 === $image->id){
                        $product->image4 = null;
                        $product->save();
                    }
                });
            }

            $filePath = 'public/products/' . $image->filename;
            // storageにある画像データを削除する必要があるため、パスを指定する。
            // 画像はImageであれば、Productsの配下にあるため、
            // 第一引数にPublic/products/ に . で繋ぎ、 filenameでパスを完成させる
            if(Storage::exists($filePath)){
                Storage::delete($filePath);
            }
            Image::findOrFail($id)->delete();

            return redirect()
            ->route('owner.images.index')
            ->with(['message'=>'画像を削除しました。' , 'status'=>'error'],);
        }
    }
}

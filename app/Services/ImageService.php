<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use InterventionImage;

class ImageService
{
  public static function upload($imageFile, $folderName){
    // folderNameの引数はStorageに保存する場所として、
    // shopsやproductsのフォルダにも入れる汎用的な仕組みにしたいので、
    // 入れている
    // そしてpublic/の後に . で繋げて$folderNameと入れる

    $fileName = uniqid(rand().'_');
    // 画像の名前パスが重ならないように24文字のランダムな数字を入れる関数である
    $extension = $imageFile->extension();
    // ファイルのコンテンツを元に拡張子を推測

    $fileNameToStore = $fileName. '.' . $extension;
    $resizedImage = InterventionImage::make($imageFile)->resize(1920,1080)->encode();
    // 画像の縦横比を調整
    Storage::put('public/' . $folderName . '/' . $fileNameToStore, $resizedImage);


    return $fileNameToStore;

  }
}

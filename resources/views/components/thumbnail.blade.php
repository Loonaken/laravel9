@php
    if($type === 'shops'){
      $path = 'storage/shops/';
    }
    if($type === 'images'){
      $path = 'storage/products/';
    }
    
@endphp


<div>
  @if (empty($shop->filename))
  <img src="{{asset('images/no_image.jpg')}}" alt="">
  @else
  <img src="{{asset('storage/shops/' . $filename)}}"  alt="">
  @endif
</div>

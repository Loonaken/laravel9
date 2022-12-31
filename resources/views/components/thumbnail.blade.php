@php
    if($type === 'shops'){
      $path = 'storage/shops/';
    }
    if($type === 'products'){
      $path = 'storage/products/';
    }

@endphp


<div>
  @if (empty($filename))
  {{-- しっかりと関数名とreturn関数名が一致しているかチェックする --}}
  <img src="{{ asset('images/no_image.jpg')}}">
  @else
  <img src="{{ asset($path . $filename)}}">
  @endif
</div>

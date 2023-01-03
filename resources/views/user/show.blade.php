<x-app-layout>
  <x-slot name="header">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        商品の詳細
      </h2>
  </x-slot>

  <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-6 bg-white border-b border-gray-200">
                <div class="md:flex md:justify-around">
                  <div class="md:w-1/2" >
                    <x-thumbnail filename="{{$product->imageFirst->filename ?? ''}}" type="products" />
                  </div>
                  <div class="md:w-1/2 ml-4">
                    <h1 class="text-gray-900 mb-2 text-3xl title-font font-medium">{{$product->name}}</h1>
                    <h2 class="text-sm mb-4 title-font text-gray-500 tracking-widest">{{$product->category->name}} </h2>
                    <p class="leading-relaxed mb-4">{{$product->information}}</p>
                      <div class="flex justify-around items-center">
                        <p class="mt-1 text-lg">{{ number_format($product->price) }}
                          <span class="text-base text-gray-600">円（税込）</span>
                        <div class="flex items-center">
                            <span class="mr-3">数量</span>
                            <div class="relative">
                              <select class="rounded border appearance-none border-gray-300 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-200 focus:border-yellow-500 text-base pl-3 pr-10">
                                <option>SM</option>
                                <option>M</option>
                                <option>L</option>
                                <option>XL</option>
                              </select>
                            </div>
                        </div>
                        <button class="flex ml-auto text-white bg-yellow-500 border-0 py-2 px-6 focus:outline-none hover:bg-yellow-600 rounded">カートに入れる</button>
                      </div>
                    </div>
                </div>
              </div>
          </div>
      </div>
  </div>
</x-app-layout>

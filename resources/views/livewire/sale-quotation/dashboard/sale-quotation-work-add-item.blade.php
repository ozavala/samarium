<div>


  <!-- Flash message div -->
  <div class="">
    @if (session()->has('errorMessage'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle mr-3"></i>
        {{ session('errorMessage') }}
        <button type="button" class="close text-white" data-dismiss="alert" aria-label="Close">
          <span class="text-danger" aria-hidden="true">&times;</span>
        </button>
      </div>
    @endif
  </div>


  {{-- Show in bigger screen --}}
  <div class="mb-3 border shadow-sm d-none d-md-block" style="">

    @if (true)
    <div class="table-responsive m-0">
      <table class="table table-sm table-bordered m-0">
        <thead>
          <tr class="bg-white" style="font-size: calc(0.6rem + 0.2vw);">
            <th class="py-2 border-0-rm pl-2" style="width: 200px;">Search Item</th>
            @if (true)
            <th class="py-2">Category</th>
            <th class="py-2">Item</th>
            <th class="py-2" style="width: 100px;">Price</th>
            <th class="py-2" style="width: 50px;">Qty</th>
            <th class="py-2" style="width: 100px;">Total</th>
            @endif
          </tr>
        </thead>
  
        <tbody>
          <tr class="p-0 font-weight-bold" style="height: 50px; font-size: calc(0.8rem + 0.2vw);">
            <td class="p-2-rm pb-4-rm h-100 bg-white border-0-rm">
              <input class="m-0 w-100 h-100 border-0 shadow-lg-rm py-2 shadow-lg-rm" type="text"
                  wire:model.defer="add_item_name" wire:keydown.enter="updateProductList"/>
            </td>
            @if (true)
            <td class="p-0 h-100">
              <select class="w-100 h-100 custom-control border-0" wire:model.defer="search_product_category_id" wire:change="selectProductCategory">
                <option>---</option>
  
                @foreach ($productCategories as $productCategory)
                  <option value="{{ $productCategory->product_category_id }}">
                    {{ $productCategory->name }}
                  </option>
                @endforeach
              </select>
            </td>
            <td class="p-0 h-100">
              <select class="w-100 h-100 custom-control border-0" wire:model.defer="product_id" wire:change="selectItem">
                <option>---</option>
  
                @foreach ($products as $product)
                  <option value="{{ $product->product_id }}">
                    {{ $product->name }}
                  </option>
                @endforeach
              </select>
            </td>
            <td class="p-0">
              <div class="d-flex flex-column justify-content-center h-100 bg-light">
                @if ($selectedProduct)
                  @php echo number_format( $selectedProduct->selling_price ); @endphp
                @endif
              </div>
            </td>
            <td class="p-0 h-100">
              <input class="w-100 h-100 font-weight-bold border-0" type="text" wire:model.defer="quantity" wire:keydown.enter="updateTotal"/>
            </td>
            <td class="p-0">
              <div class="d-flex flex-column justify-content-center h-100 bg-white">
                @if ($selectedProduct)
                  @php echo number_format( $total ); @endphp
                @endif
              </div>
            </td>
            @endif
          </tr>
        </tbody>
      </table>
    </div>
  
    @if (true)
    <div class="p-2 m-0 bg-white">
      <div class="row">
        <div class="col-md-8">
          <button class="btn btn-lg btn-success mr-3" wire:click="addItemToSaleQuotation" style="font-size: calc(0.7rem + 0.2vw);">
            <i class="fas fa-plus mr-2"></i>
            Add
          </button>
  
          <button class="btn btn-lg btn-danger" wire:click="resetInputFields" style="font-size: calc(0.7rem + 0.2vw);">
            Reset
          </button>
  
          <button wire:loading class="btn">
            <span class="spinner-border text-info mr-3" role="status">
            </span>
          </button>
  
        </div>

        @if ($selectedProduct != null)
          <div class="col-md-4" style="height: 50px;">


            <div class="float-right">
              <img src="{{ asset('storage/' . $selectedProduct->image_path) }}" class="img-fluid" style="height: 50px;">
            </div>
            <div class="clearfix">
            </div>
          </div>
        @endif
      </div>
  
    </div>
    @endif
    @endif
  
  </div>


  @if (false)
  {{-- Show in smaller screen --}}
  <div class="d-md-none mb-3">
    @if (! $modes['showMobForm'])
    <button class="btn btn-success ml-3" wire:click="showAddItemFormMob" style="font-size: 1.3rem;">
      Add item
    </button>
    @else
    <button class="btn btn-danger ml-3" wire:click="hideAddItemFormMob" style="font-size: 1.3rem;">
      Cancel
    </button>
    @endif

    <button wire:loading class="btn">
      <span class="spinner-border text-info mr-3" role="status">
      </span>
    </button>

    @if ($modes['showMobForm'])
      <div>
        @include ('partials.mob.add-item-form')
      </div>
    @endif
  </div>
  @endif


</div>

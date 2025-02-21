<div>


  <x-list-component>
    <x-slot name="listInfo">
      Total products: {{ \App\Product::count() }}
    </x-slot>

    <x-slot name="listHeadingRow">
      <th>
        Product ID
      </th>
      <th>
        Name
      </th>
      <th>
        Active status
      </th>
      <th>
        Selling price
      </th>
      <th class="text-right">
        Action
      </th>
    </x-slot>

    <x-slot name="listBody">
      @foreach ($products as $product)
        <tr>
          <td>
            {{ $product->product_id }}
          </td>
          <td class="h6" wire:click="$dispatch('displayProduct', { productId : {{ $product->product_id }} })" role="button">
            {{ \Illuminate\Support\Str::limit($product->name, 60, $end=' ...') }}
          </td>
          <td class="h6 font-weight-bold">
            @if ($product->is_active == 1)
              <span class="badge badge-pill badge-success">
                Active
              </span>
            @else
              <span class="badge badge-pill badge-danger">
                Inactive
              </span>
            @endif
          </td>
          <td>
            {{ $product->selling_price }}
          </td>
          <td class="text-right">
            @if (true)
              <button class="btn btn-primary px-2 py-1" wire:click="$dispatch('displayProduct', { productId : {{ $product->product_id }} })">
                <i class="fas fa-pencil-alt"></i>
              </button>
              <button class="btn btn-success px-2 py-1" wire:click="$dispatch('displayProduct', { productId : {{ $product->product_id }} })">
                <i class="fas fa-eye"></i>
              </button>
              <button class="btn btn-danger px-2 py-1" wire:click="">
                <i class="fas fa-trash"></i>
              </button>
            @endif
          </td>
        </tr>
      @endforeach
    </x-slot>

    <x-slot name="listPaginationLinks">
      {{ $products->links() }}
    </x-slot>
  </x-list-component>


</div>

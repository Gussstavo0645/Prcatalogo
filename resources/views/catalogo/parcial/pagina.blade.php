@php
  $pagina = $renderPage['pagina'];
  $pageNum = (int) $renderPage['page_number_label'];
  $items = collect($renderPage['items'] ?? []);

  // Clave única para NO confundir página 38 parte 1 con página 38 parte 2
  $itemsKey = $items->map(function ($p) {
      return ($p->code ?? '') . '-' . ($p->color ?? '');
  })->implode('|');

  $renderKey = $pagina->id . '-' . $pageNum . '-' . md5($itemsKey);
@endphp


<div class="page {{ $pageNum === 1 ? 'page-cover' : '' }}"
     data-density="{{ $pageNum === 1 ? 'hard' : 'soft' }}"
     data-page-number="{{ $pageNum }}"
     data-page-id="{{ $pagina->id }}"
     data-render-key="{{ $renderKey }}">

  
  <div class="page-badge">{{ $pageNum }}</div>

  <img
    src="{{ route('catalog_pages.image', $pagina->id) }}"
    class="page-img"
    alt="Página {{ $pageNum }}"
    loading="lazy"
    decoding="async"
  >

  @if($items->count() > 0)
    <div class="products-overlay">
      @foreach($items as $prod)

   
      @continue(!empty($prod->is_combo_child))

     

        @php
  if (!empty($prod->is_combo) && !empty($prod->image_path)) {
      $img = asset('storage/' . $prod->image_path);
      $imgLarge = asset('storage/' . $prod->image_path);
  } else {
      $img = route('catalog.product.thumb', [
        'code' => $prod->code,
        'color' => $prod->color
      ]);

      $imgLarge = route('catalog.product.image', [
        'code' => $prod->code,
        'color' => $prod->color
      ]);
  }
@endphp

  <div class="product-mini"
     data-code="{{ $prod->code }}"
     data-color="{{ $prod->color }}"
     data-stock='@json(collect($prod->existencias ?? [])->values(), JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)'>

   <img
  src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=="
  data-src="{{ $img }}?v={{ $prod->code }}{{ $prod->color }}"
  alt="{{ $prod->name }}"
  class="product-thumb"
  data-large="{{ $imgLarge }}?v={{ $prod->code }}{{ $prod->color }}"
  decoding="async"
  style="cursor: zoom-in;"
  onerror="this.onerror=null;this.src='https://via.placeholder.com/300x200?text=Sin+foto';"
/>

          <div class="p-code">Código: {{ $prod->display_code ?? $prod->code }}</div>
          <div class="p-name">{{ $prod->name }}</div>
          <div class="p-price">
  Q {{ number_format((float)($prod->price ?? $prod->combo_price ?? $prod->Precventa ?? 0), 2) }}
</div>

      
@php
    $isCombo = !empty($prod->is_combo);

    $comboItems = $isCombo
        ? collect($prod->combo_items ?? [])->map(function ($item) {
            return [
                'code' => data_get($item, 'code') ?: data_get($item, 'product_code') ?: '',
                'color' => data_get($item, 'color') ?: data_get($item, 'product_color') ?: '',
                'name' => data_get($item, 'name') ?: '',
                'quantity' => (int) (data_get($item, 'quantity') ?: 1),
            ];
        })->values()->all()
        : [];

    $cartPayload = [
        'id' => ($isCombo ? 'combo-' : 'prod-') . $prod->code . '-' . $prod->color,
        'is_combo' => $isCombo,
        'code' => $prod->code,
        'color' => $prod->color,
        'name' => $prod->name,
        'price' => (float)($prod->price ?? $prod->combo_price ?? $prod->Precventa ?? 0),
        'qty' => 1,
        'img' => $img,
        'combo_items' => $comboItems,
    ];
@endphp

<button
    type="button"
    class="badge bg-primary mt-1 border-0 w-100 p-2"
    onclick='event.stopPropagation(); addToCart(@json($cartPayload, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG));'
>
    1 AGREGAR
</button>
        </div>
      @endforeach
    </div>
  @endif
</div>
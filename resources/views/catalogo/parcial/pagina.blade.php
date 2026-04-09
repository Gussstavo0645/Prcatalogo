@php
  $pagina = $renderPage['pagina'];
  $pageNum = (int) $renderPage['page_number_label'];
  $items = collect($renderPage['items'] ?? []);
@endphp

<div class="page {{ $pageNum === 1 ? 'page-cover' : '' }}"
     data-density="{{ $pageNum === 1 ? 'hard' : 'soft' }}">

  <div class="page-badge">Página {{ $pageNum }}</div>

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
        @php
          $img = route('catalog.product.thumb', [
            'code' => $prod->code,
            'color' => $prod->color
          ]);

          $imgLarge = route('catalog.product.image', [
            'code' => $prod->code,
            'color' => $prod->color
          ]);
        @endphp

        <div class="product-mini">
          <img
            src="{{ $img }}?v={{ $prod->code }}{{ $prod->color }}"
            alt="{{ $prod->name }}"
            class="product-thumb"
            data-large="{{ $imgLarge }}?v={{ $prod->code }}{{ $prod->color }}"
            loading="lazy"
            decoding="async"
            style="cursor: zoom-in;"
            onerror="this.onerror=null;this.src='https://via.placeholder.com/300x200?text=Sin+foto';"
          />

          <div class="p-code">Código: {{ $prod->display_code ?? $prod->code }}</div>
          <div class="p-name">{{ $prod->name }}</div>
          <div class="p-price">Q {{ number_format($prod->price, 2) }}</div>

          <button
            type="button"
            class="badge bg-primary mt-1 border-0 w-100 p-2"
            onclick="addToCart({
              id: '{{ $prod->code }}-{{ $prod->color }}',
              code: '{{ $prod->code }}',
              color: '{{ $prod->color }}',
              name: @js($prod->name),
              price: {{ (float) $prod->price }},
              qty: {{ (int) ($prod->quantity ?? 1) }},
              img: '{{ $img }}'
            })"
          >
            {{ $prod->quantity ?? 1 }} AGREGAR
          </button>
        </div>
      @endforeach
    </div>
  @endif
</div>
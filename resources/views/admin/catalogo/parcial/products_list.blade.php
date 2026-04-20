@if($products->isEmpty())
  <div class="alert alert-info">Aún no tienes productos registrados.</div>
@else
  <div class="row g-3" id="availableProducts">
    @foreach($products as $p)
      @php
        $prod = $p instanceof \Illuminate\Support\Collection ? $p->first() : $p;
      @endphp
      @continue(!$prod)

      <div class="col-6 col-md-3 product-item"
           id="product-card-{{$prod->code}}-{{$prod->color}}"
           data-source-page="{{ (int)($prod->source_page ?? $prod->debug_page ?? 1) }}">
        <div class="card h-100">

          @if($catalog)
            <div class="text-center p-2 border-bottom">
              <input
                type="checkbox"
                class="form-check-input product-check"
                data-code="{{ trim($prod->code) }}"
                data-color="{{ trim($prod->color) }}"
                data-key="{{ trim($prod->code) }}-{{ trim($prod->color) }}"
                style="transform: scale(1.2);"
              >
            </div>
          @endif

          @php
            $imgUrl = !empty($prod->color)
                ? route('catalog.product.thumb', ['code' => $prod->code, 'color' => $prod->color])
                : route('catalog.product.thumb', ['code' => $prod->code]);
          @endphp

          <img src="{{ $imgUrl }}"
               loading="lazy"
               class="card-img-top bg-white"
               style="height:220px; object-fit:contain; width:100%;"
               alt="{{ $prod->name }}"
               onerror="this.onerror=null;this.src='https://via.placeholder.com/220x220?text=Sin+imagen';">

          <div class="card-body d-flex flex-column">
            <div class="fw-semibold p-name">{{ $prod->name }}</div>

      @php
  $codigoCompleto = $prod->code . ($prod->color ? '-' . $prod->color : '');
@endphp

<div class="small mt-1">
  <span class="badge bg-dark">Código</span> {{ $codigoCompleto }}
</div>

<div class="small mt-1">
  <span class="badge bg-primary">Página</span> {{ $prod->source_page ?? '-' }}
</div>

            <div class="text-muted small p-price">Q {{ number_format($prod->price,2) }}</div>

            <div class="p-qty mt-auto">
              <span class="badge bg-primary w-100 text-center">1 u</span>
            </div>

            <div class="d-flex gap-2 mt-2">
              <input type="number" min="1" value="1"
                     class="form-control form-control-sm"
                     style="max-width:90px"
                     id="qty-{{ trim($prod->code) }}-{{ trim($prod->color) }}">

              <input type="number" min="1"
                     value="{{ $prod->source_page ?? 1 }}"
                     class="form-control form-control-sm"
                     style="max-width:90px"
                     id="page-{{ trim($prod->code) }}-{{ trim($prod->color) }}">

              <button type="button"
                class="btn btn-primary btn-sm ms-auto"
                data-code="{{ $prod->code }}"
                data-color="{{ $prod->color }}"
                data-key="{{ trim($prod->code) }}-{{ trim($prod->color) }}"
                onclick="addToCatalog(this, {{ $catalog?->id ?? 'null' }})"
                {{ $catalog ? '' : 'disabled' }}>
                Agregar
              </button>

              <button type="button"
                class="btn btn-danger btn-sm"
                onclick="deleteProduct('{{$prod->code}}','{{$prod->color}}')">
                Eliminar
              </button>
            </div>

            @if(!$catalog)
              <div class="text-muted small mt-2">
                Primero crea un catálogo para poder agregar productos.
              </div>
            @endif
          </div>
        </div>
      </div>
    @endforeach
  </div>

  <div class="mt-3" id="productsPagination">
    {{ $products->appends(request()->query())->links() }}
  </div>
@endif
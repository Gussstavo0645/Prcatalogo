@extends('layouts.app')

@php
  $pages = $catalog->paginas
      ->sortBy('page_number')
      ->filter(fn($p) => !empty($p->archivo_binario)) //  ahora valida blob
      ->unique(fn($p) => $p->page_number)             // evita duplicados
      ->map(fn($p) => route('catalog_pages.image', $p->id)) // URL al endpoint
      ->values();
@endphp

@section('content')


<style>
  #flipbook-wrap{display:grid;place-items:center;padding:16px;margin-bottom:32px;}
  #flipbook{ width: 920px; height: 600px; overflow:hidden; visibility:hidden; }
  .flip-controls{display:flex;gap:12px;justify-content:center;align-items:center;margin-top:10px;}
  .flip-controls button{ padding:6px 12px; }
</style>

<div class="container py-3">
  @include('catalogo.parcial.produc_grid')
  <h3 class="mb-1">{{ $catalog->title }}</h3>
  @if($catalog->description)
    <p class="text-muted">{{ $catalog->description }}</p>
  @endif
</div>

<div id="flipbook-wrap">
  <div id="flipbook"></div>

  <div class="flip-controls">
    <button id="prev" class="btn btn-outline-secondary">⟵ Anterior</button>
    <span id="page-indicator" class="text-muted small">1 / {{ max(1, $pages->count()) }}</span>
    <button id="next" class="btn btn-outline-secondary">Siguiente ⟶</button>
  </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
  const pages = @json($pages);
  if (!pages.length) {
    document.getElementById('flipbook-wrap').innerHTML =
      '<div class="alert alert-info m-3">Este catálogo aún no tiene páginas.</div>';
    return;
  }

  const root = document.getElementById('flipbook');
  const pageFlip = new St.PageFlip(root, {
    width: 460,
    height: 600,
    size: 'fixed',
    showCover: true,           // ponlo en false si no quieres “tapa”/“contratapa”
    useShadow: true,
    maxShadowOpacity: 0.2,
    flippingTime: 800,
    mobileScrollSupport: true,
  });

  pageFlip.loadFromImages(pages);
  root.style.visibility = 'visible';

  const prev = document.getElementById('prev');
  const next = document.getElementById('next');
  const indicator = document.getElementById('page-indicator');

  prev.addEventListener('click', () => pageFlip.flipPrev());
  next.addEventListener('click', () => pageFlip.flipNext());

  const update = () => {
    const c = pageFlip.getCurrentPageIndex() + 1;
    const t = pageFlip.getPageCount();
    indicator.textContent = `${c} / ${t}`;
  };
  pageFlip.on('init', update);
  pageFlip.on('flip', update);

  // Navegación con teclado
  window.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft') pageFlip.flipPrev();
    if (e.key === 'ArrowRight') pageFlip.flipNext();
  });

  // DEBUG opcional:
  // console.log('pages:', pages);
})();
</script>
@endsection

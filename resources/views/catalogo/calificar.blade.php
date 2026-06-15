@extends('layouts.public')

@section('content')
<div class="container py-5">

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">

            <h3 class="fw-bold mb-2">Califica tus productos</h3>

            <p class="text-muted mb-4">
                Toca las estrellas para calificar cada producto.
            </p>

            @forelse($items as $index => $item)
                @php
                    $code = trim($item->code ?? '');
                    $color = trim($item->color ?? '');
                    $name = $item->name ?? 'Producto';
                    $ratingActual = (int) ($item->rating ?? 0);
                @endphp

                <div class="review-product-card mb-4 p-3 border rounded-4"
                     data-url="{{ route('pedido.calificar.item', $pedidoRow->id) }}"
                     data-code="{{ $code }}"
                     data-color="{{ $color }}">

                    <h5 class="mb-1">
                        {{ $name }}
                    </h5>

                    <div class="text-muted small mb-3">
                        Código: {{ $code }}{{ $color !== '' ? '-' . $color : '' }}
                    </div>

                    <label class="fw-bold d-block mb-2">
                        Calificación
                    </label>

                    <div class="rating-stars">
                        @for($star = 1; $star <= 5; $star++)
                            <button type="button"
                                    class="star-btn {{ $star <= $ratingActual ? 'active' : '' }}"
                                    data-value="{{ $star }}">
                                ★
                            </button>
                        @endfor
                    </div>

                    <div class="rating-help mt-2">
                        @if($ratingActual > 0)
                            <span class="rating-msg text-success">Calificación guardada</span>
                        @else
                            <span class="rating-msg text-muted">Toca una estrella para calificar</span>
                        @endif
                    </div>
                </div>

            @empty
                <div class="alert alert-warning">
                    Este pedido no tiene productos para calificar.
                </div>
            @endforelse

        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
    .rating-stars {
        display: flex;
        gap: 4px;
        align-items: center;
    }

    .star-btn {
        border: none;
        background: transparent;
        font-size: 30px;
        line-height: 1;
        color: #ccc;
        cursor: pointer;
        padding: 0;
        transition: 0.15s ease;
    }

    .star-btn.active {
        color: #ffc107;
    }

    .star-btn:hover {
        transform: scale(1.12);
        color: #ffca2c;
    }

    .rating-help {
        font-size: 13px;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('click', async function (e) {
    const star = e.target.closest('.star-btn');

    if (!star) return;

    const card = star.closest('.review-product-card');

    const rating = Number(star.dataset.value);
    const url = card.dataset.url;
    const code = card.dataset.code;
    const color = card.dataset.color || '';
    const msg = card.querySelector('.rating-msg');

    pintarEstrellas(card, rating);

    msg.textContent = 'Guardando...';
    msg.classList.remove('text-success', 'text-danger', 'text-muted');
    msg.classList.add('text-muted');

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                code: code,
                color: color,
                rating: rating
            })
        });

        const data = await response.json();

        if (!response.ok || !data.ok) {
            throw new Error(data.message || 'Error al guardar');
        }

        msg.textContent = 'Calificación guardada';
        msg.classList.remove('text-muted', 'text-danger');
        msg.classList.add('text-success');

    } catch (error) {
        msg.textContent = 'No se pudo guardar';
        msg.classList.remove('text-muted', 'text-success');
        msg.classList.add('text-danger');
    }
});

function pintarEstrellas(card, rating) {
    const stars = card.querySelectorAll('.star-btn');

    stars.forEach((star, index) => {
        star.classList.toggle('active', index < rating);
    });
}
</script>
@endpush
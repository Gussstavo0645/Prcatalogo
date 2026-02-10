<!-- GRID PRODUCTOS -->
<div class="container py-3">
  <div class="row g-3" id="products">


    <!-- Card ejemplo -->
    <div class="col-12 col-md-4">
      <div class="card h-100">
        <img src="https://via.placeholder.com/600x400" class="card-img-top" alt="Producto">
        <div class="card-body">
          <h6 class="card-title mb-1">Producto 1</h6>
          <div class="text-muted small mb-2">Q 45.00</div>

          <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary btn-sm" onclick="decQty(1)">-</button>
            <span id="qty-1" class="fw-bold">1</span>
            <button class="btn btn-outline-secondary btn-sm" onclick="incQty(1)">+</button>

            <button class="btn btn-primary btn-sm ms-auto" onclick="addToCart(1, 'Producto 1', 45)">
              Agregar
            </button>
          </div>
        </div>
      </div>
    </div>
    <!--copia o pega mas productos cambiando id/nombre o precio -->
  </div>
</div>


<!-- botomn de carrito flotante -->
<button class="cart-fab" onclick="toggleCart()">
  <span class="cart-badge" id="cartCount">0</span>
</button>

<!-- panel caritto simple -->

<div class="cart-panel" id="cartPanel">
<div class="cart-header">
    <strong>Tu carrito</strong>
    <button class="btn btn-sm btn-light" onclick="toggleCart()">✕</button>
  </div>

  <div class="cart-body" id="cartItems">
    <div class="text-muted small">Aún no agregas productos.</div>
  </div>

  <div class="cart-footer">
    <div class="d-flex justify-content-between">
      <span>Total</span>
      <strong id="cartTotal">Q 0.00</strong>
    </div>
    <button class="btn btn-success w-100 mt-2" onclick="checkout()">Continuar</button>
  </div>
</div>

<style>
  .cart-fab{
    position: fixed;
    right: 18px;
    bottom: 18px;
    border: none;
    background: #0d6efd;
    color: #fff;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    font-size: 20px;
    box-shadow: 0 8px 24px rgba(0,0,0,.2);
    display: grid;
    place-items: center;
    z-index: 9999;
  }
  .cart-badge{
    position: absolute;
    top: -6px;
    right: -6px;
    background: #dc3545;
    color: #fff;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    font-size: 12px;
    display: grid;
    place-items: center;
    font-weight: 700;
  }

  .cart-panel{
    position: fixed;
    right: 18px;
    bottom: 86px;
    width: 320px;
    max-height: 70vh;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 10px 30px rgba(0,0,0,.25);
    overflow: hidden;
    transform: translateY(10px);
    opacity: 0;
    pointer-events: none;
    transition: .2s ease;
    z-index: 9999;
  }
  .cart-panel.open{
    transform: translateY(0);
    opacity: 1;
    pointer-events: auto;
  }
  .cart-header{
    padding: 12px 12px;
    background: #f8f9fa;
    display:flex;
    justify-content: space-between;
    align-items:center;
    border-bottom: 1px solid #eee;
  }
  .cart-body{
    padding: 12px;
    overflow:auto;
    max-height: 45vh;
  }
  .cart-item{
    display:flex;
    justify-content: space-between;
    gap: 10px;
    padding: 10px 0;
    border-bottom: 1px dashed #e9ecef;
  }
  .cart-footer{
    padding: 12px;
    border-top: 1px solid #eee;
    background: #fff;
  }
</style>

<script>
  // cantidades por producto (para el + / - de cada card)
  const qty = {};
  function incQty(id){
    qty[id] = (qty[id] || 1) + 1;
    document.getElementById(`qty-${id}`).textContent = qty[id];
  }
  function decQty(id){
    qty[id] = Math.max(1, (qty[id] || 1) - 1);
    document.getElementById(`qty-${id}`).textContent = qty[id];
  }

  // Carrito (en memoria)
  const cart = {}; // {id: {name, price, units}}
  function addToCart(id, name, price){
    const units = qty[id] || 1;
    if(!cart[id]) cart[id] = {name, price, units: 0};
    cart[id].units += units;
    renderCart();
  }

  function renderCart(){
    const itemsEl = document.getElementById('cartItems');
    const countEl = document.getElementById('cartCount');
    const totalEl = document.getElementById('cartTotal');

    const ids = Object.keys(cart);
    if(ids.length === 0){
      itemsEl.innerHTML = '<div class="text-muted small">Aún no agregas productos.</div>';
      countEl.textContent = '0';
      totalEl.textContent = 'Q 0.00';
      return;
    }

    let totalUnits = 0;
    let total = 0;

    itemsEl.innerHTML = ids.map(id => {
      const it = cart[id];
      totalUnits += it.units;
      total += it.units * it.price;
      return `
        <div class="cart-item">
          <div>
            <div class="fw-semibold">${it.name}</div>
            <div class="text-muted small">Q ${it.price.toFixed(2)} c/u</div>
          </div>
          <div class="text-end">
            <div class="fw-bold">${it.units} u</div>
            <button class="btn btn-sm btn-outline-danger" onclick="removeItem(${id})">Quitar</button>
          </div>
        </div>
      `;
    }).join('');

    countEl.textContent = totalUnits;
    totalEl.textContent = `Q ${total.toFixed(2)}`;
  }

  function removeItem(id){
    delete cart[id];
    renderCart();
  }

  function toggleCart(){
    document.getElementById('cartPanel').classList.toggle('open');
  }

  function checkout(){
    alert('Aquí puedes enviar al checkout / crear pedido.');
  }
</script>

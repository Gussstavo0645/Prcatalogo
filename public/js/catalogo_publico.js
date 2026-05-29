const CART_KEY = 'flipbook_cart_v1';

/* ==========================
   🛒 CARRITO
========================== */
function getCart(){
  try {
    return JSON.parse(localStorage.getItem(CART_KEY) || '[]');
  } catch(e){
    return [];
  }
}

function setCart(items){
  localStorage.setItem(CART_KEY, JSON.stringify(items));


  renderCart();
  updateCartBadge();
}

function updateCartBadge(){
  const badge = document.getElementById('cartCountFab'); 
  if (!badge) return;

  const totalItems = getCart().reduce((sum, item) => sum + Number(item.qty || 0), 0);
  badge.textContent = totalItems;
}

function clearCart(){
  setCart([]);
}

function removeCartItem(index){
  let cart = getCart();
  cart.splice(index, 1);
  setCart(cart);
}

function addToCart(product){
  let cart = getCart();

  const idx = cart.findIndex(item =>
    item.code === product.code &&
    String(item.color) === String(product.color)
  );

  if (idx >= 0) {
    cart[idx].qty += Number(product.qty || 1);
  } else {
    cart.push({
      id: product.id,
      code: product.code,
      color: product.color,
      name: product.name,
      price: Number(product.price),
      qty: Number(product.qty || 1),
      img: product.img
    });
  }

  setCart(cart);
  showCartFab();
}

function renderCart(){
  const cart = getCart();
  const wrap = document.getElementById('cartItems');
  const totalEl = document.getElementById('cartTotal');

  if (!wrap) return;

  if (!cart.length){
    wrap.innerHTML = '<p class="text-muted mb-0">Tu carrito está vacío.</p>';
    if (totalEl) totalEl.textContent = 'Q 0.00';
    return;
  }

  let total = 0;

  wrap.innerHTML = cart.map((item, i) => {
    const subtotal = item.price * item.qty;
    total += subtotal;

    return `
      <div class="cart-item">
        <img src="${item.img}" alt="${item.name}">
        <div class="meta">
          <div class="name">${item.name}</div>
          <div class="sub">${item.code} | ${item.color}</div>
          <div class="sub">Q ${item.price} x ${item.qty}</div>
        </div>
        <button type="button" onclick="removeCartItem(${i})">X</button>
    
      </div>
    `;
  }).join('');

  if (totalEl) totalEl.textContent = 'Q ' + total.toFixed(2);
}

function toggleCart(){
  const panel = document.getElementById('cartPanel');

  if (!panel) {
    console.error('No existe #cartPanel');
    return;
  }

  panel.classList.toggle('d-none');
  console.log('panel clases:', panel.className);
}



function changeQty(index, delta){
  let cart = getCart();
  if (!cart[index]) return;

  cart[index].qty = Math.max(1, Number(cart[index].qty) + delta);
  setCart(cart);
}

function createPageChunkLoader(root, options = {}) {
  let loading = false;
  let preloadTriggeredFor = 0;

  const blockSize = options.blockSize || 6;
  const threshold = options.threshold || 3;

  async function loadNextBlock(pageFlip) {
    if (loading) return;

    const slug = root.dataset.slug;
    const loaded = parseInt(root.dataset.loaded || '0', 10);
    const total = parseInt(root.dataset.total || '0', 10);

    if (loaded >= total) return;

    loading = true;

    try {
      const res = await fetch(`/c/${slug}/bloque?offset=${loaded}&limit=${blockSize}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });

      if (!res.ok) throw new Error(`HTTP ${res.status}`);

      const data = await res.json();
      console.log('DATA:', data);
      if (!data.html || data.count <= 0) return;

      const currentIndex = pageFlip.getCurrentPageIndex();

      const temp = document.createElement('div');
      temp.innerHTML = data.html;

      [...temp.children].forEach(el => root.appendChild(el));

      root.dataset.loaded = String(loaded + data.count);

      const newThumbs = [...temp.querySelectorAll('.product-thumb')].slice(0, 12);
      newThumbs.forEach(img => {
        const preload = new Image();
        preload.src = img.src;
      });

      pageFlip.updateFromHtml(root.querySelectorAll('.page'));
      pageFlip.turnToPage(currentIndex);

      const indicator = document.getElementById('page-indicator');
if (indicator) {
  indicator.textContent = (pageFlip.getCurrentPageIndex() + 1) + ' / ' + (root.dataset.loaded || '0');
}

   lockFlipOnOverlay(root, '.products-overlay');
bindZoomPreload(root);
bindProductZoom(root);
    } catch (e) {
      console.error('Error cargando más páginas:', e);
    } finally {
      loading = false;
    }
  }

  function check(pageFlip) {
    const loaded = parseInt(root.dataset.loaded || '0', 10);
    const total = parseInt(root.dataset.total || '0', 10);
    const current = pageFlip.getCurrentPageIndex() + 1;

    if (loaded >= total) return;

    const triggerPoint = loaded - threshold;

    if (current >= triggerPoint && preloadTriggeredFor < loaded) {
      preloadTriggeredFor = loaded;
      loadNextBlock(pageFlip);
    }
  }

  return {
    check,
    loadNextBlock
  };
}

function lockFlipOnOverlay(root, selector) {
  root.querySelectorAll(selector).forEach((el) => {
    const stopMouse = (e) => e.stopPropagation();
    const stopTouchMove = (e) => {
      e.stopPropagation();
      if (e.cancelable) e.preventDefault();
    };

    el.addEventListener('mousedown', stopMouse, { capture: true });
    el.addEventListener('mousemove', stopMouse, { capture: true });
    el.addEventListener('mouseup', stopMouse, { capture: true });

    el.addEventListener('pointerdown', stopMouse, { capture: true });
    el.addEventListener('pointermove', stopTouchMove, { capture: true, passive: false });
    el.addEventListener('pointerup', stopMouse, { capture: true });

    el.addEventListener('touchstart', stopMouse, { capture: true, passive: false });
    el.addEventListener('touchmove', stopTouchMove, { capture: true, passive: false });
    el.addEventListener('touchend', stopMouse, { capture: true });
  });
}


/* ==========================
   ⚡ CACHE DE IMÁGENES GRANDES PARA ZOOM
========================== */
const zoomImageCache = new Map();

function preloadZoomImage(src) {
  if (!src) return null;

  if (zoomImageCache.has(src)) {
    return zoomImageCache.get(src);
  }

  const preload = new Image();
  preload.src = src;

  zoomImageCache.set(src, preload);
  return preload;
}
function bindZoomPreload(root) {
  root.querySelectorAll('.product-thumb').forEach(img => {
    if (img.dataset.zoomBound === '1') return;
    img.dataset.zoomBound = '1';

    const precargar = () => {
      const large = img.dataset.large;
      preloadZoomImage(large);
    };

    // PC: empieza a cargar al pasar el mouse
    img.addEventListener('mouseenter', precargar, { once: true });

    // PC y celular: empieza a cargar justo antes del clic
    img.addEventListener('pointerdown', precargar, { once: true });

    // Celular
    img.addEventListener('touchstart', precargar, { once: true, passive: true });
  });
}

function bindProductZoom(root) {
  root.querySelectorAll('.product-thumb').forEach(img => {
    if (img.dataset.zoomClickBound === '1') return;
    img.dataset.zoomClickBound = '1';

    img.style.cursor = 'zoom-in';

    img.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      openImgModal(this);
    });
  });
}

(function () {
  const fab = document.getElementById('cartFab');
  if (!fab) return;

  let isDragging = false;
  let moved = false;
  let startX = 0;
  let startY = 0;
  let initialLeft = 0;
  let initialTop = 0;

  // posición inicial fija para poder moverlo libremente
  function setInitialPosition() {
    const rect = fab.getBoundingClientRect();
    fab.style.left = rect.left + 'px';
    fab.style.top = rect.top + 'px';
    fab.style.right = 'auto';
    fab.style.bottom = 'auto';
  }

  setTimeout(setInitialPosition, 100);

  fab.addEventListener('pointerdown', function (e) {
    isDragging = true;
    moved = false;
    fab.classList.add('dragging');

    const rect = fab.getBoundingClientRect();
    startX = e.clientX;
    startY = e.clientY;
    initialLeft = rect.left;
    initialTop = rect.top;

    fab.setPointerCapture(e.pointerId);
  });

  fab.addEventListener('pointermove', function (e) {
    if (!isDragging) return;

    const dx = e.clientX - startX;
    const dy = e.clientY - startY;

    if (Math.abs(dx) > 4 || Math.abs(dy) > 4) {
      moved = true;
    }

    let newLeft = initialLeft + dx;
    let newTop = initialTop + dy;

    const rect = fab.getBoundingClientRect();
    const maxLeft = window.innerWidth - rect.width;
    const maxTop = window.innerHeight - rect.height;

    newLeft = Math.max(0, Math.min(newLeft, maxLeft));
    newTop = Math.max(0, Math.min(newTop, maxTop));

    fab.style.left = newLeft + 'px';
    fab.style.top = newTop + 'px';
    fab.style.right = 'auto';
    fab.style.bottom = 'auto';
  });

  fab.addEventListener('pointerup', function (e) {
    isDragging = false;
    fab.classList.remove('dragging');
    fab.releasePointerCapture(e.pointerId);
  });

  fab.addEventListener('pointercancel', function () {
    isDragging = false;
    fab.classList.remove('dragging');
  });

  // click normal abre carrito, arrastrar no
  fab.addEventListener('click', function (e) {
  e.preventDefault();
  e.stopPropagation();

  if (moved) {
    moved = false;
    return;
  }

  closeImgModal();
  toggleCart();
});

  window.addEventListener('resize', () => {
    const rect = fab.getBoundingClientRect();

    let left = rect.left;
    let top = rect.top;

    const maxLeft = window.innerWidth - rect.width;
    const maxTop = window.innerHeight - rect.height;

    left = Math.max(0, Math.min(left, maxLeft));
    top = Math.max(0, Math.min(top, maxTop));

    fab.style.left = left + 'px';
    fab.style.top = top + 'px';
  });
})();
/* ==========================
   🎯 FAB
========================== */
let cartTimer;

function showCartFab(){
  const fab = document.getElementById('cartFab');
  if (!fab) return;

  fab.style.opacity = '1';

  clearTimeout(cartTimer);
  cartTimer = setTimeout(() => {
    fab.style.opacity = '0.3';
  }, 500);
}

/* ==========================
   🧾 WIZARD (simplificado)
========================== */

let clienteDetectado = false;
window.esClienteNoInscrito = false;

function bloquearCamposCliente() {
 clienteDetectado = false;
  window.esClienteNoInscrito = false;

  const campos = ['cliNombre', 'cliTelefono', 'cliNit', 'cliDpi', 'cliCorreo'];

  campos.forEach(id => {
    const el = document.getElementById(id);
    if (el) {
      el.value = '';
      el.disabled = true;
    }
  });

  const status = document.getElementById('clienteStatus');
  if (status) {
    status.className = 'text-muted';
    status.textContent = 'Ingrese su código para continuar.';
  }
}

function desbloquearCamposCliente() {
  clienteDetectado = true;

  const campos = ['cliNombre', 'cliTelefono', 'cliNit', 'cliDpi', 'cliCorreo'];

  campos.forEach(id => {
    const el = document.getElementById(id);
    if (el) {
      el.disabled = false;
    }
  });
}

async function detectarCliente() {
  const codInput = document.getElementById('cliCodCliente');
  const btn = document.getElementById('btnBuscarCliente');
  const status = document.getElementById('clienteStatus');

  const codcliente = codInput?.value.trim() || '';

  if (!codcliente) {
    alert('Ingrese el código de cliente.');
    bloquearCamposCliente();
    codInput?.focus();
    return;
  }

  try {
    if (btn) {
      btn.disabled = true;
      btn.textContent = 'Buscando...';
    }

    const res = await fetch(`/clientes/detectar/${encodeURIComponent(codcliente)}`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });

    const data = await res.json();

   if (!res.ok || !data.ok) {
  bloquearCamposCliente();

  if (status) {
    status.className = 'text-danger';
    status.textContent = data.message || 'Código de cliente no encontrado.';
  }

  alert(data.message || 'Código de cliente no encontrado.');
  return;
}

   document.getElementById('cliNombre').value = data.cliente?.Nombre || '';
document.getElementById('cliTelefono').value = '';
document.getElementById('cliNit').value = '';
document.getElementById('cliDpi').value = '';
document.getElementById('cliCorreo').value = '';

window.esClienteNoInscrito = [
  'cliente_no_inscrito',
  'no_inscrito',
  'bodega'
].includes(data.tipo);

console.log('Tipo cliente:', data.tipo);
console.log('Cliente no inscrito:', window.esClienteNoInscrito);

desbloquearCamposCliente();
document.getElementById('cliNombre')?.focus();

    if (status) {
      status.className = 'text-success';
      status.textContent = data.message || 'Código válido. Complete los datos del cliente.';
    }

  } catch (error) {
    console.error('Error buscando cliente:', error);
    bloquearCamposCliente();
    alert('Error buscando el cliente.');
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.textContent = 'Buscar';
    }
  }
}

function validateStep(step) {
 if (step === 1) {
  const codcliente = document.getElementById('cliCodCliente')?.value.trim();
  const nombre = document.getElementById('cliNombre')?.value.trim();
  const telefono = document.getElementById('cliTelefono')?.value.trim();

  if (!codcliente) {
    alert('Ingrese el código de cliente.');
    document.getElementById('cliCodCliente')?.focus();
    return false;
  }

  if (!clienteDetectado) {
    alert('Primero debe buscar y detectar el cliente.');
    document.getElementById('cliCodCliente')?.focus();
    return false;
  }

  if (!nombre) {
    alert('Ingresa el nombre completo');
    document.getElementById('cliNombre')?.focus();
    return false;
  }

  if (!telefono) {
    alert('Ingresa el teléfono o WhatsApp');
    document.getElementById('cliTelefono')?.focus();
    return false;
  }

  return true;
}

  if (step === 2) {
    const direccion = document.getElementById('entDireccion')?.value.trim();
    const ciudad = document.getElementById('entCiudad')?.value.trim();
    const tipo = document.getElementById('entTipo')?.value.trim();

    if (!direccion) {
      alert('Ingresa la dirección');
      document.getElementById('entDireccion')?.focus();
      return false;
    }

    if (!ciudad) {
      alert('Ingresa la ciudad');
      document.getElementById('entCiudad')?.focus();
      return false;
    }

    if (!tipo) {
      alert('Selecciona el tipo de entrega');
      document.getElementById('entTipo')?.focus();
      return false;
    }

    return true;
  }

 if (step === 3) {
  const metodo = document.getElementById('pagoMetodo')?.value.trim() || '';
  const factura = document.getElementById('pagoFactura')?.value.trim();

  if (!metodo) {
    alert('Selecciona el método de pago');
    document.getElementById('pagoMetodo')?.focus();
    return false;
  }

  if (!factura) {
    alert('Selecciona si desea factura');
    document.getElementById('pagoFactura')?.focus();
    return false;
  }

  return true;
}

  return true;
}
async function checkout(){

  const storeSelect = document.getElementById('store_id');
const store_id = storeSelect ? storeSelect.value : null;

if (!store_id) {
  alert('Debes seleccionar una tienda antes de continuar');
  return;
}

  if (!getCart().length){
    alert('Carrito vacío');
    return;
  }

  // cerrar carrito
  const cartPanel = document.getElementById('cartPanel');
  cartPanel?.classList.add('d-none');

  // calcular total
  const total = getCart().reduce((sum, item) => {
    return sum + (Number(item.price) * Number(item.qty));
  }, 0);

  const wizardTotal = document.getElementById('wizardTotal');
  if (wizardTotal) {
    wizardTotal.textContent = 'Q ' + total.toFixed(2);
  }

  const modalEl = document.getElementById('checkoutModal');

if (modalEl) {
  const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
  modalInstance.hide();
}

showStep(1);

const chkNoInscrito = document.getElementById('chkClienteNoInscrito');
const codClienteInput = document.getElementById('cliCodCliente');
const btnBuscarClienteCheckout = document.getElementById('btnBuscarCliente');

if (chkNoInscrito) chkNoInscrito.checked = false;
if (codClienteInput) {
  codClienteInput.value = '';
  codClienteInput.readOnly = false;
}
if (btnBuscarClienteCheckout) {
  btnBuscarClienteCheckout.disabled = false;
}

bloquearCamposCliente();

  //  salir de fullscrren luego ed ir a pagar
  if (document.fullscreenElement) {
    await document.exitFullscreen();
  }

  // abrir el modal
  const modal = new bootstrap.Modal(modalEl, {
    backdrop: 'static',
    keyboard: false
  });

  modal.show();
}
let currentStep = 1;

function showStep(step) {
  currentStep = step;

  document.querySelectorAll('.wizard-step').forEach(el => {
    el.classList.remove('active');
  });

  document.getElementById('step' + step)?.classList.add('active');

  document.querySelectorAll('.step-pill').forEach((pill, index) => {
    const n = index + 1;
    pill.classList.remove('active', 'done');

    if (n < step) {
      pill.classList.add('done');
    } else if (n === step) {
      pill.classList.add('active');
    }
  });

  const btnBack = document.getElementById('btnBack');
  const btnNext = document.getElementById('btnNext');
  const btnConfirm = document.getElementById('btnConfirm');

  if (btnBack) {
    btnBack.style.display = step === 1 ? 'none' : 'inline-block';
  }

  if (btnNext) {
    btnNext.classList.toggle('d-none', step === 3);
  }

  if (btnConfirm) {
    btnConfirm.classList.toggle('d-none', step !== 3);
  }
}

document.addEventListener('DOMContentLoaded', function () {
  const btnBack = document.getElementById('btnBack');
  const btnNext = document.getElementById('btnNext');
  const btnConfirm = document.getElementById('btnConfirm');
    const btnBuscarCliente = document.getElementById('btnBuscarCliente');
  const cliCodCliente = document.getElementById('cliCodCliente');
  const chkClienteNoInscrito = document.getElementById('chkClienteNoInscrito');

  btnBuscarCliente?.addEventListener('click', detectarCliente);

  cliCodCliente?.addEventListener('input', function () {
    bloquearCamposCliente();
  });

  chkClienteNoInscrito?.addEventListener('change', async function () {
  const codInput = document.getElementById('cliCodCliente');
  const btnBuscar = document.getElementById('btnBuscarCliente');
  const storeSelect = document.getElementById('store_id');
  const storeId = storeSelect?.value || '';

  if (this.checked) {
    if (!storeId) {
      alert('Primero debes seleccionar una tienda.');
      this.checked = false;
      return;
    }

    try {
      const res = await fetch(`/clientes/no-inscrito/tienda/${encodeURIComponent(storeId)}`, {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      const data = await res.json();

      if (!res.ok || !data.ok) {
        alert(data.message || 'No se pudo obtener el código de cliente no inscrito.');
        this.checked = false;
        return;
      }

      if (codInput) {
        codInput.value = data.codigo || '';
        codInput.readOnly = true;
      }

      await detectarCliente();

      if (btnBuscar) {
        btnBuscar.disabled = true;
      }

    } catch (error) {
      console.error('Error obteniendo cliente no inscrito:', error);
      alert('Ocurrió un error al obtener el código de cliente no inscrito.');
      this.checked = false;
    }

  } else {
    if (codInput) {
      codInput.value = '';
      codInput.readOnly = false;
    }

    if (btnBuscar) {
      btnBuscar.disabled = false;
    }

    bloquearCamposCliente();
  }
});

 btnNext?.addEventListener('click', function () {
  if (!validateStep(currentStep)) return;

  if (currentStep < 3) {
    showStep(currentStep + 1);
  }
});

  btnBack?.addEventListener('click', function () {
    if (currentStep > 1) {
      showStep(currentStep - 1);
    }
  });

  btnConfirm?.addEventListener('click', async function () {
  if (!validateStep(1)) return;
  if (!validateStep(2)) return;
  if (!validateStep(3)) return;

  await submitOrder();
});

  showStep(1);
});

async function submitOrder() {
  const cart = getCart();

  if (!cart.length) {
    alert('Carrito vacío');
    return;
  }
const totalCarrito = cart.reduce((sum, item) => {
  return sum + (Number(item.price || 0) * Number(item.qty || 1));
}, 0);

if (!window.esClienteNoInscrito && totalCarrito < 225) {
  const modalEl = document.getElementById('checkoutModal');
  const modalInstance = bootstrap.Modal.getInstance(modalEl);

  modalInstance?.hide();

  document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
  document.body.classList.remove('modal-open');
  document.body.style.removeProperty('padding-right');

  await Swal.fire({
    icon: 'warning',
    title: 'Pedido mínimo',
    html: `
      <p>El pedido mínimo es de <b>Q225.00</b>.</p>
      <p>Tu carrito actual es de <b>Q${totalCarrito.toFixed(2)}</b>.</p>
      <p>Agrega más productos para continuar.</p>
    `,
    confirmButtonText: 'OK',
    confirmButtonColor: '#C2185B'
  });

  return;
}
const storeSelect = document.getElementById('store_id');
const store_id = storeSelect ? storeSelect.value : '';

if (!store_id) {
  await Swal.fire({
    icon: 'warning',
    title: 'Seleccione una tienda',
    text: 'Debes seleccionar una tienda antes de confirmar el pedido.',
    confirmButtonColor: '#C2185B'
  });
  return;
}
 const payload = {
    catalog_id: document.getElementById('catalog_id')?.value || '',

    CodCliente: document.getElementById('cliCodCliente')?.value.trim() || '',
    Nombre: document.getElementById('cliNombre')?.value.trim() || '',
    Telefono: document.getElementById('cliTelefono')?.value.trim() || '',
    nit: document.getElementById('cliNit')?.value.trim() || '',
    dpi: document.getElementById('cliDpi')?.value.trim() || '',
    correo: document.getElementById('cliCorreo')?.value.trim() || '',

    direccion: document.getElementById('entDireccion')?.value.trim() || '',
    ciudad: document.getElementById('entCiudad')?.value.trim() || '',
    entrega_tipo: document.getElementById('entTipo')?.value || '',
    notas: document.getElementById('entNotas')?.value.trim() || '',

    pago_metodo: document.getElementById('pagoMetodo')?.value || '',
    requiere_factura: document.getElementById('pagoFactura')?.value || '',
    store_id: store_id,

    items: cart.map(item => ({
        code: String(item.code ?? ''),
        color: item.color == null ? '' : String(item.color),
        name: String(item.name ?? 'Producto'),
        quantity: Number(item.qty || 1),
        price: Number(item.price || 0)
    }))
};

  const btnConfirm = document.getElementById('btnConfirm');

  if (btnConfirm) {
    btnConfirm.disabled = true;
    btnConfirm.textContent = 'Enviando...';
  }

  try {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    console.log('PAYLOAD PEDIDO:', payload);

    const res = await fetch('/pedido/finalizar', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token,
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(payload)
    });

    const data = await res.json();

    if (!res.ok) {
      console.error('Error backend:', data);

      await Swal.fire({
        icon: 'error',
        title: 'Error',
        text: data.message || 'Error al crear el pedido',
        confirmButtonColor: '#C2185B'
      });

      return;
    }

    if (data.requiere_pago_online && data.pago_metodo === 'neopay') {
  const modalEl = document.getElementById('checkoutModal');
  const modalInstance = bootstrap.Modal.getInstance(modalEl);
  modalInstance?.hide();

  const pagoRes = await fetch(`/pedidos/${data.pedido_id}/neopay/iniciar`, {
    method: 'POST',
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': token
    }
  });

  const pagoData = await pagoRes.json();

  if (!pagoRes.ok || !pagoData.ok) {
    await Swal.fire({
      icon: 'warning',
      title: 'Pedido creado',
      html: `
        <p>Tu pedido fue creado, pero no se pudo iniciar NeoPay.</p>
        <p><b>Pedido:</b> #${data.pedido_id}</p>
        <p>${pagoData.message || 'Intenta nuevamente o contacta a la tienda.'}</p>
      `,
      confirmButtonColor: '#C2185B'
    });

    return;
  }

  clearCart();
  resetCheckoutForm();
  showStep(1);

  if (pagoData.redirect_url) {
    window.location.href = pagoData.redirect_url;
    return;
  }

  await Swal.fire({
    icon: 'info',
    title: 'Pedido preparado para NeoPay',
    html: `
      <p>El pedido quedó preparado para pago en línea.</p>
      <p><b>Pedido:</b> #${data.pedido_id}</p>
      <p><b>Referencia:</b> ${pagoData.referencia || 'Pendiente'}</p>
      <p class="text-muted">Falta conectar la API real de NeoNet para redirigir al pago.</p>
    `,
    confirmButtonColor: '#C2185B'
  });

  return;
}

 let premioHtml = '';

if (data.premio) {
  const acumulado = Number(data.premio.total_acumulado || 0);
  const pedidoActual = Number(data.premio.total_pedido_actual || 0);
  const proyectado = Number(data.premio.total_proyectado || acumulado);
  const faltaC1 = Number(data.premio.faltante_c1 || 0);

  if (data.premio.cliente_no_inscrito || window.esClienteNoInscrito) {

    premioHtml = `
      <hr>
      <div style="text-align:left">
        <h5>👤 Cliente no inscrito</h5>
        <p><b>Este pedido fue recibido correctamente.</b></p>
        <p>No tiene mínimo de compra.</p>
        <p>No acumula compras y no aplica a premios.</p>
      </div>
    `;
  } else if (data.premio.aplica && data.premio.premio) {
    premioHtml = `
      <hr>
      <div style="text-align:left">
        <h5>🎁 Premio disponible</h5>
        <p><b>Con este pedido, al ser confirmado, aplicas a premio ${data.premio.premio.codtproducto}.</b></p>
        <p><b>Acumulado confirmado:</b> Q${acumulado.toFixed(2)}</p>
        <p><b>Pedido actual:</b> Q${pedidoActual.toFixed(2)}</p>
        <p><b>Acumulado al confirmar:</b> Q${proyectado.toFixed(2)}</p>
        <p><b>Premio:</b> ${data.premio.premio.descripcion}</p>
        <p style="font-size:13px;color:#666">
          La tienda confirmará la entrega del premio.
        </p>
      </div>
    `;
  } else {
    premioHtml = `
      <hr>
      <div style="text-align:left">
        <h5>🎁 Acumulado de premio</h5>
        <p><b>Acumulado confirmado del mes:</b> Q${acumulado.toFixed(2)}</p>
        <p>${data.premio.mensaje}</p>
        <p><b>Te faltan:</b> Q${faltaC1.toFixed(2)} para llegar al premio del Rango 1.</p>
      </div>
    `;
  }
}

    let total = 0;
    let detalle = '';

    cart.forEach((item, i) => {
      const qty = Number(item.qty || 1);
      const price = Number(item.price || 0);
      const subtotal = qty * price;
      total += subtotal;

      const code = item.code || '';
      const color = item.color ? `-${item.color}` : '';
      const name = item.name || 'Producto';

      detalle += `${i + 1}. ${name} (${code}${color}) x${qty} - Q ${subtotal.toFixed(2)}\n`;
    });

    const selectedOption = storeSelect.options[storeSelect.selectedIndex];

    const store = {
      name: selectedOption.textContent.trim(),
      whatsapp: selectedOption.dataset.whatsapp || '',
      address: selectedOption.dataset.address || '',
      hours: selectedOption.dataset.hours || '',
      manager: selectedOption.dataset.manager || ''
    };

    const mensaje = [
      'Hola, se ha creado un nuevo pedido:',
      '',
      `Tienda: ${store.name || 'Tienda'}`,
      `Dirección tienda: ${store.address || 'No especificada'}`,
      `Horario: ${store.hours || 'No especificado'}`,
      `Responsable: ${store.manager || 'No especificado'}`,
      '',
      `Código cliente: ${payload.CodCliente}`,
      `Cliente: ${payload.Nombre}`,
      `Teléfono: ${payload.Telefono}`,
      `NIT: ${payload.nit || 'No especificado'}`,
      `DPI: ${payload.dpi || 'No especificado'}`,
      `Dirección entrega: ${payload.direccion}`,
      `Ciudad: ${payload.ciudad}`,
      `Entrega: ${payload.entrega_tipo}`,
      `Método de pago: ${payload.pago_metodo}`,
      `¿Factura?: ${payload.requiere_factura}`,
      '',
      'Productos:',
      detalle,
      `Total: Q ${total.toFixed(2)}`
    ].join('\n');

    const modalEl = document.getElementById('checkoutModal');
    const modalInstance = bootstrap.Modal.getInstance(modalEl);
    modalInstance?.hide();

    await Swal.fire({
      icon: 'success',
      title: 'Pedido recibido',
      html: `
        <p>Ahora te llevaremos a WhatsApp para enviarlo.</p>
        ${premioHtml}
      `,
      confirmButtonText: 'Continuar',
      confirmButtonColor: '#C2185B'
    });

    clearCart();
    resetCheckoutForm();
    showStep(1);

    const numeroEmpresa = (store.whatsapp || '50237553802').replace(/\D/g, '');
    const waUrl = `https://wa.me/${numeroEmpresa}?text=${encodeURIComponent(mensaje)}`;
    window.open(waUrl, '_blank');

  } catch (error) {
    console.error('Error enviando pedido:', error);

    await Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Ocurrió un error al enviar el pedido',
      confirmButtonColor: '#C2185B'
    });

  } finally {
    if (btnConfirm) {
      btnConfirm.disabled = false;
      const metodoActual = document.getElementById('pagoMetodo')?.value || '';

btnConfirm.textContent = metodoActual === 'neopay'
  ? 'Continuar a pago'
  : 'Confirmar pedido';
    }
  }
}


function resetCheckoutForm() {

    const chkNoInscrito = document.getElementById('chkClienteNoInscrito');
  const codClienteInput = document.getElementById('cliCodCliente');
  const btnBuscarClienteReset = document.getElementById('btnBuscarCliente');

  if (chkNoInscrito) chkNoInscrito.checked = false;
  if (codClienteInput) codClienteInput.readOnly = false;
  if (btnBuscarClienteReset) btnBuscarClienteReset.disabled = false;

  document.getElementById('cliCodCliente') && (document.getElementById('cliCodCliente').value = '');
  document.getElementById('cliNombre') && (document.getElementById('cliNombre').value = '');
  document.getElementById('cliTelefono') && (document.getElementById('cliTelefono').value = '');
  document.getElementById('cliNit') && (document.getElementById('cliNit').value = '');
  document.getElementById('cliDpi') && (document.getElementById('cliDpi').value = '');
  document.getElementById('cliCorreo') && (document.getElementById('cliCorreo').value = '');

  bloquearCamposCliente();

  document.getElementById('entDireccion') && (document.getElementById('entDireccion').value = '');
  document.getElementById('entCiudad') && (document.getElementById('entCiudad').value = 'Guatemala');
  document.getElementById('entTipo') && (document.getElementById('entTipo').value = 'envio');
  document.getElementById('entNotas') && (document.getElementById('entNotas').value = '');

 document.getElementById('pagoMetodo') && (document.getElementById('pagoMetodo').value = 'efectivo');
  document.getElementById('pagoFactura') && (document.getElementById('pagoFactura').value = 'no');
}
/* ==========================
   🔍 MODAL IMAGEN
========================== */
function closeImgModal(){
  const modal = document.getElementById('imgModal');
  const img = document.getElementById('imgModalSrc');

  modal?.classList.remove('active');
  if (img) img.src = '';
}



/* ==========================
   📖 FLIPBOOK + FULLSCREEN
========================== */
(function () {
  const root = document.getElementById('flipbook');
  const wrap = document.getElementById('flipbook-wrap');
  const btn = document.getElementById('btnFullscreen');
    const indicator = document.getElementById('page-indicator');

  if (!root || !wrap) return;

    

  const chunkLoader = createPageChunkLoader(root, {
    blockSize: 6,
    threshold: 2
  });

function isSingle() {
  const vw = window.innerWidth;
  const vh = window.innerHeight;
  const shortest = Math.min(vw, vh);

  return shortest <= 600;
}
 // function isSingle() {
  //  return window.innerWidth <= 768;
 // }


function getBaseSize() {
  const vw = window.innerWidth;
  const vh = window.innerHeight;

  const shortest = Math.min(vw, vh);

  //  MÓVIL
  if (isSingle ()) {
    return { width: 460, height: 600, portrait: true };
  }

  //  TABLET (aquí entra tu tablet SIEMPRE)
  if (shortest <= 750) {
    return { width: 490, height: 610, portrait: false }
  }

  //  PC / LAPTOP
  return { width: 800, height: 900, portrait: false }}
  //function getBaseSize() {
    //return isSingle()
    //  d? { width: 460, height: 590, portrait: true }
    //  : { width: 460, height: 600, portrait: false };
  //}

  let base = getBaseSize();

  const pageFlip = new St.PageFlip(root, {
    width: base.width,
    height: base.height,
    size: 'fixed',
    minWidth: base.width,
    maxWidth: base.width,
    minHeight: base.height,
    maxHeight: base.height,
    showCover: true,
    mobileScrollSupport: true,
    usePortrait: base.portrait,
    autoSize: false,
    maxShadowOpacity: 0.98,
    flippingTime:1400
  });

pageFlip.loadFromHTML(root.querySelectorAll('.page'));

const prevBtn = document.getElementById('prev');
const nextBtn = document.getElementById('next');

if (prevBtn) {
  prevBtn.addEventListener('click', function () {
    pageFlip.flipPrev();
  });
}

if (nextBtn) {
  nextBtn.addEventListener('click', function () {
    pageFlip.flipNext();
  });
}

window.pageFlip = pageFlip;
function cargarThumbsCercanas() {
  const pages = Array.from(root.querySelectorAll('.page'));
  const current = pageFlip.getCurrentPageIndex();

  const indicesACargar = [
    current - 1,
    current,
    current + 1,
    current + 2
  ];

  indicesACargar.forEach(index => {
    const page = pages[index];
    if (!page) return;

    page.querySelectorAll('img.product-thumb[data-src]').forEach(img => {
      img.src = img.dataset.src;
      img.removeAttribute('data-src');
    });
  });
}

// Cargar imágenes iniciales visibles
cargarThumbsCercanas();

// Cargar imágenes cuando cambia de página
pageFlip.on('flip', function () {
  cargarThumbsCercanas();
});
lockFlipOnOverlay(root, '.products-overlay');
//bindZoomPreload(root);
bindProductZoom(root);

    function updatePageIndicator() {
    if (!indicator) return;

    const current = pageFlip.getCurrentPageIndex() + 1;
    const loaded = parseInt(root.dataset.loaded || '0', 10);

    indicator.textContent = current + ' / ' + loaded;
  }

  function updateFlipbook() {
    base = getBaseSize();

    root.style.width = base.width + 'px';
    root.style.height = base.height + 'px';
    root.style.transform = 'scale(1)';
    root.style.transformOrigin = 'center center';

    pageFlip.update({
      width: base.width,
      height: base.height,
      size: 'fixed',
      minWidth: base.width,
      maxWidth: base.width,
      minHeight: base.height,
      maxHeight: base.height,
      showCover: true,
      mobileScrollSupport: true,
      usePortrait: base.portrait,
      autoSize: false,
      maxShadowOpacity: 0.35
    });

    setTimeout(() => {
  try {
    pageFlip.updateFromHtml(root.querySelectorAll('.page'));
lockFlipOnOverlay(root, '.products-overlay');
bindZoomPreload(root);
bindProductZoom(root);
  } catch (e) {
    console.warn('updateFromHtml:', e);
  }
}, 50);

    console.log('base:', base);
console.log('flipbook width:', document.getElementById('flipbook').offsetWidth);
  }

  function isFullscreenActive() {
    return !!document.fullscreenElement || !!document.webkitFullscreenElement;
  }

  function applyFullscreenScale() {
    //updateFlipbook();
      updatePageIndicator();

    if (!isFullscreenActive()) return;

    const vw = window.innerWidth ;
    const vh = window.innerHeight ;

    const scaleX = vw / base.width;
    const scaleY = vh / base.height;
    const scale = Math.min(scaleX, scaleY) * 0.99;

    root.style.transform = `scale(${scale})`;
    root.style.transformOrigin = 'center center';
  }

  async function toggleFullscreen() {
    try {
      if (!isFullscreenActive()) {
        if (wrap.requestFullscreen) {
          await wrap.requestFullscreen();
        } else if (wrap.webkitRequestFullscreen) {
          await wrap.webkitRequestFullscreen();
        }
      } else {
        if (document.exitFullscreen) {
          await document.exitFullscreen();
        } else if (document.webkitExitFullscreen) {
          await document.webkitExitFullscreen();
        }
      }

      setTimeout(() => {
        applyFullscreenScale();
      }, 180);
    } catch (e) {
      console.warn('Error fullscreen:', e);
    }
  }

  btn?.addEventListener('click', toggleFullscreen);
  document.addEventListener('fullscreenchange', () => {
    setTimeout(() => {
      applyFullscreenScale();
    }, 180);
  });

  document.addEventListener('webkitfullscreenchange', () => {
    setTimeout(() => {
      applyFullscreenScale();
    }, 180);
  });

  window.addEventListener('resize', () => {
    if (isFullscreenActive()) {
      applyFullscreenScale();
    } else {
      updateFlipbook();
    }
  });

  window.addEventListener('orientationchange', () => {
    setTimeout(() => {
      if (isFullscreenActive()) {
        applyFullscreenScale();
      } else {
        updateFlipbook();
      }
    }, 150);
  });


  //boton expandir solo para tablet
  updateFlipbook();

  function isTablet() {
  const vw = window.innerWidth;
  const vh = window.innerHeight;
  const shortest = Math.min(vw, vh);

  return shortest > 600 && shortest <= 900;
}

function updateFullscreenButton() {
  const btn = document.getElementById('btnFullscreen');
  if (!btn) return;

  if (isTablet()) {
    btn.style.display = 'block';
  } else {
    btn.style.display = 'none';
  }
}

updateFullscreenButton();

window.addEventListener('resize', updateFullscreenButton);
window.addEventListener('orientationchange', updateFullscreenButton);
 pageFlip.on('flip', () => {
  updatePageIndicator();
  chunkLoader.check(pageFlip);
});
})();

//ZZOOOOOMMM AL PRODUCTO
function openImgModal(img) {
  console.log('zoom sí entró');

  const modal = document.getElementById('imgModal');
  const modalImg = document.getElementById('imgModalSrc');

  if (!modal || !modalImg || !img) {
    console.warn('Falta modal, modalImg o img');
    return;
  }

  const thumbSrc = img.src;
  const largeSrc = img.dataset.large || thumbSrc;

  const cachedImg = preloadZoomImage(largeSrc);

  // Si la imagen grande ya está cargada, la muestra de una vez
  if (cachedImg && cachedImg.complete && cachedImg.naturalWidth > 0) {
    modalImg.src = largeSrc;
    modal.classList.add('active');
    return;
  }

  // Si todavía no termina de cargar, abre con miniatura y la cambia enseguida
  modalImg.src = thumbSrc;
  modal.classList.add('active');

  if (cachedImg) {
    cachedImg.onload = function () {
      modalImg.src = largeSrc;
    };

    cachedImg.onerror = function () {
      modalImg.src = thumbSrc;
    };
  }
}

document.getElementById('imgModal')?.addEventListener('click', function (e) {
  if (e.target.id === 'imgModal') {
    closeImgModal();
  }
});

document.getElementById('imgModalClose')?.addEventListener('click', function (e) {
  e.stopPropagation();
  closeImgModal();
});

document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') {
    closeImgModal();
  }
});

document.addEventListener('DOMContentLoaded', function () {
  renderCart();
  updateCartBadge();

 
});

document.addEventListener('DOMContentLoaded', function () {
  const btn = document.getElementById('btnConsultarAcumulado');
  const input = document.getElementById('consultaCodCliente');
  const box = document.getElementById('resultadoAcumuladoModal');
const modalAcumuladoEl = document.getElementById('modalAcumulado');
const modalAcumulado = modalAcumuladoEl
  ? bootstrap.Modal.getOrCreateInstance(modalAcumuladoEl)
  : null;

  if (!btn || !input || !box) return;

  btn.addEventListener('click', consultarAcumulado);

  input.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
      consultarAcumulado();
    }
  });

  async function consultarAcumulado() {
    const codcliente = input.value.trim();

    if (!codcliente) {
      box.innerHTML = `
        <div class="alert alert-warning mb-0">
          Ingresa tu código de cliente.
        </div>
      `;
      input.focus();
      return;
    }

   btn.disabled = true;
btn.textContent = 'Consultando...';

box.innerHTML = `
  <div class="acumulado-loading">
    <div>
      <div class="spinner-border text-light" role="status"></div>
      <div class="mt-3 fw-bold">Consultando acumulado...</div>
    </div>
  </div>
`;

modalAcumulado?.show();

    try {
      const res = await fetch(`/clientes/acumulado/${encodeURIComponent(codcliente)}`, {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      const data = await res.json();

      if (!res.ok || !data.ok) {
        box.innerHTML = `
          <div class="alert alert-danger mb-0">
            ${data.message || 'No se pudo consultar el acumulado.'}
          </div>
        `;
        return;
      }

      if (data.tipo === 'cliente_no_inscrito') {
        box.innerHTML = `
          <div class="alert alert-info mb-0">
            <b>Cliente no inscrito</b><br>
            Este código no tiene mínimo de compra, pero no acumula compras ni aplica a premios.
          </div>
        `;
        return;
      }

      const acumulado = Number(data.acumulado || 0);
      const puntos = Number(data.puntos || 0);
      const faltaC1 = Number(data.faltante_c1 || 0);
      const faltaC2 = Number(data.faltante_c2 || 0);

const mejorOpcion = data.mejor_opcion || null;
const opcionesPremio = Array.isArray(data.opciones_premio) ? data.opciones_premio : [];

let estadoPremio = '';
let opcionesCanjeHtml = '';
let tituloTerceraTarjeta = 'Faltante para Rango 1';
let valorTerceraTarjeta = `Q${faltaC1.toFixed(2)}`;

// Función para escribir bonito el texto de premios
function textoDePremios(opcion) {
  const c1 = Number(opcion.cantidad_c1 || 0);
  const c2 = Number(opcion.cantidad_c2 || 0);

  let textos = [];

  if (c2 > 0) {
    textos.push(`${c2} premio${c2 > 1 ? 's' : ''} del Rango 2`);
  }

  if (c1 > 0) {
    textos.push(`${c1} premio${c1 > 1 ? 's' : ''} del Rango 1`);
  }

  return textos.join(' + ');
}

if (mejorOpcion) {
  const usado = Number(mejorOpcion.monto_usado || 0);
  const saldo = Number(mejorOpcion.saldo_restante || 0);
  const faltaOtroC1 = Number(mejorOpcion.faltante_para_otro_c1 || 0);
  const textoRecomendado = textoDePremios(mejorOpcion);

  tituloTerceraTarjeta = 'Canje recomendado';
  valorTerceraTarjeta = textoRecomendado;

  estadoPremio = `
    <div class="alert alert-success mt-3 mb-0">
      🎉 <b>Opción recomendada:</b> ${textoRecomendado}<br>
      <span>Usa: <b>Q${usado.toFixed(2)}</b></span><br>
      <span>Saldo restante: <b>Q${saldo.toFixed(2)}</b></span><br>
      <span>Faltante para otro Premio del Rango 1: <b>Q${faltaOtroC1.toFixed(2)}</b></span>
    </div>
  `;

  // Mostrar todas las opciones de canje
  opcionesCanjeHtml = `
    <div class="mt-3">
      <h6 class="fw-bold mb-2">🎁 Todas tus opciones de canje</h6>

      <div class="row g-2">
        ${opcionesPremio.map((opcion, index) => {
          const usadoOpcion = Number(opcion.monto_usado || 0);
          const saldoOpcion = Number(opcion.saldo_restante || 0);
          const textoOpcion = textoDePremios(opcion);

          return `
            <div class="col-md-6">
              <div class="canje-opcion-card border rounded-3 p-3 bg-white h-100">
                <div class="d-flex justify-content-between align-items-start gap-2">
                  <b>${textoOpcion}</b>
                  ${index === 0 ? '<span class="badge text-bg-success">Recomendada</span>' : ''}
                </div>

                <div class="small mt-2">
                  <div>Usa: <b>Q${usadoOpcion.toFixed(2)}</b></div>
                  <div>Saldo restante: <b>Q${saldoOpcion.toFixed(2)}</b></div>
                </div>
              </div>
            </div>
          `;
        }).join('')}
      </div>
    </div>
  `;

} else {
  estadoPremio = `
    <div class="alert alert-warning mt-3 mb-0">
      Aún no llega a premio. Le faltan <b>Q${faltaC1.toFixed(2)}</b> para llegar a Rango 1.
    </div>
  `;
}

      box.innerHTML = `
        <div class="acumulado-box">
          <h5 class="mb-3">🎁 Acumulado del mes</h5>

          <div class="acumulado-grid">
            <div class="acumulado-item">
              <span>Acumulado confirmado</span>
              <strong>Q${acumulado.toFixed(2)}</strong>
            </div>

            <div class="acumulado-item">
              <span>Puntos acumulados</span>
              <strong>${puntos.toFixed(0)}</strong>
            </div>

            <div class="acumulado-item">
  <span>${tituloTerceraTarjeta}</span>
  <strong>${valorTerceraTarjeta}</strong>
</div>
          </div>

  <div class="mt-3">
  ${estadoPremio}
  ${opcionesCanjeHtml}
</div>

<p class="mt-2 mb-0 text-muted small">
  Solo cuentan compras al contado confirmadas.
</p>
        </div>
      `;

    } catch (error) {
      console.error(error);

      box.innerHTML = `
        <div class="alert alert-danger mb-0">
          Ocurrió un error al consultar el acumulado.
        </div>
      `;
    } finally {
      btn.disabled = false;
      btn.textContent = 'Consultar';
    }
  }
});


document.addEventListener('DOMContentLoaded', function () {

    const modal = document.getElementById('imgModal');
    const modalImg = document.getElementById('imgModalSrc');
    const closeBtn = modal ? modal.querySelector('.img-close') : null;
    const rewardImages = document.querySelectorAll('.reward-zoom-img');

    function abrirModal(img) {
        if (!modal || !modalImg) return;

        modalImg.src = img.dataset.full || img.src;
        modalImg.alt = img.alt || 'Premio ampliado';

        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function cerrarModal() {
        if (!modal || !modalImg) return;

        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
        modalImg.src = '';
        document.body.style.overflow = '';
    }

    rewardImages.forEach(img => {
        img.addEventListener('click', function () {
            abrirModal(this);
        });
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', cerrarModal);
    }

    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                cerrarModal();
            }
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal && modal.classList.contains('active')) {
            cerrarModal();
        }
    });

});

document.addEventListener('DOMContentLoaded', function () {
  const pagoMetodo = document.getElementById('pagoMetodo');
  const neoPayInfo = document.getElementById('neoPayInfo');
  const btnConfirm = document.getElementById('btnConfirm');

  if (!pagoMetodo) return;

  function actualizarMetodoPago() {
    if (pagoMetodo.value === 'neopay') {
      neoPayInfo?.classList.remove('d-none');

      if (btnConfirm) {
        btnConfirm.textContent = 'Continuar a pago';
      }
    } else {
      neoPayInfo?.classList.add('d-none');

      if (btnConfirm) {
        btnConfirm.textContent = 'Confirmar pedido';
      }
    }
  }

  pagoMetodo.addEventListener('change', actualizarMetodoPago);
  actualizarMetodoPago();
});


(function () {
    if (document.getElementById('stockTooltip')) return;

    const tooltip = document.createElement('div');
    tooltip.id = 'stockTooltip';
    document.body.appendChild(tooltip);

    let activeCard = null;

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (s) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[s];
        });
    }

    function renderStock(data) {
        if (!Array.isArray(data) || data.length === 0) {
            return `
                <div class="stock-title">Existencias</div>
                <div class="stock-empty">Sin unidades disponibles.</div>
            `;
        }

        const total = data.reduce((sum, item) => {
            return sum + Number(item.stock || 0);
        }, 0);

        const rows = data.map(item => {
    const tienda = item.tienda || item.Nombodega || item.bodega || 'Tienda';
    const stock = Number(item.stock || item.Saldo || 0);

    return `
        <div class="stock-line">
            <span>${escapeHtml(tienda)}</span>
            <b>${stock}</b>
        </div>
    `;
}).join('');


//${total}
        return `
            <div class="stock-title">Existencias: </div>
            ${rows}
        `;
    }

    function moveTooltip(event) {
        let x = event.clientX + 18;
        let y = event.clientY + 18;

        tooltip.style.left = x + 'px';
        tooltip.style.top = y + 'px';

        const rect = tooltip.getBoundingClientRect();

        if (rect.right > window.innerWidth - 12) {
            x = event.clientX - rect.width - 18;
        }

        if (rect.bottom > window.innerHeight - 12) {
            y = event.clientY - rect.height - 18;
        }

        tooltip.style.left = x + 'px';
        tooltip.style.top = y + 'px';
    }

    document.addEventListener('pointerover', function (event) {
        if (event.pointerType !== 'mouse') return;

        const card = event.target.closest('.product-mini');
        if (!card) return;

        activeCard = card;

        let data = [];

        try {
            const rawStock = card.getAttribute('data-stock') || '[]';
data = JSON.parse(rawStock);
        } catch (e) {
            data = [];
        }

        tooltip.innerHTML = renderStock(data);
        tooltip.classList.add('visible');
        moveTooltip(event);
    });

    document.addEventListener('pointermove', function (event) {
        if (!activeCard) return;
        moveTooltip(event);
    });

    document.addEventListener('pointerout', function (event) {
        const card = event.target.closest('.product-mini');

        if (!card) return;
        if (card.contains(event.relatedTarget)) return;

        activeCard = null;
        tooltip.classList.remove('visible');
    });
})();

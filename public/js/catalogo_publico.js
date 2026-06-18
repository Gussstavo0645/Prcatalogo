const CART_KEY = 'flipbook_cart_v1';


function getCsrfToken() {
  const metaToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  const inputToken = document.querySelector('input[name="_token"]')?.value;

  return metaToken || inputToken || '';
}

function esTelefono() {
  const vw = window.innerWidth;
  const vh = window.innerHeight;

  const shortest = Math.min(vw, vh);
  const longest = Math.max(vw, vh);

  return shortest < 600 && longest <= 950;
}

function esTablet() {
  const vw = window.innerWidth;
  const vh = window.innerHeight;

  const shortest = Math.min(vw, vh);
  const longest = Math.max(vw, vh);

  return (
    shortest >= 600 ||
    (shortest >= 540 && longest > 950)
  );
}

async function salirFullscreenSiActivo() {
  try {
    if (document.fullscreenElement && document.exitFullscreen) {
      await document.exitFullscreen();
    } else if (document.webkitFullscreenElement && document.webkitExitFullscreen) {
      document.webkitExitFullscreen();
    }
  } catch (e) {
    // Si el navegador bloquea salir de fullscreen, seguimos limpiando el modo CSS.
  }

  sessionStorage.removeItem('catalog_keep_fullscreen');
  sessionStorage.removeItem('catalog_restore_fullscreen');

  // También quitar pantalla completa por CSS
  document.body.classList.remove(
    'tablet-catalog-fullscreen',
    'fs-portrait',
    'fs-landscape'
  );

  const flipbook = document.getElementById('flipbook');

  if (flipbook) {
    flipbook.style.transform = 'scale(1)';
    flipbook.style.opacity = '1';
  }

  setTimeout(() => {
    window.forzarAjusteFlipbook?.();
  }, 200);
}
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

  const panel = document.getElementById('cartPanel');

  if (panel && !panel.classList.contains('d-none')) {
    setTimeout(() => {
      positionCartPanelNearFab();
    }, 10);
  }
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

function escHtml(value) {
  const div = document.createElement('div');
  div.textContent = value ?? '';
  return div.innerHTML;
}

function money(value) {
  return 'Q ' + Number(value || 0).toFixed(2);
}

function renderCart(){
  const cart = getCart();
  const wrap = document.getElementById('cartItems');
  const totalEl = document.getElementById('cartTotal');

  if (!wrap) return;

  if (!cart.length){
    wrap.innerHTML = `
      <div class="cart-empty">
        <div class="cart-empty-icon">🛒</div>
        <strong>Tu carrito está vacío</strong>
        <span>Agrega productos del catálogo para iniciar tu pedido.</span>
      </div>
    `;

    if (totalEl) totalEl.textContent = 'Q 0.00';
    return;
  }

  let total = 0;

  wrap.innerHTML = cart.map((item, i) => {
    const price = Number(item.price || 0);
    const qty = Number(item.qty || 1);
    const subtotal = price * qty;

    total += subtotal;

    return `
      <div class="cart-product-card">
        <img 
          class="cart-product-img"
          src="${escHtml(item.img || '')}" 
          alt="${escHtml(item.name || 'Producto')}"
        >

        <div class="cart-product-info">
          <div class="cart-product-name">
            ${escHtml(item.name || 'Producto')}
          </div>

          <div class="cart-product-code">
            Código: ${escHtml(item.code || '')}
            ${item.color ? ` | Color: ${escHtml(item.color)}` : ''}
          </div>

          <div class="cart-product-price">
            <span>${money(price)} c/u</span>
            <strong>${money(subtotal)}</strong>
          </div>

          <div class="cart-qty-row">
            <button type="button" class="cart-qty-btn" onclick="changeQty(${i}, -1)">−</button>
            <span class="cart-qty-number">${qty}</span>
            <button type="button" class="cart-qty-btn" onclick="changeQty(${i}, 1)">+</button>
          </div>
        </div>

        <button 
          type="button" 
          class="cart-remove-btn" 
          onclick="removeCartItem(${i})"
          title="Quitar producto"
        >
          ×
        </button>
      </div>
    `;
  }).join('');

  if (totalEl) totalEl.textContent = money(total);
}
function positionCartPanelNearFab() {
  const panel = document.getElementById('cartPanel');
  const fab = document.getElementById('cartFab');

  if (!panel || !fab) return;

  const margen = 12;
  const fabRect = fab.getBoundingClientRect();

  panel.style.visibility = 'hidden';
  panel.classList.remove('d-none');

  const panelRect = panel.getBoundingClientRect();

  let left = fabRect.left;
  let top = fabRect.top - panelRect.height - 12;

  // Si no cabe arriba, se abre abajo de la burbuja
  if (top < margen) {
    top = fabRect.bottom + 12;
  }

  // Que no se salga a la derecha
  if (left + panelRect.width > window.innerWidth - margen) {
    left = window.innerWidth - panelRect.width - margen;
  }

  // Que no se salga a la izquierda
  if (left < margen) {
    left = margen;
  }

  // Que no se salga abajo
  if (top + panelRect.height > window.innerHeight - margen) {
    top = window.innerHeight - panelRect.height - margen;
  }

  panel.style.position = 'fixed';
  panel.style.left = left + 'px';
  panel.style.top = top + 'px';
  panel.style.right = 'auto';
  panel.style.bottom = 'auto';
  panel.style.visibility = 'visible';
}
function openCart(){
  const panel = document.getElementById('cartPanel');

  if (!panel) {
    console.error('No existe #cartPanel');
    return;
  }

  renderCart();

  panel.classList.remove('d-none');
  document.body.classList.add('cart-open');

  setTimeout(() => {
    positionCartPanelNearFab();
  }, 10);
}

function closeCart(){
  const panel = document.getElementById('cartPanel');

  if (!panel) return;

  panel.classList.add('d-none');
  document.body.classList.remove('cart-open');
}

function toggleCart(){
  const panel = document.getElementById('cartPanel');

  if (!panel) {
    console.error('No existe #cartPanel');
    return;
  }

  if (panel.classList.contains('d-none')) {
    openCart();
  } else {
    closeCart();
  }
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

  const requestedOffsets = new Set();

  function getPageKey(page) {
    if (!page) return '';

    // Esta es la clave correcta.
    // Permite página 38 parte 1 y página 38 parte 2.
    if (page.dataset.renderKey) {
      return String(page.dataset.renderKey).trim();
    }

    // Fallback
    if (page.dataset.pageId && page.dataset.pageNumber) {
      return 'page-' + page.dataset.pageId + '-' + page.dataset.pageNumber;
    }

    if (page.dataset.pageNumber) {
      return 'num-' + String(page.dataset.pageNumber).trim();
    }

    const badge = page.querySelector('.page-badge');

    if (badge) {
      return badge.textContent.trim();
    }

    return page.outerHTML.slice(0, 150);
  }
  function getLoadedRealPages() {
    return root.querySelectorAll('.page').length;
  }

  async function loadNextBlock(pageFlip) {
    if (loading) return;

    const slug = root.dataset.slug;
    const total = parseInt(root.dataset.total || '0', 10);

    // Usar páginas reales del DOM, no solo dataset.loaded
    const loaded = getLoadedRealPages();

    if (loaded >= total) return;

      if (requestedOffsets.has(loaded)) {
      return;
    }

    requestedOffsets.add(loaded);
    loading = true;

    try {
      const res = await fetch(`/c/${slug}/bloque?offset=${loaded}&limit=${blockSize}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });

      if (!res.ok) throw new Error(`HTTP ${res.status}`);

      const data = await res.json();

      if (!data.html || Number(data.count || 0) <= 0) return;

      const currentIndex = pageFlip.getCurrentPageIndex();

      const temp = document.createElement('div');
      temp.innerHTML = data.html;

      const existingKeys = new Set(
        Array.from(root.querySelectorAll('.page')).map(getPageKey)
      );

      const nuevasPaginas = Array.from(temp.querySelectorAll('.page'));

      let agregadas = 0;

      nuevasPaginas.forEach(page => {
        const key = getPageKey(page);

        if (existingKeys.has(key)) {
          console.warn('Página repetida ignorada:', key);
          return;
        }

        existingKeys.add(key);
        root.appendChild(page);
        agregadas++;
      });

      // Actualiza loaded con el total real del DOM
      root.dataset.loaded = String(getLoadedRealPages());

      if (agregadas <= 0) {
        console.warn('El bloque vino repetido. No se agregó ninguna página.');
        return;
      }

      const newThumbs = Array.from(root.querySelectorAll('.product-thumb')).slice(-12);
      newThumbs.forEach(img => {
        const preload = new Image();
        preload.src = img.src || img.dataset.src || '';
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
    const loaded = getLoadedRealPages();
    const total = parseInt(root.dataset.total || '0', 10);
    const current = pageFlip.getCurrentPageIndex() + 1;

    root.dataset.loaded = String(loaded);

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

  function isMobile() {
    return window.innerWidth <= 600;
  }

  function fixMobilePosition() {
    if (!isMobile()) return;

    const rect = fab.getBoundingClientRect();
    const maxLeft = window.innerWidth - rect.width;
    const margen = 14;

    let left = rect.left;

    if (!left || left < margen || left > maxLeft) {
      left = maxLeft - margen;
    }

    left = Math.max(margen, Math.min(left, maxLeft - margen));

    fab.style.position = 'fixed';
    fab.style.left = left + 'px';
    fab.style.right = 'auto';
    fab.style.top = 'auto';
   fab.style.bottom = '12px';
  }

  function setInitialPosition() {
    const rect = fab.getBoundingClientRect();

    if (isMobile()) {
      const maxLeft = window.innerWidth - rect.width;
      const margen = 14;

      fab.style.position = 'fixed';
      fab.style.left = Math.max(margen, maxLeft - margen) + 'px';
      fab.style.right = 'auto';
      fab.style.top = 'auto';
      fab.style.bottom = '12px';
      return;
    }

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

    if (Math.abs(dx) > 4) {
      moved = true;
    }

    const rect = fab.getBoundingClientRect();
    const maxLeft = window.innerWidth - rect.width;
    const margen = 14;

    let newLeft = initialLeft + dx;

    if (isMobile()) {
      // EN CELULAR SOLO SE MUEVE HORIZONTAL
      newLeft = Math.max(margen, Math.min(newLeft, maxLeft - margen));

      fab.style.position = 'fixed';
      fab.style.left = newLeft + 'px';
      fab.style.right = 'auto';
      fab.style.top = 'auto';
      fab.style.bottom = '12px';

    } else {
      // EN PC/TABLET SE MUEVE LIBRE
      const dy = e.clientY - startY;
      let newTop = initialTop + dy;
      const maxTop = window.innerHeight - rect.height;

      newLeft = Math.max(0, Math.min(newLeft, maxLeft));
      newTop = Math.max(0, Math.min(newTop, maxTop));

      fab.style.left = newLeft + 'px';
      fab.style.top = newTop + 'px';
      fab.style.right = 'auto';
      fab.style.bottom = 'auto';
    }
  });

 fab.addEventListener('pointerup', function (e) {
  isDragging = false;
  fab.classList.remove('dragging');

  if (isMobile()) {
    fixMobilePosition();
  }

  const panel = document.getElementById('cartPanel');

  if (panel && !panel.classList.contains('d-none')) {
    setTimeout(() => {
      positionCartPanelNearFab();
    }, 10);
  }

  fab.releasePointerCapture(e.pointerId);
});

  fab.addEventListener('pointercancel', function () {
    isDragging = false;
    fab.classList.remove('dragging');

    if (isMobile()) {
      fixMobilePosition();
    }
  });

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

 

  window.addEventListener('resize', function () {
    fixMobilePosition();
  });

  window.addEventListener('orientationchange', function () {
    setTimeout(fixMobilePosition, 200);
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
document.body.classList.remove('cart-open');

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



// salir de pantalla completa antes de abrir el formulario de pedido
await salirFullscreenSiActivo();

if (!modalEl) {
  console.error('No existe #checkoutModal');
  alert('No se encontró el formulario de pedido.');
  return;
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
    _token: getCsrfToken(),

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
const token = getCsrfToken();

if (!token) {
  await Swal.fire({
    icon: 'error',
    title: 'Error de seguridad',
    text: 'No se encontró el token CSRF. Recarga la página e intenta nuevamente.',
    confirmButtonColor: '#C2185B'
  });

  return;
}


const res = await fetch('/pedido/finalizar', {
  method: 'POST',
  credentials: 'same-origin',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': token
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
    credentials: 'same-origin',
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

if (data.premio && data.premio.mostrar_acumulado !== false) {
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

const reviewUrl = data.pedido_id 
  ? `/pedido/${data.pedido_id}/calificar`
  : null;

await Swal.fire({
  icon: 'success',
  title: 'Pedido recibido',
  html: `
    <p>Tu pedido fue creado correctamente.</p>
    <p>Ahora te llevaremos a WhatsApp para enviarlo.</p>
    ${premioHtml}
    ${reviewUrl ? `
      <hr>
      <div style="text-align:left">
        <h5>⭐ Califica tus productos</h5>
        <p style="margin-bottom:0">
          Después de enviar el pedido por WhatsApp, podrás calificar los productos.
        </p>
      </div>
    ` : ''}
  `,
  confirmButtonText: 'Continuar a WhatsApp',
  confirmButtonColor: '#C2185B',
  allowOutsideClick: false,
  allowEscapeKey: false
});

clearCart();
resetCheckoutForm();
showStep(1);

const numeroEmpresa = (store.whatsapp || '50237553802').replace(/\D/g, '');
const waUrl = `https://wa.me/${numeroEmpresa}?text=${encodeURIComponent(mensaje)}`;

window.open(waUrl, '_blank');

if (reviewUrl) {
  setTimeout(async () => {
    const resultReview = await Swal.fire({
      icon: 'question',
      title: '¿Quieres calificar tus productos?',
      html: `
        <p>Ya abrimos WhatsApp para enviar tu pedido.</p>
        <p>También puedes calificar los productos que compraste.</p>
      `,
      showCancelButton: true,
      confirmButtonText: 'Calificar ahora',
      cancelButtonText: 'Después',
      confirmButtonColor: '#6d3cff',
      cancelButtonColor: '#6c757d'
    });

    if (resultReview.isConfirmed) {
      window.open(reviewUrl, '_blank');
    }
  }, 800);
}

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
   📖 FLIPBOOK + FULLSCREEN
========================== */
(function () {
  const root = document.getElementById('flipbook');
  const wrap = document.getElementById('flipbook-wrap');
  const indicator = document.getElementById('page-indicator');

  if (!root || !wrap) return;

    

  const chunkLoader = createPageChunkLoader(root, {
    blockSize: 12,
    threshold: 4
  });



function getBaseSize() {
  const vw = window.innerWidth;
  const vh = window.innerHeight;

  // MÓVIL
  if (esTelefono()) {
    return { width: 350, height: 500, portrait: true };
  }

  // TABLET VERTICAL: una página
  if (vh > vw) {
    return { width: 720, height: 1000, portrait: true };
  }

  // TABLET HORIZONTAL: dos páginas tipo libro
  if (vw > vh && vw <= 1200) {
    return { width: 470, height: 900, portrait: false };
  }

  // PC / LAPTOP
  return { width: 800, height: 900, portrait: false };
}

function shouldShowCover() {
  const vw = window.innerWidth;
  const vh = window.innerHeight;

  // En tablet horizontal no usar portada sola
  if (vw > vh && vw <= 1200) {
    return false;
  }

  return true;
}
  

  let base = getBaseSize();

  const pageFlip = new St.PageFlip(root, {
    width: base.width,
    height: base.height,
    size: 'fixed',
    minWidth: base.width,
    maxWidth: base.width,
    minHeight: base.height,
    maxHeight: base.height,
showCover: shouldShowCover(),
    mobileScrollSupport: true,
    usePortrait: base.portrait,
    autoSize: false,
    maxShadowOpacity: 0.98,
    flippingTime:1400
  });

  root.style.width = (base.portrait ? base.width : base.width * 2) + 'px';
root.style.height = base.height + 'px';

pageFlip.loadFromHTML(root.querySelectorAll('.page'));

// Precargar el siguiente bloque para que en teléfono no se quede en página 6
setTimeout(() => {
  chunkLoader.loadNextBlock(pageFlip);
}, 600);

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
const fsPrevBtn = document.getElementById('fsPrev');
const fsNextBtn = document.getElementById('fsNext');

function bindFlipButton(button, action) {
  if (!button) return;

  button.addEventListener('pointerdown', function (e) {
    e.preventDefault();
    e.stopPropagation();
  }, { passive: false });

  button.addEventListener('click', function (e) {
    e.preventDefault();
    e.stopPropagation();

    if (action === 'prev') {
      pageFlip.flipPrev();
    }

    if (action === 'next') {
      pageFlip.flipNext();
    }
  });
}

bindFlipButton(fsPrevBtn, 'prev');
bindFlipButton(fsNextBtn, 'next');

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
bindZoomPreload(root);
bindProductZoom(root);

    function updatePageIndicator() {
    if (!indicator) return;

    const current = pageFlip.getCurrentPageIndex() + 1;
    const loaded = parseInt(root.dataset.loaded || '0', 10);

    indicator.textContent = current + ' / ' + loaded;
  }

function updateFlipbook() {
  base = getBaseSize();

  const realWidth = base.portrait ? base.width : base.width * 2;

  root.style.width = realWidth + 'px';
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
    showCover: shouldShowCover(),
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
}
 function isFullscreenActive() {
  return (
    !!document.fullscreenElement ||
    !!document.webkitFullscreenElement ||
    document.body.classList.contains('tablet-catalog-fullscreen')
  );
}

function applyFullscreenScale() {
  updatePageIndicator();

  const fullscreenCssActivo = document.body.classList.contains('tablet-catalog-fullscreen');

  if (!isFullscreenActive() && !fullscreenCssActivo) return;

  base = getBaseSize();

  const vw = window.innerWidth;
  const vh = window.innerHeight;

  const realWidth = base.portrait ? base.width : base.width * 2;
  const realHeight = base.height;

  root.style.width = realWidth + 'px';
  root.style.height = realHeight + 'px';

  const scaleX = vw / realWidth;
  const scaleY = vh / realHeight;

  // Mantiene la proporción real del catálogo
  const scale = Math.min(scaleX, scaleY) * 0.98;

  root.style.transform = `scale(${scale})`;
  root.style.transformOrigin = 'center center';

}

window.forzarAjusteFlipbook = function () {
  try {
    updateFlipbook();

    setTimeout(() => {
      applyFullscreenScale();
      updatePageIndicator();
      root.style.opacity = '1';
    }, 200);

  } catch (e) {
    console.warn('No se pudo forzar ajuste del flipbook:', e);
  }
};
let lastFlipbookOrientation = window.innerHeight >= window.innerWidth
  ? 'portrait'
  : 'landscape';

function getCurrentFlipbookOrientation() {
  return window.innerHeight >= window.innerWidth
    ? 'portrait'
    : 'landscape';
}

function refreshFlipbookAfterRotate() {
  const orientacionActual = getCurrentFlipbookOrientation();

  // Evita que el scroll del teléfono oculte el catálogo
  if (orientacionActual === lastFlipbookOrientation && !isFullscreenActive()) {
    root.style.opacity = '1';
    return;
  }

  clearTimeout(window.__flipbookRotateTimer);

  // Oculta solo cuando realmente gira la pantalla
  root.style.opacity = '0';
  root.style.transform = 'scale(1)';

  window.__flipbookRotateTimer = setTimeout(() => {
    const currentIndex = pageFlip.getCurrentPageIndex();

    base = getBaseSize();

    const realWidth = base.portrait ? base.width : base.width * 2;

    root.style.width = realWidth + 'px';
    root.style.height = base.height + 'px';

    pageFlip.update({
      width: base.width,
      height: base.height,
      size: 'fixed',
      minWidth: base.width,
      maxWidth: base.width,
      minHeight: base.height,
      maxHeight: base.height,
      showCover: shouldShowCover(),
      mobileScrollSupport: true,
      usePortrait: base.portrait,
      autoSize: false,
      maxShadowOpacity: 0.35
    });

    setTimeout(() => {
      try {
        pageFlip.updateFromHtml(root.querySelectorAll('.page'));

        const totalPages = root.querySelectorAll('.page').length;
        const safeIndex = Math.min(currentIndex, totalPages - 1);

        pageFlip.turnToPage(safeIndex);
      } catch (e) {
        console.warn('updateFromHtml después de giro:', e);
      }

      if (isFullscreenActive()) {
        applyFullscreenScale();
      }

      updatePageIndicator();

      root.style.opacity = '1';
      lastFlipbookOrientation = getCurrentFlipbookOrientation();

    }, 300);

  }, 500);
}


window.addEventListener('resize', function () {
  const orientacionActual = getCurrentFlipbookOrientation();

  // Si solo fue scroll en teléfono, NO recalcular catálogo
  if (orientacionActual === lastFlipbookOrientation && !isFullscreenActive()) {
    root.style.opacity = '1';
    return;
  }

  refreshFlipbookAfterRotate();
});

window.addEventListener('orientationchange', function () {
  clearTimeout(window.__catalogOrientationReloadTimer);

  window.__catalogOrientationReloadTimer = setTimeout(() => {
    if (!esTablet()) return;

    const storeSelect = document.getElementById('store_id');

    const estabaAmpliado =
      sessionStorage.getItem('catalog_keep_fullscreen') === '1' ||
      document.body.classList.contains('tablet-catalog-fullscreen') ||
      document.fullscreenElement ||
      document.webkitFullscreenElement;

    if (storeSelect && storeSelect.value) {
      sessionStorage.setItem('catalog_store_selected', storeSelect.value);
    }

    if (estabaAmpliado) {
      sessionStorage.setItem('catalog_restore_fullscreen', '1');
    } else {
      sessionStorage.removeItem('catalog_restore_fullscreen');
    }

    window.location.reload();
  }, 500);
});


  //boton expandir solo para tablet
  updateFlipbook();

 

function updateFullscreenButton() {
  const btn = document.getElementById('btnFullscreen');
  if (!btn) return;

  if (esTablet()) {
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
/* ==========================
   🔍 MODAL PRODUCTO QUICK VIEW
========================== */

let quickProduct = null;
let quickQty = 1;

function formatQuickMoney(value) {
  return 'Q ' + Number(value || 0).toFixed(2);
}

function closeImgModal() {
  const modal = document.getElementById('imgModal');
  const img = document.getElementById('imgModalSrc');

  modal?.classList.remove('active');

  if (img) img.src = '';

  quickProduct = null;
  quickQty = 1;

  const quickQtyValue = document.getElementById('quickQtyValue');
  if (quickQtyValue) quickQtyValue.textContent = '1';
}

function openImgModal(img) {
  const modal = document.getElementById('imgModal');
  const modalImg = document.getElementById('imgModalSrc');

  const quickCode = document.getElementById('quickCode');
  const quickName = document.getElementById('quickName');
  const quickPrice = document.getElementById('quickPrice');
  const quickQtyValue = document.getElementById('quickQtyValue');
  const quickRating = document.getElementById('quickRating');

  if (!modal || !modalImg || !img) {
    console.warn('Falta modal, modalImg o img');
    return;
  }

  quickQty = 1;

  const card = img.closest('.product-mini');

  let payload = {};

  try {
    payload = JSON.parse(card?.dataset.cart || '{}');
  } catch (e) {
    console.warn('No se pudo leer data-cart:', e);
    payload = {};
  }

  const largeSrc = img.dataset.img || img.dataset.large || img.src;

 quickProduct = {
    ...payload,
    id: payload.id || `${img.dataset.code || ''}-${img.dataset.color || ''}`,
    code: payload.code || img.dataset.code || '',
    color: payload.color || img.dataset.color || '',
    name: payload.name || img.dataset.name || '',
    price: Number(payload.price || img.dataset.price || 0),
    img: largeSrc,
    qty: 1,

    rating: Number(
        payload.rating ||
        payload.avg_rating ||
        img.dataset.rating ||
        0
    ),

    reviews: Number(
        payload.reviews ||
        payload.total_reviews ||
        img.dataset.reviews ||
        0
    )
};

  modalImg.src = largeSrc;

  if (quickCode) {
const displayCode = img.dataset.displayCode || quickProduct.code || '';

quickCode.textContent = displayCode
  ? `Código: ${displayCode}`
  : 'Producto';
  }

  if (quickName) {
    quickName.textContent = quickProduct.name || 'Producto';
  }

  if (quickPrice) {
    quickPrice.textContent = formatQuickMoney(quickProduct.price);
  }

  if (quickRating) {
    quickRating.innerHTML = renderProductRating(
        quickProduct.rating,
        quickProduct.reviews
    );
}

  if (quickQtyValue) {
    quickQtyValue.textContent = quickQty;
  }

  modal.classList.add('active');
}

document.addEventListener('DOMContentLoaded', function () {
  const imgModal = document.getElementById('imgModal');
  const imgModalClose = document.getElementById('imgModalClose');

  const quickQtyMinus = document.getElementById('quickQtyMinus');
  const quickQtyPlus = document.getElementById('quickQtyPlus');
  const quickQtyValue = document.getElementById('quickQtyValue');
  const quickAddBtn = document.getElementById('quickAddBtn');

  imgModalClose?.addEventListener('click', closeImgModal);

  imgModal?.addEventListener('click', function (e) {
    if (e.target === imgModal) {
      closeImgModal();
    }
  });

  quickQtyMinus?.addEventListener('click', function (e) {
    e.preventDefault();
    e.stopPropagation();

    if (quickQty > 1) {
      quickQty--;
      if (quickQtyValue) quickQtyValue.textContent = quickQty;
    }
  });

  quickQtyPlus?.addEventListener('click', function (e) {
    e.preventDefault();
    e.stopPropagation();

    quickQty++;
    if (quickQtyValue) quickQtyValue.textContent = quickQty;
  });

  quickAddBtn?.addEventListener('click', function (e) {
    e.preventDefault();
    e.stopPropagation();

    if (!quickProduct) return;

    quickProduct.qty = quickQty;

    if (typeof addToCart === 'function') {
      addToCart(quickProduct);
    } else {
      console.error('No existe addToCart');
      return;
    }

    closeImgModal();

    if (typeof openCart === 'function') {
      openCart();
    }
  });
});

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
      const flipbook = document.getElementById('flipbook');
const mesope = flipbook?.dataset?.mesyope || '';


const res = await fetch(`/clientes/acumulado/${encodeURIComponent(codcliente)}?mesope=${encodeURIComponent(mesope)}`, {
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

function updateSafeBottom() {
  if (!window.visualViewport) {
    document.documentElement.style.setProperty('--safe-bottom', '0px');
    return;
  }

  const bottomSpace = window.innerHeight - window.visualViewport.height - window.visualViewport.offsetTop;

  document.documentElement.style.setProperty(
    '--safe-bottom',
    Math.max(0, bottomSpace) + 'px'
  );
}

window.visualViewport?.addEventListener('resize', updateSafeBottom);
window.visualViewport?.addEventListener('scroll', updateSafeBottom);
window.addEventListener('resize', updateSafeBottom);
window.addEventListener('orientationchange', updateSafeBottom);
document.addEventListener('DOMContentLoaded', updateSafeBottom);

updateSafeBottom();


function moverFlotantesSegunFullscreen() {
  const wrap = document.getElementById('flipbook-wrap');
  const cartFab = document.getElementById('cartFab');
  const cartPanel = document.getElementById('cartPanel');
  const imgModal = document.getElementById('imgModal');
  const floatingButtons = document.querySelector('.floating-buttons');

  const fullscreenActivo =
    document.fullscreenElement ||
    document.webkitFullscreenElement;

  // Si está en pantalla completa, los botones/modal deben estar dentro del wrap
  if (fullscreenActivo && wrap) {
    if (cartFab && cartFab.parentElement !== wrap) {
      wrap.appendChild(cartFab);
    }

    if (cartPanel && cartPanel.parentElement !== wrap) {
      wrap.appendChild(cartPanel);
    }

    if (imgModal && imgModal.parentElement !== wrap) {
      wrap.appendChild(imgModal);
    }

    if (floatingButtons && floatingButtons.parentElement !== wrap) {
      wrap.appendChild(floatingButtons);
    }

    return;
  }

  // Si NO está en pantalla completa, los botones/modal deben estar en body
  if (cartFab && cartFab.parentElement !== document.body) {
    document.body.appendChild(cartFab);
  }

  if (cartPanel && cartPanel.parentElement !== document.body) {
    document.body.appendChild(cartPanel);
  }

  if (imgModal && imgModal.parentElement !== document.body) {
    document.body.appendChild(imgModal);
  }


}

document.addEventListener('fullscreenchange', moverFlotantesSegunFullscreen);
document.addEventListener('webkitfullscreenchange', moverFlotantesSegunFullscreen);
document.addEventListener('DOMContentLoaded', moverFlotantesSegunFullscreen);
moverFlotantesSegunFullscreen();

document.addEventListener('DOMContentLoaded', function () {

  const storeSelect = document.getElementById('store_id');
  const flipbookWrap = document.getElementById('flipbook-wrap');



  function actualizarOrientacionCatalogo() {
    // EN TELÉFONO NO APLICAR CLASES DE FULLSCREEN
    if (esTelefono()) {
      document.body.classList.remove(
        'tablet-catalog-fullscreen',
        'fs-portrait',
        'fs-landscape'
      );
      return;
    }

    const esVertical = window.innerHeight >= window.innerWidth;

    document.body.classList.toggle('fs-portrait', esVertical);
    document.body.classList.toggle('fs-landscape', !esVertical);
  }

  async function abrirCatalogoAlSeleccionarTienda() {
    if (!storeSelect || !flipbookWrap) {
      
      return;
    }

    if (!storeSelect.value) {
    
      return;
    }
if (esTelefono()) {
  

  document.body.classList.remove(
    'tablet-catalog-fullscreen',
    'fs-portrait',
    'fs-landscape'
  );

await salirFullscreenSiActivo();

  return;
}
if (!esTablet()) {
  
  return;
}
 
    

    actualizarOrientacionCatalogo();

    // SOLO TABLET / PANTALLA GRANDE
    document.body.classList.add('tablet-catalog-fullscreen');
    

    try {
      if (!document.fullscreenElement && !document.webkitFullscreenElement) {
        if (flipbookWrap.requestFullscreen) {
          await flipbookWrap.requestFullscreen();
        } else if (flipbookWrap.webkitRequestFullscreen) {
          await flipbookWrap.webkitRequestFullscreen();
        }
      }
    } catch (e) {
      
    }

   setTimeout(() => {
  actualizarOrientacionCatalogo();
  window.forzarAjusteFlipbook?.();
}, 300);

setTimeout(() => {
  actualizarOrientacionCatalogo();
  window.forzarAjusteFlipbook?.();
}, 800);
  }

  storeSelect?.addEventListener('change', abrirCatalogoAlSeleccionarTienda);

  window.addEventListener('resize', actualizarOrientacionCatalogo);

  window.addEventListener('orientationchange', function () {
    setTimeout(() => {
      actualizarOrientacionCatalogo();
      window.dispatchEvent(new Event('resize'));
    }, 400);
  });

  document.addEventListener('fullscreenchange', function () {
    actualizarOrientacionCatalogo();

    if (!document.fullscreenElement && !document.webkitFullscreenElement) {
      document.body.classList.remove(
        'tablet-catalog-fullscreen',
        'fs-portrait',
        'fs-landscape'
      );
    }
  });

  document.addEventListener('webkitfullscreenchange', function () {
    actualizarOrientacionCatalogo();

    if (!document.fullscreenElement && !document.webkitFullscreenElement) {
      document.body.classList.remove(
        'tablet-catalog-fullscreen',
        'fs-portrait',
        'fs-landscape'
      );
    }
  });
});

document.addEventListener('DOMContentLoaded', function () {
  const btnExitFullscreen = document.getElementById('btnExitFullscreen');

  btnExitFullscreen?.addEventListener('click', async function (e) {
    e.preventDefault();
    e.stopPropagation();

    await salirFullscreenSiActivo();
  });
});

document.addEventListener('DOMContentLoaded', function () {
  const btnFullscreen = document.getElementById('btnFullscreen');
  const flipbookWrap = document.getElementById('flipbook-wrap');

  btnFullscreen?.addEventListener('click', async function (e) {
    e.preventDefault();
    e.stopPropagation();

    if (!flipbookWrap) return;

    if (esTelefono()) {
      
      return;
    }

    const esVertical = window.innerHeight >= window.innerWidth;

    document.body.classList.add('tablet-catalog-fullscreen');
    sessionStorage.setItem('catalog_restore_fullscreen', '1');
    document.body.classList.toggle('fs-portrait', esVertical);
    document.body.classList.toggle('fs-landscape', !esVertical);

    try {
      if (!document.fullscreenElement && !document.webkitFullscreenElement) {
        if (flipbookWrap.requestFullscreen) {
          await flipbookWrap.requestFullscreen();
        } else if (flipbookWrap.webkitRequestFullscreen) {
          await flipbookWrap.webkitRequestFullscreen();
        }
      }
    } catch (e) {
      
    }

    setTimeout(() => {
      window.forzarAjusteFlipbook?.();
    }, 300);

    setTimeout(() => {
      window.forzarAjusteFlipbook?.();
    }, 800);
  });
});
document.addEventListener('DOMContentLoaded', function () {
  const storeSelect = document.getElementById('store_id');

  const savedStore = sessionStorage.getItem('catalog_store_selected');
  const restoreFullscreen =
    sessionStorage.getItem('catalog_restore_fullscreen') === '1' ||
    sessionStorage.getItem('catalog_keep_fullscreen') === '1';

  if (storeSelect && savedStore) {
    storeSelect.value = savedStore;
  }

  if (restoreFullscreen && !esTelefono()) {
    const esVertical = window.innerHeight >= window.innerWidth;

    document.body.classList.add('tablet-catalog-fullscreen');
    document.body.classList.toggle('fs-portrait', esVertical);
    document.body.classList.toggle('fs-landscape', !esVertical);

    sessionStorage.setItem('catalog_keep_fullscreen', '1');
  }

  setTimeout(() => {
    window.forzarAjusteFlipbook?.();
  }, 400);

  setTimeout(() => {
    window.forzarAjusteFlipbook?.();
  }, 900);

  setTimeout(() => {
    window.forzarAjusteFlipbook?.();
  }, 1300);
});

document.addEventListener('DOMContentLoaded', function () {
    const slider = document.querySelector('.best-sellers-wide');
    const track = document.querySelector('[data-best-track]');
    const prev = document.querySelector('[data-best-prev]');
    const next = document.querySelector('[data-best-next]');
    const dotsBox = document.querySelector('[data-best-dots]');

    if (!track) return;

    let autoplayTimer = null;
    const autoplayDelay = 1600;

    function getStep() {
        const card = track.querySelector('.best-card');
        if (!card) return 300;

        const styles = window.getComputedStyle(track);
        const gap = parseFloat(styles.columnGap || styles.gap) || 22;

        return card.getBoundingClientRect().width + gap;
    }

    function hasOverflow() {
        return track.scrollWidth > track.clientWidth + 5;
    }

    function scrollNext() {
        if (!hasOverflow()) return;

        const maxScroll = track.scrollWidth - track.clientWidth - 5;

        if (track.scrollLeft >= maxScroll) {
            track.scrollTo({
                left: 0,
                behavior: 'smooth'
            });
        } else {
            track.scrollBy({
                left: getStep(),
                behavior: 'smooth'
            });
        }
    }

    function scrollPrev() {
        if (!hasOverflow()) return;

        if (track.scrollLeft <= 5) {
            track.scrollTo({
                left: track.scrollWidth,
                behavior: 'smooth'
            });
        } else {
            track.scrollBy({
                left: -getStep(),
                behavior: 'smooth'
            });
        }
    }

    function stopAutoplay() {
        if (autoplayTimer) {
            clearInterval(autoplayTimer);
            autoplayTimer = null;
        }
    }

    function startAutoplay() {
        stopAutoplay();

        if (!hasOverflow()) return;

        autoplayTimer = setInterval(function () {
            if (document.hidden) return;
            scrollNext();
        }, autoplayDelay);
    }

    function restartAutoplay() {
        stopAutoplay();

        setTimeout(function () {
            startAutoplay();
        }, 4500);
    }

    prev?.addEventListener('click', function () {
        scrollPrev();
        restartAutoplay();
    });

    next?.addEventListener('click', function () {
        scrollNext();
        restartAutoplay();
    });

    function buildDots() {
        if (!dotsBox) return;

        const totalPages = Math.max(1, Math.ceil(track.scrollWidth / track.clientWidth));
        dotsBox.innerHTML = '';

        for (let i = 0; i < totalPages; i++) {
            const dot = document.createElement('button');

            dot.addEventListener('click', function () {
                track.scrollTo({
                    left: i * track.clientWidth,
                    behavior: 'smooth'
                });

                restartAutoplay();
            });

            dotsBox.appendChild(dot);
        }

        updateDots();
    }

    function updateDots() {
        if (!dotsBox) return;

        const dots = dotsBox.querySelectorAll('button');
        const activeIndex = Math.round(track.scrollLeft / track.clientWidth);

        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === activeIndex);
        });
    }

    track.addEventListener('scroll', updateDots);

    window.addEventListener('resize', function () {
        buildDots();
        restartAutoplay();
    });

    slider?.addEventListener('mouseenter', stopAutoplay);
    slider?.addEventListener('mouseleave', startAutoplay);

    slider?.addEventListener('touchstart', stopAutoplay, { passive: true });
    slider?.addEventListener('touchend', restartAutoplay, { passive: true });

    buildDots();
    startAutoplay();
});

function addBestSellerToCart(btn) {
    const code = btn.dataset.code || '';
    const color = btn.dataset.color || '';
    const rating = Number(btn.dataset.rating || 0);
const reviews = Number(btn.dataset.reviews || 0);
    const id = color ? `${code}-${color}` : code;

    const product = {
        id: id,
        code: code,
        color: color,
        name: btn.dataset.name || 'Producto',
        price: Number(btn.dataset.price || 0),
        img: btn.dataset.img || '',
        qty: 1
    };

    if (typeof addToCart === 'function') {
        addToCart(product);
    } else {
        console.error('No existe la función addToCart');
        return;
    }

    const originalText = btn.dataset.originalText || btn.textContent;
    btn.dataset.originalText = originalText;

    btn.disabled = true;
    btn.textContent = 'Agregado ✓';

    setTimeout(() => {
        btn.disabled = false;
        btn.textContent = originalText;
    }, 900);
}


function renderProductRating(rating, reviews) {
    rating = Number(rating || 0);
    reviews = Number(reviews || 0);

    if (reviews <= 0 || rating <= 0) {
        return `
            <div class="product-rating-view">
                <span class="rating-stars-view">☆☆☆☆☆</span>
                <span class="rating-text">Sin calificaciones todavía</span>
            </div>
        `;
    }

    const filledStars = Math.round(rating);
    let stars = '';

    for (let i = 1; i <= 5; i++) {
        stars += i <= filledStars ? '★' : '☆';
    }

    return `
        <div class="product-rating-view">
            <span class="rating-stars-view">${stars}</span>
            <span class="rating-number">${rating.toFixed(1)} / 5</span>
            <span class="rating-text">(${reviews} calificación${reviews === 1 ? '' : 'es'})</span>
        </div>
    `;
}
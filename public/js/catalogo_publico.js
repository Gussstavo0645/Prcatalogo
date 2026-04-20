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

function bindZoomPreload(root) {
  root.querySelectorAll('.product-thumb').forEach(img => {
    if (img.dataset.zoomBound === '1') return;
    img.dataset.zoomBound = '1';

    img.addEventListener('mouseenter', () => {
      const large = img.dataset.large;
      if (!large) return;

      const preload = new Image();
      preload.src = large;
    }, { once: true });
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

    img.addEventListener('pointerup', function (e) {
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

function validateStep(step) {
  if (step === 1) {
    const nombre = document.getElementById('cliNombre')?.value.trim();
    const telefono = document.getElementById('cliTelefono')?.value.trim();

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
    const metodo = document.getElementById('pagoMetodo')?.value.trim();
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
  if (!modalEl) {
    console.warn('No existe #checkoutModal');
    return;
  }

  showStep(1);

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

  const payload = {
    nombre_cliente: document.getElementById('cliNombre')?.value.trim() || '',
    telefono_cliente: document.getElementById('cliTelefono')?.value.trim() || '',
    cliente_correo: document.getElementById('cliCorreo')?.value.trim() || '',

    direccion: document.getElementById('entDireccion')?.value.trim() || '',
    ciudad: document.getElementById('entCiudad')?.value.trim() || '',
    entrega_tipo: document.getElementById('entTipo')?.value || '',
    notas: document.getElementById('entNotas')?.value.trim() || '',

    pago_metodo: document.getElementById('pagoMetodo')?.value || '',
    requiere_factura: document.getElementById('pagoFactura')?.value || '',

    items: cart.map(item => ({
      code: item.code,
      color: item.color,
      name: item.name,
      quantity: Number(item.qty),
      price: Number(item.price)
    }))
  };

  const btnConfirm = document.getElementById('btnConfirm');
  if (btnConfirm) {
    btnConfirm.disabled = true;
    btnConfirm.textContent = 'Enviando...';
  }

  try {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

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
      alert(data.message || 'No se pudo enviar el pedido');
      return;
    }

    alert(data.message || 'Pedido enviado correctamente');

    clearCart();

    const modalEl = document.getElementById('checkoutModal');
    const modalInstance = bootstrap.Modal.getInstance(modalEl);
    modalInstance?.hide();

    resetCheckoutForm();
    showStep(1);

  } catch (error) {
    console.error('Error enviando pedido:', error);
    alert('Ocurrió un error al enviar el pedido');
  } finally {
    if (btnConfirm) {
      btnConfirm.disabled = false;
      btnConfirm.textContent = 'Confirmar pedido';
    }
  }
}

function resetCheckoutForm() {
  document.getElementById('cliNombre') && (document.getElementById('cliNombre').value = '');
  document.getElementById('cliTelefono') && (document.getElementById('cliTelefono').value = '');
  document.getElementById('cliCorreo') && (document.getElementById('cliCorreo').value = '');

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
    maxShadowOpacity: 0.98
  });

pageFlip.loadFromHTML(root.querySelectorAll('.page'));
window.pageFlip = pageFlip;
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
  const largeSrc = img.dataset.large || img.src;

  console.log({ thumbSrc, largeSrc });

  modalImg.src = thumbSrc;
  modal.classList.add('active');

  modalImg.onerror = function () {
    this.onerror = null;
    this.src = thumbSrc;
  };

  const preloader = new Image();
  preloader.onload = function () {
    modalImg.src = largeSrc;
  };
  preloader.onerror = function () {
    modalImg.src = thumbSrc;
  };
  preloader.src = largeSrc;
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






/* precargar imagen */
document.querySelectorAll('.product-thumb').forEach(img => {
  img.addEventListener('mouseenter', () => {
    const large = img.dataset.large;
    if (!large) return;

    const preload = new Image();
    preload.src = large;
  }, { once: true });
});


document.addEventListener('DOMContentLoaded', function () {
  renderCart();
  updateCartBadge();

 
});
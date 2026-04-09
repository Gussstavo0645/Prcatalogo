const CART_KEY = 'flipbook_cart_v1';

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

  const cart = getCart();
  const totalItems = cart.reduce((sum, item) => sum + Number(item.qty || 0), 0);

  badge.textContent = totalItems;
}

function clearCart(){
  setCart([]);
}

function toggleCart(){
  document.getElementById('cartPanel')?.classList.toggle('d-none');
}

function openCart(){
  document.getElementById('cartPanel')?.classList.remove('d-none');
}


function removeFromCart(id){
  setCart(getCart().filter(x => x.id !== id));
}

function removeCartItem(index) {
  let cart = getCart();
  cart.splice(index, 1);
  setCart(cart);
}

function changeQty(id, delta){
  const cart = getCart();
  const it = cart.find(x => x.id === id);
  if(!it) return;
  it.qty = Math.max(1, (it.qty || 1) + delta);
  setCart(cart);
}

function renderCart() {
  const cart = getCart();
  const wrap = document.getElementById('cartItems');
  const totalEl = document.getElementById('cartTotal');

  if (!wrap) return;

  if (cart.length === 0) {
    wrap.innerHTML = '<p class="text-muted mb-0">Tu carrito está vacío.</p>';
    if (totalEl) totalEl.textContent = 'Q 0.00';
    return;
  }

  let total = 0;

  wrap.innerHTML = cart.map((item, index) => {
    const subtotal = Number(item.price) * Number(item.qty);
    total += subtotal;

    return `
      <div class="cart-item d-flex gap-2 align-items-center mb-2 p-2 border rounded">
        <img src="${item.img}" alt="${item.name}" style="width:60px;height:60px;object-fit:contain;background:#fff;border-radius:8px;">
        <div class="flex-grow-1">
          <div class="fw-bold">${item.name}</div>
          <div class="small text-muted">Código: ${item.code} | Color: ${item.color}</div>
          <div class="small">Q ${Number(item.price).toFixed(2)} x ${item.qty}</div>
          <div class="fw-semibold">Subtotal: Q ${subtotal.toFixed(2)}</div>
        </div>
        <button type="button" class="btn btn-sm btn-danger" onclick="removeCartItem(${index})">X</button>
      </div>
    `;
  }).join('');

  if (totalEl) totalEl.textContent = 'Q ' + total.toFixed(2);
}

/* ==========================
   WIZARD 3 PASOS
========================== */
let wizardStep = 1;
let checkoutModalInstance = null;

function wizardSetHeader(step){
  document.querySelectorAll('.step-pill').forEach(p => {
    const s = Number(p.dataset.step);
    p.classList.remove('active','done');
    if(s < step) p.classList.add('done');
    if(s === step) p.classList.add('active');
  });
}

function wizardShowStep(step){
  wizardStep = step;
  document.querySelectorAll('.wizard-step').forEach(el => el.classList.remove('active'));
  document.getElementById('step'+step)?.classList.add('active');

  const btnBack = document.getElementById('btnBack');
  const btnNext = document.getElementById('btnNext');
  const btnConfirm = document.getElementById('btnConfirm');

  if(btnBack) btnBack.style.display = (step === 1) ? 'none' : '';
  if(btnNext) btnNext.classList.toggle('d-none', step === 3);
  if(btnConfirm) btnConfirm.classList.toggle('d-none', step !== 3);

  wizardSetHeader(step);
}

function wizardTotal(){
  const cart = getCart();
  const total = cart.reduce((sum, x) => sum + (Number(x.price)||0) * (x.qty||1), 0);
  const el = document.getElementById('wizardTotal');
  if(el) el.textContent = 'Q ' + total.toFixed(2);
  return total;
}

function openCheckoutWizard(){
  const cart = getCart();
  if(cart.length === 0){
    alert('Tu carrito está vacío.');
    return;
  }
  wizardShowStep(1);
  wizardTotal();
  const modalEl = document.getElementById('checkoutModal');
  checkoutModalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
  checkoutModalInstance.show();
}

function checkout(){
  openCheckoutWizard();
}

function validateStep1(){
  const nombre = (document.getElementById('cliNombre')?.value || '').trim();
  const tel = (document.getElementById('cliTelefono')?.value || '').trim();
  if(!nombre){ alert('Nombre requerido'); return false; }
  if(!tel){ alert('Teléfono requerido'); return false; }
  return true;
}

function validateStep2(){
  const dir = (document.getElementById('entDireccion')?.value || '').trim();
  const ciudad = (document.getElementById('entCiudad')?.value || '').trim();
  const tipo = (document.getElementById('entTipo')?.value || '').trim();
  if(!dir){ alert('Dirección requerida'); return false; }
  if(!ciudad){ alert('Ciudad requerida'); return false; }
  if(!tipo){ alert('Tipo de entrega requerido'); return false; }
  return true;
}

function validateStep3(){
  const metodo = (document.getElementById('pagoMetodo')?.value || '').trim();
  const factura = (document.getElementById('pagoFactura')?.value || '').trim();
  if(!metodo){ alert('Método de pago requerido'); return false; }
  if(!factura){ alert('Factura: seleccioná Sí o No'); return false; }
  return true;
}


/* ==========================
   PAGEFLIP INIT + LOCK OVERLAY
========================== */

const isSinglePageView = () => {
  const vw = window.innerWidth;
  const vh = window.innerHeight;
  const isPortrait = vh > vw;

  if (vw <= 768) return true;      // teléfono
  if (vw <= 1366) return isPortrait; // tablet vertical = 1, horizontal = 2
  return false;                    // escritorio
};

function toggleCatalogFullscreen() {
  const wrap = document.getElementById('flipbook-wrap');
  if (!wrap) return;

  wrap.classList.toggle('force-expanded');

  setTimeout(() => {
    resizeFlipbook();
  }, 80);
}

window.toggleCatalogFullscreen = toggleCatalogFullscreen;
window.toggleCatalogFullscreen = toggleCatalogFullscreen;

window.toggleCatalogFullscreen = toggleCatalogFullscreen;

(function () {
  const root = document.getElementById('flipbook');
  const flipbookWrap = document.getElementById('flipbook-wrap');
  const btnFullscreen = document.getElementById('btnFullscreen');
  if (!root) return;

  let loadingMorePages = false;
  let preloadTriggeredFor = 0;

  async function loadMorePages(pageFlip) {
    if (loadingMorePages) return;

    const slug = root.dataset.slug;
    let loaded = parseInt(root.dataset.loaded || '0', 10);
    const total = parseInt(root.dataset.total || '0', 10);

    if (loaded >= total) return;

    loadingMorePages = true;

    try {
      const res = await fetch(`/c/${slug}/bloque?offset=${loaded}&limit=6`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });

      if (!res.ok) throw new Error(`HTTP ${res.status}`);

      const data = await res.json();

      if (data.html && data.count > 0) {
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

        lockFlipOnOverlay('.products-overlay');
        lockFlipOnOverlay('.product-mini');

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
    } catch (e) {
      console.error('Error cargando más páginas:', e);
    } finally {
      loadingMorePages = false;
    }
  }

  function maybeLoadMore(pageFlip) {
    const loaded = parseInt(root.dataset.loaded || '0', 10);
    const total = parseInt(root.dataset.total || '0', 10);
    const current = pageFlip.getCurrentPageIndex() + 1;

    if (loaded >= total) return;

    const triggerPoint = loaded - 3;

    if (current >= triggerPoint && preloadTriggeredFor < loaded) {
      preloadTriggeredFor = loaded;
      loadMorePages(pageFlip);
    }
  }

  const pages = root.querySelectorAll('.page');

  if (typeof St === 'undefined' || !St.PageFlip) {
    console.error('PageFlip NO está cargado. Revisa los <script> del layout.');
    return;
  }

  const isTabletOrPhone = () => window.innerWidth <= 1366;

const pageFlip = new St.PageFlip(root, {
  width: 460,
  height: 600,
  size: 'fixed',
  minWidth: 320,
  maxWidth: 920,
  minHeight: 420,
  maxHeight: 600,
  showCover: true,
  startPage: 0,
  useShadow: true,
  maxShadowOpacity: 0.2,
  flippingTime: 800,
  mobileScrollSupport: true,
  usePortrait: isSinglePageView(),
  autoSize: false
});

  


window.pageFlip = pageFlip;
window.pageFlipRoot = root;
pageFlip.loadFromHTML(pages);

function showFsDebug(text) {
  let box = document.getElementById('fsDebugBox');

  if (!box) {
    box = document.createElement('div');
    box.id = 'fsDebugBox';
    box.style.position = 'fixed';
    box.style.left = '10px';
    box.style.top = '10px';
    box.style.zIndex = '999999';
    box.style.background = 'rgba(0,0,0,.8)';
    box.style.color = '#fff';
    box.style.padding = '8px 10px';
    box.style.borderRadius = '8px';
    box.style.fontSize = '12px';
    box.style.lineHeight = '1.3';
    document.body.appendChild(box);
  }

  box.textContent = text;
}


function resizeFlipbook() {
  const expanded = document.getElementById('flipbook-wrap')?.classList.contains('force-expanded');
  const singlePage = expanded ? (window.innerWidth <= window.innerHeight) : isSinglePageView();

  requestAnimationFrame(() => {
    try {
     if (fullscreenMode) {
  const horizontal = window.innerWidth > window.innerHeight;

  if (horizontal) {
    // 2 páginas - ocupar casi toda la pantalla
    const width = Math.min(window.innerWidth - 20, Math.floor((window.innerHeight - 20) * 920 / 600));

    root.style.width = width + 'px';
    root.style.height = 'auto';
    root.style.maxWidth = 'none';
    root.style.maxHeight = 'none';
    root.style.aspectRatio = '920 / 600';
    root.style.margin = '0 auto';

    pageFlip.update({
      width: 920,
      height: 600,
      size: 'fixed',
      minWidth: 920,
      maxWidth: 920,
      minHeight: 600,
      maxHeight: 600,
      showCover: true,
      mobileScrollSupport: true,
      usePortrait: false,
      autoSize: false
    });
  } else {
    // 1 página - ocupar casi toda la pantalla
    const width = Math.min(window.innerWidth - 20, Math.floor((window.innerHeight - 20) * 460 / 600));

    root.style.width = width + 'px';
    root.style.height = 'auto';
    root.style.maxWidth = 'none';
    root.style.maxHeight = 'none';
    root.style.aspectRatio = '460 / 600';
    root.style.margin = '0 auto';

    pageFlip.update({
      width: 460,
      height: 600,
      size: 'fixed',
      minWidth: 460,
      maxWidth: 460,
      minHeight: 600,
      maxHeight: 600,
      showCover: true,
      mobileScrollSupport: true,
      usePortrait: true,
      autoSize: false
    });
  }
} else {
        if (singlePage) {
          root.style.width = 'calc(100vw - 16px)';
          root.style.maxWidth = '460px';
          root.style.height = 'auto';
          root.style.aspectRatio = '460 / 600';
          root.style.margin = '0 auto';
        } else {
          root.style.width = 'min(calc(100vw - 24px), 920px)';
          root.style.maxWidth = '920px';
          root.style.height = 'auto';
          root.style.aspectRatio = '920 / 600';
          root.style.margin = '0 auto';
        }

        pageFlip.update({
          width: 460,
          height: 600,
          size: 'fixed',
          minWidth: 320,
          maxWidth: singlePage ? 460 : 920,
          minHeight: 420,
          maxHeight: 600,
          showCover: true,
          mobileScrollSupport: true,
          usePortrait: singlePage,
          autoSize: false
        });
      }

      pageFlip.updateFromHtml(root.querySelectorAll('.page'));
      pageFlip.turnToPage(pageFlip.getCurrentPageIndex());
      update();
    } catch (e) {
      console.warn('No se pudo actualizar PageFlip:', e);
    }
  });
}

  function lockFlipOnOverlay(selector) {
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

  lockFlipOnOverlay('.products-overlay');
  lockFlipOnOverlay('.product-mini');

  const prev = document.getElementById('prev');
  const next = document.getElementById('next');
  const indicator = document.getElementById('page-indicator');

  if (prev) prev.addEventListener('click', () => pageFlip.flipPrev());
  if (next) next.addEventListener('click', () => pageFlip.flipNext());

  const update = () => {
  const pagesNow = root.querySelectorAll('.page');

  if (indicator) {
    indicator.textContent = (pageFlip.getCurrentPageIndex() + 1) + ' / ' + pageFlip.getPageCount();
  }

  const idx = pageFlip.getCurrentPageIndex();
  const singlePage = isSinglePageView();

  pagesNow.forEach(p => p.classList.remove('is-visible'));

  // SIEMPRE muestra la actual
  if (pagesNow[idx]) pagesNow[idx].classList.add('is-visible');

  // SOLO en escritorio muestra la segunda
  if (!singlePage && pagesNow[idx + 1]) {
    pagesNow[idx + 1].classList.add('is-visible');
  }
};

  resizeFlipbook();
  update();

window.addEventListener('resize', () => {
  resizeFlipbook();
  update();
});

window.addEventListener('orientationchange', () => {
  setTimeout(() => {
    resizeFlipbook();
    update();
  }, 200);
});

  pageFlip.on('init', () => {
    update();
  });

  pageFlip.on('flip', () => {
    update();
    maybeLoadMore(pageFlip);
  });

 function syncFullscreenButton() {
  const cssMode = document.body.classList.contains('catalog-fullscreen');
  const nativeMode = !!document.fullscreenElement;

  if (btnFullscreen) {
    btnFullscreen.textContent = (cssMode || nativeMode) ? '✕' : '⛶';
  }

  }

 document.addEventListener('fullscreenchange', () => {
  if (!document.fullscreenElement) {
    document.body.classList.remove('catalog-fullscreen');
  }

  syncFullscreenButton();

  setTimeout(() => {
    resizeFlipbook();
    pageFlip.updateFromHtml(root.querySelectorAll('.page'));
    pageFlip.turnToPage(pageFlip.getCurrentPageIndex());
    update();
  }, 250);
});
})();
/* ==========================
   DOM READY (listeners)
========================== */
document.addEventListener('DOMContentLoaded', () => {
  renderCart();
  updateCartBadge();

  document.getElementById('btnNext')?.addEventListener('click', () => {
    if(wizardStep === 1 && !validateStep1()) return;
    if(wizardStep === 2 && !validateStep2()) return;
    wizardShowStep(Math.min(3, wizardStep + 1));
  });

  document.getElementById('btnBack')?.addEventListener('click', () => {
    wizardShowStep(Math.max(1, wizardStep - 1));
  });

  document.getElementById('btnConfirm')?.addEventListener('click', async () => {
    if(!validateStep3()) return;

    const cart = getCart();

    const items = cart.map(x => ({
      code: x.code,
      color: x.color,
      quantity: x.qty || 1,
      name: x.name,
      price: x.price
    }));

    const nombre_cliente   = (document.getElementById('cliNombre')?.value || '').trim();
    const telefono_cliente = (document.getElementById('cliTelefono')?.value || '').trim();
    const correo           = (document.getElementById('cliCorreo')?.value || '').trim();

    const direccion        = (document.getElementById('entDireccion')?.value || '').trim();
    const ciudad           = (document.getElementById('entCiudad')?.value || '').trim();
    const entrega_tipo     = (document.getElementById('entTipo')?.value || '').trim();
    const notas            = (document.getElementById('entNotas')?.value || '').trim();

    const pago_metodo      = (document.getElementById('pagoMetodo')?.value || '').trim();
    const requiere_factura = (document.getElementById('pagoFactura')?.value || '').trim();

    try{
      const res = await fetch("/pedido/finalizar", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || ""
        },
        body: JSON.stringify({
          nombre_cliente,
          telefono_cliente,
          correo,
          direccion,
          ciudad,
          entrega_tipo,
          notas,
          pago_metodo,
          requiere_factura,
          items
        })
      });

      const raw = await res.text();

      if(!res.ok){
        console.log('STATUS:', res.status);
        console.log('RESPUESTA:', raw);
        alert(`Error (${res.status}). Mirá consola (F12).`);
        return;
      }

      const data = JSON.parse(raw);
      clearCart();

      if(checkoutModalInstance) checkoutModalInstance.hide();

      alert(`Pedido #${data.pedido_id} creado. Total: Q ${Number(data.total).toFixed(2)}`);
    } catch(e){
      console.error(e);
      alert('Error de conexión. Mirá consola (F12).');
    }
  });
});


function moveFocusOnEnter(fields) {
  fields.forEach((field, index) => {
    if (!field) return;

    field.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        e.preventDefault();

        const nextField = fields[index + 1];
        if (nextField) {
          nextField.focus();
        } else {
          document.getElementById('btnNext')?.click();
        }
      }
    });
  });
}

document.addEventListener('DOMContentLoaded', () => {
  moveFocusOnEnter([
    document.getElementById('cliNombre'),
    document.getElementById('cliTelefono'),
    document.getElementById('cliCorreo'),
    document.getElementById('btnNext')
  ]);

  moveFocusOnEnter([
    document.getElementById('entDireccion'),
    document.getElementById('entCiudad'),
    document.getElementById('entTipo'),
    document.getElementById('entNotas'),
    document.getElementById('btnNext')
  ]);
});

function closeImgModal(){
  const modal = document.getElementById('imgModal');
  const modalImg = document.getElementById('imgModalSrc');

  modal?.classList.remove('active');
  if (modalImg) modalImg.src = '';
}

document.addEventListener('click', function(e){
  const img = e.target.closest('.product-thumb');
  if(!img) return;

  const modal = document.getElementById('imgModal');
  const modalImg = document.getElementById('imgModalSrc');

  if (!modal || !modalImg) return;
  if(modal.classList.contains('active')) return;

  const thumbSrc = img.src;
  const largeSrc = img.dataset.large || img.src;

  // abrir rápido con thumb
  modalImg.src = thumbSrc;
  modal.classList.add('active');

  // si falla la grande, se queda la thumb
  modalImg.onerror = function () {
    this.onerror = null;
    this.src = thumbSrc;
  };

  // cargar la grande en segundo plano
  const preloader = new Image();
  preloader.onload = function () {
    modalImg.src = largeSrc;
  };
  preloader.onerror = function () {
    modalImg.src = thumbSrc;
  };
  preloader.src = largeSrc;
});

document.getElementById('imgModal')?.addEventListener('click', closeImgModal);

document.getElementById('imgModalClose')?.addEventListener('click', function(e){
  e.stopPropagation();
  closeImgModal();
});

document.addEventListener('keydown', function(e){
  if(e.key === 'Escape'){
    closeImgModal();
  }
});

document.querySelectorAll('.product-thumb').forEach(img => {
  img.addEventListener('mouseenter', () => {
    const large = img.dataset.large;
    if (!large) return;

    const preload = new Image();
    preload.src = large;
  }, { once: true });
});

let cartAutoHideTimer = null;

function showCartFab() {
  const fab = document.getElementById('cartFab');
  if (!fab) return;

  fab.style.opacity = '1';
  fab.style.pointerEvents = 'auto';

  clearTimeout(cartAutoHideTimer);

  cartAutoHideTimer = setTimeout(() => {
    fab.style.opacity = '0.3';
  }, 500); // se desvanece después de 3s
}

// detectar interacción con catálogo
document.addEventListener('click', (e) => {
  if (e.target.closest('.product-mini')) {
    showCartFab();
  }
});

// también al agregar producto
function addToCart(product) {
  let cart = getCart();

  const idx = cart.findIndex(item =>
    item.code === product.code && String(item.color) === String(product.color)
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
  showCartFab(); // 🔥 aquí
}


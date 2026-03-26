
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
(function () {
  const root = document.getElementById('flipbook');
  if (!root) return;

  const pages = root.querySelectorAll('.page');

  if (typeof St === 'undefined' || !St.PageFlip) {
    console.error('PageFlip NO está cargado. Revisa los <script> del layout.');
    return;
  }

  const pageFlip = new St.PageFlip(root, {
    width: 460,
    height: 600,
    size: 'fixed',
    showCover: true,
    startPage: 0,
    useShadow: true,
    maxShadowOpacity: 0.2,
    flippingTime: 800,
    mobileScrollSupport: true,
  });

  pageFlip.loadFromHTML(pages);

  function lockFlipOnOverlay(selector){
    root.querySelectorAll(selector).forEach((el) => {
      const stopMouse = (e) => e.stopPropagation();
      const stopTouchMove = (e) => {
        e.stopPropagation();
        if (e.cancelable) e.preventDefault();
      };

      el.addEventListener('mousedown', stopMouse, { capture:true });
      el.addEventListener('mousemove', stopMouse, { capture:true });
      el.addEventListener('mouseup', stopMouse, { capture:true });

      el.addEventListener('pointerdown', stopMouse, { capture:true });
      el.addEventListener('pointermove', stopTouchMove, { capture:true, passive:false });
      el.addEventListener('pointerup', stopMouse, { capture:true });

      el.addEventListener('touchstart', stopMouse, { capture:true, passive:false });
      el.addEventListener('touchmove', stopTouchMove, { capture:true, passive:false });
      el.addEventListener('touchend', stopMouse, { capture:true });
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
    if (indicator) {
      indicator.textContent = (pageFlip.getCurrentPageIndex() + 1) + ' / ' + pageFlip.getPageCount();
    }

    const idx = pageFlip.getCurrentPageIndex();
    pages.forEach(p => p.classList.remove('is-visible'));
    if (pages[idx]) pages[idx].classList.add('is-visible');
    if (pages[idx + 1]) pages[idx + 1].classList.add('is-visible');
  };

  update();
  pageFlip.on('init', update);
  pageFlip.on('flip', update);
})();

/* ==========================
   DOM READY (listeners)
========================== */
document.addEventListener('DOMContentLoaded', () => {
  renderCart();

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

  modalImg.onerror = function () {
    this.onerror = null;
    this.src = img.src;
  };

  modalImg.src = img.dataset.large || img.src;
  modal.classList.add('active');
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


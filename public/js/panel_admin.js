/*del create.blade.php*/ 
function formatPrice(q){
  return Number(q).toLocaleString('es-GT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function updateCartCount(){
  const panel = document.getElementById('cartPanel');
  const count = panel ? panel.querySelectorAll('[data-cart-item="1"]').length : 0;
  const badge = document.getElementById('cartCount');
  if(badge) badge.textContent = String(count);
}

function renderCartRow(product, qty, catalogId, pageNumber){
  const code = (product.code || '').trim();
const color = (product.color || '').trim();

const key = product.is_combo
  ? `combo-${product.id}-${pageNumber}`
  : `${code}-${color}-${pageNumber}`;

  let imgUrl = 'https://via.placeholder.com/44x44?text=Sin+foto';

  if (product.is_combo && product.image_path) {
    imgUrl = `/storage/${product.image_path}`;
  } else if (code) {
    imgUrl = color
      ? `${window.__PRODUCT_IMG_BASE__}/${encodeURIComponent(code)}/${encodeURIComponent(color)}?v=${code}${color}`
      : `${window.__PRODUCT_IMG_BASE__}/${encodeURIComponent(code)}?v=${code}`;
  }

  return `
    <div class="d-flex align-items-center gap-2 border rounded p-2 mb-2"
         data-cart-item="1"
         id="cart-item-${key}">
      <img data-src="${imgUrl}"
           class="hover-img"
           style="width:44px;height:44px;object-fit:contain;border-radius:8px;background:#fff;cursor:pointer;"
           alt=""
           onerror="this.onerror=null;this.src='https://via.placeholder.com/44x44?text=Sin+foto'">

      <div class="flex-grow-1">
        <div class="fw-semibold small">${product.name ?? 'Producto no encontrado'}</div>
        <div class="text-muted small">Q ${formatPrice(product.price ?? 0)}</div>
        <div class="text-muted small">Pág: ${pageNumber}</div>
      </div>

      <span class="badge bg-secondary" id="cart-qty-${key}">${qty} u</span>

     <button type="button"
  class="btn btn-outline-danger btn-sm"
  onclick="${
    product.is_combo
      ? `removeCombo(${product.id})`
      : `removeFromCatalog('${code}', '${color}', ${catalogId}, ${pageNumber})`
  }">✕</button>
    </div>
  `;
}

async function removeCombo(comboId){
  if(!confirm('¿Quitar este combo del catálogo?')){
    return;
  }

  try {
    const res = await fetch(`/admin/catalogos/combos/${comboId}`, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      }
    });

    const data = await res.json();

    if(!res.ok || !data.ok){
      alert(data?.message || 'Error al eliminar combo');
      return;
    }

    document.querySelector(`[id^="cart-item-combo-${comboId}-"]`)?.remove();
    updateCartCount();

    const panel = document.getElementById('cartPanel');
    if(panel && !panel.querySelector('[data-cart-item="1"]')){
      panel.innerHTML = `
        <div class="text-muted small px-2 py-2" id="cartEmpty">
          Aún no has agregado productos.
        </div>
      `;
    }

  } catch (e) {
    console.error(e);
    alert('Ocurrió un error al eliminar el combo.');
  }
}
async function addToCatalog(btn, catalogId){
  const oldText = btn?.textContent || 'Agregar';

  if(btn){
    btn.disabled = true;
    btn.textContent = '...';
  }

  try {
    if(!catalogId){
      alert('Primero crea un catálogo.');
      return;
    }

    const code = (btn?.dataset.code || '').trim();
    const color = (btn?.dataset.color || '').trim();
    const keyBase = (btn?.dataset.key || `${code}-${color}`).trim();

    if(!code){
      alert('El producto no tiene código.');
      return;
    }

    const qtyInput = document.getElementById(`qty-${keyBase}`);
    const pageInput = document.getElementById(`page-${keyBase}`);

    const qty = qtyInput ? Number(qtyInput.value || 1) : 1;
    const pageNumber = pageInput ? Number(pageInput.value || 1) : 1;

    const res = await fetch(`/admin/catalogos/${catalogId}/products`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({
        code: code,
        color: color,
        quantity: qty,
        page_number: pageNumber
      })
    });

    let data = null;
    const ct = res.headers.get('content-type') || '';

    if (ct.includes('application/json')) {
      data = await res.json();
    } else {
      const text = await res.text();
      console.log('RESPUESTA NO JSON:', text);
      alert('El servidor no devolvió JSON. Revisa consola (F12).');
      return;
    }

    if(!res.ok || !data || !data.ok){
      console.log('DATA ERROR:', data);
      alert(data?.message || 'No se pudo agregar.');
      return;
    }

    if(!data.product){
      console.log('DATA SIN PRODUCT:', data);
      alert('El servidor no devolvió product. Revisa el controller addProduct().');
      return;
    }

    const key = `${data.product.code}-${data.product.color}-${data.page_number}`;

    const panel = document.getElementById('cartPanel');
    if(panel){
      document.getElementById('cartEmpty')?.remove();

      const existingCart = document.getElementById(`cart-item-${key}`);
      if(existingCart){
        const b = document.getElementById(`cart-qty-${key}`);
        if(b) b.textContent = `${data.quantity} u`;
      } else {
        panel.insertAdjacentHTML(
          'afterbegin',
          renderCartRow(data.product, data.quantity, catalogId, data.page_number)
        );
      }
    }

    updateCartCount();

  } catch (e) {
    console.error(e);
    alert('Ocurrió un error al agregar el producto.');
  } finally {
    if(btn){
      btn.disabled = false;
      btn.textContent = oldText;
    }
  }
}

async function removeFromCatalog(code, color, catalogId, pageNumber = 1){
  if(!confirm('¿Quitar este producto del catálogo?')){
    return;
  }

  try {
    const res = await fetch(`/admin/catalogos/${catalogId}/products/remove-by-code`, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({
        code: code,
        color: color,
        page_number: pageNumber
      })
    });

    const data = await res.json();

    if(!res.ok || !data.ok){
      alert(data?.message || 'Error al quitar');
      return;
    }

    const key = `${code}-${color}-${pageNumber}`;
    document.getElementById(`cart-item-${key}`)?.remove();

    const panel = document.getElementById('cartPanel');
    if(panel && !panel.querySelector('[data-cart-item="1"]')){
      panel.innerHTML = `
        <div class="text-muted small px-2 py-2" id="cartEmpty">
          Aún no has agregado productos.
        </div>
      `;
    }

    updateCartCount();

  } catch (e) {
    console.error(e);
    alert('Ocurrió un error al quitar el producto.');
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const panel = document.getElementById('cartPanel');
  const catalogId = window.__CATALOG_ID__;
  const items = window.__CART_ITEMS__ || [];

  if(!panel) return;

  panel.innerHTML = '';

  if(!items.length){
    panel.insertAdjacentHTML('beforeend', `
      <div class="text-muted small px-2 py-2" id="cartEmpty">
        Aún no has agregado productos.
      </div>
    `);
  } else {
    items.forEach(it => {
      panel.insertAdjacentHTML('beforeend',
        renderCartRow(it.product, it.quantity, catalogId, it.page_number)
      );
    });
  }

  updateCartCount();
});



function initBulkActions() {
  const checks = document.querySelectorAll('.product-check');
  const selectedCount = document.getElementById('selectedCount');
  const selectAllBtn = document.getElementById('selectAllBtn');
  const unselectAllBtn = document.getElementById('unselectAllBtn');
  const bulkAddBtn = document.getElementById('bulkAddBtn');
  const catalogId = window.__CATALOG_ID__;

  function updateSelectedCount() {
    if (!selectedCount) return;
    const total = document.querySelectorAll('.product-check:checked').length;
    selectedCount.value = total + ' productos';
  }

  checks.forEach(chk => {
    chk.addEventListener('change', updateSelectedCount);
  });

  if (selectAllBtn) {
    selectAllBtn.onclick = function () {
      document.querySelectorAll('.product-check').forEach(chk => chk.checked = true);
      updateSelectedCount();
    };
  }

  if (unselectAllBtn) {
    unselectAllBtn.onclick = function () {
      document.querySelectorAll('.product-check').forEach(chk => chk.checked = false);
      updateSelectedCount();
    };
  }

  async function bulkAddSelected() {
    if (!catalogId) {
      alert('Primero crea o selecciona un catálogo.');
      return;
    }

    const selected = Array.from(document.querySelectorAll('.product-check:checked'));

    if (!selected.length) {
      alert('Debes seleccionar al menos un producto.');
      return;
    }

    const pageInput = document.getElementById('bulkPageNumber');
    const qtyInput = document.getElementById('bulkQuantity');

    const pageNumber = Number(pageInput?.value || 1);
    const quantity = Number(qtyInput?.value || 1);

    if (!pageNumber || pageNumber < 1) {
      alert('Debes indicar una página válida.');
      return;
    }

    if (!quantity || quantity < 1) {
      alert('Debes indicar una cantidad válida.');
      return;
    }

    if (bulkAddBtn) {
      bulkAddBtn.disabled = true;
      bulkAddBtn.textContent = 'Agregando...';
    }

    let okCount = 0;
    let failCount = 0;

    for (const chk of selected) {
      const code = (chk.dataset.code || '').trim();
      const color = (chk.dataset.color || '').trim();
      const key = (chk.dataset.key || `${code}-${color}`).trim();

      if (!code) {
        failCount++;
        continue;
      }

      try {
        const res = await fetch(`/admin/catalogos/${catalogId}/products`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify({
            code: code,
            color: color,
            quantity: quantity,
            page_number: pageNumber
          })
        });

        let data = null;
        const ct = res.headers.get('content-type') || '';

        if (ct.includes('application/json')) {
          data = await res.json();
        } else {
          failCount++;
          continue;
        }

        if (!res.ok || !data || !data.ok || !data.product) {
          failCount++;
          continue;
        }

        const itemKey = `${data.product.code}-${data.product.color}-${data.page_number}`;
        const panel = document.getElementById('cartPanel');

        if (panel) {
          document.getElementById('cartEmpty')?.remove();

          const existingCart = document.getElementById(`cart-item-${itemKey}`);
          if (existingCart) {
            const b = document.getElementById(`cart-qty-${itemKey}`);
            if (b) b.textContent = `${data.quantity} u`;
          } else {
            panel.insertAdjacentHTML(
              'afterbegin',
              renderCartRow(data.product, data.quantity, catalogId, data.page_number)
            );
          }
        }

        const qtyBox = document.getElementById(`qty-${key}`);
        const pageBox = document.getElementById(`page-${key}`);
        if (qtyBox) qtyBox.value = quantity;
        if (pageBox) pageBox.value = pageNumber;

        chk.checked = false;
        okCount++;

      } catch (e) {
        console.error('Error bulk add:', code, color, e);
        failCount++;
      }
    }

    updateCartCount();
    updateSelectedCount();

    if (bulkAddBtn) {
      bulkAddBtn.disabled = false;
      bulkAddBtn.textContent = 'Agregar seleccionados';
    }

    if (okCount > 0 && failCount === 0) {
      alert(`Se agregaron ${okCount} productos correctamente.`);
    } else if (okCount > 0 && failCount > 0) {
      alert(`Se agregaron ${okCount} productos y fallaron ${failCount}.`);
    } else {
      alert('No se pudo agregar ningún producto.');
    }
  }

  if (bulkAddBtn) {
    bulkAddBtn.onclick = bulkAddSelected;
  }

  updateSelectedCount();
}

document.addEventListener('DOMContentLoaded', initBulkActions);



async function loadProductsSection(url) {
  const container = document.getElementById('productsSection');
  if (!container) return;

  try {
    container.style.opacity = '0.5';
    container.innerHTML = '<div class="alert alert-info">Cargando productos...</div>';

    const res = await fetch(url, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    });

    const html = await res.text();
    container.innerHTML = html;

    initBulkActions();

  } catch (err) {
    console.error(err);
    container.innerHTML = '<div class="alert alert-danger">Error al cargar productos</div>';
  } finally {
    container.style.opacity = '1';
  }
}


document.addEventListener('click', function(e){
  const link = e.target.closest('#productsSection .pagination a');
  if(!link) return;

  e.preventDefault();
  loadProductsSection(link.href);
});




document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('catalogHeaderForm');
  if (!form) return;

  form.querySelectorAll('select').forEach(select => {
    select.addEventListener('change', () => {
      form.submit();
    });
  });
});



document.addEventListener('click', function(e){
  const btn = e.target.closest('#clearProductsFilter');
  if(!btn) return;

  const form = document.getElementById('productsFilterForm');
  if(!form) return;

  const pageInput = form.querySelector('input[name="filter_page"]');
  if(pageInput) pageInput.value = '';

  const url = new URL(form.action, window.location.origin);
  const formData = new FormData(form);

  for (const [key, value] of formData.entries()) {
    if (value !== '') {
      url.searchParams.set(key, value);
    }
  }

  loadProductsSection(url.toString());
});



document.addEventListener('submit', function(e){
  const form = e.target.closest('#productsFilterForm');
  if (!form) return;

  e.preventDefault();

  const url = new URL(form.action, window.location.origin);
  const formData = new FormData(form);

  for (const [key, value] of formData.entries()) {
    if (value !== null && value !== '') {
      url.searchParams.set(key, value);
    }
  }

  loadProductsSection(url.toString());
});



document.addEventListener('mouseover', function(e){
  const img = e.target.closest('.hover-img');
  if(!img) return;

  if (!img.dataset.loaded) {
    img.src = img.dataset.src;
    img.dataset.loaded = '1';
  }
});

/* */

function initAdminCatalogTabs() {
  const buttons = document.querySelectorAll('.admin-tab-btn');
  const sections = document.querySelectorAll('.admin-tab-panel');

  if (!buttons.length || !sections.length) return;

  function openTab(targetId) {
    const target = document.getElementById(targetId);
    const button = document.querySelector(`.admin-tab-btn[data-target="${targetId}"]`);

    if (!target || !button || button.disabled) return;

    sections.forEach(section => {
      section.classList.remove('active');
    });

    buttons.forEach(btn => {
      btn.classList.remove('active');
    });

    target.classList.add('active');
    button.classList.add('active');

    localStorage.setItem('admin_catalog_active_tab', targetId);

    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  }

  buttons.forEach(button => {
    button.addEventListener('click', function () {
      openTab(this.dataset.target);
    });
  });

  const savedTab = localStorage.getItem('admin_catalog_active_tab');

  if (savedTab && document.getElementById(savedTab)) {
    const savedButton = document.querySelector(`.admin-tab-btn[data-target="${savedTab}"]`);

    if (savedButton && !savedButton.disabled) {
      openTab(savedTab);
    }
  }
}

document.addEventListener('DOMContentLoaded', initAdminCatalogTabs);
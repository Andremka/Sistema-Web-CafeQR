/**
 * =========================================================
 *  CafeQR — Frontend (sin backend / sin base de datos)
 * =========================================================
 * Todo el estado vive en memoria del navegador (variables JS).
 * No hay onclick="" en el HTML: cada elemento interactivo se
 * declara con un atributo data-action y este archivo conecta
 * los listeners una sola vez, al cargar la página.
 *
 * Cuando se conecte la API real (Laravel / Blade, según el
 * diseño de arquitectura del proyecto), los puntos exactos a
 * reemplazar por llamadas fetch() están marcados con:
 *   // TODO(API):
 * =========================================================
 */

/* ============================================================
   1. DATOS
   ============================================================ */

/** Categorías del menú, en el orden en que se muestran. */
const PRODUCT_CATEGORIES = [
  { id: 'bebidas', label: 'Bebidas', icon: '☕' },
  { id: 'salados', label: 'Salados', icon: '🥪' },
  { id: 'dulces', label: 'Dulces', icon: '🧁' },
  { id: 'combos', label: 'Combos', icon: '🍽️' },
];

/** Catálogo de productos del día. Única fuente de verdad para el menú. */
const PRODUCTS = [
  { id: 'americano', name: 'Café Americano', price: 6, icon: '☕', category: 'bebidas' },
  { id: 'cappuccino', name: 'Cappuccino', price: 9, icon: '☕', category: 'bebidas' },
  { id: 'mocaccino', name: 'Mocaccino', price: 10, icon: '☕', category: 'bebidas' },
  { id: 'chocolate', name: 'Chocolate Caliente', price: 7, icon: '🍫', category: 'bebidas' },
  { id: 'te', name: 'Té Emoliente', price: 4, icon: '🍵', category: 'bebidas' },
  { id: 'te-verde', name: 'Té Verde', price: 4, icon: '🍵', category: 'bebidas' },
  { id: 'frappe', name: 'Frappé de Café', price: 12, icon: '🥤', category: 'bebidas' },
  { id: 'milkshake', name: 'Milkshake de Vainilla', price: 12, icon: '🥤', category: 'bebidas' },
  { id: 'jugo', name: 'Jugo Natural', price: 8, icon: '🧃', category: 'bebidas' },
  { id: 'maracuya', name: 'Jugo de Maracuyá', price: 8, icon: '🧃', category: 'bebidas' },
  { id: 'limonada', name: 'Limonada', price: 6, icon: '🍋', category: 'bebidas' },

  { id: 'sandwich', name: 'Sandwich Mixto', price: 12, icon: '🥪', category: 'salados' },
  { id: 'saltena', name: 'Salteña', price: 7, icon: '🥟', category: 'salados' },
  { id: 'empanada', name: 'Empanada de Queso', price: 6, icon: '🫓', category: 'salados' },
  { id: 'empanada-carne', name: 'Empanada de Carne', price: 7, icon: '🥟', category: 'salados' },
  { id: 'tostada', name: 'Tostada Jamón y Queso', price: 9, icon: '🍞', category: 'salados' },
  { id: 'choripan', name: 'Choripán', price: 10, icon: '🌭', category: 'salados' },
  { id: 'wrap', name: 'Wrap de Pollo', price: 11, icon: '🌯', category: 'salados' },
  { id: 'hamburguesa', name: 'Hamburguesa Sencilla', price: 15, icon: '🍔', category: 'salados' },

  { id: 'muffin', name: 'Muffin', price: 5, icon: '🧁', category: 'dulces' },
  { id: 'brownie', name: 'Brownie', price: 6, icon: '🍫', category: 'dulces' },
  { id: 'galletas', name: 'Galletas de Avena', price: 4, icon: '🍪', category: 'dulces' },
  { id: 'alfajor', name: 'Alfajor', price: 5, icon: '🥮', category: 'dulces' },
  { id: 'croissant', name: 'Croissant', price: 6, icon: '🥐', category: 'dulces' },
  { id: 'flan', name: 'Flan', price: 6, icon: '🍮', category: 'dulces' },
  { id: 'panqueque', name: 'Panqueque', price: 8, icon: '🥞', category: 'dulces' },
  { id: 'waffle', name: 'Waffle con Miel', price: 9, icon: '🧇', category: 'dulces' },
  { id: 'cheesecake', name: 'Cheesecake', price: 8, icon: '🍰', category: 'dulces' },
  { id: 'torta', name: 'Torta de Chocolate (porción)', price: 9, icon: '🍰', category: 'dulces' },

  { id: 'combo-tarde', name: 'Combo Tarde (Té + Alfajor)', price: 8, icon: '🍽️', category: 'combos' },
  { id: 'combo-desayuno', name: 'Combo Desayuno (Café + Muffin)', price: 10, icon: '🍽️', category: 'combos' },
  { id: 'combo-salado', name: 'Combo Salado (Sandwich + Jugo)', price: 18, icon: '🍽️', category: 'combos' },
  { id: 'combo-energia', name: 'Combo Energía (Frappé + Brownie)', price: 16, icon: '🍽️', category: 'combos' },
  { id: 'combo-ejecutivo', name: 'Combo Ejecutivo (Sandwich + Jugo + Muffin)', price: 22, icon: '🍽️', category: 'combos' },
];

/** Cuenta única del administrador para esta demo (sin backend). */
const ADMIN_EMAIL = 'admin@cafeqr.edu';
const ADMIN_PASSWORD = 'admin123';

/** Códigos de cupón válidos para esta demo (sin backend). */
const VALID_CUPONES = { UNIVALLE10: 10, CAFEQR20: 20 };

/* ============================================================
   2. ESTADO DE LA SESIÓN (en memoria, se reinicia al recargar)
   ============================================================ */

const state = {
  cart: [],              // [{ id, name, price, qty }]
  total: 0,
  userEmail: '',
  userName: '',
  userPhone: '',
  isAdmin: false,
  cupoBalance: 50,
  orderHistory: [],       // pedidos ya pagados, más reciente primero
  allOrders: [],           // TODOS los pedidos de TODOS los usuarios (vista del administrador)
  currentOrder: null,     // pedido activo (factura / seguimiento)
  orderSeq: 1000,         // correlativo de número de pedido
  payAccount: {
    banco: 'Banco Nacional de Bolivia',
    titular: 'Cafetería CafeQR · UNIVALLE',
    numero: '4013256789',
  },
};

/**
 * Pedidos de ejemplo de otros estudiantes, para que el panel de administrador
 * no se vea vacío en la demo. En producción esto vendría de GET /pedidos (admin).
 */
function seedAdminOrders() {
  state.allOrders = [
    {
      orderNumber: 997, pickupCode: 4821, ready: true,
      client: 'ana.perez@est.univalle.edu',
      items: [{ name: 'Café Americano', price: 6, qty: 2 }, { name: 'Muffin', price: 5, qty: 1 }],
      total: 17, date: '21/9/2026', time: '08:15 a. m.',
    },
    {
      orderNumber: 998, pickupCode: 5710, ready: true,
      client: 'carlos.mamani@est.univalle.edu',
      items: [{ name: 'Sandwich Mixto', price: 12, qty: 1 }, { name: 'Jugo Natural', price: 8, qty: 1 }],
      total: 20, date: '21/9/2026', time: '09:40 a. m.',
    },
    {
      orderNumber: 999, pickupCode: 3306, ready: false,
      client: 'daniela.rojas@est.univalle.edu',
      items: [{ name: 'Frappé de Café', price: 12, qty: 1 }, { name: 'Brownie', price: 6, qty: 2 }],
      total: 24, date: '22/9/2026', time: '07:52 a. m.',
    },
  ];
}

/* ============================================================
   3. UTILIDADES
   ============================================================ */

/**
 * Da formato de moneda boliviana a un monto (sin decimales si es entero).
 * @param {number} amount
 * @returns {string}
 */
function formatBs(amount) {
  return amount.toFixed(amount % 1 === 0 ? 0 : 2);
}

/**
 * Calcula las iniciales a mostrar en el avatar a partir de un nombre o correo.
 * @param {string} text
 * @returns {string}
 */
function initials(text) {
  const clean = (text || '').split('@')[0].trim();
  if (!clean) return 'U';
  const parts = clean.replace(/[._-]/g, ' ').split(' ').filter(Boolean);
  return (parts[0]?.[0] || 'U').toUpperCase() + (parts[1]?.[0] || '').toUpperCase();
}

/**
 * Muestra una notificación flotante temporal (toast) en la esquina superior.
 * @param {string} message
 * @param {'info'|'success'|'error'} [type]
 */
function showToast(message, type = 'info') {
  let stack = document.querySelector('.toast-stack');
  if (!stack) {
    stack = document.createElement('div');
    stack.className = 'toast-stack';
    stack.setAttribute('aria-live', 'polite');
    document.body.appendChild(stack);
  }
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  const icon = type === 'success' ? '✓' : type === 'error' ? '⚠' : 'ℹ';
  toast.innerHTML = `<span aria-hidden="true">${icon}</span><span>${message}</span>`;
  stack.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(-6px)';
    toast.style.transition = 'all 0.2s ease';
    setTimeout(() => toast.remove(), 220);
  }, 2600);
}

/**
 * Marca campos inválidos y muestra un mensaje de error bajo un formulario.
 * @param {HTMLElement} errorEl - elemento donde se escribe el mensaje
 * @param {HTMLInputElement[]} invalidInputs - inputs a resaltar en rojo
 * @param {string} message
 */
function showFormError(errorEl, invalidInputs, message) {
  invalidInputs.forEach(i => i.classList.add('input-error'));
  errorEl.innerText = message;
  errorEl.style.display = 'block';
}

function clearFormError(errorEl, inputs) {
  inputs.forEach(i => i.classList.remove('input-error'));
  errorEl.style.display = 'none';
}

/* ============================================================
   4. NAVEGACIÓN ENTRE PANTALLAS
   ============================================================ */

/**
 * Muestra la pantalla indicada y oculta el resto. Las pantallas que dependen
 * de un pedido activo (pago, confirmación, factura, seguimiento) redirigen
 * al menú si se intenta entrar sin un pedido en curso.
 * @param {string} screenId - id de la sección .screen a mostrar
 */
function goTo(screenId) {
  const needsCart = screenId === 'screen-pay' && state.cart.length === 0;
  const needsOrder = ['screen-confirm', 'screen-invoice', 'screen-tracking'].includes(screenId) && !state.currentOrder;
  const needsAdmin = screenId === 'screen-admin' && !state.isAdmin;

  if (needsCart) {
    showToast('Tu carrito está vacío. Agrega productos primero.', 'error');
    screenId = 'screen-menu';
  } else if (needsOrder) {
    showToast('No tienes un pedido en curso.', 'error');
    screenId = 'screen-menu';
  } else if (needsAdmin) {
    showToast('Esa sección es solo para el administrador.', 'error');
    screenId = state.userEmail ? 'screen-menu' : 'screen-login';
  }

  if (screenId === 'screen-admin') renderAdminPanel();

  document.querySelectorAll('.screen').forEach(s => s.classList.remove('active'));
  const target = document.getElementById(screenId);
  if (!target) return;
  target.classList.add('active');

  const navMap = { 'screen-menu': 0, 'screen-pay': 1, 'screen-tracking': 2 };
  const links = document.querySelectorAll('.menu-top > .nav-btn');
  links.forEach(l => l.classList.remove('current'));
  if (navMap[screenId] !== undefined && links[navMap[screenId]]) {
    links[navMap[screenId]].classList.add('current');
  }

  window.scrollTo({ top: 0, behavior: 'smooth' });
}

/* ============================================================
   5. AUTENTICACIÓN (simulada en el navegador)
   ============================================================ */

function login() {
  const userInput = document.getElementById('userInput');
  const passInput = document.getElementById('passInput');
  const errorMsg = document.getElementById('loginError');
  const user = userInput.value.trim();
  const pass = passInput.value.trim();

  clearFormError(errorMsg, [userInput, passInput]);

  if (user === '' || pass === '') {
    showFormError(errorMsg, [user === '' && userInput, pass === '' && passInput].filter(Boolean),
      '⚠️ Completa ambos campos para continuar.');
    return;
  }
  if (!user.includes('@')) {
    showFormError(errorMsg, [userInput], '⚠️ Ingresa un correo institucional válido.');
    return;
  }

  userInput.value = '';
  passInput.value = '';

  // Cuenta única de administrador (ve todos los pedidos de todos los usuarios).
  if (user.toLowerCase() === ADMIN_EMAIL && pass === ADMIN_PASSWORD) {
    state.isAdmin = true;
    state.userEmail = user;
    setUserDisplay(user, 'Administrador');
    document.body.classList.add('is-admin');
    document.getElementById('navLinks').style.display = 'flex';
    goTo('screen-admin');
    showToast('Bienvenido, Administrador', 'success');
    return;
  }

  // TODO(API): reemplazar por POST /login { correo, password } y guardar el token recibido.
  state.isAdmin = false;
  document.body.classList.remove('is-admin');
  state.userEmail = user;
  setUserDisplay(user);

  document.getElementById('navLinks').style.display = 'flex';
  goTo('screen-menu');
  showToast(`Bienvenido, ${user.split('@')[0]}`, 'success');
}

function goToRegister() {
  document.getElementById('loginError').style.display = 'none';
  goTo('screen-register');
}

/**
 * Simula el envío de un enlace de recuperación de contraseña por correo.
 * Sin backend: solo valida el correo y confirma con un toast.
 */
function sendResetLink() {
  const input = document.getElementById('forgotEmail');
  const errorMsg = document.getElementById('forgotError');
  const email = input.value.trim();

  clearFormError(errorMsg, [input]);

  if (!email.includes('@')) {
    showFormError(errorMsg, [input], '⚠️ Ingresa un correo institucional válido.');
    return;
  }

  // TODO(API): reemplazar por POST /password/recuperar { correo }.
  input.value = '';
  showToast(`Enlace de recuperación enviado a ${email}`, 'success');
  goTo('screen-login');
}

function register() {
  const nameInput = document.getElementById('regName');
  const userInput = document.getElementById('regEmail');
  const passInput = document.getElementById('regPass');
  const errorMsg = document.getElementById('registerError');
  const inputs = [nameInput, userInput, passInput];

  clearFormError(errorMsg, inputs);

  const empty = inputs.filter(i => !i.value.trim());
  if (empty.length) {
    showFormError(errorMsg, empty, '⚠️ Completa todos los campos para registrarte.');
    return;
  }
  if (!userInput.value.includes('@')) {
    showFormError(errorMsg, [userInput], '⚠️ Ingresa un correo institucional válido.');
    return;
  }

  // TODO(API): reemplazar por POST /registro { nombre, correo, password }.
  state.isAdmin = false;
  document.body.classList.remove('is-admin');
  state.userEmail = userInput.value.trim();
  state.userName = nameInput.value.trim();
  setUserDisplay(state.userEmail, state.userName);

  document.getElementById('navLinks').style.display = 'flex';
  inputs.forEach(i => (i.value = ''));
  goTo('screen-menu');
  showToast('Cuenta creada. ¡Bienvenido a CafeQR!', 'success');
}

/**
 * Refleja el usuario activo en el nombre, correo e iniciales del avatar.
 * @param {string} email
 * @param {string} [name]
 */
function setUserDisplay(email, name) {
  const shortName = name || email.split('@')[0];
  document.getElementById('userEmailDisplay').innerText = email;
  document.getElementById('userNameDisplay').innerText = shortName;
  const nameTag2 = document.getElementById('userNameDisplay2');
  if (nameTag2) nameTag2.innerText = shortName;
  document.querySelectorAll('.user-tag-avatar, .avatar-circle').forEach(el => {
    el.innerText = initials(name || email);
  });
}

function logout() {
  closeUserMenu();
  state.cart = [];
  state.total = 0;
  state.isAdmin = false;
  document.body.classList.remove('is-admin');
  renderCart();
  document.getElementById('navLinks').style.display = 'none';
  goTo('screen-login');
  showToast('Sesión cerrada.', 'info');
}

/* ============================================================
   6. MENÚ DE USUARIO (dropdown del header)
   ============================================================ */

function toggleUserMenu(event) {
  event.stopPropagation();
  document.querySelector('.user-menu-wrapper').classList.toggle('open');
}

function closeUserMenu() {
  document.querySelector('.user-menu-wrapper')?.classList.remove('open');
}

function goToOrders() {
  closeUserMenu();
  renderOrders();
  goTo('screen-orders');
}

function redeemCupo() {
  const codeInput = document.getElementById('cupoCode');
  const code = codeInput.value.trim().toUpperCase();

  if (!code) {
    showToast('Ingresa un código de cupón.', 'error');
    return;
  }

  // TODO(API): reemplazar por POST /cupones/canjear { codigo }.
  if (VALID_CUPONES[code]) {
    state.cupoBalance += VALID_CUPONES[code];
    document.getElementById('cupoBalance').innerText = 'Bs ' + state.cupoBalance;
    showToast(`Cupón aplicado: +Bs ${VALID_CUPONES[code]}`, 'success');
    codeInput.value = '';
  } else {
    showToast('Código de cupón no válido.', 'error');
  }
}

/* ============================================================
   7. MENÚ Y CARRITO
   ============================================================ */

/** Dibuja el menú agrupado por categorías a partir de PRODUCTS (una vez, al cargar). */
function renderProducts(filterText) {
  const container = document.getElementById('productGrid');
  const emptyState = document.getElementById('productEmptyState');
  if (!container) return;

  const query = (filterText || '').trim().toLowerCase();
  let visibleCount = 0;

  container.innerHTML = PRODUCT_CATEGORIES.map(cat => {
    const items = PRODUCTS
      .filter(p => p.category === cat.id)
      .filter(p => !query || p.name.toLowerCase().includes(query))
      .sort((a, b) => a.price - b.price);
    if (!items.length) return '';
    visibleCount += items.length;
    return `
      <div class="product-section">
        <h3 class="product-section-title">${cat.icon} ${cat.label}</h3>
        <div class="grid-products">
          ${items.map(p => `
            <button class="card-product" type="button" data-action="add-to-cart" data-id="${p.id}">
              <div class="img-placeholder" aria-hidden="true">${p.icon}</div>
              <h3>${p.name}</h3>
              <p class="price">Bs ${formatBs(p.price)}</p>
            </button>
          `).join('')}
        </div>
      </div>
    `;
  }).join('');

  if (emptyState) emptyState.hidden = visibleCount > 0;
}

/** Filtra el menú en vivo mientras el usuario escribe en el buscador. */
function filterProducts() {
  const input = document.getElementById('productSearch');
  renderProducts(input ? input.value : '');
}

function addToCart(productId, cardEl) {
  const product = PRODUCTS.find(p => p.id === productId);
  if (!product) return;

  const existing = state.cart.find(item => item.id === productId);
  if (existing) {
    existing.qty += 1;
  } else {
    state.cart.push({ id: product.id, name: product.name, price: product.price, qty: 1 });
  }

  renderCart();
  showToast(`${product.name} agregado al pedido`, 'success');

  if (cardEl) {
    cardEl.classList.add('flash');
    setTimeout(() => cardEl.classList.remove('flash'), 300);
  }
}

function changeQty(index, delta) {
  const item = state.cart[index];
  if (!item) return;
  item.qty += delta;
  if (item.qty <= 0) state.cart.splice(index, 1);
  renderCart();
}

function removeFromCart(index) {
  state.cart.splice(index, 1);
  renderCart();
}

/** Vuelve a pintar el listado del carrito, el total y el contador del header. */
function renderCart() {
  const cartItems = document.getElementById('cartItems');
  const cartCount = document.getElementById('cartCount');
  const payBtn = document.getElementById('goToPayBtn');

  if (state.cart.length === 0) {
    cartItems.innerHTML = '<p class="empty-cart">Aún no agregaste productos.<br>Toca un producto del menú para empezar.</p>';
  } else {
    cartItems.innerHTML = state.cart.map((item, index) => `
      <div class="cart-line">
        <div class="cart-line-info">
          <span>${item.name}</span>
          <span>Bs ${formatBs(item.price)} c/u · subtotal Bs ${formatBs(item.price * item.qty)}</span>
        </div>
        <div class="qty-control">
          <button class="qty-btn" type="button" data-action="qty-dec" data-index="${index}" aria-label="Quitar una unidad de ${item.name}">−</button>
          <span class="qty-value">${item.qty}</span>
          <button class="qty-btn" type="button" data-action="qty-inc" data-index="${index}" aria-label="Agregar una unidad de ${item.name}">+</button>
        </div>
        <button class="btn-remove" type="button" data-action="remove-item" data-index="${index}" title="Quitar producto" aria-label="Quitar ${item.name} del pedido">✕</button>
      </div>
    `).join('');
  }

  state.total = state.cart.reduce((sum, item) => sum + item.price * item.qty, 0);
  const itemCount = state.cart.reduce((sum, item) => sum + item.qty, 0);

  document.getElementById('total').innerText = formatBs(state.total);
  const total2 = document.getElementById('total2');
  if (total2) total2.innerText = formatBs(state.total);

  if (cartCount) cartCount.innerText = itemCount > 0 ? `(${itemCount})` : '';
  if (payBtn) payBtn.disabled = state.cart.length === 0;
}

function goToPay() {
  if (state.cart.length === 0) {
    showToast('Agrega al menos un producto antes de continuar.', 'error');
    return;
  }
  renderQr();
  renderPayAccountSummary();
  goTo('screen-pay');
}

/* ============================================================
   8. CUENTA QUE RECIBE EL PAGO (editable, se refleja en la factura)
   ============================================================ */

function renderPayAccountSummary() {
  const el = document.getElementById('payAccountSummary');
  if (!el) return;
  const a = state.payAccount;
  el.innerText = `${a.banco} · ${a.titular} · Nro ${a.numero}`;
}

function toggleAccountEdit() {
  const editBox = document.getElementById('payAccountEdit');
  if (!editBox) return;
  const willOpen = editBox.hasAttribute('hidden');
  if (willOpen) {
    document.getElementById('accBanco').value = state.payAccount.banco;
    document.getElementById('accTitular').value = state.payAccount.titular;
    document.getElementById('accNumero').value = state.payAccount.numero;
    editBox.removeAttribute('hidden');
  } else {
    editBox.setAttribute('hidden', '');
  }
}

function saveAccount() {
  const banco = document.getElementById('accBanco').value.trim();
  const titular = document.getElementById('accTitular').value.trim();
  const numero = document.getElementById('accNumero').value.trim();

  if (!banco || !titular || !numero) {
    showToast('Completa banco, titular y número de cuenta.', 'error');
    return;
  }

  // TODO(API): reemplazar por PUT /cuenta-cobro { banco, titular, numero }.
  state.payAccount = { banco, titular, numero };
  renderPayAccountSummary();
  document.getElementById('payAccountEdit').setAttribute('hidden', '');
  renderQr();
  showToast('Cuenta que recibe el pago actualizada.', 'success');
}

/* ============================================================
   9. CÓDIGO QR DE PAGO
   Generado 100% en el navegador (SVG), sin librerías externas.
   Sirve solo para la demo visual: no codifica una transacción
   real hasta que exista la pasarela de pago en el backend.
   ============================================================ */

/** Generador pseudoaleatorio determinista (algoritmo Lehmer / Park-Miller). */
function seededRandom(seed) {
  let s = seed % 2147483647;
  if (s <= 0) s += 2147483646;
  return function () {
    s = (s * 16807) % 2147483647;
    return (s - 1) / 2147483646;
  };
}

/** Dibuja un patrón tipo QR (con los tres "finder patterns") dentro de #qrBox. */
function renderQr() {
  const box = document.getElementById('qrBox');
  if (!box) return;

  const accSeed = (state.payAccount.numero || '').length * 7 + (state.payAccount.banco || '').length;
  const seed = Math.floor(Date.now() % 100000) + state.cart.reduce((s, i) => s + i.name.length * i.qty, 1) + accSeed;
  const rand = seededRandom(seed);
  const size = 17;
  const cell = 200 / size;
  const ink = '#5C1730';
  let modules = '';

  const isFinder = (r, c) => (r < 7 && c < 7) || (r < 7 && c >= size - 7) || (r >= size - 7 && c < 7);

  for (let r = 0; r < size; r++) {
    for (let c = 0; c < size; c++) {
      if (isFinder(r, c)) continue;
      if (rand() > 0.55) {
        modules += `<rect x="${c * cell}" y="${r * cell}" width="${cell * 0.92}" height="${cell * 0.92}" fill="${ink}"/>`;
      }
    }
  }

  const finderPattern = (x, y) => `
    <rect x="${x}" y="${y}" width="${cell * 7}" height="${cell * 7}" fill="${ink}"/>
    <rect x="${x + cell}" y="${y + cell}" width="${cell * 5}" height="${cell * 5}" fill="white"/>
    <rect x="${x + cell * 2}" y="${y + cell * 2}" width="${cell * 3}" height="${cell * 3}" fill="${ink}"/>
  `;

  box.innerHTML = `
    <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Código QR de pago simulado">
      <rect width="200" height="200" fill="white"/>
      ${modules}
      ${finderPattern(0, 0)}
      ${finderPattern(200 - cell * 7, 0)}
      ${finderPattern(0, 200 - cell * 7)}
    </svg>
  `;
}

/* ============================================================
   10. PAGO: VERIFICACIÓN, CANCELACIÓN Y CONFIRMACIÓN
   ============================================================ */

/**
 * Simula la verificación del pago contra el banco/pasarela antes de
 * confirmarlo. Deshabilita los botones y muestra un estado de carga.
 */
function startPaymentVerification() {
  const payBtn = document.getElementById('payNowBtn');
  const cancelBtn = document.getElementById('cancelOrderBtn');
  const failBtn = document.getElementById('failPayBtn');
  const verifyBox = document.getElementById('verifyingBox');

  [payBtn, cancelBtn, failBtn].forEach(b => { if (b) b.disabled = true; });
  if (verifyBox) verifyBox.hidden = false;

  // TODO(API): reemplazar por el polling/webhook real de la pasarela de pago
  // (ver HU-10: "Pago del pedido mediante código QR").
  setTimeout(() => {
    if (verifyBox) verifyBox.hidden = true;
    [payBtn, cancelBtn, failBtn].forEach(b => { if (b) b.disabled = false; });
    confirmPay();
  }, 1400);
}

/** Cancela el pedido en curso y vuelve al menú, sin cobrar nada. */
function cancelOrder() {
  goTo('screen-menu');
  showToast('Pedido cancelado. No se realizó ningún cobro.', 'info');
}

/** Confirma el pago: genera número de pedido, código de recojo, fecha/hora y factura. */
function confirmPay() {
  const pickupCode = Math.floor(1000 + Math.random() * 9000);
  const orderNumber = state.orderSeq++;
  const now = new Date();

  state.currentOrder = {
    orderNumber,
    pickupCode,
    items: state.cart.map(i => ({ ...i })),
    total: state.total,
    date: now.toLocaleDateString('es-BO'),
    time: now.toLocaleTimeString('es-BO', { hour: '2-digit', minute: '2-digit' }),
    payAccount: { ...state.payAccount },
    client: state.userName ? `${state.userName} (${state.userEmail})` : state.userEmail,
    ready: false,
  };
  state.orderHistory.unshift(state.currentOrder);
  state.allOrders.unshift(state.currentOrder);

  renderConfirm();
  renderInvoice();
  resetTrackingSteps();

  state.cart = [];
  renderCart();
  showToast('Pago verificado correctamente ✓', 'success');
  goTo('screen-confirm');
}

function failPay() {
  goTo('screen-error');
}

function copyPickupCode() {
  const code = state.currentOrder?.pickupCode;
  if (!code) return;

  const fallbackCopy = () => {
    const tmp = document.createElement('input');
    tmp.value = String(code);
    document.body.appendChild(tmp);
    tmp.select();
    document.execCommand('copy');
    tmp.remove();
  };

  if (navigator.clipboard?.writeText) {
    navigator.clipboard.writeText(String(code)).then(
      () => showToast('Código copiado al portapapeles', 'success'),
      fallbackCopy
    );
  } else {
    fallbackCopy();
    showToast('Código copiado al portapapeles', 'success');
  }
}

function renderConfirm() {
  const order = state.currentOrder;
  if (!order) return;
  document.getElementById('pickup-code').innerText = order.pickupCode;
  document.getElementById('pickup-code2').innerText = order.pickupCode;
  const confirmOrderNumber = document.getElementById('confirmOrderNumber');
  if (confirmOrderNumber) confirmOrderNumber.innerText = '#' + order.orderNumber;
}

/* ============================================================
   11. FACTURA DE PAGO
   ============================================================ */

function renderInvoice() {
  const order = state.currentOrder;
  if (!order) return;

  document.getElementById('invOrderNumber').innerText = '#' + order.orderNumber;
  document.getElementById('invDate').innerText = order.date;
  document.getElementById('invTime').innerText = order.time;
  document.getElementById('invCode').innerText = order.pickupCode;
  document.getElementById('invTotal').innerText = 'Bs ' + formatBs(order.total);
  document.getElementById('invAccount').innerText =
    `${order.payAccount.banco} · ${order.payAccount.titular} · Nro ${order.payAccount.numero}`;
  document.getElementById('invClient').innerText = order.client || '—';

  document.getElementById('invoiceItems').innerHTML = order.items.map(i => `
    <tr>
      <td>${i.name}</td>
      <td>${i.qty}</td>
      <td>Bs ${formatBs(i.price)}</td>
      <td>Bs ${formatBs(i.price * i.qty)}</td>
    </tr>
  `).join('');

  const deliveryEmail = document.getElementById('deliveryEmail');
  const deliveryPhone = document.getElementById('deliveryPhone');
  if (deliveryEmail) deliveryEmail.value = state.userEmail || '';
  if (deliveryPhone) deliveryPhone.value = state.userPhone || '';
}

function printInvoice() {
  window.print();
}

/**
 * Simula el envío del código de recojo por correo y/o SMS.
 * Sin backend: solo valida los datos y confirma con un toast.
 */
function sendCode() {
  const wantsEmail = document.getElementById('sendEmail').checked;
  const wantsSms = document.getElementById('sendSms').checked;
  const email = document.getElementById('deliveryEmail').value.trim();
  const phone = document.getElementById('deliveryPhone').value.trim();

  if (!wantsEmail && !wantsSms) {
    showToast('Elige al menos un medio: correo o mensaje de texto.', 'error');
    return;
  }
  if (wantsEmail && !email.includes('@')) {
    showToast('Ingresa un correo válido para enviarte el código.', 'error');
    return;
  }
  if (wantsSms && phone.replace(/\D/g, '').length < 7) {
    showToast('Ingresa un número de celular válido.', 'error');
    return;
  }

  // TODO(API): reemplazar por POST /pedidos/{id}/enviar-codigo { email?, phone? }.
  state.userPhone = phone;
  if (wantsEmail) showToast(`Código enviado por correo a ${email}`, 'success');
  if (wantsSms) {
    setTimeout(() => showToast(`Código enviado por SMS a ${phone}`, 'success'), wantsEmail ? 500 : 0);
  }
}

/* ============================================================
   12. SEGUIMIENTO DEL PEDIDO
   ============================================================ */

function resetTrackingSteps() {
  const prep = document.getElementById('stepPrep');
  const ready = document.getElementById('stepReady');
  prep.classList.remove('done');
  prep.classList.add('active');
  ready.classList.remove('done', 'active');
  ready.querySelector('.step-label').innerText = '4. Listo para recoger';
}

function readyOrder() {
  document.getElementById('stepPrep').classList.remove('active');
  document.getElementById('stepPrep').classList.add('done');
  const readyStep = document.getElementById('stepReady');
  readyStep.classList.add('active');
  readyStep.querySelector('.step-label').innerText = '4. ¡Listo para recoger! 🔔';
  if (state.currentOrder) state.currentOrder.ready = true;
  showToast('Tu pedido está listo para recoger', 'success');
}

/* ============================================================
   13. MIS PEDIDOS (historial)
   ============================================================ */

function renderOrders() {
  const list = document.getElementById('ordersList');
  const summary = document.getElementById('ordersSummary');

  if (state.orderHistory.length === 0) {
    list.innerHTML = '<p class="empty-cart">Aún no tienes pedidos realizados.</p>';
    if (summary) summary.innerHTML = '';
    return;
  }

  const totalSpent = state.orderHistory.reduce((sum, o) => sum + o.total, 0);
  if (summary) {
    summary.innerHTML = `<span>${state.orderHistory.length} pedido${state.orderHistory.length > 1 ? 's' : ''}</span><strong>Total gastado: Bs ${formatBs(totalSpent)}</strong>`;
  }

  list.innerHTML = state.orderHistory.map(order => `
    <div class="order-entry">
      <div class="order-entry-top">
        <span class="admin-order-num">Pedido #${order.orderNumber}</span>
        <span>${order.date} · ${order.time}</span>
        <span class="admin-order-status ${order.ready ? 'ready' : 'prep'}">${order.ready ? 'Listo' : 'En preparación'}</span>
      </div>

      <div class="order-item-list">
        ${order.items.map(i => `
          <div class="order-item-row">
            <span>${i.name}${i.qty > 1 ? ` ×${i.qty}` : ''}</span>
            <span>Bs ${formatBs(i.price * i.qty)}</span>
          </div>
        `).join('')}
      </div>

      <div class="order-entry-footer">
        <span>Código de recojo: ${order.pickupCode}</span>
        <strong>Bs ${formatBs(order.total)}</strong>
      </div>
    </div>
  `).join('');
}

/* ============================================================
   14. PANEL DE ADMINISTRADOR (ve los pedidos de todos los usuarios)
   ============================================================ */

function renderAdminPanel() {
  const statsEl = document.getElementById('adminStats');
  const listEl = document.getElementById('adminOrdersList');
  if (!statsEl || !listEl) return;

  const orders = state.allOrders;
  const totalRevenue = orders.reduce((sum, o) => sum + o.total, 0);
  const totalItems = orders.reduce((sum, o) => sum + o.items.reduce((s, i) => s + i.qty, 0), 0);

  const productCount = {};
  orders.forEach(o => o.items.forEach(i => { productCount[i.name] = (productCount[i.name] || 0) + i.qty; }));
  let topProduct = '—';
  let topQty = 0;
  Object.entries(productCount).forEach(([name, qty]) => { if (qty > topQty) { topQty = qty; topProduct = name; } });

  statsEl.innerHTML = `
    <div class="admin-stat"><span>Pedidos totales</span><strong>${orders.length}</strong></div>
    <div class="admin-stat"><span>Ventas totales</span><strong>Bs ${formatBs(totalRevenue)}</strong></div>
    <div class="admin-stat"><span>Productos vendidos</span><strong>${totalItems}</strong></div>
    <div class="admin-stat"><span>Más vendido</span><strong>${topProduct}</strong></div>
  `;

  if (orders.length === 0) {
    listEl.innerHTML = '<p class="empty-cart">Aún no hay pedidos registrados.</p>';
    return;
  }

  listEl.innerHTML = orders.map(o => `
    <div class="admin-order-entry">
      <div class="admin-order-top">
        <span class="admin-order-num">Pedido #${o.orderNumber}</span>
        <span>${o.date} · ${o.time}</span>
        <span class="admin-order-status ${o.ready ? 'ready' : 'prep'}">${o.ready ? 'Listo' : 'En preparación'}</span>
      </div>
      <div class="admin-order-client">${o.client || '—'}</div>
      <div class="admin-order-items">${o.items.map(i => `${i.name}${i.qty > 1 ? ` ×${i.qty}` : ''}`).join(', ')}</div>
      <div class="admin-order-footer">
        <span>Código de recojo: ${o.pickupCode}</span>
        <strong>Bs ${formatBs(o.total)}</strong>
      </div>
    </div>
  `).join('');
}

/* ============================================================
   15. CONEXIÓN DE EVENTOS (sin onclick en el HTML)
   Cada elemento interactivo declara data-action (y, si aplica,
   data-target / data-index / data-id). Este único listener
   delegado resuelve todas las acciones de la aplicación.
   ============================================================ */

function handleAction(event) {
  const el = event.target.closest('[data-action]');
  if (!el) return;

  const action = el.dataset.action;
  const target = el.dataset.target;
  const index = el.dataset.index !== undefined ? Number(el.dataset.index) : undefined;

  switch (action) {
    case 'goto': goTo(target); break;
    case 'goto-register': goToRegister(); break;
    case 'goto-orders': goToOrders(); break;
    case 'toggle-user-menu': toggleUserMenu(event); break;
    case 'login': login(); break;
    case 'register': register(); break;
    case 'logout': logout(); break;
    case 'send-reset': sendResetLink(); break;
    case 'redeem-cupo': redeemCupo(); break;
    case 'add-to-cart': addToCart(el.dataset.id, el); break;
    case 'qty-inc': changeQty(index, 1); break;
    case 'qty-dec': changeQty(index, -1); break;
    case 'remove-item': removeFromCart(index); break;
    case 'goto-pay': goToPay(); break;
    case 'toggle-account-edit': toggleAccountEdit(); break;
    case 'save-account': saveAccount(); break;
    case 'confirm-pay': startPaymentVerification(); break;
    case 'cancel-order': cancelOrder(); break;
    case 'fail-pay': failPay(); break;
    case 'print-invoice': printInvoice(); break;
    case 'send-code': sendCode(); break;
    case 'ready-order': readyOrder(); break;
    case 'copy-code': copyPickupCode(); break;
    default: break;
  }
}

document.addEventListener('DOMContentLoaded', () => {
  renderProducts();
  renderCart();
  renderPayAccountSummary();
  seedAdminOrders();
  document.addEventListener('click', handleAction);

  // Enviar formularios con Enter en vez de solo con el botón.
  document.getElementById('passInput')?.addEventListener('keydown', e => { if (e.key === 'Enter') login(); });
  document.getElementById('regPass')?.addEventListener('keydown', e => { if (e.key === 'Enter') register(); });
  document.getElementById('cupoCode')?.addEventListener('keydown', e => { if (e.key === 'Enter') redeemCupo(); });
  document.getElementById('forgotEmail')?.addEventListener('keydown', e => { if (e.key === 'Enter') sendResetLink(); });
  document.getElementById('productSearch')?.addEventListener('input', filterProducts);

  // Cerrar el menú de usuario al hacer clic afuera o con Escape.
  document.addEventListener('click', (e) => {
    const wrapper = document.querySelector('.user-menu-wrapper');
    if (wrapper && !wrapper.contains(e.target)) wrapper.classList.remove('open');
  });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeUserMenu(); });
});
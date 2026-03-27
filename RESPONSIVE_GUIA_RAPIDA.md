# 🚀 RESPONSIVE - GUÍA RÁPIDA DE REFERENCIA

**Sistema de Seguimiento de Documentos Contractuales**

---

## ⚡ START AQUÍ

### **Tu Link Público (Activo)**
```
https://galactoid-deb-nonmanually.ngrok-free.dev
```

**Pruébalo en:**
- 📱 Móvil (F12 → Ctrl+Shift+M → iPhone)
- 📱 Tablet (F12 → Ctrl+Shift+M → iPad)
- 💻 Desktop (F12 → Ctrl+Shift+M → Laptop)

---

## 📝 ARCHIVOS RESPONSIVOS

```
resources/css/
├── app.css
│   └─ +700 líneas media queries (EDITADO)
│
└── responsive-global.css
    └─ 450+ líneas CSS global (NUEVO)
```

---

## 🎯 LOS 3 BREAKPOINTS

| Dispositivo | Rango | Columns | Button | Input |
|-----------|-------|---------|--------|-------|
| **MÓVIL** | 320-639px | 1 col | 44x44px | 16px font |
| **TABLET** | 640-1023px | 2 cols | 40x40px | 15px font |
| **DESKTOP** | 1024px+ | 3 cols | 36x36px | 14px font |

---

## 🛠️ COMPONENTES RESPONSIVOS

### **Buttons - Touch-Friendly**
```html
<button class="btn">Enviar</button>
```
✅ Móvil: 44x44px, full-width
✅ Tablet: 40x40px, auto-width
✅ Desktop: 36x36px, auto-width

### **Inputs - Sin Zoom iOS**
```html
<input type="text" placeholder="Tu nombre">
```
✅ Móvil: 16px (NO zoom iOS), 44px height
✅ Tablet: 15px, 40px height
✅ Desktop: 14px, 36px height

### **Forms - Stacked**
```html
<form class="form">
  <div class="form-group">
    <label>Nombre</label>
    <input type="text">
  </div>
  <button type="submit">Enviar</button>
</form>
```
✅ Móvil: Vertical stacked, 100% width
✅ Tablet: Flexible layout
✅ Desktop: Normal form

### **Grids - Adaptive**
```html
<div class="grid">
  <div class="card">Card 1</div>
  <div class="card">Card 2</div>
  <div class="card">Card 3</div>
</div>
```
✅ Móvil: 1 columna (100% width)
✅ Tablet: 2 columnas (50% cada uno)
✅ Desktop: 3 columnas (33% cada uno)

### **Tables - Smart Layout**
```html
<table>
  <thead>
    <tr><th data-label="Nombre">Nombre</th>...</tr>
  </thead>
  <tbody>
    <tr>
      <td data-label="Nombre">Juan</td>
      <td data-label="Email">juan@ex.com</td>
    </tr>
  </tbody>
</table>
```
✅ Móvil: Cards layout (display: block)
✅ Tablet: Tabla normal con scroll
✅ Desktop: Tabla completa

### **Cards - Responsive**
```html
<div class="card">
  <h3>Título</h3>
  <p>Contenido</p>
</div>
```
✅ Móvil: 100% width, 12px margin
✅ Tablet: Auto-width, responsive
✅ Desktop: Spacious layout

### **Header - Sticky**
```html
<header>
  <h1>Mi App</h1>
</header>
```
✅ Móvil: Sticky top 44px, responsive font
✅ Tablet: Normal header
✅ Desktop: Full header

### **Sidebar - Hamburguesa**
```html
<aside class="sidebar">
  <!-- Navigation items -->
</aside>
```
✅ Móvil: Fixed overlay, hidden (hamburguesa)
✅ Tablet: Visible sidebar
✅ Desktop: Permanent sidebar

### **Modal - Full-Screen**
```html
<div class="modal">
  <div class="modal-content">
    <!-- Content -->
  </div>
</div>
```
✅ Móvil: 100% viewport (full-screen)
✅ Tablet: Centered, auto-size
✅ Desktop: Centered, nice corners

---

## 🎨 CSS CLASSES

### **Containers**
- `.container` → responsive padding
- `[class*="container"]` → all container-like elements
- `main`, `[role="main"]` → auto-responsive

### **Display**
- `.flex` → flex responsive
- `.grid` → grid responsive (1/2/3 cols)
- `.block` → display block
- `.hidden` → display none (mobile first)

### **Spacing**
- `.p-{1,2,3,4,6}` → responsive padding
- `.m-{1,2,3,4}` → responsive margin
- `.mt-{1,2,3,4}` → margin-top responsive
- `.mb-{1,2,3,4}` → margin-bottom responsive

### **Width**
- `.w-full` → 100% width
- `.w-1/2` → 50% (DESKTOP), 100% (MÓVIL)
- `.w-1/3` → 33% (DESKTOP), 100% (MÓVIL)

### **Dashboard**
- `.dashboard-grid` → 1/2/3 cols responsive
- `.widget` → full-width responsive
- `.card` → card component responsive

---

## 📱 MEDIA QUERIES CLAVE

```css
/* MÓVIL (320-639px) */
@media (max-width: 639px) {
  button { min-height: 44px; width: 100%; }
  input { font-size: 16px; min-height: 44px; }
  .grid { grid-template-columns: 1fr; }
  table { display: block; }
  .sidebar { transform: translateX(-100%); }
}

/* TABLET (640-1023px) */
@media (min-width: 640px) and (max-width: 1023px) {
  button { min-height: 40px; }
  input { font-size: 15px; }
  .grid { grid-template-columns: repeat(2, 1fr); }
  table { display: table; }
}

/* DESKTOP (1024px+) */
@media (min-width: 1024px) {
  button { min-height: 36px; }
  input { font-size: 14px; }
  .grid { grid-template-columns: repeat(3, 1fr); }
  .container { max-width: 1400px; }
  .sidebar { position: relative; }
}
```

---

## ✅ CHECKLIST RÁPIDO

### **Móvil (320px)**
- [ ] Header sticky en top (44px)
- [ ] Hamburguesa visible si hay sidebar
- [ ] Botones: 44x44px mínimo
- [ ] Inputs: 16px font
- [ ] Grids: 1 columna
- [ ] Sin scroll horizontal
- [ ] Texto legible
- [ ] Modals: full-screen
- [ ] Cards: full-width
- [ ] Formularios: stacked vertical

### **Tablet (768px)**
- [ ] Grids: 2 columnas
- [ ] Botones: 40x40px
- [ ] Inputs: 15px font
- [ ] Sidebar: visible
- [ ] Bien balanceado
- [ ] Scrollable if needed
- [ ] Responsive padding

### **Desktop (1024px+)**
- [ ] Grids: 3 columnas
- [ ] Container: max-width 1400px
- [ ] Botones: 36x36px
- [ ] Inputs: 14px font
- [ ] Sidebar: permanent
- [ ] Espacios amplios
- [ ] Todo visible sin scroll

---

## 🔧 VARIABLES CSS

```css
:root {
  --safe-padding-mobile: 12px;      /* Móvil */
  --safe-padding-tablet: 16px;      /* Tablet */
  --safe-padding-desktop: 20px;     /* Desktop */
  --min-touch-target: 44px;         /* Min button/input size */
}
```

**Uso:**
```css
padding: var(--safe-padding-mobile); /* Se adapta automáticamente */
```

---

## 🚀 CÓMO PROBAR

### **Opción 1: DevTools (Recomendado)**
```
1. Abre: https://galactoid-deb-nonmanually.ngrok-free.dev
2. Presiona: F12 (DevTools)
3. Presiona: Ctrl+Shift+M (Device Mode)
4. Selecciona dispositivo:
   ✓ iPhone 15/SE (móvil)
   ✓ iPad/iPad Pro (tablet)
   ✓ Laptop (desktop)
5. Presiona: F5 (Recarga)
6. ¡Todo debe ser responsive!
```

### **Opción 2: Dispositivo Real**
```
1. En tu móvil/tablet
2. Abre navegador
3. Ve a: https://galactoid-deb-nonmanually.ngrok-free.dev
4. ¡Automáticamente responsive!
```

### **Opción 3: Redimensionar Ventana**
```
1. Abre: https://galactoid-deb-nonmanually.ngrok-free.dev
2. Redimensiona ventana:
   - 320px de ancho (móvil)
   - 768px de ancho (tablet)
   - 1200px de ancho (desktop)
3. F5 después de cada redimensión
4. ¡Todo se adapta automáticamente!
```

---

## 📊 BUILDING BLOCKS

### **Tabla Responsive**
```html
<!-- Agrega data-label a cada td -->
<table>
  <tr>
    <td data-label="Nombre">Juan</td>
    <td data-label="Email">juan@ex.com</td>
  </tr>
</table>
```

### **Grid Responsive**
```html
<!-- Automáticamente 1/2/3 cols -->
<div class="grid">
  <div>Item 1</div>
  <div>Item 2</div>
  <div>Item 3</div>
</div>
```

### **Form Responsive**
```html
<!-- Automáticamente stacked en móvil -->
<form class="form">
  <div class="form-group">
    <label>Campo</label>
    <input type="text">
  </div>
  <button type="submit">Enviar</button>
</form>
```

### **Card Responsive**
```html
<!-- 100% width en móvil, auto en desktop -->
<div class="card">
  <h3>Título</h3>
  <p>Contenido</p>
</div>
```

---

## 🎯 PUNTOS CLAVE

```
✅ Mobile-First: CSS base para móvil
✅ 44px Touch: Botones/inputs tocables
✅ 16px Font: Inputs sin zoom iOS
✅ 100% Width: Móvil full-screen
✅ Adaptive Grid: 1/2/3 columnas
✅ Card Tables: Readable tablas en móvil
✅ Hamburguesa: Sidebar smart
✅ no-Scroll: Sin scroll horizontal
```

---

## 📚 DOCUMENTOS RELACIONADOS

```
📖 RESPONSIVE_COMPLETO_GUIA.md
   → Guía técnica detallada

📖 ANTES_DESPUES_RESPONSIVE.md
   → Ejemplos visuales

📖 RESUMEN_EJECUTIVO_RESPONSIVE.md
   → Resumen completo
```

---

## 🌐 LINK ACTIVO

```
https://galactoid-deb-nonmanualmente.ngrok-free.dev
```

**✅ TODO ES 100% RESPONSIVE**

---

*Last Update: 27 Marzo 2026*
*Status: ✅ Fully Responsive*
*Commit: 985a891*

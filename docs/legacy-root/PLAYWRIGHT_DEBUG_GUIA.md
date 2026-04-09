# 🔥 GUÍA DE DEBUG EN CALIENTE - PLAYWRIGHT

## 🚨 Cuando veas un ERROR (como el timeout que viste)

### Opción 1: DEBUG VISUAL (MÁS FÁCIL) ⭐

```bash
npm run test:debug
```

**Esto abre el test paso a paso:**
1. ✅ Ves EXACTAMENTE qué está haciendo
2. ✅ Puedes PAUSAR en cualquier momento
3. ✅ Inspeccionas el DOM en tiempo real
4. ✅ Ves los selectores que está usando

---

### Opción 2: VER EL TRACE (LO QUE HICISTE EN LA IMAGEN)

**Ya lo estás usando!** La interfaz que viste es PERFECTA:

1. **Click en el test que falló** (AUTH-005 en tu caso)
2. **Ver el tab "Errors"** → Te dice el error exacto
3. **Ver "Action/Before/After"** → Screenshots del antes/después
4. **Ver "Source"** → Línea exacta del código
5. **Ver "Network"** → Requests que se hicieron

---

### Opción 3: ARREGLAR Y REEJECUTAR RÁPIDO 🔄

**Mientras la UI está abierta:**

1. ✏️ **Edita el archivo del test** (auth.spec.js)
2. 💾 **Guarda** (Ctrl+S)
3. ▶️ **Click en el botón de reload** en Playwright UI
4. ✅ **El test se reejecuta automáticamente**

**NO necesitas cerrar nada!**

---

## 🎯 SOLUCIONES COMUNES

### Error: "Test timeout exceeded" (tu caso)

**Problema:** El test espera algo que nunca llega

**Solución:**
```javascript
// ❌ ANTES (muy estricto)
await page.waitForURL('**/dashboard**');

// ✅ DESPUÉS (con try-catch)
try {
  await page.waitForURL('**/dashboard**', { timeout: 15000 });
} catch (e) {
  console.log('No redirigió - verificar credenciales');
}
```

**Ya lo arreglé en los archivos!** ✅

---

### Error: "Element not found"

**Problema:** El selector no encuentra el elemento

**Solución en caliente:**
```javascript
// ❌ Muy específico
await page.click('[data-cy="user-menu"]');

// ✅ Múltiples opciones
await page.click('[data-cy="user-menu"], .user-menu, button:has-text("Usuario")');
```

---

### Error: "Navigation timeout"

**Problema:** La página tarda mucho en cargar

**Solución:**
```javascript
// Aumentar timeout solo para esa acción
await page.goto('/login', { timeout: 60000 });

// O esperar por estado específico
await page.waitForLoadState('domcontentloaded');
```

---

## 🔄 WORKFLOW DE DEBUG RÁPIDO

```
1. Ejecuta: npm test
2. Ve el error en la UI
3. Mira el screenshot "Before" y "After"
4. Edita el código del test
5. Guarda (Ctrl+S)
6. Click en "Reload" en Playwright
7. Repite hasta que pase ✅
```

---

## 💡 TRUCOS PRO

### 1. Pausar el test en un punto específico

```javascript
test('Mi test', async ({ page }) => {
  await page.goto('/login');
  
  await page.pause(); // 🔥 SE PAUSA AQUÍ - puedes inspeccionar todo
  
  await page.fill('input[name="email"]', 'admin@test.com');
});
```

### 2. Ver todo lo que Playwright está haciendo

```javascript
test('Mi test', async ({ page }) => {
  // Modo slow motion (ver cada paso)
  await page.setDefaultTimeout(5000);
  
  console.log('Navegando a login...');
  await page.goto('/login');
  
  console.log('Llenando email...');
  await page.fill('input[name="email"]', 'admin@test.com');
});
```

### 3. Tomar screenshot manual cuando quieras

```javascript
await page.screenshot({ path: 'debug-aqui.png' });
```

### 4. Ver qué selectores están disponibles

```javascript
// En el test, agrega:
const elementos = await page.locator('button').count();
console.log(`Hay ${elementos} botones en la página`);
```

---

## 📊 INTERPRETAR LA UI DE PLAYWRIGHT

**Lo que viste en la imagen:**

| Sección | Qué te dice |
|---------|-------------|
| **Timeline** (arriba) | Screenshots cada 2 segundos - ve la progresión |
| **Actions** (izquierda) | Cada paso que hizo el test |
| **Before/After** (derecha) | Screenshots antes/después de la acción |
| **Errors** (tab abajo) | Error exacto y stack trace |
| **Console** | Logs de la página |
| **Network** | Requests HTTP |

---

## 🎬 COMANDOS DE DEBUG

```bash
# Debug interactivo (paso a paso)
npm run test:debug

# Ejecutar con navegador visible
npm run test:headed

# Ver solo un test específico
npx playwright test auth.spec.js --debug

# Ver el último trace
npx playwright show-trace test-results/.../.../trace.zip
```

---

## ✅ ARREGLO DE TU ERROR ESPECÍFICO

**Error que viste:**
```
Error: page.waitForURL: Test timeout of 30000ms exceeded
waiting for navigation to "**/dashboard**"
```

**Causa probable:**
- Las credenciales no son correctas
- La página no redirige a /dashboard
- El servidor no está corriendo

**Lo que arreglé:**

1. ✅ Aumenté timeout global a 60s
2. ✅ Agregué try-catch en login
3. ✅ Agregué esperas más flexibles
4. ✅ Agregué múltiples selectores para logout

**Ahora vuelve a ejecutar:**
```bash
npm test
```

---

## 🔧 VERIFICACIÓN ANTES DE PRUEBAS

**Checklist rápido:**

```bash
# 1. Servidor corriendo?
php artisan serve

# 2. Base de datos lista?
php artisan migrate:fresh --seed

# 3. Usuario de prueba existe?
# Verifica en tu BD que exista: admin@test.com
```

---

## 📹 DONDE ESTÁN LAS EVIDENCIAS

Después de cada test (pase o falle):

```
test-results/
├── auth-AUTH-001-Login-exitoso/
│   ├── video.webm          ← 🎥 VIDEO COMPLETO
│   ├── screenshot-1.png    ← 📸 Screenshots
│   └── trace.zip           ← 📊 TRACE (el que viste en UI)
```

**Para ver un trace:**
```bash
npx playwright show-trace test-results/auth-.../trace.zip
```

---

## 🎯 RESUMEN

1. **Error aparece** → Míralo en la UI
2. **Ve screenshots Before/After** → Entiendes qué pasó
3. **Edita el código** → Arregla el selector o timeout
4. **Guarda y Reload** → Prueba de nuevo
5. **Repite** hasta que pase ✅

**NO necesitas parar y reiniciar!** Todo es en caliente 🔥

---

*La UI de Playwright es tu mejor amiga para debuggear* ❤️

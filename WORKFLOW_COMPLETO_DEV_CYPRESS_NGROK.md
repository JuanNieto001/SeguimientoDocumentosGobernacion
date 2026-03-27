# 🚀 WORKFLOW COMPLETO: Desarrollo + Cypress + Ngrok

**Sistema de Seguimiento de Documentos Contractuales - Gobernación de Caldas**
**Fecha**: 27 de Marzo de 2026

---

## 📋 FLUJO COMPLETO EN 5 MINUTOS

### Scenario 1: Testing Local + Desarrollo

```bash
# Terminal 1: Inicia API Laravel
php artisan serve
# Correrá en: http://localhost:8000

# Terminal 2: Tests automáticos
npm run cypress:open
# O: npm run cypress:run
```

**Resultado:** Tests ejecutándose contra API local ✅

---

### Scenario 2: Compartir con QA (Mismo Equipo)

```bash
# Terminal 1: API Laravel
php artisan serve

# Terminal 2: Ngrok
./start-ngrok.sh   # Mac/Linux
# O: start-ngrok.bat (Windows)
# Selecciona opción 1 (Puerto 8000)

# URL pública: https://abc123de.ngrok.io
```

**Envía a QA:** `https://abc123de.ngrok.io`
**QA abre en su navegador** → Ve cambios en tiempo real ✅

---

### Scenario 3: Testing Remoto Automatizado

```bash
# Terminal 1: API Laravel
php artisan serve

# Terminal 2: Ngrok
./start-ngrok.sh "8000"
# Obtén URL: https://abc123de.ngrok.io

# Terminal 3: Cypress con URL remota
CYPRESS_BASE_URL=https://abc123de.ngrok.io npm run cypress:run
```

**Resultado:** Tests ejecutándose contra URL pública ✅

---

### Scenario 4: Stack Completo (API + Frontend + Ngrok)

```bash
# Terminal 1: API Laravel
php artisan serve

# Terminal 2: Frontend Vite
npm run dev

# Terminal 3: Ngrok API
./start-ngrok.sh "8000"

# Terminal 4: Tests
npm run cypress:open
```

**Todas las capas expuestas y testeadas** ✅

---

## 📊 ARQUITECTURA DEL WORKFLOW

```
┌─────────────────────────────────────────────────────────────────┐
│                    DESARROLLO LOCAL                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  API Local               Frontend Local                          │
│  (http://localhost:8000) (http://localhost:5173)               │
│          ▲                       ▲                               │
│          │                       │                               │
│  ┌───────┴───────┐      ┌────────┘                              │
│  │               │      │                                       │
│  ▼               ▼      ▼                                       │
│ Cypress Tests (Local o Remoto)                                 │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
           │
           │ (Ngrok)
           ▼
┌─────────────────────────────────────────────────────────────────┐
│                    INTERNET PÚBLICO                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  https://abc123de.ngrok.io                                     │
│        ▲                                                        │
│        │                                                        │
│  ┌─────┴──────────────────────────────┐                        │
│  │ QA | Stakeholders | Tests Remotos  │                        │
│  └────────────────────────────────────┘                        │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🎯 CASOS DE USO DETALLADOS

### Caso 1: Desarrollo Local Aislado

**Sin Ngrok:**
```bash
# Solo tú trabajas localmente
php artisan serve          # Terminal 1
npm run cypress:run        # Terminal 2
```

✅ Perfecto para desarrollo individual
❌ Nadie más puede ver los cambios

---

### Caso 2: QA Testing Remoto en Vivo

**Con Ngrok:**

```bash
# Tu máquina (Desarrollador)
php artisan serve
./start-ngrok.sh
# URL: https://abc123de.ngrok.io

# Máquina de QA (Tester)
# 1. Navega a: https://abc123de.ngrok.io
# 2. Prueba el sistema
# 3. Reporta bugs
# 4. Ve cambios en tiempo real cuando corrijas
```

✅ QA prueba en tiempo real
✅ Sin deploy necesario
✅ Cambios visibles al segundo

**Workflow:**
```
Dev hace cambio
    ↓
Dev guarda
    ↓
QA ve cambio inmediatamente en https://abc123de.ngrok.io
    ↓
QA reporta bug
    ↓
Dev corrige
    ↓
QA ve corrección inmediatamente
```

---

### Caso 3: Demostración en Reunión

**Escenario:**
Necesitas mostrar el sistema a stakeholders pero no quieres compartir tu máquina.

```bash
# Tu máquina
php artisan serve
./start-ngrok.sh
# URL: https://abc123de.ngrok.io

# En la reunión
Share screen → URL de Ngrok
Todos ven cambios en tiempo real
```

---

### Caso 4: Testing Automatizado Remoto

```bash
# Tu máquina
php artisan serve
./start-ngrok.sh
# URL: https://abc123de.ngrok.io

# CI/CD o máquina de QA
CYPRESS_BASE_URL=https://abc123de.ngrok.io npm run cypress:run

# Tests automatizados prueban contra tu URL pública
```

---

### Caso 5: Integración de Webhooks

Tu API local recibe webhooks de servicios externos.

```php
# routes/api.php
Route::post('/webhooks/payment', function(Request $request) {
    Log::info('Webhook recibido', $request->all());
    return response()->json(['status' => 'ok']);
});
```

```bash
# Expone el webhook
./start-ngrok.sh
# URL: https://abc123de.ngrok.io
```

```bash
# Servicio externo envía a:
POST https://abc123de.ngrok.io/api/webhooks/payment
{
  "payment_id": "123456",
  "status": "completed"
}

# Tu API local recibe y procesa
```

---

## 🛠️ SETUP PASO A PASO

### Paso 1: Instalar Ngrok

```bash
npm install -g ngrok
```

Verifica:
```bash
ngrok --version
# Output: ngrok version 3.3.0
```

### Paso 2: Autenticarse

1. Ve a: https://dashboard.ngrok.com/auth/your-authtoken
2. Copia tu token
3. Ejecuta:
```bash
ngrok config add-authtoken TU_TOKEN_AQUI
```

Verifica:
```bash
cat ~/.ngrok2/ngrok.yml  # Mac/Linux
# O: type %USERPROFILE%\.ngrok2\ngrok.yml  # Windows
```

### Paso 3: Probar Ngrok

```bash
./start-ngrok.sh          # Mac/Linux
# O: start-ngrok.bat      # Windows
```

Selecciona opción 1 (Puerto 8000)

Verás algo como:
```
Forwarding    https://abc123de.ngrok.io -> http://localhost:8000
```

### Paso 4: Verificar Funcionamiento

Abre en navegador: `https://abc123de.ngrok.io`

Deberías ver tu API respondiendo.

### Paso 5: Configurar para Testing

Opción A: Variable de ambiente
```bash
CYPRESS_BASE_URL=https://abc123de.ngrok.io npm run cypress:run
```

Opción B: Editar `cypress.config.js`
```javascript
export default defineConfig({
  e2e: {
    baseUrl: 'https://abc123de.ngrok.io',  // Tu URL de Ngrok
  }
});
```

### Paso 6: Ejecutar Tests

```bash
npm run cypress:run
# Tests ahora ejecutan contra https://abc123de.ngrok.io
```

---

## 📱 TESTING EN MÚLTIPLES DISPOSITIVOS

### Setup

```bash
# Tu máquina
php artisan serve
./start-ngrok.sh
# URL: https://abc123de.ngrok.io
```

### Probar en otros dispositivos

```
🖥️ Desktop:   https://abc123de.ngrok.io
📱 Mobile:    https://abc123de.ngrok.io (misma URL)
💻 Laptop:    https://abc123de.ngrok.io (misma URL)
```

**Todos ven los mismos cambios en tiempo real** ✅

---

## 🔒 SEGURIDAD

### Proteger con Contraseña

```bash
ngrok http 8000 --basic-auth usuario:contraseña
```

Cuando accedan:
```
Username: usuario
Password: contraseña
```

### Limitar Acceso

```bash
# Máximo 100 requests por segundo
ngrok http 8000 --rate-limit 100

# Solo IP específica
ngrok http 8000 --request-header-add="X-Allowed-IPs: 192.168.1.100"
```

### Desactivar Ngrok

```
Ctrl + C en la terminal
```

URL pública deja de funcionar inmediatamente.

---

## 📊 MONITOREO

### Dashboard Web de Ngrok

```
http://localhost:4040
```

Verás:
- Todas las requests HTTP/HTTPS
- Headers, body, response
- Status codes y latencia
- Historial completo

### Debugging

```bash
# Ver request actualizada
# Abre: http://localhost:4040
# Click en cualquier request
# "Inspect" muestra detalles
```

---

## 🎓 EJEMPLOS DE COMANDOS

### Exponer API

```bash
ngrok http 8000
```

### Exponer con Subdomain (Plan pagado)

```bash
ngrok http 8000 --subdomain=mi-api-caldas
# URL fija: https://mi-api-caldas.ngrok.io
```

### Exponer con Autenticación

```bash
ngrok http 8000 --basic-auth user:pass
```

### Exponer con Límite de Rate

```bash
ngrok http 8000 --rate-limit 50
```

### Exponer con Headers Personalizados

```bash
ngrok http 8000 --request-header-add "X-Custom: value"
```

### Múltiples Tunnels (Requiere config file)

```bash
# ngrok.yml
tunnels:
  api:
    proto: http
    addr: 8000
  frontend:
    proto: http
    addr: 5173
```

```bash
ngrok start api frontend
```

---

## ⏱️ DURACIÓN ESPERADA

| Tarea | Tiempo |
|-------|--------|
| Descargar Ngrok | 2 min |
| Autenticar | 2 min |
| Configurar | 1 min |
| Probar | 1 min |
| **Total Setup** | **~6 min** |
| Exposición recurrente | **15 seg** |

Una vez configurado, solo necesitas: `./start-ngrok.sh` cada vez que quieras exponer.

---

## 🚀 COMANDOS MÁS USADOS

```bash
# Iniciar Ngrok
./start-ngrok.sh          # Mac/Linux con menu
start-ngrok.bat           # Windows con menu

# O directo
ngrok http 8000           # Exponer puerto 8000
ngrok http 5173           # Exponer puerto 5173

# Ver dashboard
# Abre: http://localhost:4040

# Tests con Ngrok
CYPRESS_BASE_URL=https://abc123de.ngrok.io npm run cypress:run

# Parar
# Ctrl + C
```

---

## 🐛 SOLUCIÓN DE PROBLEMAS

### URL no accesible
```bash
# Verifica que Ngrok está corriendo
curl http://localhost:4040

# Verifica API está corriendo
curl http://localhost:8000
```

### SSL issues
```bash
ngrok http 8000 --disable-http-rewrites
```

### Tests timeouts
```javascript
// cypress.config.js
defaultCommandTimeout: 15000  // Aumenta a 15 seg
```

### Conexión lenta
```bash
# Usa región más cercana
ngrok http 8000 --region eu  # Europa
ngrok http 8000 --region ap  # Asia
```

---

## 📚 DOCUMENTACIÓN RELACIONADA

- **GUIA_NGROK_INTEGRACION.md** - Guía completa de Ngrok
- **CYPRESS_QUICK_START.md** - Comandos de Cypress
- **00-COMIENZA-AQUI-FASE-3.md** - Punto de partida del proyecto

---

## ✅ CHECKLIST RÁPIDO

```
[ ] Instalar: npm install -g ngrok
[ ] Autenticar: ngrok config add-authtoken TOKEN
[ ] Verificar: ngrok --version
[ ] Inicia API: php artisan serve
[ ] Inicia Ngrok: ./start-ngrok.sh (obtén URL)
[ ] Prueba en navegador: https://abc123de.ngrok.io
[ ] Tests locales: npm run cypress:open
[ ] Tests remotos: CYPRESS_BASE_URL=... npm run cypress:run
```

---

**¡Listo para trabajar!** 🌍

Con este workflow puedes:
- ✅ Desarrollar localmente
- ✅ Probar remotamente
- ✅ Compartir en tiempo real
- ✅ Recibir webhooks
- ✅ Automatizar testing remoto

**Próximo paso:**
```bash
./start-ngrok.sh
```


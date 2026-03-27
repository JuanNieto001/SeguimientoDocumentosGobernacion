# 🌍 NGROK INTEGRATION - Desarrolla Localmente, Expón Globalmente

**Sistema de Seguimiento de Documentos Contractuales - Gobernación de Caldas**

---

## 📋 Tabla de Contenidos

1. [Instalación](#instalación)
2. [Autenticación](#autenticación)
3. [Configuración del Proyecto](#configuración-del-proyecto)
4. [Ejecución](#ejecución)
5. [Testing Remoto](#testing-remoto)
6. [Troubleshooting](#troubleshooting)

---

## 🔧 Instalación

### Opción 1: Descargar Ngrok

1. Ve a [ngrok.com](https://ngrok.com)
2. Descarga la versión para tu SO (Windows/Mac/Linux)
3. Extrae el archivo
4. Agrega la ruta al PATH

### Opción 2: NPM (Recomendado)

```bash
npm install -g ngrok
```

Verifica la instalación:
```bash
ngrok --version
```

---

## 🔑 Autenticación

### 1. Crea una Cuenta

- Ve a [dashboard.ngrok.com](https://dashboard.ngrok.com)
- Regístrate gratis
- Confirma tu email

### 2. Obtén tu Token

1. Abre tu panel: https://dashboard.ngrok.com/auth/your-authtoken
2. Copia el token (se ve así: `2Xc4VyZ5aB1cD2eF3gH4iJ5k`)

### 3. Configura Autenticación

```bash
ngrok config add-authtoken 2Xc4VyZ5aB1cD2eF3gH4iJ5k
```

Esto crea un archivo `~/.ngrok2/ngrok.yml` con tu configuración.

---

## ⚙️ Configuración del Proyecto

### Estructura de Puertos

```
Backend (Laravel API):  http://localhost:8000
Frontend (Vite):        http://localhost:5173
Cypress Tests:          127.0.0.1:3333 (internal)
```

### Crear archivo `.env.ngrok` (Opcional)

```bash
# Puerto de API a exponer
API_PORT=8000

# Puerto de Frontend a exponer (opcional)
FRONTEND_PORT=5173

# Región más cercana (us, eu, ap, au, sa, jp, in)
REGION=us
```

---

## 🚀 Ejecución

### Método 1: Exponer Solo la API (Recomendado)

```bash
ngrok http 8000
```

**Salida:**
```
Session Status                online
Account                       tu_email@example.com
Version                       3.3.0
Region                        United States (us)
Forwarding                    https://abc123de.ngrok.io -> http://localhost:8000
Connections                   ttl    opn    rt1    rt5    p50    p95
                              0      0      0.00   0.00   0.00  0.00
```

**Tu API pública es:** `https://abc123de.ngrok.io`

### Método 2: Exponer API + Frontend

Terminal 1 (API):
```bash
ngrok http 8000 --subdomain=api-caldas
```

Terminal 2 (Frontend):
```bash
ngrok http 5173 --subdomain=app-caldas
```

### Método 3: Usar Config File

Crea `ngrok.yml`:
```yaml
version: 3
authtoken: TU_TOKEN
tunnels:
  api:
    proto: http
    addr: 8000
    subdomain: api-caldas
  frontend:
    proto: http
    addr: 5173
    subdomain: app-caldas
```

Ejecuta:
```bash
ngrok start api frontend
```

---

## 🧪 Testing Remoto con Cypress

### Paso 1: Inicia Ngrok
```bash
ngrok http 8000
```

Copia la URL: `https://abc123de.ngrok.io`

### Paso 2: Configura Cypress

Edita `cypress.config.js`:
```javascript
export default defineConfig({
  e2e: {
    baseUrl: 'https://abc123de.ngrok.io',  // ← URL de Ngrok
    // ... resto de configuración
  }
});
```

### Paso 3: Ejecuta Tests
```bash
npm run cypress:run
```

Los tests ahora usan la URL pública de Ngrok.

### Método Alternativo: Variable de Entorno

```bash
# Windows
set CYPRESS_BASE_URL=https://abc123de.ngrok.io && npm run cypress:run

# Mac/Linux
CYPRESS_BASE_URL=https://abc123de.ngrok.io npm run cypress:run
```

---

## 📱 Acceso Remoto

Una vez tengas Ngrok corriendo:

### URL Pública
```
https://abc123de.ngrok.io
```

**Puedes acceder desde:**
- 🖥️ Otra máquina en la misma red
- 📱 Tu teléfono móvil
- 🌐 Desde cualquier lugar del mundo
- 🤖 APIs externas

### Testeador Remoto
```
Team member: https://abc123de.ngrok.io
    ↓
API Local: http://localhost:8000
```

---

## 🔒 Seguridad

### Activar Autenticación Ngrok

En terminal:
```bash
ngrok http 8000 --basic-auth=usuario:contraseña
```

Cuando accedan, pedirá login:
- **Usuario:** usuario
- **Contraseña:** contraseña

### Desactivar Ngrok
```
Ctrl + C en la terminal
```

La URL pública deja de funcionar inmediatamente.

---

## 📊 Monitoreo

### Dashboard Web de Ngrok

Accede a: `http://localhost:4040`

Verás:
- Todas las requests HTTP/HTTPS
- Headers y body
- Response status
- Latencia
- Debugging completo

### Inspeccionar con cURL

```bash
curl https://abc123de.ngrok.io/api/dashboard
```

---

## 🎯 Casos de Uso

### Caso 1: QA Testing Remoto

```bash
# Terminal 1: Inicia API
php artisan serve

# Terminal 2: Expone con Ngrok
ngrok http 8000

# Terminal 3: QA ejecuta tests
npm run cypress:run  # (con baseUrl en Ngrok)
```

### Caso 2: Compartir en Reunión

```bash
# Expone tu local dev
ngrok http 8000

# Comparte URL: https://abc123de.ngrok.io
# Stakeholders ven cambios en tiempo real
```

### Caso 3: Integración de APIs Externas

```bash
# Tu API pública
https://abc123de.ngrok.io/api/*

# Sistema externo puede consumir
curl https://abc123de.ngrok.io/api/procesos
```

### Caso 4: Webhook Testing

```bash
# Recibe webhooks en tu local
ngrok http 8000

# Servicio externo envía a:
POST https://abc123de.ngrok.io/api/webhooks/payment
```

---

## 🧰 Scripts Mejorados

### Crear `ngrok-setup.sh` (Mac/Linux)

```bash
#!/bin/bash

echo "🌍 Iniciando Ngrok para exposición remota..."

# Verificar si ngrok está instalado
if ! command -v ngrok &> /dev/null; then
    echo "❌ Ngrok no está instalado"
    echo "Instala con: npm install -g ngrok"
    exit 1
fi

# Obtener puerto
PORT=${1:-8000}
echo "🚀 Exponiendo puerto $PORT..."

# Iniciar Ngrok
ngrok http $PORT

# Mostrar info
echo ""
echo "✅ Ngrok iniciado"
echo "📊 Dashboard: http://localhost:4040"
echo "🔗 URL pública mostrada arriba"
```

Uso:
```bash
./ngrok-setup.sh 8000
```

### Crear `start-full-dev.sh` (All-in-one)

```bash
#!/bin/bash

echo "🚀 Iniciando stack completo..."

# Terminal 1: API Laravel
echo "▶ Iniciando API (http://localhost:8000)..."
php artisan serve &
sleep 2

# Terminal 2: Frontend Vite
echo "▶ Iniciando Frontend (http://localhost:5173)..."
npm run dev &
sleep 2

# Terminal 3: Ngrok
echo "▶ Exponiendo con Ngrok..."
ngrok http 8000

echo "✅ Stack completo iniciado"
```

Uso:
```bash
./start-full-dev.sh
```

---

## 🐛 Troubleshooting

### ❌ "ngrok: comando no encontrado"

**Solución:**
```bash
# Instala:
npm install -g ngrok

# O verifica PATH
which ngrok
```

### ❌ "Authentication failed"

**Solución:**
```bash
# Reconfigura token
ngrok config add-authtoken TU_TOKEN

# Verifica configuración
ngrok config
```

### ❌ URL cambia cada vez

**Solución:** Usa subdominios (requiere plan pagado)
```bash
ngrok http 8000 --subdomain=mi-app-caldas
# URL fija: https://mi-app-caldas.ngrok.io
```

### ❌ Tests fallan con Ngrok

**Causas posibles:**
1. SSL issues - Usa flag: `--disable-http-rewrites`
2. Timeout - Aumenta en `cypress.config.js`
3. CORS - Configura en Laravel `.env`

**Solución:**
```bash
ngrok http 8000 --disable-http-rewrites
```

### ❌ Conexión rechazada

**Verifica:**
```bash
# ¿API está corriendo?
curl http://localhost:8000

# ¿Ngrok está corriendo?
curl http://localhost:4040
```

---

## 📚 Configuraciones Avanzadas

### Limitar Conexiones

```bash
ngrok http 8000 --rate-limit 100
```

(máximo 100 requests/segundo)

### Reintento Automático

```bash
ngrok http 8000 --request-header-add "X-Ngrok: true"
```

### Region Específica

```bash
# Europa
ngrok http 8000 --region eu

# Asia Pacífico
ngrok http 8000 --region ap
```

---

## 🔄 Workflow Completo: Desarrollo + Testing Remoto

### 1. Setup Inicial
```bash
npm install -g ngrok
ngrok config add-authtoken TU_TOKEN
```

### 2. Desarrollo Local
```bash
# Terminal 1: API
php artisan serve

# Terminal 2: Frontend (opcional)
npm run dev
```

### 3. Exponer
```bash
# Terminal 3: Ngrok
ngrok http 8000
# Copia URL: https://abc123de.ngrok.io
```

### 4. Testing Local
```bash
npm run cypress:open
# Usa http://localhost:8000
```

### 5. Testing Remoto (si lo necesitas)
```bash
# Edita cypress.config.js
baseUrl: 'https://abc123de.ngrok.io'

npm run cypress:run
```

### 6. Compartir
```
Envía: https://abc123de.ngrok.io
QA puede ver los cambios en tiempo real
```

---

## 📊 Monitoreo en Tiempo Real

### Verificar Requests

1. Abre: `http://localhost:4040`
2. Verás todas las requests en vivo
3. Haz click en cualquiera para detalles
4. Inspecciona headers, body, response

### Replay Requests

```bash
# En el dashboard de Ngrok
Click en request → "Replay"
```

---

## 🎓 Ejemplos Prácticos

### Ejemplo 1: QA Testing Remoto

```bash
# Tu máquina
ngrok http 8000
# Output: https://abc123de.ngrok.io

# Máquina de QA
# Navega a: https://abc123de.ngrok.io
# Prueba el sistema en vivo
# Los cambios se ven al segundo
```

### Ejemplo 2: Demo en Reunión

```bash
# Host: Ejecuta
php artisan serve
ngrok http 8000

# Comparte URL en chat: https://abc123de.ngrok.io
# Todos ven cambios en tiempo real
```

### Ejemplo 3: Webhook Testing

Tu API recibe webhooks:
```php
// routes/api.php
Route::post('/webhooks/payment', function() {
    return response()->json(['status' => 'ok']);
});
```

Ngrok expone:
```
POST https://abc123de.ngrok.io/api/webhooks/payment
```

Servicio externo puede enviar webhooks a tu local.

---

## 🎯 Resumen Rápido

| Tarea | Comando |
|-------|---------|
| Instalar | `npm install -g ngrok` |
| Autenticar | `ngrok config add-authtoken TOKEN` |
| Exponer API | `ngrok http 8000` |
| Dashboard | `http://localhost:4040` |
| Parar | `Ctrl + C` |

---

## 📞 Soporte

**Documentación Ngrok:** https://ngrok.com/docs
**Configuración avanzada:** https://ngrok.com/docs/ngrok-agent/config

---

**¡Listo para compartir tu desarrollo!** 🌍

Con Ngrok puedes:
- ✅ Desarrollar localmente
- ✅ Exponer globalmente
- ✅ Testear remotamente
- ✅ Compartir en tiempo real
- ✅ Recibir webhooks

**Próximo paso:**
```bash
ngrok http 8000
```


# 🔗 INSTRUCCIONES PARA COMPARTIR - ACCESO REMOTO

**Sistema de Seguimiento de Documentos Contractuales - Gobernación de Caldas**
**Cómo compartir el proyecto con tu equipo**

---

## 🚀 PASO 1: GENERAR LINK PÚBLICO (En tu máquina)

### 1.1 Levanta la API
```bash
php artisan serve
# La API correrá en: http://localhost:8000
```

### 1.2 Inicia Ngrok (Nueva terminal)
```bash
# Windows
start-ngrok.bat

# Mac/Linux
./start-ngrok.sh
```

### 1.3 Selecciona Opción
```
Selecciona opción (1-4): 1

Aparecerá algo como:
↳ Forwarding: https://abc123de.ngrok.io → http://localhost:8000
```

### 1.4 Copia tu URL Pública
```
Tu URL es: https://abc123de.ngrok.io
```

---

## 📤 PASO 2: COMPARTE CON TU EQUIPO

### Copia este mensaje:

```
🔗 LINK DE ACCESO REMOTO:
   https://abc123de.ngrok.io

📱 Puedes acceder desde:
   • Navegador web
   • Otra máquina en la red
   • Tu teléfono móvil
   • Cualquier lugar del mundo

⏱️ El link está activo ahora
   (Se desactiva cuando cierre Ngrok)

🔍 Para ver los cambios:
   • Los cambios se ven al segundo
   • Sin necesidad de recargar
   • Totalmente en tiempo real
```

---

## 👥 PASO 3: OTROS USUARIOS ACCEDEN

### Para QA/Testers:

1. **Abre el link en navegador**
   ```
   https://abc123de.ngrok.io
   ```

2. **Verás la aplicación**
   ```
   Dashboard principal del sistema
   Con todos los módulos disponibles
   ```

3. **Para Testing Automatizado**
   ```bash
   # En su máquina
   CYPRESS_BASE_URL=https://abc123de.ngrok.io npm run cypress:run
   ```

### Para Stakeholders:

1. **Solo abre el link**
   ```
   https://abc123de.ngrok.io
   ```

2. **Explora la aplicación**
   ```
   Ve los cambios en tiempo real
   Sin necesidad de actualizaciones
   ```

---

## 📊 EJEMPLO PRÁCTICO

### Mi máquina (Desarrollador):

Terminal 1:
```bash
php artisan serve
# API corriendo en http://localhost:8000
```

Terminal 2:
```bash
./start-ngrok.sh
# ↳ https://abc123de.ngrok.io → http://localhost:8000
```

Terminal 3 (Opcional - Tests):
```bash
npm run cypress:run
```

### Máquina de QA:

```bash
# En su navegador
Abre: https://abc123de.ngrok.io

# O si quieren ejecutar tests
CYPRESS_BASE_URL=https://abc123de.ngrok.io npm run cypress:run
```

---

## 🎯 CASOS DE USO

### Caso 1: Demo en Reunión en Vivo

```
Yo (Dev):
  1. Levanto API: php artisan serve
  2. Levanto Ngrok: ./start-ngrok.sh
  3. Comparto URL: https://abc123de.ngrok.io

Todos (Reunión):
  1. Abren la URL
  2. Ven la aplicación en tiempo real
  3. Ven cambios mientras desarrollo
```

### Caso 2: Testing Remoto por QA

```
Yo (Dev):
  1. Levanto API: php artisan serve
  2. Levanto Ngrok: ./start-ngrok.sh
  3. Comparto URL: https://abc123de.ngrok.io

QA (Otra máquina):
  1. CYPRESS_BASE_URL=https://abc123de.ngrok.io npm run cypress:run
  2. Tests ejecutan contra mi URL pública
  3. Ven todos los cambios en vivo
```

### Caso 3: Stakeholder Verifica Cambios

```
Yo (Dev):
  1. Levanto API: php artisan serve
  2. Levanto Ngrok: ./start-ngrok.sh
  3. Envío link por email

Stakeholder (Su máquina):
  1. Abre link en navegador
  2. Explora la aplicación
  3. Ve exactamente lo que está en producción
```

---

## 🔐 SEGURIDAD

### Proteger con Contraseña

Si quieres que solo algunos accedan:

```bash
ngrok http 8000 --basic-auth usuario:contraseña
```

Le pedirá login cuando intenten acceder.

### Desactivar Acceso

```
Presiona: Ctrl + C en la terminal de Ngrok
```

El link público deja de funcionar inmediatamente.

---

## 📲 ACCESO DESDE DIFERENTES DISPOSITIVOS

### Desktop
```
https://abc123de.ngrok.io
```

### Móvil
```
https://abc123de.ngrok.io  (misma URL)
```

### Tablet
```
https://abc123de.ngrok.io  (misma URL)
```

**Todos ven exactamente lo mismo en tiempo real** ✅

---

## 📊 MONITOREO EN TIEMPO REAL

### Ver todas las requests

```
Abre: http://localhost:4040
(En tu máquina mientras Ngrok está corriendo)
```

Verás:
- Todas las requests entrantes
- Headers y body
- Response status
- Latencia
- Debugging completo

---

## 🎓 GUÍA PARA DIFERENTES ROLES

### 👨‍💻 Desarrollador
```
1. Levanta Ngrok
2. Comparte URL
3. Haz cambios en local
4. Otros ven cambios en vivo
```

### 🧪 QA/Tester
```
1. Recibe URL: https://abc123de.ngrok.io
2. Opción A:
   - Abre en navegador
   - Prueba manualmente
3. Opción B:
   - Ejecuta tests automáticos
   - CYPRESS_BASE_URL=URL npm run cypress:run
```

### 👔 Stakeholder
```
1. Recibe URL: https://abc123de.ngrok.io
2. Abre en navegador
3. Explora la aplicación
4. Ve todo en tiempo real
```

---

## ⚙️ COMANDOS RÁPIDOS

### Levanta API + Ngrok (Todo de una vez)

Si quieres un workflow automático:

```bash
# Terminal 1
php artisan serve

# Terminal 2
./start-ngrok.sh
```

### Ver URL rápido
```bash
# Se muestra en la terminal de Ngrok
# Busca: "Forwarding"
```

---

## 🐛 TROUBLESHOOTING

### No funciona el link

**Verifica:**
```
1. ¿API está corriendo?
   curl http://localhost:8000

2. ¿Ngrok está corriendo?
   curl http://localhost:4040

3. ¿Token de Ngrok configurado?
   ngrok config
```

### Link muere cuando cierra terminal

**Normal.** Ngrok solo funciona mientras está corriendo.
Si necesita persistencia, compra un plan de Ngrok con subdomain fijo.

### Alguien no puede acceder

**Posibles causas:**
1. Cerraste Ngrok
2. Cerraste la API
3. Firewall bloqueando

**Soluciones:**
1. Reinicia Ngrok
2. Redirealiza el link
3. Comparte link nuevo

---

## 📞 SOPORTE PARA QUIENES ACCESAN

### Si alguien reporta problema

```
Pregunta 1: ¿Ves el link en el navegador?
Si no → Revisa que tippe la URL correctamente

Pregunta 2: ¿Ves la aplicación pero con errores?
Si sí → Probablemente mi firewall/API cerró
       Dile que reinicie el link

Pregunta 3: ¿Cambios no se ven?
Si no → Dile que recargue F5
       Si persiste → Ngrok necesita reiniciar
```

---

## 🎯 CHECKLIST PARA COMPARTIR

Antes de compartir, verifica:

```
[ ] API levantada: php artisan serve
[ ] Ngrok iniciado: ./start-ngrok.sh
[ ] URL pública copiada: https://abc123.ngrok.io
[ ] Otros pueden acceder a esa URL
[ ] Sus cambios se ven en tiempo real
[ ] Screenshots se capturan
[ ] Tests corren automáticamente
```

---

## 📧 EMAIL PARA COMPARTIR

Copia y modifica:

```
Asunto: Link para acceder al proyecto

Hola,

Aquí está el link para acceder al Sistema de Seguimiento de Documentos:

🔗 https://abc123de.ngrok.io

Puedes:
✓ Explorar la aplicación en tu navegador
✓ Acceder desde cualquier dispositivo
✓ Ver cambios en tiempo real
✓ Ejecutar tests automáticos

El link está activo ahora.
Se desactiva cuando cierre la conexión.

Gracias,
[Tu nombre]
```

---

## 🚀 PRÓXIMOS PASOS

1. **Levanta API**: `php artisan serve`
2. **Inicia Ngrok**: `./start-ngrok.sh`
3. **Copia URL**: `https://abc123de.ngrok.io`
4. **Comparte**: Envía a tu equipo
5. **Monitorea**: Ve en `http://localhost:4040`

---

## 📚 DOCUMENTACIÓN RELACIONADA

- **GUIA_NGROK_INTEGRACION.md** - Setup completo
- **WORKFLOW_COMPLETO_DEV_CYPRESS_NGROK.md** - Workflow integrado
- **CYPRESS_QUICK_START.md** - Comandos de testing

---

**¡Listo para compartir con tu equipo!** 🎉


# 👥 USUARIOS DEL SISTEMA - PRUEBAS QA

## 🔐 CREDENCIALES DE ACCESO

**⚠️ IMPORTANTE - Passwords diferentes:**

| Usuario | Email | Password |
|---------|-------|----------|
| **Admin** | `admin@demo.com` | `12345678` ⭐ |
| **Todos los demás** | (varios) | `12345` |

---

## 📋 LISTADO COMPLETO DE USUARIOS

### 🎯 ADMINISTRADORES

| Email | Nombre | Rol | Permisos |
|-------|--------|-----|----------|
| `admin@demo.com` | Admin | Administrador | TODOS |
| `jesin@demo.com` | Jesin | Administrador | TODOS |

---

### 👔 UNIDAD SOLICITANTE (Sistemas)

**Secretaría:** Planeación  
**Unidad:** Unidad de Sistemas

| Email | Nombre | Rol | Etapas Responsables |
|-------|--------|-----|---------------------|
| `jefe.sistemas@demo.com` | Jefe Unidad Sistemas | unidad_solicitante | 0, 2, 3, 4, 9 |
| `abogado.sistemas@demo.com` | Abogado Sistemas | unidad_solicitante | 2, 3, 4, 9 |
| `sistemas@demo.com` | Usuario Sistemas | usuario_general | Consulta |

---

### 📊 PLANEACIÓN

**Secretaría:** Planeación

| Email | Nombre | Rol | Etapas Responsables |
|-------|--------|-----|---------------------|
| `descentralizacion@demo.com` | Descentralización | planeacion | 1, 7 |
| `planeacion@demo.com` | Planeación | planeacion | 1, 7 |
| `secretario.planeacion@demo.com` | Secretario Planeación | secretario | 7 (firma RPC) |
| `regalias@demo.com` | Regalías | planeacion | Apoyo |

---

### 💰 HACIENDA

**Secretaría:** Hacienda

| Email | Nombre | Rol | Etapas Responsables |
|-------|--------|-----|---------------------|
| `hacienda@demo.com` | Hacienda | hacienda | 1 (Compatibilidad) |
| `presupuesto@demo.com` | Presupuesto | hacienda | 1 (CDP) |
| `contabilidad@demo.com` | Contabilidad | hacienda | Apoyo |
| `rentas@demo.com` | Rentas | hacienda | Apoyo |

---

### ⚖️ JURÍDICA

**Secretaría:** Jurídica

| Email | Nombre | Rol | Etapas Responsables |
|-------|--------|-----|---------------------|
| `juridica@demo.com` | Jurídica | juridica | 5, 8 |
| `radicacion@demo.com` | Radicación | juridica | 5, 8 |

---

### 🛒 GENERAL (Contratación)

**Secretaría:** General

| Email | Nombre | Rol | Etapas Responsables |
|-------|--------|-----|---------------------|
| `compras@demo.com` | Compras y Suministros | contratacion | Apoyo |
| `secop@demo.com` | SECOP II | secop | 6 (Publicación) |
| `talentohumano@demo.com` | Talento Humano | talento_humano | Apoyo |

---

## 🔄 MAPEO DE USUARIOS POR ETAPA CD-PN

| Etapa | Nombre | Responsable(s) | Emails |
|-------|--------|----------------|--------|
| **0** | Definición Necesidad | Unidad Solicitante | `jefe.sistemas@demo.com` |
| **1** | Solicitud Docs Iniciales | Planeación + Hacienda | `descentralizacion@demo.com`<br>`planeacion@demo.com`<br>`hacienda@demo.com`<br>`presupuesto@demo.com` |
| **2** | Validación Contratista | Unidad Solicitante | `jefe.sistemas@demo.com`<br>`abogado.sistemas@demo.com` |
| **3** | Proyección Contrato | Unidad Solicitante | `abogado.sistemas@demo.com` |
| **4** | Carpeta Precontractual | Unidad Solicitante | `abogado.sistemas@demo.com` |
| **5** | Radicación Jurídica | Jurídica | `juridica@demo.com`<br>`radicacion@demo.com` |
| **6** | Publicación SECOP II | SECOP | `secop@demo.com` |
| **7** | Solicitud RPC | Planeación | `descentralizacion@demo.com`<br>`secretario.planeacion@demo.com` |
| **8** | Radicación Final | Jurídica | `juridica@demo.com`<br>`radicacion@demo.com` |
| **9** | Cierre | Unidad Solicitante | `jefe.sistemas@demo.com` |

---

## 🧪 USUARIOS PARA PRUEBAS

### Login Básico
```javascript
// Admin
email: 'admin@demo.com'
password: '12345'

// Unidad Solicitante
email: 'jefe.sistemas@demo.com'
password: '12345'
```

### Por Funcionalidad

**Crear Proceso:**
- `jefe.sistemas@demo.com` ✅

**Solicitar CDP:**
- `presupuesto@demo.com` ✅
- `hacienda@demo.com` ✅

**Aprobar Jurídica:**
- `juridica@demo.com` ✅

**Publicar SECOP:**
- `secop@demo.com` ✅

**Firmar RPC:**
- `secretario.planeacion@demo.com` ✅

**Sin Permisos (para tests negativos):**
- `sistemas@demo.com` ❌

---

## 📝 NOTAS IMPORTANTES

1. **Passwords:**
   - ⭐ `admin@demo.com` → `12345678` (diferente al resto)
   - Resto de usuarios → `12345`
2. **Roles críticos:** 
   - `admin` - Acceso total
   - `unidad_solicitante` - Puede crear procesos
   - `planeacion` - Maneja CDP
   - `juridica` - Radicación
   - `secop` - Publicación SECOP II

3. **Para pruebas de permisos:**
   - Usar `sistemas@demo.com` (sin permisos especiales)
   - Intentar acciones restringidas

4. **Seeders:**
   - `AdminUserSeeder.php` - Admins
   - `AreaUsersSeeder.php` - Usuarios por área
   - `ProductionSeederStructure.php` - Estructura completa

---

## 🚀 COMANDOS ÚTILES

```bash
# Resetear y crear usuarios
php artisan migrate:fresh --seed

# Solo usuarios
php artisan db:seed --class=AdminUserSeeder
php artisan db:seed --class=AreaUsersSeeder

# Verificar usuarios
php artisan tinker
>>> User::pluck('email')
```

---

**Última actualización:** 7 Abril 2026 - 21:53  
**Total usuarios:** 20+  
**Passwords:** admin=`12345678` | otros=`12345`

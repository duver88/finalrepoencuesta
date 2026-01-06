# 🔄 MIGRACIÓN A MYSQL - INSTRUCCIONES

## ❌ PROBLEMA ACTUAL

Laravel no puede conectarse a MySQL. El error es:
```
Access denied for user 'root'@'localhost' (using password: YES)
```

Esto significa que la **contraseña de MySQL no es correcta**.

---

## 🔧 SOLUCIÓN: Configurar Credenciales Correctas

### Paso 1: Verificar tu contraseña de MySQL

Abre MySQL en tu terminal y prueba la conexión:

```bash
# Prueba con diferentes contraseñas comunes:
mysql -u root -p
# Cuando te pida password, prueba:
# - (vacío - solo presiona Enter)
# - root
# - 12345678
# - password
# - admin
```

### Paso 2: Actualizar el archivo .env

Una vez que sepas tu contraseña correcta, edita:
```
survey-app/.env
```

Y actualiza estas líneas con TU contraseña:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pv
DB_USERNAME=root
DB_PASSWORD=TU_CONTRASEÑA_AQUI    ← Cambia esto
```

**Ejemplos:**

Si tu contraseña es **vacía** (sin contraseña):
```env
DB_PASSWORD=
```

Si tu contraseña es **"12345678"**:
```env
DB_PASSWORD=12345678
```

Si tu contraseña es **"root"**:
```env
DB_PASSWORD=root
```

---

## 🚀 DESPUÉS DE ACTUALIZAR LA CONTRASEÑA

### Paso 1: Limpiar caché de Laravel

```bash
cd survey-app
php artisan config:clear
php artisan cache:clear
```

### Paso 2: Probar conexión

```bash
php artisan db:show
```

Si sale información de MySQL, ¡la conexión funciona! ✅

### Paso 3: Ejecutar migraciones

```bash
php artisan migrate:fresh --seed
```

Esto:
- ✅ Creará todas las tablas en MySQL
- ✅ Creará el usuario admin
- ✅ Inicializará la base de datos

---

## 📊 MIGRAR DATOS DE SQLITE (OPCIONAL)

Si ya tienes encuestas y votos en SQLite y quieres conservarlos:

### Opción 1: Copiar manualmente (Recomendado si hay pocas encuestas)

1. Crea las encuestas manualmente en el nuevo sistema
2. Los votos antiguos no se pueden migrar (por seguridad)

### Opción 2: Script de migración

He preparado un comando para migrar:

```bash
php artisan migrate:fresh --seed  # Crea tablas en MySQL
```

Luego manualmente:
1. Exporta encuestas de SQLite
2. Créalas en la interfaz admin

---

## ✅ VERIFICACIÓN FINAL

Una vez que las migraciones funcionen:

1. **Inicia el servidor:**
   ```bash
   php artisan serve
   ```

2. **Accede al admin:**
   - URL: http://127.0.0.1:8000/HZlflogiis
   - Email: admin@surveys.com
   - Password: Admin@2025!SecureP4ss#Survey

3. **Crea una encuesta de prueba**

4. **Vota en la encuesta**

5. **Verifica los resultados**

---

## 🔍 SOLUCIÓN DE PROBLEMAS

### Error: "SQLSTATE[HY000] [1045] Access denied"

**Causa:** Contraseña incorrecta en `.env`

**Solución:**
1. Verifica tu contraseña de MySQL con: `mysql -u root -p`
2. Actualiza `DB_PASSWORD` en `.env`
3. Ejecuta: `php artisan config:clear`

### Error: "SQLSTATE[HY000] [2002] No connection could be made"

**Causa:** MySQL no está corriendo

**Solución:**
- Inicia XAMPP/WAMP/MAMP
- O inicia MySQL: `net start mysql` (Windows)

### Error: "Base de datos 'pv' no existe"

**Solución:**
Crea la base de datos manualmente:

```bash
mysql -u root -p
```

Luego en MySQL:
```sql
CREATE DATABASE pv CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

---

## 📝 CONFIGURACIÓN ACTUAL

Tu archivo `.env` debe quedar así:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pv
DB_USERNAME=root
DB_PASSWORD=TU_CONTRASEÑA

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

---

## 🎯 RESUMEN DE COMANDOS

```bash
# 1. Edita .env con la contraseña correcta

# 2. Limpia caché
cd survey-app
php artisan config:clear

# 3. Prueba conexión
php artisan db:show

# 4. Ejecuta migraciones
php artisan migrate:fresh --seed

# 5. Inicia servidor
php artisan serve
```

---

## ❓ ¿NECESITAS AYUDA?

Si no sabes tu contraseña de MySQL, puedes:

1. **Resetear la contraseña de MySQL** (Google: "reset mysql root password")
2. **Usar otra base de datos** (cambiar `DB_DATABASE=pv` a otro nombre)
3. **Seguir usando SQLite** (cambiar `DB_CONNECTION=sqlite` en `.env`)

---

**Una vez que sepas tu contraseña correcta de MySQL, actualiza el `.env` y avísame para continuar la migración.**

# 🔒 MEDIDAS DE SEGURIDAD IMPLEMENTADAS

Este documento detalla todas las medidas de seguridad implementadas en el sistema de encuestas para proteger contra ataques maliciosos.

## 📋 ÍNDICE
1. [Protección contra Ataques Comunes](#protección-contra-ataques-comunes)
2. [Validación y Sanitización](#validación-y-sanitización)
3. [Rate Limiting](#rate-limiting)
4. [Detección de Bots](#detección-de-bots)
5. [Headers de Seguridad](#headers-de-seguridad)
6. [Seguridad en Archivos](#seguridad-en-archivos)
7. [Prevención de Votos Duplicados](#prevención-de-votos-duplicados)
8. [Recomendaciones Adicionales](#recomendaciones-adicionales)

---

## 🛡️ PROTECCIÓN CONTRA ATAQUES COMUNES

### ✅ 1. CSRF (Cross-Site Request Forgery)
**Protección:** Laravel incluye protección CSRF por defecto
- Token CSRF en todos los formularios: `@csrf`
- Validación automática en cada petición POST/PUT/DELETE
- **Ubicación:** Todos los formularios en `/resources/views/`

### ✅ 2. XSS (Cross-Site Scripting)
**Protección:** Múltiples capas
- **Blade Templates:** Escapado automático con `{{ $variable }}`
- **Content Security Policy (CSP):** Headers configurados
- **X-XSS-Protection:** Header activado
- **Ubicación:** `SecurityHeadersMiddleware.php`

```php
// CSP configurado para permitir solo recursos confiables
"script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net"
```

### ✅ 3. SQL Injection
**Protección:** Eloquent ORM y Query Builder
- **NO** se usan queries crudas sin preparar
- Todos los parámetros son sanitizados automáticamente
- Validación estricta de IDs y datos
- **Ejemplo:**
```php
// SEGURO - Usando Eloquent
Vote::where('survey_id', $surveyId)->where('ip_address', $ipAddress)->exists();

// NUNCA se hace esto:
// DB::raw("SELECT * FROM votes WHERE survey_id = $surveyId") ❌
```

### ✅ 4. Clickjacking
**Protección:** Header X-Frame-Options
- Configurado como `SAMEORIGIN`
- Previene que la app sea embebida en iframes maliciosos
- **Ubicación:** `SecurityHeadersMiddleware.php`

---

## 🔍 VALIDACIÓN Y SANITIZACIÓN

### Validación de Encuestas (Admin)
```php
// app/Http/Controllers/Admin/SurveyController.php
'title' => 'required|string|max:255|min:3',
'description' => 'nullable|string|max:1000',
'banner' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048|dimensions:min_width=100,min_height=100,max_width=4000,max_height=4000',
'questions' => 'required|array|min:1|max:50',
'questions.*.question_text' => 'required|string|max:500',
'questions.*.options' => 'required|array|min:2|max:20',
'questions.*.options.*' => 'required|string|max:255',
```

**Límites:**
- Máximo 50 preguntas por encuesta
- Máximo 20 opciones por pregunta
- Título: 3-255 caracteres
- Descripción: máximo 1000 caracteres
- Banner: máximo 2MB, solo imágenes válidas

### Validación de Votos (Público)
```php
// app/Http/Controllers/SurveyController.php
'answers' => 'required|array|min:1|max:50',
'answers.*' => 'required|exists:question_options,id',
'fingerprint' => 'required|string|max:100',

// Validación adicional: verificar que las respuestas pertenezcan a la encuesta
foreach ($validated['answers'] as $questionId => $optionId) {
    // Verifica en BD que la opción corresponde a la pregunta de esta encuesta
}
```

---

## ⏱️ RATE LIMITING

### 1. Rate Limiting en Login
**Límite:** 5 intentos por minuto por IP
- Previene ataques de fuerza bruta
- **Ubicación:** `app/Http/Controllers/AuthController.php`

```php
$key = 'login.' . $request->ip();
if (RateLimiter::tooManyAttempts($key, 5)) {
    $seconds = RateLimiter::availableIn($key);
    throw ValidationException::withMessages([
        'email' => ["Demasiados intentos. Espera {$seconds} segundos."],
    ]);
}
```

### 2. Rate Limiting en Votación
**Límite:** 3 intentos de voto cada 10 minutos por IP
- Previene spam de votos
- Previene ataques de denegación de servicio (DoS)
- **Ubicación:** `app/Http/Middleware/PreventDuplicateVote.php`

```php
$key = 'vote_attempt:' . $ipAddress;
if (RateLimiter::tooManyAttempts($key, 3)) {
    $seconds = RateLimiter::availableIn($key);
    return back()->with('error', 'Demasiados intentos. Espera ' . ceil($seconds / 60) . ' minutos.');
}
```

---

## 🤖 DETECCIÓN DE BOTS

### Honeypot Fields (Campos Trampa)
**Campos ocultos que los bots llenan automáticamente:**
```html
<!-- Ubicación: resources/views/surveys/show.blade.php -->
<input type="text" name="website" style="position:absolute;left:-9999px;" tabindex="-1" autocomplete="off">
<input type="text" name="url_field" style="position:absolute;left:-9999px;" tabindex="-1" autocomplete="off">
```

**Cómo funciona:**
- Los campos están ocultos visualmente
- Los usuarios reales NO los llenan
- Los bots automáticos SÍ los llenan
- Si se detecta que están llenos → **Bloqueo 403**

### Detección por User-Agent
**Patrones de bots bloqueados:**
```php
$botPatterns = [
    'bot', 'crawler', 'spider', 'scraper', 'curl', 'wget',
    'python-requests', 'postman', 'insomnia', 'http', 'scrape', 'harvest'
];

// Si el User-Agent contiene alguno → Bloqueo 403
// Si el User-Agent está vacío → Bloqueo 403
```

**Herramientas bloqueadas:**
- ❌ curl
- ❌ wget
- ❌ Python requests
- ❌ Postman
- ❌ Insomnia
- ❌ Scrapers automáticos
- ❌ Crawlers

---

## 🔐 HEADERS DE SEGURIDAD

**Middleware:** `SecurityHeadersMiddleware`
**Se aplica a:** Todas las rutas automáticamente

### Headers Configurados:

1. **X-Frame-Options: SAMEORIGIN**
   - Previene clickjacking
   - Solo permite embeber en el mismo origen

2. **X-XSS-Protection: 1; mode=block**
   - Activa protección XSS del navegador
   - Bloquea la página si detecta ataque

3. **X-Content-Type-Options: nosniff**
   - Previene MIME type sniffing
   - El navegador respeta el Content-Type declarado

4. **Referrer-Policy: strict-origin-when-cross-origin**
   - Controla qué información se envía en el header Referer

5. **Content-Security-Policy (CSP)**
   - Define qué recursos puede cargar la página
   - Solo permite scripts/estilos de orígenes confiables
   ```
   default-src 'self'
   script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net
   style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net
   ```

6. **Permissions-Policy**
   - Deshabilita APIs peligrosas:
   - ❌ Geolocalización
   - ❌ Micrófono
   - ❌ Cámara

**Ubicación:** `app/Http/Middleware/SecurityHeadersMiddleware.php`

---

## 📁 SEGURIDAD EN ARCHIVOS

### Validación de Imágenes (Banners)

**1. Validación Laravel:**
```php
'banner' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048|dimensions:min_width=100,min_height=100,max_width=4000,max_height=4000'
```

**2. Validación Manual Adicional:**
```php
// Verificar extensión
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
if (!in_array(strtolower($extension), $allowedExtensions)) {
    throw new \Exception('Tipo de archivo no permitido.');
}

// Verificar MIME type real
$allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($mimeType, $allowedMimes)) {
    throw new \Exception('El archivo no es una imagen válida.');
}
```

**Protección contra:**
- ❌ Subir archivos PHP disfrazados de imagen
- ❌ Subir archivos ejecutables
- ❌ Subir archivos muy grandes (DoS)
- ❌ Subir archivos con dimensiones maliciosas

**Ubicación:** `app/Http/Controllers/Admin/SurveyController.php`

---

## 🎯 PREVENCIÓN DE VOTOS DUPLICADOS

### Triple Capa de Seguridad:

**1. IP Address**
```php
$ipAddress = $request->ip();
Vote::where('survey_id', $surveyId)
    ->where('ip_address', $ipAddress)
    ->exists();
```

**2. Fingerprint del Navegador**
```javascript
// Generado con características únicas del navegador
const data = [
    nav.userAgent,
    nav.language,
    screen.colorDepth,
    screen.width + 'x' + screen.height,
    new Date().getTimezoneOffset(),
    !!window.sessionStorage,
    !!window.localStorage
].join('|');

// Guardado en LocalStorage
localStorage.setItem('survey_fingerprint', fingerprint);
```

**3. Cookie Persistente**
```php
// Después de votar, se establece cookie de 1 año
return $response->cookie('survey_fingerprint', $fingerprint, 525600);
```

**Resumen:**
- Si la IP ya votó → ❌ Bloqueado
- Si el Fingerprint ya votó → ❌ Bloqueado
- Si la Cookie existe → ❌ Bloqueado

---

## 🔒 CONFIGURACIÓN DE SESIONES SEGURAS

**Archivo:** `.env`

```env
SESSION_DRIVER=database          # Sesiones en BD (más seguro que archivos)
SESSION_LIFETIME=120             # 2 horas de expiración
SESSION_ENCRYPT=false            # No necesario con HTTPS
SESSION_HTTP_ONLY=true           # Cookie no accesible desde JavaScript
SESSION_SAME_SITE=strict         # Solo cookies del mismo sitio
BCRYPT_ROUNDS=12                 # Alto costo de hashing de contraseñas
```

---

## ⚠️ RECOMENDACIONES ADICIONALES

### Para Producción (IMPORTANTE):

1. **✅ Cambiar APP_ENV a production**
   ```env
   APP_ENV=production
   APP_DEBUG=false
   ```

2. **✅ Activar HTTPS**
   ```env
   SESSION_SECURE_COOKIE=true
   APP_URL=https://tudominio.com
   ```

3. **✅ Configurar Firewall del Servidor**
   - Permitir solo puertos 80 (HTTP) y 443 (HTTPS)
   - Bloquear acceso directo a base de datos

4. **✅ Backups Automáticos**
   - Respaldar la base de datos SQLite diariamente
   - Ubicación: `database/database.sqlite`

5. **✅ Monitoreo de Logs**
   - Revisar `storage/logs/laravel.log` regularmente
   - Buscar patrones de ataques (múltiples 403, 422, 500)

6. **✅ Actualizar Dependencias**
   ```bash
   composer update
   php artisan optimize:clear
   ```

7. **✅ Ocultar Información del Servidor**
   - Configurar servidor web para no revelar versión de PHP/Laravel
   - Eliminar headers como `X-Powered-By`

8. **✅ Límites del Servidor Web**
   - Configurar timeout de requests (máx 30 segundos)
   - Limitar tamaño de requests (máx 5MB)
   - Limitar conexiones simultáneas por IP

---

## 📊 MONITOREO DE SEGURIDAD

### Qué Revisar en los Logs:

```bash
# Ver últimos errores
tail -f storage/logs/laravel.log

# Buscar intentos de ataque
grep "403" storage/logs/laravel.log
grep "422" storage/logs/laravel.log
grep "Acceso denegado" storage/logs/laravel.log
```

### Señales de Ataque:
- ⚠️ Muchos errores 403 desde la misma IP
- ⚠️ Muchos errores 422 (datos inválidos)
- ⚠️ Intentos de login fallidos repetidos
- ⚠️ Rate limiting activado frecuentemente
- ⚠️ User-Agents sospechosos en logs

---

## 🎯 RESUMEN DE PROTECCIONES

| Amenaza | Protección | Estado |
|---------|-----------|--------|
| CSRF | Token + Middleware | ✅ Activo |
| XSS | CSP + Escapado + Headers | ✅ Activo |
| SQL Injection | Eloquent ORM + Validación | ✅ Activo |
| Clickjacking | X-Frame-Options | ✅ Activo |
| Votos Duplicados | IP + Fingerprint + Cookie | ✅ Activo |
| Bots | Honeypot + User-Agent | ✅ Activo |
| Fuerza Bruta | Rate Limiting | ✅ Activo |
| Archivos Maliciosos | Validación MIME + Extensión | ✅ Activo |
| DoS Simple | Rate Limiting | ✅ Activo |
| Session Hijacking | HTTP-Only + SameSite | ✅ Activo |

---

## 📞 CONTACTO DE SEGURIDAD

Si detectas una vulnerabilidad:
1. **NO** la publiques públicamente
2. Contacta al administrador del sistema
3. Proporciona detalles técnicos
4. Espera respuesta antes de divulgar

---

**Última actualización:** 2025-10-23
**Versión Laravel:** 11.x
**Nivel de Seguridad:** 🔒 Alto

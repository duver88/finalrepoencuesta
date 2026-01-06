# Sistema de Encuestas - Laravel 11

## Descripción

Sistema completo de gestión de encuestas desarrollado con Laravel 11, que incluye:

- **Panel de Administración Seguro** con autenticación protegida
- **Gestión Completa de Encuestas** (CRUD) con preguntas dinámicas
- **Prevención de Votos Duplicados** mediante IP + Fingerprint de navegador
- **Resultados Analíticos** con gráficos y estadísticas
- **Vista Pública** para que usuarios voten de forma anónima
- **Subida de Imágenes** para banners de encuestas

## Características Principales

### Seguridad
- Usuario admin con contraseña super segura
- Rate limiting en login (5 intentos por minuto)
- Middleware de autenticación y autorización
- Prevención de votos duplicados por IP y fingerprint del navegador
- Protección CSRF en todos los formularios

### Funcionalidades del Admin
- Dashboard con estadísticas generales
- Crear encuestas con:
  - Título y descripción
  - Banner/imagen
  - Múltiples preguntas
  - Opciones de respuesta dinámicas
  - Tipos de pregunta: opción única o múltiple
- Editar encuestas existentes
- Publicar/despublicar encuestas
- Ver resultados analíticos con porcentajes y gráficos
- Obtener link público de cada encuesta

### Vista Pública
- Interfaz amigable para votantes
- Sistema de detección de votos duplicados
- Confirmación visual después de votar
- Diseño responsivo

## Credenciales de Acceso

**Panel de Administración:**
- URL: `http://localhost:8000/login`
- Email: `admin@surveys.com`
- Contraseña: `Admin@2025!SecureP4ss#Survey`

## Instalación y Configuración

### Requisitos
- PHP >= 8.2
- Composer
- Node.js >= 18
- SQLite (ya configurado)

### Pasos de Instalación

1. **Las dependencias ya están instaladas**, pero si necesitas reinstalar:
```bash
cd survey-app
composer install
npm install
```

2. **La base de datos ya está configurada y con datos**, pero si necesitas reiniciar:
```bash
php artisan migrate:fresh
php artisan db:seed --class=AdminSeeder
```

3. **Los assets ya están compilados**, pero si necesitas recompilar:
```bash
npm run build
```

4. **Iniciar el servidor:**
```bash
php artisan serve
```

5. **Acceder a la aplicación:**
- Admin: http://localhost:8000/login
- Después de crear una encuesta, obtendrás el link público: http://localhost:8000/survey/{slug}

## Estructura del Proyecto

### Modelos
- **User**: Usuario administrador
- **Survey**: Encuesta principal
- **Question**: Preguntas de la encuesta
- **QuestionOption**: Opciones de respuesta
- **Vote**: Registro de votos

### Controladores
- **AuthController**: Manejo de autenticación
- **Admin/DashboardController**: Dashboard del admin
- **Admin/SurveyController**: CRUD de encuestas
- **SurveyController**: Vista pública y votación

### Middlewares
- **AdminMiddleware**: Verifica que el usuario sea admin
- **PreventDuplicateVote**: Previene votos duplicados por IP y fingerprint

### Rutas Principales

#### Públicas
- `GET /login` - Login del administrador
- `GET /survey/{slug}` - Ver encuesta pública
- `POST /survey/{slug}/vote` - Registrar voto
- `GET /survey/{slug}/thanks` - Página de agradecimiento

#### Admin (requiere autenticación)
- `GET /admin/dashboard` - Dashboard principal
- `GET /admin/surveys` - Listado de encuestas
- `GET /admin/surveys/create` - Crear encuesta
- `POST /admin/surveys` - Guardar encuesta
- `GET /admin/surveys/{survey}` - Ver resultados
- `GET /admin/surveys/{survey}/edit` - Editar encuesta
- `PUT /admin/surveys/{survey}` - Actualizar encuesta
- `DELETE /admin/surveys/{survey}` - Eliminar encuesta
- `POST /admin/surveys/{survey}/publish` - Publicar encuesta
- `POST /admin/surveys/{survey}/unpublish` - Despublicar encuesta

## Uso del Sistema

### Crear una Encuesta

1. Inicia sesión en el panel de administración
2. Click en "Nueva Encuesta"
3. Completa el formulario:
   - Título de la encuesta
   - Descripción (opcional)
   - Subir banner/imagen (opcional)
   - Agregar preguntas con el botón "+ Agregar Pregunta"
   - Para cada pregunta:
     - Escribe el texto de la pregunta
     - Selecciona el tipo (opción única o múltiple)
     - Agrega al menos 2 opciones de respuesta
4. Click en "Crear Encuesta"
5. La encuesta se guarda como borrador (inactiva)
6. Click en "Publicar" para activarla
7. Copia el link público para compartir

### Ver Resultados Analíticos

1. En el dashboard o listado de encuestas
2. Click en "Resultados" o "Ver Resultados"
3. Verás:
   - Votantes únicos
   - Total de respuestas
   - Resultados por pregunta con:
     - Número de votos por opción
     - Porcentaje
     - Gráfico de barras visual

### Editar una Encuesta

1. En el listado de encuestas, click en "Editar"
2. Modifica los campos necesarios
3. Puedes cambiar:
   - Título y descripción
   - Banner
   - Texto de preguntas
   - Texto de opciones
   - Estado (activa/inactiva)
4. Click en "Actualizar Encuesta"

**Nota:** Las preguntas y opciones que tienen votos no deben eliminarse para mantener la integridad de los datos.

### Prevención de Votos Duplicados

El sistema implementa un mecanismo de doble verificación:

1. **Por IP**: Se registra la IP del votante
2. **Por Fingerprint**: Se genera un identificador único basado en:
   - Canvas fingerprinting
   - User agent
   - Idioma del navegador
   - Resolución de pantalla

El fingerprint se guarda en una cookie con duración de 1 año.

## Tecnologías Utilizadas

- **Backend**: Laravel 11
- **Base de Datos**: SQLite
- **Frontend**: Blade Templates + TailwindCSS
- **Build Tool**: Vite
- **JavaScript**: Vanilla JS para funcionalidades dinámicas

## Seguridad Implementada

1. **Autenticación**:
   - Contraseña hasheada con bcrypt
   - Session tokens
   - CSRF protection

2. **Autorización**:
   - Middleware de admin
   - Verificación de permisos en cada acción

3. **Rate Limiting**:
   - 5 intentos de login por minuto por IP

4. **Validación**:
   - Validación de datos en servidor
   - Sanitización de inputs
   - Protección contra inyección SQL (Eloquent ORM)

5. **Prevención de Fraude**:
   - Detección de votos duplicados
   - Fingerprinting de navegador
   - Registro de IP

## Base de Datos

### Tablas Principales

- **users**: Usuarios administradores
- **surveys**: Encuestas
- **questions**: Preguntas de las encuestas
- **question_options**: Opciones de respuesta
- **votes**: Registro de votos (incluye IP y fingerprint)

### Relaciones

- Survey -> hasMany -> Questions
- Question -> hasMany -> QuestionOptions
- Survey -> hasMany -> Votes
- Question -> hasMany -> Votes
- QuestionOption -> hasMany -> Votes

## Posibles Mejoras Futuras

1. **Exportar Resultados**: PDF, Excel, CSV
2. **Tipos de Preguntas Adicionales**: texto libre, escalas, fecha
3. **Dashboard Mejorado**: Gráficos más avanzados con Chart.js
4. **Notificaciones**: Email cuando alguien vota
5. **Encuestas Programadas**: Fecha de inicio y fin
6. **Múltiples Admins**: Sistema de roles y permisos
7. **API REST**: Para integraciones externas
8. **Temas Personalizables**: Para la vista pública

## Soporte

Para problemas o preguntas:
- Revisa los logs en `storage/logs/laravel.log`
- Verifica que el servidor esté corriendo: `php artisan serve`
- Asegúrate de que los assets estén compilados: `npm run build`

## Licencia

Este proyecto fue creado para demostración y aprendizaje.

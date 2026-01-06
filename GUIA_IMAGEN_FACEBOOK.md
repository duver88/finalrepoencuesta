# 📱 Guía: Imagen Separada para Facebook

## ✅ Funcionalidad Implementada

Ahora puedes tener **dos imágenes diferentes**:
1. **Banner de la encuesta**: Se muestra en la página web
2. **Imagen para Facebook**: Se usa cuando compartes en redes sociales (Facebook, WhatsApp, Twitter, etc.)

---

## 🎯 Cómo Funciona

### **Orden de Prioridad:**

```
1. ¿Hay imagen de Facebook (og_image)?
   ✅ Usa esa imagen

2. Si no, ¿hay banner de la encuesta?
   ✅ Usa el banner

3. Si no, usa la imagen por defecto
   ✅ /public/images/default-survey-preview.jpg
```

---

## 📝 Cómo Usar

### **1. Al Crear una Encuesta Nueva:**

1. Ve a **Admin → Encuestas → Crear Nueva**
2. Llena el formulario:
   - **Banner/Imagen de la Encuesta**: Sube la imagen que quieres mostrar EN la página
   - **Imagen para Facebook (Open Graph)**: Sube la imagen optimizada de 1200x630 px
3. Haz clic en **Crear Encuesta**

### **2. Al Editar una Encuesta Existente:**

1. Ve a **Admin → Encuestas → (tu encuesta) → Editar**
2. Busca la sección **"Imagen para Facebook (Open Graph)"**
3. Sube una imagen de 1200x630 píxeles
4. Haz clic en **Guardar Cambios**

---

## 📐 Dimensiones Recomendadas

| Imagen | Dimensiones Recomendadas |
|--------|-------------------------|
| **Banner de la encuesta** | Cualquier tamaño (se adapta) |
| **Imagen para Facebook** | **1200 x 630 píxeles** (obligatorio) |

**Importante:** La imagen de Facebook DEBE ser 1200x630 para verse perfecta en:
- Facebook
- WhatsApp
- Twitter
- LinkedIn
- Telegram

---

## 🖼️ Cómo Crear la Imagen de Facebook

### **Opción 1: Canva (Gratis y Fácil)**

1. Ve a: https://www.canva.com
2. Haz clic en "Crear un diseño"
3. Selecciona **"Personalizado"** → Escribe: **1200 x 630**
4. Diseña tu imagen:
   ```
   ┌─────────────────────────────────────┐
   │  🏛️ Cultura Popular Bucaramanga     │
   │                                     │
   │      ENCUESTA DE FAVORABILIDAD      │
   │                                     │
   │      🇨🇴 ¡Participa ahora!           │
   └─────────────────────────────────────┘
          1200px × 630px
   ```
5. Descarga como **JPG** (alta calidad)
6. Súbela en el admin

### **Opción 2: Photoshop/GIMP**

1. Nuevo documento: 1200 x 630 px
2. Diseña tu imagen
3. Exporta como JPG (calidad 90%)

### **Opción 3: Redimensionar imagen existente**

Usa: https://www.iloveimg.com/es/redimensionar-imagen
- Sube tu imagen
- Establece: 1200 x 630
- Modo: "Rellenar"
- Descarga

---

## 🧪 Cómo Probar

### **Paso 1: Sube la imagen**
- En el admin, edita tu encuesta
- Sube la imagen de Facebook (1200x630)
- Guarda

### **Paso 2: Limpia caché de Facebook**
1. Ve a: https://developers.facebook.com/tools/debug/
2. Pega la URL de tu encuesta:
   ```
   https://culturapopularbucaramanga.com/survey/tu-encuesta-slug
   ```
3. Haz clic en **"Scrap Again"** (Volver a Scrapear)
4. Verifica que la imagen se vea completa y sin recortes

### **Paso 3: Prueba compartir**
- Comparte el link en Facebook/WhatsApp
- La imagen debería verse **perfecta** ahora

---

## 📊 Antes vs Después

### ❌ Antes (Sin imagen separada):
- La misma imagen se usaba para todo
- Facebook recortaba la imagen
- Se veía mal al compartir

### ✅ Después (Con imagen separada):
- Banner bonito EN la página
- Imagen optimizada para Facebook (1200x630)
- Se ve perfecta al compartir
- Más profesional

---

## 🗂️ Archivos Modificados

| Archivo | Cambio |
|---------|--------|
| `surveys` table | Agregada columna `og_image` |
| `Survey.php` | Campo `og_image` agregado |
| `create.blade.php` | Nuevo campo para subir imagen OG |
| `edit.blade.php` | Nuevo campo para subir imagen OG |
| `SurveyController.php` | Validación y subida de imagen OG |
| `show.blade.php` | Usa `og_image` primero, luego `banner` |

---

## 🚀 Para Implementar en Producción

### **1. Ejecuta el SQL en el servidor:**

Usa phpMyAdmin o línea de comandos:

```bash
# Opción A: phpMyAdmin
# - Ve a la pestaña SQL
# - Copia y pega el contenido de add_og_image_column.sql
# - Ejecuta

# Opción B: Línea de comandos
mysql -u pular_pvddad -p pular_pvddad < add_og_image_column.sql
```

### **2. Sube los archivos actualizados al servidor**

### **3. Listo!**

Ahora puedes editar tus encuestas y agregar imágenes de Facebook.

---

## ❓ Preguntas Frecuentes

**P: ¿Es obligatorio subir la imagen de Facebook?**
R: No, es opcional. Si no la subes, usará el banner principal.

**P: ¿Puedo usar una imagen diferente al banner?**
R: Sí, ese es justamente el propósito. Puedes tener un banner para la web y otro para redes sociales.

**P: ¿Qué pasa si subo una imagen de tamaño incorrecto?**
R: Facebook la redimensionará automáticamente, pero puede verse recortada. Mejor usar 1200x630.

**P: ¿Funciona con WhatsApp?**
R: Sí, WhatsApp usa las mismas etiquetas Open Graph que Facebook.

**P: ¿Debo eliminar las encuestas existentes?**
R: No, las encuestas existentes seguirán funcionando. Solo que ahora puedes agregarles imagen de Facebook.

---

## 📞 Soporte

Si tienes problemas:
1. Verifica que la imagen sea 1200x630
2. Limpia la caché de Facebook
3. Verifica que el archivo SQL se ejecutó correctamente

¡Listo! Ahora tus encuestas se verán perfectas cuando las compartas en redes sociales. 🎉

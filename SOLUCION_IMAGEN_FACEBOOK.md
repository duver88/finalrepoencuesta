# 🖼️ Solución: Imagen Cortada en Facebook

## 📋 Problema Actual

Facebook está recortando la imagen porque **no tiene las dimensiones correctas** para Open Graph.

---

## ✅ Solución Rápida

### **Opción 1: Usar herramienta online (Recomendado)**

1. Ve a: https://www.iloveimg.com/es/redimensionar-imagen
2. Sube tu imagen actual: `public/images/default-survey-preview.jpg`
3. Configura:
   - **Ancho**: 1200 píxeles
   - **Alto**: 630 píxeles
   - **Modo**: "Rellenar" o "Ajustar y rellenar"
4. Descarga la imagen optimizada
5. Reemplaza el archivo en `public/images/default-survey-preview.jpg`

### **Opción 2: Crear nueva imagen en Canva**

1. Ve a: https://www.canva.com
2. Crea un diseño personalizado:
   - **Dimensiones**: 1200 x 630 píxeles
3. Diseña tu imagen con:
   - Logo de Cultura Popular Bucaramanga
   - Título de la encuesta
   - Colores de Colombia (Amarillo, Azul, Rojo)
4. Descarga como JPG (calidad alta)
5. Guarda en `public/images/default-survey-preview.jpg`

### **Opción 3: Usar comando de ImageMagick (si tienes instalado)**

```bash
# Redimensionar y centrar la imagen
convert public/images/default-survey-preview.jpg -resize 1200x630^ -gravity center -extent 1200x630 public/images/default-survey-preview-optimized.jpg

# Luego reemplaza el archivo original
mv public/images/default-survey-preview-optimized.jpg public/images/default-survey-preview.jpg
```

---

## 📐 Requisitos de Facebook Open Graph

| Característica | Valor Recomendado |
|----------------|-------------------|
| **Ancho** | 1200 píxeles |
| **Alto** | 630 píxeles |
| **Relación de aspecto** | 1.91:1 |
| **Tamaño mínimo** | 200x200 px |
| **Tamaño máximo archivo** | 8 MB |
| **Formato** | JPG o PNG |

---

## 🔧 Si Cada Encuesta Tiene su Propio Banner

Si quieres que cada encuesta use su propia imagen en Facebook:

### **Al subir el banner en el admin:**

1. Asegúrate de que la imagen tenga **1200x630 píxeles**
2. O sube una imagen más grande y el sistema la redimensionará

### **Modificación en el código (Ya implementada):**

El sistema ahora usa automáticamente el banner de cada encuesta:
- Si la encuesta **tiene banner** → usa ese banner
- Si **NO tiene banner** → usa la imagen por defecto

```php
// En show.blade.php (línea 7)
@section('og_image_full', $survey->banner ? asset('storage/' . $survey->banner) : url('images/default-survey-preview.jpg'))
```

---

## 🧪 Cómo Probar

1. **Sube la imagen optimizada** (1200x630)
2. **Limpia la caché de Facebook**:
   - Ve a: https://developers.facebook.com/tools/debug/
   - Pega la URL de tu encuesta
   - Haz clic en "Scrape Again" (Volver a Scrapear)
3. **Verifica la vista previa**
   - La imagen debe verse completa, sin recortes

---

## 📝 Plantilla de Diseño Recomendada

Para la imagen de Open Graph (1200x630), incluye:

```
┌─────────────────────────────────────────┐
│  [LOGO] Cultura Popular Bucaramanga     │
│                                         │
│        ENCUESTA DE FAVORABILIDAD        │
│        Alcaldía de Bucaramanga          │
│                                         │
│    🇨🇴 Participa y comparte tu opinión   │
│                                         │
│  [Colores: Amarillo, Azul, Rojo]        │
└─────────────────────────────────────────┘
      1200px × 630px
```

---

## ⚠️ Errores Comunes

| Error | Solución |
|-------|----------|
| Imagen cortada | Redimensiona a 1200x630 |
| Imagen borrosa | Usa calidad alta (>80%) |
| No se actualiza | Limpia caché de Facebook |
| Imagen muy pesada | Comprime a menos de 500KB |

---

## 🚀 Después de Optimizar

1. Sube la imagen optimizada al servidor
2. Limpia caché de Facebook (link arriba)
3. Comparte en Facebook y verifica

**¡La imagen debería verse perfecta ahora!** ✅

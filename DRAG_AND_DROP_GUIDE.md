# 🔄 Guía de Drag & Drop - Reordenamiento de Preguntas y Opciones

**Fecha:** 24 de Octubre, 2025
**Versión:** 1.0 - Sistema de Arrastrar y Soltar

---

## 🎯 ¿Qué es el Drag & Drop?

El sistema de **Drag & Drop** (arrastrar y soltar) permite reorganizar el orden de las preguntas y sus opciones de forma visual e intuitiva, simplemente arrastrándolas con el mouse.

---

## ✨ Características Principales

### **1. Reordenar Preguntas**
- ✅ Arrastra preguntas completas para cambiar su orden
- ✅ Funciona con preguntas existentes y nuevas
- ✅ Los índices se actualizan automáticamente
- ✅ Los inputs mantienen sus datos al reordenar

### **2. Reordenar Opciones**
- ✅ Arrastra opciones dentro de cada pregunta
- ✅ Funciona independientemente en cada pregunta
- ✅ Los números se renumeran automáticamente
- ✅ Los colores y textos se mantienen intactos

### **3. Indicadores Visuales**
- 🎨 Icono de grip vertical (`⋮⋮`) en elementos arrastrables
- 🎨 Cursor cambia a "move" al pasar sobre áreas arrastrables
- 🎨 Efecto fantasma durante el arrastre
- 🎨 Hover effects para mejor UX

---

## 🖱️ Cómo Usar

### **Reordenar Preguntas:**

1. **Ubicar el área de arrastre:**
   - Coloca el cursor sobre el **header** de la pregunta (área gris)
   - Verás el icono `⋮⋮` a la izquierda del título

2. **Arrastrar:**
   - Haz click y mantén presionado sobre el header
   - Arrastra hacia arriba o abajo
   - Un placeholder (línea punteada azul) mostrará dónde se insertará

3. **Soltar:**
   - Suelta el botón del mouse en la posición deseada
   - La pregunta se reubica automáticamente
   - Los números de pregunta se actualizan (Pregunta 1, 2, 3...)

**Ejemplo:**
```
┌─────────────────────────────────────┐
│ ⋮⋮ Pregunta 1  ⚠️ 45 votos [🗑️]     │ ← Arrástrala aquí
├─────────────────────────────────────┤
│  ¿Cuál es tu favorito?              │
│  • Opción 1                         │
│  • Opción 2                         │
└─────────────────────────────────────┘

Arrastra hacia abajo ↓

┌─────────────────────────────────────┐
│ ⋮⋮ Pregunta 2  [🗑️]                 │
├─────────────────────────────────────┤
│  ...                                 │
└─────────────────────────────────────┘
```

### **Reordenar Opciones:**

1. **Ubicar el icono de arrastre:**
   - Cada opción tiene un icono `⋮⋮` al inicio
   - Es el primer elemento (antes del número)

2. **Arrastrar:**
   - Haz click sobre el icono `⋮⋮`
   - Arrastra la opción hacia arriba o abajo dentro de la misma pregunta
   - No puedes mover opciones entre diferentes preguntas

3. **Soltar:**
   - Suelta en la nueva posición
   - Los números se renumeran automáticamente (1, 2, 3...)

**Ejemplo:**
```
Opciones de Respuesta:
┌─────────────────────────────────────┐
│ ⋮⋮  1  [Opción A] [🎨] [🗑️]         │ ← Arrástrala
│ ⋮⋮  2  [Opción B] [🎨] [🗑️]         │
│ ⋮⋮  3  [Opción C] [🎨] ⚠️ 20 [🗑️]   │
└─────────────────────────────────────┘

Después de arrastrar Opción C al primer lugar:

┌─────────────────────────────────────┐
│ ⋮⋮  1  [Opción C] [🎨] ⚠️ 20 [🗑️]   │
│ ⋮⋮  2  [Opción A] [🎨] [🗑️]         │
│ ⋮⋮  3  [Opción B] [🎨] [🗑️]         │
└─────────────────────────────────────┘
```

---

## 🔧 Detalles Técnicos

### **Librería Utilizada:**
- **SortableJS v1.15.0**
- CDN: `https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js`
- Licencia: MIT
- Ligera y sin dependencias

### **Configuración:**

**Para Preguntas:**
```javascript
new Sortable(questionsContainer, {
    animation: 150,              // Animación de 150ms
    handle: '.draggable-handle', // Solo arrastra desde el header
    ghostClass: 'sortable-ghost',// Clase durante el arrastre
    dragClass: 'sortable-drag',  // Clase del elemento arrastrado
    onEnd: updateQuestionIndices // Callback al soltar
});
```

**Para Opciones:**
```javascript
new Sortable(optionsContainer, {
    animation: 150,
    handle: '.draggable-option-handle', // Solo desde el icono ⋮⋮
    ghostClass: 'sortable-ghost',
    dragClass: 'sortable-drag',
    onEnd: function() {
        renumberOptions(questionIndex);
        updateOptionNames(questionIndex);
    }
});
```

### **Funciones Principales:**

#### **1. `initializeSortable()`**
- Inicializa Sortable al cargar la página
- Se ejecuta en `DOMContentLoaded`
- Aplica a preguntas existentes y sus opciones

#### **2. `initializeOptionsSortable(questionIndex)`**
- Inicializa Sortable para las opciones de una pregunta específica
- Se llama al crear nuevas preguntas dinámicamente

#### **3. `updateQuestionIndices()`**
- Actualiza los números visibles (Pregunta 1, 2, 3...)
- Actualiza los atributos `name` de todos los inputs
- Mantiene el orden correcto en el formulario

#### **4. `updateOptionNames(questionIndex)`**
- Actualiza los índices de las opciones en los nombres de inputs
- Actualiza las clases con índices (`option-id-X-Y`)
- Actualiza los IDs de los divs

#### **5. `renumberOptions(questionIndex)`**
- Renumera visualmente las opciones (1, 2, 3...)
- Busca el segundo span (después del icono de grip)

---

## 🎨 Estilos CSS

### **Clases de Estado:**

```css
/* Elemento fantasma durante el arrastre */
.sortable-ghost {
    opacity: 0.4;
    background: #f8f9fa;
    border: 2px dashed #0d6efd;
}

/* Elemento siendo arrastrado */
.sortable-drag {
    opacity: 1;
    cursor: grabbing !important;
}

/* Hover sobre área arrastrable */
.draggable-handle:hover {
    background-color: #e9ecef !important;
    transition: background-color 0.2s;
}

/* Handle de opciones */
.draggable-option-handle {
    cursor: grab;
}

.draggable-option-handle:active {
    cursor: grabbing;
}

/* Hover sobre opciones */
.option-row:hover {
    background-color: #f8f9fa;
    transition: background-color 0.2s;
}
```

---

## 📱 Compatibilidad

### **Navegadores Soportados:**
- ✅ Chrome 24+
- ✅ Firefox 18+
- ✅ Safari 9+
- ✅ Edge 12+
- ✅ Opera 15+
- ✅ Mobile browsers (iOS Safari, Chrome Android)

### **Dispositivos:**
- ✅ Desktop (mouse)
- ✅ Laptop (trackpad)
- ✅ Tablet (touch)
- ✅ Mobile (touch)

**Nota:** En dispositivos táctiles, el arrastre funciona con gestos de tocar y mantener.

---

## ⚠️ Consideraciones Importantes

### **1. Orden en la Base de Datos:**
- El campo `order` en las tablas `questions` y `question_options` determina el orden real
- Al guardar, el backend usa el índice del array del request
- El drag & drop solo afecta el orden visual hasta que guardes

### **2. Preguntas con Votos:**
- ✅ Puedes reordenar preguntas que tienen votos
- ✅ Los votos NO se ven afectados por el reordenamiento
- ✅ Las estadísticas se mantienen correctas

### **3. Nuevas Preguntas:**
- Las preguntas agregadas dinámicamente también son arrastrables
- Se inicializa Sortable automáticamente al crearlas
- Sus opciones también son arrastrables

### **4. Persistencia:**
- Los cambios de orden **NO se guardan** hasta que hagas click en "Actualizar Encuesta"
- Puedes recargar la página para deshacer cambios no guardados
- El orden se envía al servidor según el orden final en el DOM

---

## 🔍 Debugging

### **Problemas Comunes:**

**1. El drag & drop no funciona:**
- Verifica que SortableJS esté cargado (console: `typeof Sortable`)
- Revisa que `initializeSortable()` se ejecute en DOMContentLoaded
- Confirma que los contenedores tengan los IDs correctos

**2. Los números no se actualizan:**
- Verifica que `renumberOptions()` se llame en `onEnd`
- Confirma que el selector de spans sea correcto (`numberSpans[1]`)

**3. Los inputs pierden datos al reordenar:**
- Verifica que `updateQuestionIndices()` actualice los nombres correctamente
- Confirma que no haya regex incorrectos en `updateOptionNames()`

**4. Drag & drop en nuevas preguntas no funciona:**
- Verifica que `initializeOptionsSortable()` se llame al crear pregunta
- Confirma que el container tenga el ID `new-options-container-${index}`

---

## 📊 Flujo de Datos

### **Al Reordenar Preguntas:**

```
1. Usuario arrastra pregunta
   ↓
2. Sortable mueve el elemento en el DOM
   ↓
3. onEnd callback ejecuta updateQuestionIndices()
   ↓
4. Se actualizan:
   - Número visual (Pregunta X)
   - Atributo name de todos los inputs (questions[newIndex])
   ↓
5. Al enviar formulario:
   - Los datos se envían con los nuevos índices
   - Backend recibe array ordenado
   - Se guarda según el orden del array
```

### **Al Reordenar Opciones:**

```
1. Usuario arrastra opción
   ↓
2. Sortable mueve el elemento en el DOM
   ↓
3. onEnd callback ejecuta:
   - renumberOptions(questionIndex)
   - updateOptionNames(questionIndex)
   ↓
4. Se actualizan:
   - Número visual (1, 2, 3...)
   - Atributos name (questions[X][options][newIndex])
   - Clases con índices
   - IDs de divs
   ↓
5. Al enviar formulario:
   - Las opciones se envían en el nuevo orden
   - Backend las guarda según el índice del array
```

---

## 🎯 Casos de Uso

### **Caso 1: Reorganizar Prioridad**
```
Usuario: "Quiero que la pregunta más importante sea la primera"
Solución: Arrastra la pregunta al tope de la lista
```

### **Caso 2: Agrupar Preguntas Similares**
```
Usuario: "Quiero juntar las preguntas de satisfacción"
Solución: Arrastra las preguntas relacionadas para agruparlas
```

### **Caso 3: Opciones en Orden Lógico**
```
Usuario: "Las opciones deben estar de menor a mayor"
Solución: Arrastra las opciones en el orden correcto
```

### **Caso 4: Corregir Errores de Orden**
```
Usuario: "Agregué las opciones en orden incorrecto"
Solución: Reordena sin necesidad de eliminar y volver a crear
```

---

## 🚀 Ventajas del Sistema

### **1. Usabilidad:**
- 🎯 Interfaz intuitiva y natural
- 🎯 No requiere instrucciones complejas
- 🎯 Feedback visual inmediato

### **2. Eficiencia:**
- ⚡ Más rápido que eliminar y volver a crear
- ⚡ Sin pérdida de datos al reordenar
- ⚡ Cambios reversibles antes de guardar

### **3. Flexibilidad:**
- 🔧 Funciona con preguntas existentes y nuevas
- 🔧 Compatible con opciones con votos
- 🔧 Independiente del sistema de eliminación

### **4. Experiencia:**
- ✨ Animaciones suaves
- ✨ Cursor apropiado para cada acción
- ✨ Efectos hover informativos

---

## 📝 Documentación Relacionada

- **SISTEMA_ELIMINACION_FINAL.md** - Sistema de eliminación con conservación de votos
- **RESUMEN_SISTEMA_COMPLETO.md** - Estado general del sistema de encuestas

---

## 🔄 Próximas Mejoras (Opcional)

### **Posibles Funcionalidades Futuras:**

1. **Drag & Drop entre Preguntas:**
   - Mover opciones de una pregunta a otra
   - Útil para reorganizar grandes encuestas

2. **Orden Personalizado por Sección:**
   - Agrupar preguntas en secciones
   - Reordenar secciones completas

3. **Guardado Automático del Orden:**
   - Guardar orden sin necesidad de "Actualizar Encuesta"
   - Mediante AJAX en segundo plano

4. **Historial de Cambios:**
   - Ver cambios de orden anteriores
   - Revertir a ordenamientos previos

---

## ✅ Resumen Rápido

**¿Qué puedo hacer?**
- ✅ Reordenar preguntas arrastrándolas por el header
- ✅ Reordenar opciones arrastrándolas por el icono `⋮⋮`
- ✅ Funciona con preguntas/opciones nuevas y existentes
- ✅ Funciona con elementos que tienen votos
- ✅ Los cambios se guardan al hacer click en "Actualizar Encuesta"

**¿Qué NO afecta?**
- ❌ NO afecta los votos existentes
- ❌ NO elimina datos
- ❌ NO se guarda automáticamente (debes hacer click en guardar)

**¿Qué se actualiza automáticamente?**
- ✅ Números de pregunta (Pregunta 1, 2, 3...)
- ✅ Números de opción (1, 2, 3...)
- ✅ Nombres de inputs (para envío correcto)
- ✅ IDs y clases internas

---

**Implementado:** 24 de Octubre, 2025
**Versión:** 1.0 - Drag & Drop Completo
**Librería:** SortableJS v1.15.0

¡Sistema de reordenamiento intuitivo y profesional! 🎉

#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script para reordenar bloques en categoriasOficios.php
1. Mueve el bloque 'Nueva Categoría u Oficio' después del bloque 'Categorías y Oficios'
"""

import re

# Leer archivo
with open('views/admin/categoriasOficios.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Buscar inicio del bloque de formularios
form_start = content.find('<!-- BLOQUE: NUEVA CATEGORÍA U OFICIO -->')
print(f"Inicio formularios: {form_start}")

# Buscar fin del bloque de formularios (antes del bloque de categorías)
categories_start = content.find('<!-- BLOQUE: CATEGORÍAS Y OFICIOS -->', form_start)
print(f"Inicio categorías: {categories_start}")

# Extraer el bloque de formularios (incluyendo el salto de línea anterior)
forms_block = content[form_start:categories_start]
print(f"Longitud bloque formularios: {len(forms_block)}")

# Eliminar el bloque de formularios de su posición original
content_without_forms = content[:form_start] + content[categories_start:]

# Buscar fin del bloque de categorías
# Buscar "</section>" después del bloque de categorías seguido de "<script>"
pattern = r'(</section>\s+<script>)'
match = re.search(pattern, content_without_forms[content_without_forms.find('<!-- BLOQUE: CATEGORÍAS Y OFICIOS -->'):])

if match:
    # Posición absoluta en el contenido sin formularios
    insertion_point = content_without_forms.find('<!-- BLOQUE: CATEGORÍAS Y OFICIOS -->') + match.start()
    print(f"Punto de inserción: {insertion_point}")
    
    # Insertar el bloque de formularios
    new_content = content_without_forms[:insertion_point] + "\n" + forms_block + content_without_forms[insertion_point:]
    
    # Guardar archivo
    with open('views/admin/categoriasOficios.php', 'w', encoding='utf-8') as f:
        f.write(new_content)
    
    print("✅ Archivo actualizado exitosamente!")
else:
    print("❌ No se encontró el punto de inserción")

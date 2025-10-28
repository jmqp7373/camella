#!/usr/bin/env python3
"""
Script para extraer todos los íconos de los optgroups y generar array JavaScript
"""

icons_data = """
🧹 Escoba - Limpieza|fa-solid fa-broom
🎨 Spray - Limpieza profunda|fa-solid fa-spray-can
🧴 Jabón - Productos limpieza|fa-solid fa-pump-soap
🪣 Balde - Limpieza|fa-solid fa-bucket
✨ Desinfección - Higiene|fa-solid fa-hand-sparkles
💨 Ventilación - Aire limpio|fa-solid fa-wind
🚽 Sanitario - Limpieza sanitaria|fa-solid fa-toilet
🗑️ Contenedor - Recolección basuras|fa-solid fa-dumpster
🧽 Esponja - Limpieza|fa-solid fa-sponge
🔨 Martillo - Construcción|fa-solid fa-hammer
⛑️ Casco - Obra|fa-solid fa-hard-hat
🏢 Edificio - Construcción|fa-solid fa-building
🧱 Llana - Albañilería|fa-solid fa-trowel
📐 Escuadra - Medición|fa-solid fa-ruler-combined
📏 Regla - Medición|fa-solid fa-ruler
📏 Nivel - Nivelación|fa-solid fa-level
⛏️ Excavación - Movimiento tierras|fa-solid fa-person-digging
🧱 Mampostería - Obra|fa-solid fa-trowel-bricks
🏭 Bodega - Almacén|fa-solid fa-warehouse
🏭 Industrial - Planta|fa-solid fa-industry
🏙️ Urbano - Desarrollo|fa-solid fa-city
🔧 Llave - Mecánica|fa-solid fa-wrench
🪛 Destornillador - Reparaciones|fa-solid fa-screwdriver
🧰 Caja herramientas - Multiservicios|fa-solid fa-toolbox
🛠️ Herramientas - Mantenimiento|fa-solid fa-tools
⚙️ Engranaje - Mecánica|fa-solid fa-gear
⚙️ Engranajes - Mantenimiento|fa-solid fa-gears
🔧 Herramientas - Reparación|fa-solid fa-screwdriver-wrench
🛢️ Aceite - Lubricación|fa-solid fa-oil-can
🧰 Kit reparación - Emergencia|fa-solid fa-kit-medical
📋 Mantenimiento - Contrato|fa-solid fa-file-contract
🖌️ Pincel - Pintura|fa-solid fa-paintbrush
🎨 Rodillo - Pintura paredes|fa-solid fa-paint-roller
🎨 Paleta - Decoración|fa-solid fa-palette
💧 Bote pintura - Pintura|fa-solid fa-fill-drip
🖌️ Brocha - Pintura|fa-solid fa-brush
📏 Cinta - Pintura|fa-solid fa-tape
〰️ Diseño - Decoración|fa-solid fa-bezier-curve
🔌 Enchufe - Electricidad|fa-solid fa-plug
⚡ Rayo - Electricidad|fa-solid fa-bolt
💡 Bombilla - Iluminación|fa-solid fa-lightbulb
🔋 Batería - Energía|fa-solid fa-battery-full
⚡ Instalación eléctrica|fa-solid fa-plug-circle-bolt
☀️ Panel solar - Energía|fa-solid fa-solar-panel
⚡ Alta tensión - Electricidad|fa-solid fa-bolt-lightning
📡 Torre - Telecomunicaciones|fa-solid fa-tower-cell
🚡 Cableado - Instalaciones|fa-solid fa-cable-car
🚰 Grifo - Plomería|fa-solid fa-faucet
🚿 Ducha - Instalación|fa-solid fa-shower
🚰 Lavamanos - Instalación|fa-solid fa-sink
💧 Gota - Agua|fa-solid fa-droplet
🚰 Tubería - Instalación|fa-solid fa-pipe
💧 Fugas - Reparación|fa-solid fa-faucet-drip
🌊 Agua - Fontanería|fa-solid fa-water
🧻 Sanitarios - Baño|fa-solid fa-toilet-paper
💨 Bomba - Agua|fa-solid fa-pump
🪚 Sierra - Carpintería|fa-solid fa-saw
✏️ Lápiz - Carpintería|fa-solid fa-pencil
🌳 Madera - Carpintería|fa-solid fa-tree
🪛 Destornillador - Ensamble|fa-solid fa-screwdriver
🪑 Mesa - Muebles|fa-solid fa-table
🚪 Puerta - Carpintería|fa-solid fa-door-open
🪜 Escaleras - Carpintería|fa-solid fa-stairs
📦 Muebles - Ensamble|fa-solid fa-cubes
🛋️ Sofá - Muebles|fa-solid fa-couch
🪑 Silla - Muebles|fa-solid fa-chair
🛏️ Cama - Muebles|fa-solid fa-bed
🪟 Ventana - Vidrios|fa-solid fa-window-maximize
🏠 Casa - Hogar|fa-solid fa-house
🏠 Hogar - Residencial|fa-solid fa-home
🍳 Cocina - Muebles|fa-solid fa-kitchen-set
📺 TV - Entretenimiento|fa-solid fa-tv
💡 Lámpara - Iluminación|fa-solid fa-lamp
🛋️ Love seat - Muebles|fa-solid fa-loveseat
🚚 Camión - Mudanzas|fa-solid fa-truck
🚐 Van - Transporte|fa-solid fa-van-shuttle
🚗 Auto - Transporte|fa-solid fa-car
🏍️ Moto - Mensajería|fa-solid fa-motorcycle
🚲 Bicicleta - Domicilios|fa-solid fa-bicycle
📦 Caja - Empaque|fa-solid fa-box
📦 Cajas - Mudanzas|fa-solid fa-boxes-stacked
🚚 Mudanzas - Transporte|fa-solid fa-truck-moving
🚚 Envío rápido - Express|fa-solid fa-truck-fast
📦 Pallet - Carga|fa-solid fa-pallet
📦 Envío - Logística|fa-solid fa-shipping-fast
🌳 Árbol - Jardinería|fa-solid fa-tree
🌱 Planta - Siembra|fa-solid fa-seedling
🍃 Hoja - Jardinería|fa-solid fa-leaf
✂️ Tijeras - Poda|fa-solid fa-scissors
🌸 Flor - Jardinería|fa-solid fa-flower
🍀 Trébol - Jardín|fa-solid fa-clover
🌱 Riego - Plantas|fa-solid fa-sun-plant-wilt
🧱 Pala - Jardinería|fa-solid fa-trowel
⛰️ Paisajismo - Terreno|fa-solid fa-mountain
🌿 Césped - Jardín|fa-solid fa-grass
🍳 Cocina - Gastronomía|fa-solid fa-kitchen-set
🍴 Cubiertos - Restaurante|fa-solid fa-utensils
🍕 Pizza - Comida|fa-solid fa-pizza-slice
🍔 Hamburguesa - Fast food|fa-solid fa-burger
🎂 Pastel - Repostería|fa-solid fa-cake-candles
☕ Café - Bebidas|fa-solid fa-mug-hot
🥂 Copas - Eventos|fa-solid fa-champagne-glasses
🍲 Bowl - Comida|fa-solid fa-bowl-food
🍪 Galleta - Panadería|fa-solid fa-cookie
🧀 Queso - Gastronomía|fa-solid fa-cheese
🥓 Bacon - Cocina|fa-solid fa-bacon
🍦 Helado - Postres|fa-solid fa-ice-cream
💻 Computadora - Informática|fa-solid fa-computer
💻 Laptop - Reparación|fa-solid fa-laptop
📱 Móvil - Tecnología|fa-solid fa-mobile
📶 WiFi - Internet|fa-solid fa-wifi
🌐 Red - Redes|fa-solid fa-network-wired
📷 Cámara - Fotografía|fa-solid fa-camera
📹 Video - Audiovisual|fa-solid fa-video
🖥️ Servidor - IT|fa-solid fa-server
💾 Chip - Hardware|fa-solid fa-microchip
⌨️ Teclado - Informática|fa-solid fa-keyboard
🖱️ Mouse - Periféricos|fa-solid fa-mouse
🎧 Audífonos - Audio|fa-solid fa-headset
✂️ Tijeras - Peluquería|fa-solid fa-scissors
✂️ Corte - Estilista|fa-solid fa-cut
💅 Spray - Belleza|fa-solid fa-spray-can-sparkles
✨ Manicure - Uñas|fa-solid fa-hand-sparkles
😊 Facial - Spa|fa-solid fa-face-smile
✨ Maquillaje - Belleza|fa-solid fa-wand-magic-sparkles
💎 Premium - Lujo|fa-solid fa-gem
👕 Camisa - Ropa|fa-solid fa-shirt
🧴 Detergente - Lavandería|fa-solid fa-jug-detergent
🧦 Calcetín - Ropa|fa-solid fa-sock
👕 Camiseta - Ropa|fa-solid fa-tshirt
🧤 Guante - Ropa|fa-solid fa-mitten
🦺 Chaleco - Ropa|fa-solid fa-vest
🔥 Plancha - Lavandería|fa-solid fa-iron
📏 Costura - Arreglos|fa-solid fa-tape
🐾 Huella - Mascotas|fa-solid fa-paw
🐕 Perro - Veterinaria|fa-solid fa-dog
🐈 Gato - Veterinaria|fa-solid fa-cat
🐟 Pez - Acuarios|fa-solid fa-fish
🦴 Hueso - Veterinaria|fa-solid fa-bone
🐴 Caballo - Veterinaria|fa-solid fa-horse
🕊️ Ave - Veterinaria|fa-solid fa-dove
🐕 Protección - Mascotas|fa-solid fa-shield-dog
🍚 Alimento - Mascotas|fa-solid fa-bowl-rice
❤️ Pulso - Salud|fa-solid fa-heart-pulse
💼 Médico - Emergencia|fa-solid fa-suitcase-medical
🩺 Estetoscopio - Consulta|fa-solid fa-stethoscope
👩‍⚕️ Enfermera - Cuidado|fa-solid fa-user-nurse
♿ Silla ruedas - Movilidad|fa-solid fa-wheelchair
💝 Cuidado - Asistencia|fa-solid fa-hand-holding-heart
🏥 Hospital - Salud|fa-solid fa-hospital
💊 Medicinas - Farmacia|fa-solid fa-pills
💼 Paramédico - Emergencia|fa-solid fa-briefcase-medical
🎓 Graduación - Educación|fa-solid fa-graduation-cap
📖 Libro - Enseñanza|fa-solid fa-book
👨‍🏫 Profesor - Clases|fa-solid fa-chalkboard-user
🖊️ Pluma - Escritura|fa-solid fa-pen
🏫 Escuela - Educación|fa-solid fa-school
🎓 Estudiante - Educación|fa-solid fa-user-graduate
📖 Lectura - Educación|fa-solid fa-book-open
🍎 Manzana - Educación|fa-solid fa-apple-whole
🛡️ Escudo - Seguridad|fa-solid fa-shield
🔒 Candado - Seguridad|fa-solid fa-lock
🔑 Llave - Cerrajería|fa-solid fa-key
📹 Cámara - Vigilancia|fa-solid fa-video
🛡️ Protección - Seguridad|fa-solid fa-shield-halved
👮 Guardia - Vigilancia|fa-solid fa-user-shield
🔔 Alarma - Seguridad|fa-solid fa-bell
🧯 Extintor - Seguridad|fa-solid fa-fire-extinguisher
🎵 Música - Eventos|fa-solid fa-music
🎤 Micrófono - Audio|fa-solid fa-microphone
🎁 Regalos - Eventos|fa-solid fa-gifts
🥂 Brindis - Fiesta|fa-solid fa-champagne-glasses
🎸 Guitarra - Música|fa-solid fa-guitar
🥁 Batería - Música|fa-solid fa-drum
📷 Fotografía - Eventos|fa-solid fa-camera-retro
✨ Decoración - Eventos|fa-solid fa-wand-magic-sparkles
💼 Maletín - Negocios|fa-solid fa-briefcase
🧮 Calculadora - Finanzas|fa-solid fa-calculator
🖨️ Impresora - Oficina|fa-solid fa-print
📈 Gráfica - Análisis|fa-solid fa-chart-line
💵 Dinero - Finanzas|fa-solid fa-money-bill
📋 Clipboard - Administración|fa-solid fa-clipboard
📁 Carpeta - Archivo|fa-solid fa-folder
📞 Teléfono - Atención|fa-solid fa-phone
🔥 Fuego - Popular|fa-solid fa-fire
⭐ Estrella - Destacado|fa-solid fa-star
✅ Check - Verificado|fa-solid fa-circle-check
👥 Usuarios - Comunidad|fa-solid fa-users
🤝 Acuerdo - Servicios|fa-solid fa-handshake
🏅 Medalla - Excelencia|fa-solid fa-medal
🏆 Trofeo - Premium|fa-solid fa-trophy
👑 Corona - VIP|fa-solid fa-crown
"""

# Procesar y generar array JavaScript
lines = [l.strip() for l in icons_data.strip().split('\n') if '|' in l]
seen = set()
js_items = []

for line in lines:
    label, icon_class = line.split('|')
    if icon_class not in seen:
        seen.add(icon_class)
        js_items.append(f"    {{ icon: '{icon_class}', label: '{label}' }}")

print("const iconosDisponibles = [")
print(",\n".join(js_items))
print("];")
print(f"\n// Total: {len(js_items)} íconos únicos")

# -*- coding: utf-8 -*-
import re

# Leer el archivo
with open('views/admin/categoriasOficios.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Nuevo array limpio con iconos válidos de Font Awesome 6
new_icons_array = """const iconosDisponibles = [
    // LIMPIEZA Y ASEO
    { icon: 'fa-solid fa-broom', label: 'broom - Escoba' },
    { icon: 'fa-solid fa-spray-can', label: 'spray-can - Spray' },
    { icon: 'fa-solid fa-pump-soap', label: 'pump-soap - Jabon' },
    { icon: 'fa-solid fa-hand-sparkles', label: 'hand-sparkles - Desinfeccion' },
    { icon: 'fa-solid fa-wind', label: 'wind - Ventilacion' },
    { icon: 'fa-solid fa-toilet', label: 'toilet - Sanitario' },
    { icon: 'fa-solid fa-dumpster', label: 'dumpster - Contenedor' },
    { icon: 'fa-solid fa-sponge', label: 'sponge - Esponja' },
    
    // CONSTRUCCION Y ALBANILERIA
    { icon: 'fa-solid fa-hammer', label: 'hammer - Martillo' },
    { icon: 'fa-solid fa-hard-hat', label: 'hard-hat - Casco' },
    { icon: 'fa-solid fa-building', label: 'building - Edificio' },
    { icon: 'fa-solid fa-trowel', label: 'trowel - Llana' },
    { icon: 'fa-solid fa-ruler-combined', label: 'ruler-combined - Escuadra' },
    { icon: 'fa-solid fa-ruler', label: 'ruler - Regla' },
    { icon: 'fa-solid fa-person-digging', label: 'person-digging - Excavacion' },
    { icon: 'fa-solid fa-trowel-bricks', label: 'trowel-bricks - Mamposteria' },
    { icon: 'fa-solid fa-warehouse', label: 'warehouse - Bodega' },
    { icon: 'fa-solid fa-industry', label: 'industry - Industrial' },
    { icon: 'fa-solid fa-city', label: 'city - Urbano' },
    
    // REPARACIONES Y MANTENIMIENTO
    { icon: 'fa-solid fa-wrench', label: 'wrench - Llave' },
    { icon: 'fa-solid fa-screwdriver', label: 'screwdriver - Destornillador' },
    { icon: 'fa-solid fa-toolbox', label: 'toolbox - Caja Herramientas' },
    { icon: 'fa-solid fa-gears', label: 'gears - Engranajes' },
    { icon: 'fa-solid fa-screwdriver-wrench', label: 'screwdriver-wrench - Herramientas' },
    { icon: 'fa-solid fa-oil-can', label: 'oil-can - Aceite' },
    
    // PINTURA Y DECORACION
    { icon: 'fa-solid fa-paintbrush', label: 'paintbrush - Pincel' },
    { icon: 'fa-solid fa-paint-roller', label: 'paint-roller - Rodillo' },
    { icon: 'fa-solid fa-palette', label: 'palette - Paleta' },
    { icon: 'fa-solid fa-fill-drip', label: 'fill-drip - Bote Pintura' },
    { icon: 'fa-solid fa-brush', label: 'brush - Brocha' },
    
    // ELECTRICIDAD
    { icon: 'fa-solid fa-plug', label: 'plug - Enchufe' },
    { icon: 'fa-solid fa-bolt', label: 'bolt - Rayo' },
    { icon: 'fa-solid fa-lightbulb', label: 'lightbulb - Bombilla' },
    { icon: 'fa-solid fa-battery-full', label: 'battery-full - Bateria' },
    { icon: 'fa-solid fa-plug-circle-bolt', label: 'plug-circle-bolt - Instalacion' },
    { icon: 'fa-solid fa-solar-panel', label: 'solar-panel - Panel Solar' },
    { icon: 'fa-solid fa-bolt-lightning', label: 'bolt-lightning - Alta Tension' },
    { icon: 'fa-solid fa-tower-cell', label: 'tower-cell - Torre' },
    
    // PLOMERIA
    { icon: 'fa-solid fa-faucet', label: 'faucet - Grifo' },
    { icon: 'fa-solid fa-shower', label: 'shower - Ducha' },
    { icon: 'fa-solid fa-sink', label: 'sink - Lavamanos' },
    { icon: 'fa-solid fa-droplet', label: 'droplet - Gota' },
    { icon: 'fa-solid fa-faucet-drip', label: 'faucet-drip - Fugas' },
    { icon: 'fa-solid fa-water', label: 'water - Agua' },
    { icon: 'fa-solid fa-toilet-paper', label: 'toilet-paper - Sanitarios' },
    
    // CARPINTERIA
    { icon: 'fa-solid fa-hammer', label: 'hammer - Martillo' },
    { icon: 'fa-solid fa-screwdriver', label: 'screwdriver - Destornillador' },
    { icon: 'fa-solid fa-ruler', label: 'ruler - Regla' },
    { icon: 'fa-solid fa-table', label: 'table - Mesa' },
    { icon: 'fa-solid fa-door-open', label: 'door-open - Puerta' },
    { icon: 'fa-solid fa-stairs', label: 'stairs - Escaleras' },
    
    // MUEBLES Y HOGAR
    { icon: 'fa-solid fa-couch', label: 'couch - Sofa' },
    { icon: 'fa-solid fa-chair', label: 'chair - Silla' },
    { icon: 'fa-solid fa-bed', label: 'bed - Cama' },
    { icon: 'fa-solid fa-house', label: 'house - Casa' },
    { icon: 'fa-solid fa-kitchen-set', label: 'kitchen-set - Cocina' },
    { icon: 'fa-solid fa-tv', label: 'tv - Televisor' },
    
    // TRANSPORTE Y MUDANZAS
    { icon: 'fa-solid fa-truck', label: 'truck - Camion' },
    { icon: 'fa-solid fa-van-shuttle', label: 'van-shuttle - Van' },
    { icon: 'fa-solid fa-car', label: 'car - Auto' },
    { icon: 'fa-solid fa-motorcycle', label: 'motorcycle - Moto' },
    { icon: 'fa-solid fa-bicycle', label: 'bicycle - Bicicleta' },
    { icon: 'fa-solid fa-box', label: 'box - Caja' },
    { icon: 'fa-solid fa-boxes-stacked', label: 'boxes-stacked - Cajas' },
    { icon: 'fa-solid fa-truck-moving', label: 'truck-moving - Mudanzas' },
    { icon: 'fa-solid fa-truck-fast', label: 'truck-fast - Envio Rapido' },
    
    // JARDINERIA Y PAISAJISMO
    { icon: 'fa-solid fa-tree', label: 'tree - Arbol' },
    { icon: 'fa-solid fa-seedling', label: 'seedling - Planta' },
    { icon: 'fa-solid fa-leaf', label: 'leaf - Hoja' },
    { icon: 'fa-solid fa-scissors', label: 'scissors - Tijeras' },
    { icon: 'fa-solid fa-mountain', label: 'mountain - Paisajismo' },
    
    // GASTRONOMIA Y COCINA
    { icon: 'fa-solid fa-utensils', label: 'utensils - Cubiertos' },
    { icon: 'fa-solid fa-pizza-slice', label: 'pizza-slice - Pizza' },
    { icon: 'fa-solid fa-burger', label: 'burger - Hamburguesa' },
    { icon: 'fa-solid fa-mug-hot', label: 'mug-hot - Cafe' },
    { icon: 'fa-solid fa-bowl-food', label: 'bowl-food - Bowl' },
    { icon: 'fa-solid fa-cookie', label: 'cookie - Galleta' },
    { icon: 'fa-solid fa-cheese', label: 'cheese - Queso' },
    { icon: 'fa-solid fa-bacon', label: 'bacon - Bacon' },
    { icon: 'fa-solid fa-ice-cream', label: 'ice-cream - Helado' },
    
    // TECNOLOGIA
    { icon: 'fa-solid fa-computer', label: 'computer - Computadora' },
    { icon: 'fa-solid fa-laptop', label: 'laptop - Laptop' },
    { icon: 'fa-solid fa-mobile', label: 'mobile - Movil' },
    { icon: 'fa-solid fa-wifi', label: 'wifi - WiFi' },
    { icon: 'fa-solid fa-network-wired', label: 'network-wired - Red' },
    { icon: 'fa-solid fa-camera', label: 'camera - Camara' },
    { icon: 'fa-solid fa-video', label: 'video - Video' },
    { icon: 'fa-solid fa-server', label: 'server - Servidor' },
    { icon: 'fa-solid fa-microchip', label: 'microchip - Chip' },
    { icon: 'fa-solid fa-keyboard', label: 'keyboard - Teclado' },
    { icon: 'fa-solid fa-headphones', label: 'headphones - Audifonos' },
    
    // BELLEZA Y CUIDADO PERSONAL
    { icon: 'fa-solid fa-scissors', label: 'scissors - Corte' },
    { icon: 'fa-solid fa-spray-can-sparkles', label: 'spray-can-sparkles - Spray' },
    { icon: 'fa-solid fa-face-smile', label: 'face-smile - Facial' },
    { icon: 'fa-solid fa-wand-magic-sparkles', label: 'wand-magic-sparkles - Maquillaje' },
    { icon: 'fa-solid fa-gem', label: 'gem - Premium' },
    
    // ROPA Y LAVANDERIA
    { icon: 'fa-solid fa-shirt', label: 'shirt - Camisa' },
    { icon: 'fa-solid fa-jug-detergent', label: 'jug-detergent - Detergente' },
    { icon: 'fa-solid fa-vest', label: 'vest - Chaleco' },
    
    // MASCOTAS
    { icon: 'fa-solid fa-paw', label: 'paw - Huella' },
    { icon: 'fa-solid fa-dog', label: 'dog - Perro' },
    { icon: 'fa-solid fa-cat', label: 'cat - Gato' },
    { icon: 'fa-solid fa-fish', label: 'fish - Pez' },
    { icon: 'fa-solid fa-bone', label: 'bone - Hueso' },
    { icon: 'fa-solid fa-horse', label: 'horse - Caballo' },
    { icon: 'fa-solid fa-dove', label: 'dove - Ave' },
    { icon: 'fa-solid fa-shield-dog', label: 'shield-dog - Proteccion' },
    
    // SALUD Y CUIDADO
    { icon: 'fa-solid fa-heart-pulse', label: 'heart-pulse - Pulso' },
    { icon: 'fa-solid fa-suitcase-medical', label: 'suitcase-medical - Medico' },
    { icon: 'fa-solid fa-stethoscope', label: 'stethoscope - Estetoscopio' },
    { icon: 'fa-solid fa-user-nurse', label: 'user-nurse - Enfermera' },
    { icon: 'fa-solid fa-wheelchair', label: 'wheelchair - Silla Ruedas' },
    { icon: 'fa-solid fa-hand-holding-heart', label: 'hand-holding-heart - Cuidado' },
    { icon: 'fa-solid fa-hospital', label: 'hospital - Hospital' },
    { icon: 'fa-solid fa-pills', label: 'pills - Medicinas' },
    
    // EDUCACION
    { icon: 'fa-solid fa-graduation-cap', label: 'graduation-cap - Graduacion' },
    { icon: 'fa-solid fa-book', label: 'book - Libro' },
    { icon: 'fa-solid fa-chalkboard-user', label: 'chalkboard-user - Profesor' },
    { icon: 'fa-solid fa-pen', label: 'pen - Pluma' },
    { icon: 'fa-solid fa-school', label: 'school - Escuela' },
    { icon: 'fa-solid fa-user-graduate', label: 'user-graduate - Estudiante' },
    { icon: 'fa-solid fa-book-open', label: 'book-open - Lectura' },
    { icon: 'fa-solid fa-apple-whole', label: 'apple-whole - Manzana' },
    
    // SEGURIDAD
    { icon: 'fa-solid fa-shield', label: 'shield - Escudo' },
    { icon: 'fa-solid fa-lock', label: 'lock - Candado' },
    { icon: 'fa-solid fa-key', label: 'key - Llave' },
    { icon: 'fa-solid fa-shield-halved', label: 'shield-halved - Proteccion' },
    { icon: 'fa-solid fa-user-shield', label: 'user-shield - Guardia' },
    { icon: 'fa-solid fa-bell', label: 'bell - Alarma' },
    { icon: 'fa-solid fa-fire-extinguisher', label: 'fire-extinguisher - Extintor' },
    
    // EVENTOS Y ENTRETENIMIENTO
    { icon: 'fa-solid fa-music', label: 'music - Musica' },
    { icon: 'fa-solid fa-microphone', label: 'microphone - Microfono' },
    { icon: 'fa-solid fa-gifts', label: 'gifts - Regalos' },
    { icon: 'fa-solid fa-cake-candles', label: 'cake-candles - Pastel' },
    { icon: 'fa-solid fa-champagne-glasses', label: 'champagne-glasses - Brindis' },
    { icon: 'fa-solid fa-guitar', label: 'guitar - Guitarra' },
    { icon: 'fa-solid fa-drum', label: 'drum - Bateria' },
    { icon: 'fa-solid fa-camera-retro', label: 'camera-retro - Fotografia' },
    
    // OFICINA Y NEGOCIOS
    { icon: 'fa-solid fa-briefcase', label: 'briefcase - Maletin' },
    { icon: 'fa-solid fa-calculator', label: 'calculator - Calculadora' },
    { icon: 'fa-solid fa-print', label: 'print - Impresora' },
    { icon: 'fa-solid fa-chart-line', label: 'chart-line - Grafica' },
    { icon: 'fa-solid fa-money-bill', label: 'money-bill - Dinero' },
    { icon: 'fa-solid fa-clipboard', label: 'clipboard - Clipboard' },
    { icon: 'fa-solid fa-folder', label: 'folder - Carpeta' },
    { icon: 'fa-solid fa-phone', label: 'phone - Telefono' },
    
    // VARIOS
    { icon: 'fa-solid fa-fire', label: 'fire - Fuego' },
    { icon: 'fa-solid fa-star', label: 'star - Estrella' },
    { icon: 'fa-solid fa-circle-check', label: 'circle-check - Check' },
    { icon: 'fa-solid fa-users', label: 'users - Usuarios' },
    { icon: 'fa-solid fa-handshake', label: 'handshake - Acuerdo' },
    { icon: 'fa-solid fa-medal', label: 'medal - Medalla' },
    { icon: 'fa-solid fa-trophy', label: 'trophy - Trofeo' },
    { icon: 'fa-solid fa-crown', label: 'crown - Corona' },
    { icon: 'fa-solid fa-user-tie', label: 'user-tie - Profesional' }
];"""

# Buscar y reemplazar el array completo
pattern = r'const iconosDisponibles = \[[\s\S]*?\];'
content = re.sub(pattern, new_icons_array, content, count=1)

# Guardar el archivo
with open('views/admin/categoriasOficios.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("Archivo actualizado correctamente")

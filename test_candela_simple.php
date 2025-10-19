<?php
/**
 * Test simple para diagnosticar por qué las candelas no funcionan
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Conexión directa sin dependencias
try {
    $pdo = new PDO('mysql:host=localhost;dbname=camella_db;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'>";
echo "<title>Test Candelas</title>";
echo "<style>
body { font-family: Arial; padding: 20px; background: #f5f5f5; }
.oficio { 
    background: white; 
    padding: 15px; 
    margin: 10px 0; 
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 15px;
}
.candela-toggle { 
    cursor: pointer; 
    width: 24px; 
    height: 24px;
    transition: all 0.3s;
}
.candela-toggle:hover { 
    transform: scale(1.2);
}
.log {
    background: #222;
    color: #0f0;
    padding: 15px;
    margin: 20px 0;
    border-radius: 8px;
    font-family: monospace;
    font-size: 12px;
    max-height: 300px;
    overflow-y: auto;
}
.log div { margin: 3px 0; }
.error { color: #f00; }
.success { color: #0f0; }
.info { color: #0ff; }
</style>
</head><body>";

echo "<h1>🔥 Test de Candelas - Diagnóstico</h1>";

// 1. Verificar que la tabla oficios existe y tiene datos
echo "<h2>1. Verificación de Base de Datos</h2>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM oficios WHERE activo = 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>✅ Tabla 'oficios' existe con <strong>{$result['total']}</strong> oficios activos</p>";
    
    // Obtener algunos oficios
    $stmt = $pdo->query("SELECT id, titulo, popular, categoria_id FROM oficios WHERE activo = 1 LIMIT 5");
    $oficios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>2. Oficios de Prueba</h2>";
    
    foreach ($oficios as $oficio) {
        $imagenSrc = $oficio['popular'] == 1 
            ? 'assets/images/app/candela1.png' 
            : 'assets/images/app/candela0.png';
        
        echo "<div class='oficio'>";
        echo "<img src='$imagenSrc' class='candela-toggle' data-id='{$oficio['id']}' data-popular='{$oficio['popular']}' alt='candela'>";
        echo "<span><strong>{$oficio['titulo']}</strong> (ID: {$oficio['id']}, Popular: {$oficio['popular']})</span>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h2>3. Log de Eventos</h2>";
echo "<div class='log' id='log'></div>";

?>

<script>
const log = document.getElementById('log');

function addLog(message, type = 'info') {
    const div = document.createElement('div');
    div.className = type;
    div.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;
    log.appendChild(div);
    log.scrollTop = log.scrollHeight;
}

addLog('🔥 Sistema iniciado', 'success');

// Interceptar console.log
const originalLog = console.log;
console.log = function(...args) {
    originalLog.apply(console, args);
    addLog(args.join(' '), 'info');
};

const originalError = console.error;
console.error = function(...args) {
    originalError.apply(console, args);
    addLog('ERROR: ' + args.join(' '), 'error');
};

// Verificar imágenes
addLog('Verificando existencia de imágenes...');
const img1 = new Image();
img1.onload = () => addLog('✅ candela1.png cargada correctamente', 'success');
img1.onerror = () => addLog('❌ candela1.png NO ENCONTRADA', 'error');
img1.src = 'assets/images/app/candela1.png';

const img0 = new Image();
img0.onload = () => addLog('✅ candela0.png cargada correctamente', 'success');
img0.onerror = () => addLog('❌ candela0.png NO ENCONTRADA', 'error');
img0.src = 'assets/images/app/candela0.png';

// Añadir eventos a las candelas
document.addEventListener('DOMContentLoaded', function() {
    const candelas = document.querySelectorAll('.candela-toggle');
    addLog(`Candelas encontradas: ${candelas.length}`, 'success');
    
    candelas.forEach(candela => {
        addLog(`Agregando evento a oficio ID: ${candela.dataset.id}`);
        
        candela.addEventListener('click', async function(e) {
            e.preventDefault();
            const id = this.dataset.id;
            const popularActual = this.dataset.popular;
            
            addLog(`🔥 CLIC en oficio ID: ${id}, Estado actual: ${popularActual}`, 'info');
            
            // Construir URL
            const url = `controllers/OficioController.php?action=togglePopular&id=${id}`;
            addLog(`📡 Llamando a: ${url}`, 'info');
            
            try {
                const response = await fetch(url);
                addLog(`📥 Response status: ${response.status}`, response.ok ? 'success' : 'error');
                
                const text = await response.text();
                addLog(`📦 Response raw: ${text.substring(0, 200)}`, 'info');
                
                let data;
                try {
                    data = JSON.parse(text);
                    addLog(`✅ JSON parseado: ${JSON.stringify(data)}`, 'success');
                } catch (parseError) {
                    addLog(`❌ Error parseando JSON: ${parseError.message}`, 'error');
                    addLog(`Respuesta recibida: ${text}`, 'error');
                    return;
                }
                
                if (data.success) {
                    addLog(`✅ Toggle exitoso! Nuevo estado: ${data.newState}`, 'success');
                    
                    // Actualizar imagen
                    const nuevaImagen = data.newState == 1 
                        ? 'assets/images/app/candela1.png'
                        : 'assets/images/app/candela0.png';
                    
                    this.src = nuevaImagen;
                    this.dataset.popular = data.newState;
                    this.style.opacity = data.newState == 1 ? '1' : '0.5';
                    
                    addLog(`🎨 Imagen actualizada a: ${nuevaImagen}`, 'success');
                } else {
                    addLog(`❌ Error en respuesta: ${data.message}`, 'error');
                }
                
            } catch (error) {
                addLog(`❌ Error en fetch: ${error.message}`, 'error');
                addLog(`Stack: ${error.stack}`, 'error');
            }
        });
    });
});
</script>

</body></html>

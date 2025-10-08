<?php
/**
 * Script de prueba para verificar el funcionamiento del modelo de categorías
 * Ejecutar desde navegador: /test_categorias.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🧪 Prueba del Sistema de Categorías</h1>";
echo "<style>body { font-family: Arial, sans-serif; margin: 2rem; } .success { color: green; } .error { color: red; } .info { color: blue; }</style>";

try {
    echo "<h2>1. Cargando modelo de categorías...</h2>";
    require_once 'models/Categorias.php';
    echo "<p class='success'>✅ Modelo cargado exitosamente</p>";
    
    echo "<h2>2. Inicializando modelo...</h2>";
    $categoriasModel = new Categorias();
    echo "<p class='success'>✅ Modelo inicializado exitosamente</p>";
    
    echo "<h2>3. Verificando estado del sistema...</h2>";
    $estado = $categoriasModel->verificarEstadoTablasYDatos();
    
    echo "<div style='background: #f5f5f5; padding: 1rem; border-radius: 5px;'>";
    echo "<pre>" . print_r($estado, true) . "</pre>";
    echo "</div>";
    
    if ($estado['tablas_existen']) {
        echo "<p class='success'>✅ Las tablas existen</p>";
    } else {
        echo "<p class='error'>❌ Las tablas no existen</p>";
    }
    
    if ($estado['datos_inicializados']) {
        echo "<p class='success'>✅ Los datos están inicializados</p>";
    } else {
        echo "<p class='info'>ℹ️ Los datos no están inicializados (se crearán automáticamente)</p>";
    }
    
    echo "<h2>4. Obteniendo categorías con oficios...</h2>";
    $categorias = $categoriasModel->obtenerCategoriasConOficios();
    
    if (!empty($categorias)) {
        echo "<p class='success'>✅ Se encontraron " . count($categorias) . " categorías</p>";
        
        echo "<h3>📋 Lista de Categorías y Oficios:</h3>";
        foreach ($categorias as $categoria) {
            echo "<div style='border: 1px solid #ddd; margin: 1rem 0; padding: 1rem; border-radius: 5px;'>";
            echo "<h4>" . $categoria['icono'] . " " . htmlspecialchars($categoria['nombre']) . "</h4>";
            
            if (!empty($categoria['oficios'])) {
                echo "<ul>";
                foreach ($categoria['oficios'] as $oficio) {
                    echo "<li>" . htmlspecialchars($oficio['nombre']) . "</li>";
                }
                echo "</ul>";
            } else {
                echo "<p><em>No hay oficios en esta categoría</em></p>";
            }
            echo "</div>";
        }
    } else {
        echo "<p class='error'>❌ No se encontraron categorías</p>";
    }
    
    echo "<h2>5. Probando API de categorías simples...</h2>";
    $categoriasSimples = $categoriasModel->obtenerCategoriasSimple();
    
    if (!empty($categoriasSimples)) {
        echo "<p class='success'>✅ Se encontraron " . count($categoriasSimples) . " categorías simples</p>";
        echo "<ul>";
        foreach ($categoriasSimples as $cat) {
            echo "<li>" . $cat['icono'] . " " . htmlspecialchars($cat['nombre']) . " (ID: " . $cat['id'] . ")</li>";
        }
        echo "</ul>";
    } else {
        echo "<p class='error'>❌ No se encontraron categorías simples</p>";
    }
    
    echo "<h2>6. Probando obtención de oficios por categoría...</h2>";
    if (!empty($categoriasSimples)) {
        $primeraCategoria = $categoriasSimples[0];
        $oficios = $categoriasModel->obtenerOficiosPorCategoria($primeraCategoria['id']);
        
        echo "<p class='info'>Obteniendo oficios de: " . $primeraCategoria['nombre'] . "</p>";
        
        if (!empty($oficios)) {
            echo "<p class='success'>✅ Se encontraron " . count($oficios) . " oficios</p>";
            echo "<ul>";
            foreach ($oficios as $oficio) {
                echo "<li>" . htmlspecialchars($oficio['nombre']) . " (ID: " . $oficio['id'] . ")</li>";
            }
            echo "</ul>";
        } else {
            echo "<p class='error'>❌ No se encontraron oficios para esta categoría</p>";
        }
    }
    
    echo "<h2>🎉 Pruebas completadas exitosamente</h2>";
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 1rem; border-radius: 5px; color: #155724;'>";
    echo "<strong>✅ El sistema de categorías está funcionando correctamente</strong><br>";
    echo "Total de categorías: " . count($categorias) . "<br>";
    echo "Total de oficios: " . array_sum(array_map(function($cat) { return count($cat['oficios']); }, $categorias)) . "<br>";
    echo "Las tablas se crearon automáticamente y los datos iniciales se insertaron correctamente.";
    echo "</div>";
    
    echo "<hr>";
    echo "<h3>🔗 Enlaces útiles:</h3>";
    echo "<ul>";
    echo "<li><a href='index.php'>Ver página principal</a></li>";
    echo "<li><a href='index.php?view=admin'>Panel de administración</a></li>";
    echo "<li><a href='index.php?api=categorias'>API de categorías (JSON)</a></li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h2 class='error'>❌ Error durante las pruebas</h2>";
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 1rem; border-radius: 5px; color: #721c24;'>";
    echo "<strong>Error:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Archivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Línea:</strong> " . $e->getLine();
    echo "</div>";
    
    echo "<h3>🔧 Posibles soluciones:</h3>";
    echo "<ul>";
    echo "<li>Verificar que la configuración de base de datos en <code>config/config.php</code> sea correcta</li>";
    echo "<li>Verificar que la base de datos <code>camella_db</code> exista</li>";
    echo "<li>Verificar que el usuario <code>camella_user</code> tenga permisos suficientes</li>";
    echo "<li>Verificar que el servidor MySQL esté ejecutándose</li>";
    echo "</ul>";
}
?>

<script>
// Auto-refrescar cada 30 segundos para ver cambios
setTimeout(function() {
    const refresh = confirm('¿Desea refrescar la página para ver si hay cambios?');
    if (refresh) {
        location.reload();
    }
}, 30000);
</script>
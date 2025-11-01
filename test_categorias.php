<?php
/**
 * Script de prueba para verificar carga de categorías
 * ELIMINAR DESPUÉS DE VERIFICAR
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Test Categorías</title></head><body>";
echo "<h1>Test de Carga de Categorías</h1>";

try {
    echo "<p>1. Cargando configuración...</p>";
    require_once __DIR__ . '/config/database.php';
    echo "<p style='color:green'>✓ Config cargada</p>";
    
    echo "<p>2. Obteniendo conexión PDO...</p>";
    $pdo = getPDO();
    echo "<p style='color:green'>✓ PDO conectado</p>";
    
    echo "<p>3. Cargando modelo Categorias...</p>";
    require_once __DIR__ . '/models/Categorias.php';
    echo "<p style='color:green'>✓ Modelo cargado</p>";
    
    echo "<p>4. Creando instancia...</p>";
    $categoriasModel = new Categorias();
    echo "<p style='color:green'>✓ Instancia creada</p>";
    
    echo "<p>5. Obteniendo categorías...</p>";
    $categorias = $categoriasModel->obtenerCategoriasConOficios();
    echo "<p style='color:green'>✓ Categorías obtenidas: " . count($categorias) . "</p>";
    
    if (!empty($categorias)) {
        echo "<h2>Categorías encontradas:</h2><ul>";
        foreach ($categorias as $cat) {
            echo "<li><strong>{$cat['nombre']}</strong> - {$cat['total_oficios']} oficios - {$cat['total_anuncios']} anuncios</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color:orange'>⚠ No hay categorías en la base de datos</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "</body></html>";
?>

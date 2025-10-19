<?php
$pdo = new PDO('mysql:host=localhost;dbname=camella_db;charset=utf8mb4', 'camella_user', 'Reylondres7373');

echo "=== TABLAS EN LA BASE DE DATOS ===\n\n";
$tables = $pdo->query('SHOW TABLES');
while($t = $tables->fetch(PDO::FETCH_NUM)) {
    echo "- " . $t[0] . "\n";
}

// Verificar si existe servicios
echo "\n=== VERIFICANDO TABLA servicios ===\n";
try {
    $count = $pdo->query("SELECT COUNT(*) FROM servicios");
    echo "Total registros en servicios: " . $count->fetchColumn() . "\n";
    
    $desc = $pdo->query("DESCRIBE servicios");
    echo "\nColumnas de servicios:\n";
    while($col = $desc->fetch(PDO::FETCH_ASSOC)) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

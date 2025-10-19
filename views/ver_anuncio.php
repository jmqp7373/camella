<?php
/**
 * Redirect a la vista unificada de anuncios
 * Mantiene compatibilidad con enlaces antiguos
 */
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
header("Location: /views/bloques/publicar.php?modo=ver&id={$id}");
exit;


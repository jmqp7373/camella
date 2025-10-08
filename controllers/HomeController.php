<?php
/**
 * Controlador del Home
 * Gestiona la página principal y la visualización de categorías
 */

require_once 'models/Categorias.php';

class HomeController {
    private $categoriasModel;
    
    public function __construct() {
        $this->categoriasModel = new Categorias();
    }
    
    /**
     * Página principal con categorías dinámicas
     */
    public function index() {
        // Asegurar que las tablas y datos estén inicializados
        $this->categoriasModel->inicializarTablasYDatos();
        
        // Obtener categorías con oficios desde la base de datos
        $categorias = $this->categoriasModel->obtenerCategoriasConOficios();
        
        // Variables para la vista
        $pageTitle = "Inicio";
        $data = [
            'categorias' => $categorias
        ];
        
        include 'views/home.php';
    }
    
    /**
     * API para obtener categorías (para JavaScript)
     */
    public function apiCategorias() {
        header('Content-Type: application/json');
        
        try {
            $categorias = $this->categoriasModel->obtenerCategoriasConOficios();
            echo json_encode([
                'exito' => true,
                'datos' => $categorias
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'exito' => false,
                'mensaje' => 'Error obteniendo categorías: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Buscar ofertas por categoría
     */
    public function buscarPorCategoria() {
        $categoria_id = $_GET['categoria'] ?? 0;
        $oficio_id = $_GET['oficio'] ?? 0;
        
        // Obtener información de la categoría/oficio seleccionado
        $categorias = $this->categoriasModel->obtenerCategoriasConOficios();
        
        $categoria_seleccionada = null;
        $oficio_seleccionado = null;
        
        foreach ($categorias as $categoria) {
            if ($categoria['id'] == $categoria_id) {
                $categoria_seleccionada = $categoria;
                
                if ($oficio_id) {
                    foreach ($categoria['oficios'] as $oficio) {
                        if ($oficio['id'] == $oficio_id) {
                            $oficio_seleccionado = $oficio;
                            break;
                        }
                    }
                }
                break;
            }
        }
        
        // Variables para la vista
        $pageTitle = "Búsqueda de Ofertas";
        $data = [
            'categoria_seleccionada' => $categoria_seleccionada,
            'oficio_seleccionado' => $oficio_seleccionado,
            'categoria_id' => $categoria_id,
            'oficio_id' => $oficio_id
        ];
        
        include 'views/busqueda.php';
    }
}
?>
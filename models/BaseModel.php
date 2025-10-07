<?php
/**
 * Modelo Base para Camella.com.co
 * Contiene funciones comunes para todos los modelos
 */

class BaseModel {
    
    protected $db;
    protected $table;
    
    public function __construct() {
        // Configuración de base de datos (futuro)
        // $this->db = new PDO(...);
    }
    
    /**
     * Obtener todos los registros
     */
    public function getAll() {
        // Implementación futura con base de datos
        return [];
    }
    
    /**
     * Obtener registro por ID
     */
    public function getById($id) {
        // Implementación futura con base de datos
        return null;
    }
    
    /**
     * Crear nuevo registro
     */
    public function create($data) {
        // Implementación futura con base de datos
        return false;
    }
    
    /**
     * Actualizar registro
     */
    public function update($id, $data) {
        // Implementación futura con base de datos
        return false;
    }
    
    /**
     * Eliminar registro
     */
    public function delete($id) {
        // Implementación futura con base de datos
        return false;
    }
    
    /**
     * Validar datos según reglas
     */
    protected function validate($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = isset($data[$field]) ? $data[$field] : null;
            
            // Verificar si es requerido
            if (isset($rule['required']) && $rule['required'] && empty($value)) {
                $errors[$field] = "El campo {$field} es requerido";
                continue;
            }
            
            // Verificar tipo de dato
            if (!empty($value) && isset($rule['type'])) {
                switch ($rule['type']) {
                    case 'email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field] = "Formato de email inválido";
                        }
                        break;
                    case 'integer':
                        if (!filter_var($value, FILTER_VALIDATE_INT)) {
                            $errors[$field] = "Debe ser un número entero";
                        }
                        break;
                    case 'string':
                        if (!is_string($value)) {
                            $errors[$field] = "Debe ser texto";
                        }
                        break;
                }
            }
            
            // Verificar longitud mínima
            if (!empty($value) && isset($rule['min_length'])) {
                if (strlen($value) < $rule['min_length']) {
                    $errors[$field] = "Mínimo {$rule['min_length']} caracteres";
                }
            }
            
            // Verificar longitud máxima
            if (!empty($value) && isset($rule['max_length'])) {
                if (strlen($value) > $rule['max_length']) {
                    $errors[$field] = "Máximo {$rule['max_length']} caracteres";
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Escapar datos para prevenir XSS
     */
    protected function escape($data) {
        if (is_array($data)) {
            return array_map([$this, 'escape'], $data);
        }
        
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Modelo de ejemplo para Ofertas de Empleo
 */
class JobOfferModel extends BaseModel {
    
    protected $table = 'job_offers';
    
    /**
     * Obtener ofertas por categoría
     */
    public function getByCategory($category) {
        // Datos de ejemplo (futuro: consulta a BD)
        $sample_jobs = [
            [
                'id' => 1,
                'title' => 'Desarrollador Full Stack',
                'company' => 'TechSolutions Colombia',
                'category' => 'tecnologia',
                'location' => 'Bogotá',
                'salary' => '$3,000,000 - $4,500,000',
                'type' => 'Tiempo completo',
                'created_at' => '2025-01-01'
            ],
            [
                'id' => 2,
                'title' => 'Diseñador UX/UI',
                'company' => 'Creative Agency',
                'category' => 'diseño',
                'location' => 'Medellín',
                'salary' => '$2,500,000 - $3,800,000',
                'type' => 'Tiempo completo',
                'created_at' => '2025-01-02'
            ]
        ];
        
        return array_filter($sample_jobs, function($job) use ($category) {
            return $job['category'] === $category;
        });
    }
    
    /**
     * Buscar ofertas por término
     */
    public function search($term) {
        // Implementación futura
        return [];
    }
}

/**
 * Modelo de ejemplo para Empresas
 */
class CompanyModel extends BaseModel {
    
    protected $table = 'companies';
    
    /**
     * Obtener empresas por sector
     */
    public function getBySector($sector) {
        // Datos de ejemplo
        $sample_companies = [
            [
                'id' => 1,
                'name' => 'TechSolutions Colombia',
                'sector' => 'tecnologia',
                'size' => 'grande',
                'location' => 'Bogotá',
                'active_jobs' => 25,
                'rating' => 4.8
            ]
        ];
        
        return array_filter($sample_companies, function($company) use ($sector) {
            return $company['sector'] === $sector;
        });
    }
}
?>
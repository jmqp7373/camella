<?php
/**
 * Controlador de Subida de Imágenes para Anuncios
 * Maneja la carga, validación y eliminación de imágenes
 * Máximo: 5 imágenes por anuncio
 */

class ImageUploadController {
    
    private $db;
    private $uploadDir;
    private $maxImages = 5;
    private $maxFileSize = 5242880; // 5MB en bytes
    private $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    private $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    public function __construct() {
        require_once __DIR__ . '/../config/database.php';
        $this->db = getPDO();
        
        // Ruta absoluta a la carpeta de anuncios
        $this->uploadDir = __DIR__ . '/../assets/images/anuncios/';
        
        // Verificar que la carpeta exista y tenga permisos de escritura
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
        
        if (!is_writable($this->uploadDir)) {
            throw new Exception("La carpeta de anuncios no tiene permisos de escritura");
        }
    }
    
    /**
     * Subir imágenes para un anuncio
     * POST: anuncio_id, images[]
     */
    public function upload() {
        header('Content-Type: application/json');
        
        try {
            // Verificar sesión
            session_start();
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('Usuario no autenticado');
            }
            
            $userId = $_SESSION['user_id'];
            
            // Validar anuncio_id
            if (!isset($_POST['anuncio_id']) || empty($_POST['anuncio_id'])) {
                throw new Exception('ID de anuncio no proporcionado');
            }
            
            $anuncioId = (int)$_POST['anuncio_id'];
            
            // Verificar que el anuncio pertenezca al usuario
            $stmt = $this->db->prepare("
                SELECT id FROM anuncios 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$anuncioId, $userId]);
            
            if (!$stmt->fetch()) {
                throw new Exception('Anuncio no encontrado o no autorizado');
            }
            
            // Verificar cuántas imágenes ya tiene el anuncio
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total 
                FROM anuncio_imagenes 
                WHERE anuncio_id = ?
            ");
            $stmt->execute([$anuncioId]);
            $currentImages = $stmt->fetch()['total'];
            
            // Validar que no exceda el límite
            if (!isset($_FILES['images'])) {
                throw new Exception('No se recibieron archivos');
            }
            
            $filesCount = count($_FILES['images']['name']);
            $remainingSlots = $this->maxImages - $currentImages;
            
            if ($remainingSlots <= 0) {
                throw new Exception("Este anuncio ya tiene el máximo de {$this->maxImages} imágenes");
            }
            
            if ($filesCount > $remainingSlots) {
                throw new Exception("Solo puedes subir {$remainingSlots} imagen(es) más");
            }
            
            // Procesar cada archivo
            $uploadedFiles = [];
            $errors = [];
            
            for ($i = 0; $i < $filesCount; $i++) {
                // Verificar si hay error en la subida
                if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) {
                    $errors[] = "Error al subir archivo " . ($_FILES['images']['name'][$i] ?? $i);
                    continue;
                }
                
                $fileName = $_FILES['images']['name'][$i];
                $fileTmpName = $_FILES['images']['tmp_name'][$i];
                $fileSize = $_FILES['images']['size'][$i];
                $fileType = $_FILES['images']['type'][$i];
                
                // Validar tipo de archivo
                if (!in_array($fileType, $this->allowedTypes)) {
                    $errors[] = "$fileName: Tipo de archivo no permitido";
                    continue;
                }
                
                // Validar extensión
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                if (!in_array($fileExtension, $this->allowedExtensions)) {
                    $errors[] = "$fileName: Extensión no permitida";
                    continue;
                }
                
                // Validar tamaño
                if ($fileSize > $this->maxFileSize) {
                    $maxMB = $this->maxFileSize / 1048576;
                    $errors[] = "$fileName: Tamaño excede {$maxMB}MB";
                    continue;
                }
                
                // Generar nombre único y seguro
                $uniqueName = $this->generateFileName($anuncioId, $fileExtension);
                $targetPath = $this->uploadDir . $uniqueName;
                
                // Mover archivo a destino
                if (!move_uploaded_file($fileTmpName, $targetPath)) {
                    $errors[] = "$fileName: Error al guardar archivo";
                    continue;
                }
                
                // Insertar en base de datos - Guardar ruta relativa sin barra inicial
                $relativePath = 'assets/images/anuncios/' . $uniqueName;
                $orden = $currentImages + $i + 1;
                
                try {
                    $stmt = $this->db->prepare("
                        INSERT INTO anuncio_imagenes (anuncio_id, ruta, orden)
                        VALUES (?, ?, ?)
                    ");
                    $stmt->execute([$anuncioId, $relativePath, $orden]);
                    
                    $uploadedFiles[] = [
                        'id' => $this->db->lastInsertId(),
                        'ruta' => $relativePath,
                        'orden' => $orden
                    ];
                    
                } catch (PDOException $e) {
                    // Si falla la BD, eliminar archivo físico
                    unlink($targetPath);
                    $errors[] = "$fileName: Error al registrar en base de datos";
                }
            }
            
            // Respuesta
            echo json_encode([
                'success' => count($uploadedFiles) > 0,
                'uploaded' => $uploadedFiles,
                'errors' => $errors,
                'message' => count($uploadedFiles) . ' imagen(es) subida(s) exitosamente'
            ]);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Eliminar una imagen específica
     * POST: imagen_id
     */
    public function delete() {
        header('Content-Type: application/json');
        
        try {
            // Verificar sesión
            session_start();
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('Usuario no autenticado');
            }
            
            $userId = $_SESSION['user_id'];
            
            // Validar imagen_id
            if (!isset($_POST['imagen_id']) || empty($_POST['imagen_id'])) {
                throw new Exception('ID de imagen no proporcionado');
            }
            
            $imagenId = (int)$_POST['imagen_id'];
            
            // Obtener información de la imagen y verificar pertenencia
            $stmt = $this->db->prepare("
                SELECT ai.id, ai.ruta, ai.anuncio_id
                FROM anuncio_imagenes ai
                INNER JOIN anuncios a ON ai.anuncio_id = a.id
                WHERE ai.id = ? AND a.user_id = ?
            ");
            $stmt->execute([$imagenId, $userId]);
            $imagen = $stmt->fetch();
            
            if (!$imagen) {
                throw new Exception('Imagen no encontrada o no autorizada');
            }
            
            // Eliminar archivo físico
            $filePath = __DIR__ . '/..' . $imagen['ruta'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // Eliminar registro de base de datos
            $stmt = $this->db->prepare("DELETE FROM anuncio_imagenes WHERE id = ?");
            $stmt->execute([$imagenId]);
            
            // Reordenar las imágenes restantes
            $stmt = $this->db->prepare("
                SELECT id FROM anuncio_imagenes 
                WHERE anuncio_id = ? 
                ORDER BY orden
            ");
            $stmt->execute([$imagen['anuncio_id']]);
            $imagenesRestantes = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $updateStmt = $this->db->prepare("UPDATE anuncio_imagenes SET orden = ? WHERE id = ?");
            foreach ($imagenesRestantes as $index => $imgId) {
                $updateStmt->execute([$index + 1, $imgId]);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Imagen eliminada exitosamente'
            ]);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Obtener todas las imágenes de un anuncio
     * GET: anuncio_id
     */
    public function getImages() {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_GET['anuncio_id']) || empty($_GET['anuncio_id'])) {
                throw new Exception('ID de anuncio no proporcionado');
            }
            
            $anuncioId = (int)$_GET['anuncio_id'];
            
            $stmt = $this->db->prepare("
                SELECT id, ruta, orden, created_at
                FROM anuncio_imagenes
                WHERE anuncio_id = ?
                ORDER BY orden
            ");
            $stmt->execute([$anuncioId]);
            $imagenes = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'images' => $imagenes,
                'total' => count($imagenes),
                'remaining' => $this->maxImages - count($imagenes)
            ]);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Generar nombre único para el archivo
     */
    private function generateFileName($anuncioId, $extension) {
        $timestamp = time();
        $random = bin2hex(random_bytes(8));
        return "anuncio_{$anuncioId}_{$timestamp}_{$random}.{$extension}";
    }
}

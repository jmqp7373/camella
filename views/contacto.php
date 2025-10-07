<?php 
$pageTitle = "Contacto"; 
?>

<div class="contacto-header">
    <h1 class="page-title">
        <i class="fas fa-envelope"></i> 
        Contacto
    </h1>
    <p class="page-subtitle">
        ¿Tienes preguntas o necesitas ayuda? Estamos aquí para apoyarte
    </p>
</div>

<div class="contact-content">
    <div class="contact-info">
        <h3><i class="fas fa-info-circle"></i> Información de Contacto</h3>
        <div class="info-item">
            <i class="fas fa-envelope"></i>
            <span>contacto@camella.com.co</span>
        </div>
        <div class="info-item">
            <i class="fas fa-phone"></i>
            <span>+57 (1) 234-5678</span>
        </div>
        <div class="info-item">
            <i class="fas fa-map-marker-alt"></i>
            <span>Bogotá, Colombia</span>
        </div>
    </div>
    
    <div class="contact-form">
        <h3><i class="fas fa-paper-plane"></i> Envíanos un mensaje</h3>
        <form>
            <input type="text" placeholder="Tu nombre completo" required>
            <input type="email" placeholder="Tu email" required>
            <textarea placeholder="Tu mensaje" rows="5" required></textarea>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-send"></i> Enviar Mensaje
            </button>
        </form>
    </div>
</div>

<style>
.contact-content {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 3rem;
    margin-top: 2rem;
}

.contact-info h3, .contact-form h3 {
    color: #333;
    margin-bottom: 1.5rem;
    font-size: 1.4rem;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 10px;
}

.info-item i {
    color: #667eea;
    width: 20px;
}

.contact-form form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.contact-form input, .contact-form textarea {
    padding: 1rem;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.contact-form input:focus, .contact-form textarea:focus {
    outline: none;
    border-color: #667eea;
}

@media (max-width: 768px) {
    .contact-content {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
}
</style>
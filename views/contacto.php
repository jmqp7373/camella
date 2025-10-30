<?php 
$pageTitle = "Contacto"; 
?>

<!-- JSON-LD Schema para Organización -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "Camella.com.co",
  "url": "https://camella.com.co",
  "contactPoint": [{
    "@type": "ContactPoint",
    "contactType": "customer support",
    "telephone": "+57 310 3951529",
    "areaServed": "CO",
    "availableLanguage": "es"
  }],
  "address": {
    "@type": "PostalAddress",
    "addressLocality": "Medellín",
    "addressRegion": "Antioquia",
    "addressCountry": "CO"
  },
  "sameAs": []
}
</script>

<div class="contacto-header">
    <h1 class="page-title">
        <i class="fas fa-envelope"></i> 
        Contacto
    </h1>
    <p class="page-subtitle">
        ¿Tienes preguntas o necesitas ayuda? Estamos aquí para apoyarte en Medellín, Colombia
    </p>
</div>

<div class="contact-content">
    <div class="contact-info">
        <h3><i class="fas fa-info-circle"></i> Información de Contacto</h3>
        
        <div class="info-item">
            <i class="fas fa-envelope"></i>
            <div class="contact-details">
                <span class="contact-label">Email</span>
                <a href="mailto:admin@camella.com.co" class="contact-value">admin@camella.com.co</a>
            </div>
        </div>
        
        <div class="info-item">
            <i class="fab fa-whatsapp whatsapp-icon"></i>
            <div class="contact-details">
                <span class="contact-label">WhatsApp</span>
                <div class="contact-actions">
                    <a href="tel:+573103951529" class="contact-value phone-link">+57 310 3951529</a>
                    <a href="https://wa.me/573103951529?text=Hola%20Camella" 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="btn btn-whatsapp">
                        <i class="fab fa-whatsapp"></i> Escribir por WhatsApp
                    </a>
                </div>
            </div>
        </div>
        
        <div class="info-item">
            <i class="fas fa-phone"></i>
            <div class="contact-details">
                <span class="contact-label">Teléfono</span>
                <a href="tel:+573103951529" class="contact-value">+57 310 3951529</a>
            </div>
        </div>
        
        <div class="info-item">
            <i class="fas fa-map-marker-alt"></i>
            <div class="contact-details">
                <span class="contact-label">Ubicación</span>
                <span class="contact-value">Medellín, Colombia</span>
            </div>
        </div>
        
        <div class="info-item">
            <i class="fas fa-clock"></i>
            <div class="contact-details">
                <span class="contact-label">Horario de Atención</span>
                <span class="contact-value">Lunes a Viernes: 8:00 AM - 6:00 PM (GMT-5)</span>
            </div>
        </div>
    </div>
    
    <div class="contact-form">
        <h3><i class="fas fa-paper-plane"></i> Envíanos un mensaje</h3>
        <p class="form-description">
            Completa el formulario y nos pondremos en contacto contigo a la brevedad desde Medellín, Colombia.
        </p>
        <form class="contact-form-wrapper">
            <div class="form-group">
                <label for="nombre">Nombre completo *</label>
                <input type="text" id="nombre" name="nombre" placeholder="Tu nombre completo" required>
            </div>
            <div class="form-group">
                <label for="email">Correo electrónico *</label>
                <input type="email" id="email" name="email" placeholder="tu@email.com" required>
            </div>
            <div class="form-group">
                <label for="telefono">Teléfono / WhatsApp</label>
                <input type="tel" id="telefono" name="telefono" placeholder="+57 310 1234567">
            </div>
            <div class="form-group">
                <label for="asunto">Asunto</label>
                <select id="asunto" name="asunto" class="form-select" required>
                    <option value="">Selecciona un asunto</option>
                    <option value="empleo">Búsqueda de empleo</option>
                    <option value="empresa">Servicios para empresas</option>
                    <option value="soporte">Soporte técnico</option>
                    <option value="otro">Otro</option>
                </select>
            </div>
            <div class="form-group">
                <label for="mensaje">Mensaje *</label>
                <textarea id="mensaje" name="mensaje" rows="5" placeholder="Escribe tu mensaje aquí..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-send">
                <i class="fas fa-send"></i> Enviar Mensaje
            </button>
        </form>
    </div>
    
    <!-- Mapa de ubicación -->
    <div class="location-map">
        <h3><i class="fas fa-map-marked-alt"></i> Nuestra Ubicación</h3>
        <div class="map-container">
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d253687.46190803248!2d-75.69618313828124!3d6.244203099999998!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8e4428dfb80fad05%3A0x42137cfcc7b53b56!2sMedell%C3%ADn%2C%20Antioquia%2C%20Colombia!5e0!3m2!1ses!2s!4v1695000000000!5m2!1ses!2s" 
                width="100%" 
                height="300" 
                style="border:0; border-radius: 10px;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade"
                title="Ubicación Camella.com.co en Medellín, Colombia">
            </iframe>
        </div>
        <p class="map-description">
            Nos encontramos en la hermosa ciudad de Medellín, Antioquia, Colombia. 
            <strong>¡Contáctanos por WhatsApp para atención inmediata!</strong>
        </p>
    </div>
</div>

<style>
/* Estilos específicos para la página de contacto */
.contact-content {
    display: grid;
    grid-template-columns: 1fr;
    gap: 3rem;
    margin-top: 2rem;
}

.contact-info, .contact-form, .location-map {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    border: 1px solid #e9ecef;
}

.contact-info h3, .contact-form h3, .location-map h3 {
    color: #333;
    margin-bottom: 1.5rem;
    font-size: 1.4rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.info-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 10px;
    border-left: 4px solid #002b47;
    transition: all 0.3s ease;
}

.info-item:hover {
    background: #e8f4fd;
    border-left-color: #28a745;
}

.info-item i {
    color: #002b47;
    font-size: 1.5rem;
    margin-top: 0.2rem;
    flex-shrink: 0;
}

.whatsapp-icon {
    color: #25D366 !important;
}

.contact-details {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    width: 100%;
}

.contact-label {
    font-weight: 600;
    color: #495057;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.contact-value {
    font-size: 1.1rem;
    color: #333;
    text-decoration: none;
    transition: color 0.3s ease;
}

.contact-value:hover {
    color: #002b47;
}

.contact-actions {
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
}

.btn-whatsapp {
    background: #25D366 !important;
    color: white !important;
    padding: 0.8rem 1.5rem;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    justify-content: center;
    max-width: 200px;
}

.btn-whatsapp:hover {
    background: #128C7E !important;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(37, 211, 102, 0.4);
}

.form-description {
    color: #666;
    margin-bottom: 2rem;
    line-height: 1.6;
}

.contact-form-wrapper {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group label {
    font-weight: 600;
    color: #495057;
    font-size: 0.95rem;
}

.form-group input, 
.form-group textarea, 
.form-group select {
    padding: 1rem;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
    font-family: inherit;
}

.form-group input:focus, 
.form-group textarea:focus, 
.form-group select:focus {
    outline: none;
    border-color: #002b47;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.btn-send {
    margin-top: 1rem;
    padding: 1rem 2rem;
    font-size: 1.1rem;
    font-weight: 600;
}

.location-map {
    text-align: center;
}

.map-container {
    margin: 1.5rem 0;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.map-description {
    color: #666;
    line-height: 1.6;
    margin-top: 1rem;
    font-style: italic;
}

@media (min-width: 768px) {
    .contact-content {
        grid-template-columns: 1fr 1fr;
    }
    
    .location-map {
        grid-column: 1 / -1;
    }
}

@media (max-width: 768px) {
    .contact-content {
        gap: 2rem;
    }
    
    .contact-info, .contact-form, .location-map {
        padding: 1.5rem;
    }
    
    .info-item {
        padding: 1rem;
    }
    
    .contact-actions {
        align-items: stretch;
    }
    
    .btn-whatsapp {
        max-width: none;
    }
}
</style>

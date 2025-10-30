<?php 
$pageTitle = "Centro de Ayuda"; 
?>

<div class="ayuda-container">
    <div class="ayuda-header">
        <h1 class="page-title">
            <i class="fas fa-question-circle"></i> 
            Centro de Ayuda
        </h1>
        <p class="page-subtitle">
            Encuentra respuestas a las preguntas más frecuentes sobre Camella.com.co
        </p>
    </div>

    <div class="help-content">
        <!-- Búsqueda rápida -->
        <div class="search-help">
            <input type="text" placeholder="¿En qué te podemos ayudar? Busca aquí..." class="help-search-input">
            <button class="btn btn-primary"><i class="fas fa-search"></i> Buscar</button>
        </div>

        <!-- Categorías de ayuda -->
        <div class="help-categories">
            <div class="category-card">
                <div class="category-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h3>Registro y Cuenta</h3>
                <p>Cómo crear tu perfil, actualizar información y gestionar tu cuenta</p>
                <ul class="help-links">
                    <li><a href="#registro">¿Cómo me registro en Camella.com.co?</a></li>
                    <li><a href="#perfil">¿Cómo actualizo mi perfil profesional?</a></li>
                    <li><a href="#contraseña">¿Olvidé mi contraseña, qué hago?</a></li>
                </ul>
            </div>

            <div class="category-card">
                <div class="category-icon">
                    <i class="fas fa-briefcase"></i>
                </div>
                <h3>Búsqueda de Empleo</h3>
                <p>Tips para encontrar las mejores oportunidades laborales</p>
                <ul class="help-links">
                    <li><a href="#buscar">¿Cómo busco ofertas de trabajo?</a></li>
                    <li><a href="#aplicar">¿Cómo aplico a una oferta laboral?</a></li>
                    <li><a href="#alertas">¿Puedo recibir alertas de empleo?</a></li>
                </ul>
            </div>

            <div class="category-card">
                <div class="category-icon">
                    <i class="fas fa-building"></i>
                </div>
                <h3>Para Empresas</h3>
                <p>Guía para empresas que buscan talento</p>
                <ul class="help-links">
                    <li><a href="#publicar">¿Cómo publico una oferta de empleo?</a></li>
                    <li><a href="#candidatos">¿Cómo veo los candidatos?</a></li>
                    <li><a href="#planes">¿Qué planes tienen para empresas?</a></li>
                </ul>
            </div>

            <div class="category-card">
                <div class="category-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Seguridad y Privacidad</h3>
                <p>Mantén tu información segura en nuestra plataforma</p>
                <ul class="help-links">
                    <li><a href="#privacidad">¿Cómo protegen mi información?</a></li>
                    <li><a href="#reportar">¿Cómo reporto un perfil sospechoso?</a></li>
                    <li><a href="#datos">¿Puedo eliminar mi cuenta?</a></li>
                </ul>
            </div>
        </div>

        <!-- Preguntas Frecuentes -->
        <div class="faq-section">
            <h2><i class="fas fa-question"></i> Preguntas Frecuentes</h2>
            
            <div class="faq-item" id="registro">
                <div class="faq-question">
                    <h3>¿Es gratis registrarse en Camella.com.co?</h3>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Sí, el registro y la búsqueda de empleo son completamente gratuitos para los candidatos. Solo cobramos a las empresas por servicios premium de reclutamiento.</p>
                </div>
            </div>

            <div class="faq-item" id="perfil">
                <div class="faq-question">
                    <h3>¿Cómo hago que mi perfil sea más atractivo para los empleadores?</h3>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Completa toda tu información, añade una foto profesional, detalla tu experiencia y habilidades, y mantén tu perfil actualizado. Los perfiles completos reciben 5x más visualizaciones.</p>
                </div>
            </div>

            <div class="faq-item" id="buscar">
                <div class="faq-question">
                    <h3>¿Cómo puedo filtrar las ofertas de trabajo?</h3>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Utiliza nuestros filtros por ubicación, sector, tipo de contrato, rango salarial y nivel de experiencia. También puedes buscar por palabras clave específicas.</p>
                </div>
            </div>

            <div class="faq-item" id="publicar">
                <div class="faq-question">
                    <h3>¿Cuánto cuesta publicar una oferta de empleo?</h3>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Tenemos planes flexibles desde $50.000 COP por oferta individual, hasta planes corporativos con ofertas ilimitadas. Contáctanos para conocer las opciones que mejor se adapten a tu empresa.</p>
                </div>
            </div>

            <div class="faq-item" id="privacidad">
                <div class="faq-question">
                    <h3>¿Qué pasa con mi información personal?</h3>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Tu información está protegida bajo nuestras políticas de privacidad. Solo las empresas que tú autorices podrán ver tu información de contacto completa. Lee nuestra <a href="index.php?view=privacidad">Política de Privacidad</a> para más detalles.</p>
                </div>
            </div>
        </div>

        <!-- Contacto directo -->
        <div class="contact-support">
            <h2><i class="fas fa-headset"></i> ¿No encontraste lo que buscabas?</h2>
            <div class="contact-options">
                <div class="contact-option">
                    <i class="fas fa-envelope"></i>
                    <h4>Correo Electrónico</h4>
                    <p><a href="mailto:admin@camella.com.co">admin@camella.com.co</a></p>
                    <span class="response-time">Respuesta en 24 horas</span>
                </div>
                
                <div class="contact-option">
                    <i class="fab fa-whatsapp"></i>
                    <h4>WhatsApp</h4>
                    <p><a href="https://wa.me/573103951529" target="_blank">+57 310 3951529</a></p>
                    <span class="response-time">Lun - Vie: 8:00 AM - 6:00 PM</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos para el Centro de Ayuda */
.ayuda-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 2rem;
}

.ayuda-header {
    text-align: center;
    margin-bottom: 3rem;
}

.search-help {
    display: flex;
    gap: 1rem;
    margin-bottom: 3rem;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.help-search-input {
    flex: 1;
    padding: 1rem;
    border: 2px solid #e9ecef;
    border-radius: 25px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.help-search-input:focus {
    outline: none;
    border-color: #002b47;
}

.help-categories {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-bottom: 4rem;
}

.category-card {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    border-color: #002b47;
}

.category-icon {
    text-align: center;
    margin-bottom: 1rem;
}

.category-icon i {
    font-size: 3rem;
    color: #002b47;
}

.category-card h3 {
    color: #333;
    text-align: center;
    margin-bottom: 1rem;
    font-size: 1.4rem;
}

.category-card p {
    color: #666;
    text-align: center;
    margin-bottom: 1.5rem;
    line-height: 1.6;
}

.help-links {
    list-style: none;
    padding: 0;
}

.help-links li {
    margin-bottom: 0.8rem;
}

.help-links a {
    color: #002b47;
    text-decoration: none;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    padding: 0.5rem;
    border-radius: 5px;
}

.help-links a:hover {
    background: #f8f9fa;
    color: #5a6fd8;
    padding-left: 1rem;
}

.help-links a::before {
    content: "→";
    margin-right: 0.5rem;
    font-weight: bold;
}

.faq-section {
    margin-bottom: 4rem;
}

.faq-section h2 {
    color: #333;
    text-align: center;
    margin-bottom: 2rem;
    font-size: 2rem;
}

.faq-item {
    background: white;
    border-radius: 10px;
    margin-bottom: 1rem;
    border: 1px solid #e9ecef;
    overflow: hidden;
    transition: all 0.3s ease;
}

.faq-item:hover {
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.faq-question {
    padding: 1.5rem;
    background: #f8f9fa;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: background 0.3s ease;
}

.faq-question:hover {
    background: #e9ecef;
}

.faq-question h3 {
    margin: 0;
    color: #333;
    font-size: 1.2rem;
}

.faq-question i {
    color: #002b47;
    transition: transform 0.3s ease;
}

.faq-item.active .faq-question i {
    transform: rotate(180deg);
}

.faq-answer {
    padding: 0 1.5rem;
    max-height: 0;
    overflow: hidden;
    transition: all 0.3s ease;
}

.faq-item.active .faq-answer {
    padding: 1.5rem;
    max-height: 200px;
}

.faq-answer p {
    margin: 0;
    color: #666;
    line-height: 1.6;
}

.faq-answer a {
    color: #002b47;
    text-decoration: none;
}

.faq-answer a:hover {
    text-decoration: underline;
}

.contact-support {
    background: linear-gradient(135deg, var(--azul-fondo) 0%, var(--azul-fondo) 100%);
    color: white;
    padding: 3rem;
    border-radius: 15px;
    text-align: center;
}

.contact-support h2 {
    margin-bottom: 2rem;
    font-size: 1.8rem;
}

.contact-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.contact-option {
    background: rgba(255,255,255,0.1);
    padding: 2rem;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.contact-option:hover {
    background: rgba(255,255,255,0.2);
    transform: translateY(-5px);
}

.contact-option i {
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.contact-option h4 {
    margin-bottom: 0.5rem;
    font-size: 1.2rem;
}

.contact-option p {
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
}

.contact-option a {
    color: white;
    text-decoration: none;
}

.contact-option a:hover {
    text-decoration: underline;
}

.response-time {
    font-size: 0.9rem;
    opacity: 0.8;
}

@media (max-width: 768px) {
    .search-help {
        flex-direction: column;
    }
    
    .help-categories {
        grid-template-columns: 1fr;
    }
    
    .contact-options {
        grid-template-columns: 1fr;
    }
    
    .faq-question h3 {
        font-size: 1rem;
    }
}
</style>

<script>
// Funcionalidad para FAQ acordeón
document.addEventListener('DOMContentLoaded', function() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        
        question.addEventListener('click', () => {
            // Cerrar otros items abiertos
            faqItems.forEach(otherItem => {
                if (otherItem !== item) {
                    otherItem.classList.remove('active');
                }
            });
            
            // Toggle el item actual
            item.classList.toggle('active');
        });
    });

    // Funcionalidad de búsqueda
    const searchInput = document.querySelector('.help-search-input');
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const allText = document.querySelectorAll('.category-card, .faq-item');
        
        allText.forEach(element => {
            const text = element.textContent.toLowerCase();
            const parent = element.closest('.category-card, .faq-item');
            
            if (text.includes(searchTerm) || searchTerm === '') {
                parent.style.display = 'block';
                parent.style.animation = 'fadeIn 0.5s ease';
            } else {
                parent.style.display = 'none';
            }
        });
    });
});
</script>

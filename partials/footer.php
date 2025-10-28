    </main>
    
    <!-- Footer Fijo -->
    <footer class="main-footer">
        <div class="footer-content">
            <div class="footer-info">
                <p>&copy; <?php echo date('Y'); ?> <strong>Camella.com.co</strong> - Portal de Empleo Líder en Colombia</p>
            </div>
            
            <nav class="footer-nav">
                <ul class="footer-links">
                    <li><a href="index.php?view=privacidad"><i class="fas fa-shield-alt"></i> Privacidad</a></li>
                    <li><a href="index.php?view=terminos"><i class="fas fa-file-contract"></i> Términos</a></li>
                    <li><a href="index.php?view=contacto"><i class="fas fa-phone"></i> Soporte</a></li>
                    <li><a href="index.php?view=ayuda"><i class="fas fa-question-circle"></i> Ayuda</a></li>
                </ul>
            </nav>
            
            <div class="footer-company">
                <div class="company-details">
                    <p class="company-name">
                        <i class="fas fa-building"></i> 
                        Propiedad de <strong>Digital Wise Company S.A.S.</strong>
                    </p>
                    <div class="company-info-grid">
                        <span class="info-item">
                            <i class="fas fa-id-card"></i> 
                            NIT: 901652435-6
                        </span>
                        <span class="info-item">
                            <i class="fas fa-map-marker-alt"></i> 
                            Cra 70 #32-82, Medellín, Colombia
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <!-- JavaScript principal de Camella -->
    <script src="<?= app_url('assets/js/main.js') ?>"></script>
    
    <script>
        // Funciones adicionales específicas del footer
        document.addEventListener('DOMContentLoaded', function() {
            // Agregar interactividad a las tarjetas de categorías
            const categoryCards = document.querySelectorAll('.category-card');
            categoryCards.forEach(card => {
                card.addEventListener('click', function() {
                    // Aquí se puede agregar funcionalidad para mostrar ofertas de esa categoría
                    console.log('Categoría seleccionada:', this.querySelector('.category-title').textContent);
                });
            });

            // Smooth scroll para enlaces internos
            const links = document.querySelectorAll('a[href^="#"]');
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href');
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        targetElement.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                });
            });

            // Mostrar información de la página en consola
            console.log('🚀 Camella.com.co - Portal de Empleo Cargado');
            console.log('📅 Fecha de carga:', new Date().toLocaleString());
            console.log('🔗 URL actual:', window.location.href);
        });

        // Función para mostrar mensajes de éxito/error (para futuras funcionalidades)
        function showMessage(message, type = 'info') {
            const messageDiv = document.createElement('div');
            messageDiv.className = `alert alert-${type}`;
            messageDiv.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i>
                ${message}
            `;
            messageDiv.style.cssText = `
                position: fixed;
                top: 100px;
                right: 20px;
                background: ${type === 'success' ? '#d4edda' : '#cce7ff'};
                border: 1px solid ${type === 'success' ? '#c3e6cb' : '#99d6ff'};
                color: ${type === 'success' ? '#155724' : '#004085'};
                padding: 1rem;
                border-radius: 5px;
                z-index: 10000;
                max-width: 300px;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            `;
            
            document.body.appendChild(messageDiv);
            
            setTimeout(() => {
                messageDiv.remove();
            }, 5000);
        }
    </script>
</body>
</html>

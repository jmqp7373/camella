/**
 * CAMELLA.COM.CO - JavaScript Principal
 * Funcionalidades generales del sitio
 */

// Esperar a que el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', function() {
    
    // ========================================
    // SCROLL SUAVE HACIA SECCIONES CON ANCHOR
    // ========================================
    
    // Aplicar a todos los enlaces que apunten a #crear-anuncio o cualquier anchor interno
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (!targetId || targetId === '#') return;
            
            const target = document.querySelector(targetId);
            
            if (target) {
                // Calcular posición con offset para header fijo (si aplica)
                const headerOffset = 100;
                const elementPosition = target.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                
                // Scroll suave
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
                
                // Agregar clase temporal para destacar
                target.classList.add('highlight-target');
                setTimeout(() => {
                    target.classList.remove('highlight-target');
                }, 2000);
            }
        });
    });
    
    // ========================================
    // TOOLTIPS Y POPOVERS (Bootstrap 5)
    // ========================================
    
    // Inicializar tooltips de Bootstrap si existen
    const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // ========================================
    // ANIMACIONES AL HACER SCROLL
    // ========================================
    
    // Detectar elementos visibles al hacer scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in-visible');
            }
        });
    }, observerOptions);
    
    // Observar tarjetas de anuncios
    document.querySelectorAll('.card-anuncio').forEach(card => {
        observer.observe(card);
    });
    
    // ========================================
    // CONFIRMACIÓN DE ELIMINACIÓN
    // ========================================
    
    // Agregar confirmación a botones de eliminar
    document.querySelectorAll('[data-confirm-delete]').forEach(button => {
        button.addEventListener('click', function(e) {
            const confirmMessage = this.getAttribute('data-confirm-delete') || 
                                  '¿Estás seguro de que deseas eliminar este elemento?';
            
            if (!confirm(confirmMessage)) {
                e.preventDefault();
                return false;
            }
        });
    });
    
    // ========================================
    // AUTO-HIDE DE ALERTAS
    // ========================================
    
    // Ocultar automáticamente alertas después de 5 segundos
    document.querySelectorAll('.alert:not(.alert-permanent)').forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
    
    // ========================================
    // FORMULARIO DE CONTACTO
    // ========================================
    
    const f=document.getElementById('contactForm'); 
    if(f) {
        const msg=document.getElementById('formMessage');
        function show(type,txt){ 
            msg.innerHTML=`<div class="alert alert-${type} alert-dismissible fade show" role="alert">${txt}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`; 
            setTimeout(()=>msg.innerHTML='',5000);
        }
        f.addEventListener('submit',async function(e){
            e.preventDefault();
            const btn=this.querySelector('button[type="submit"]'), t=btn.innerHTML, ep=this.dataset.endpoint||'controllers/send-email.php';
            btn.disabled=true; btn.innerHTML='<i class="fas fa-spinner fa-spin me-2"></i>Enviando...';
            try{
                const r=await fetch(ep,{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'},body:new FormData(this)});
                const j=await r.json();
                if(j.success){ show('success','✅ '+(j.message||'Enviado')); this.reset(); } else { show('danger','❌ '+(j.error||'Error al enviar')); }
            }catch(err){ show('danger','❌ Error de conexión. Intenta nuevamente.'); }
            finally{ btn.disabled=false; btn.innerHTML=t; }
        });
    }
    
    console.log('✅ Camella.com.co JavaScript cargado correctamente');
});

// ========================================
// FUNCIONES AUXILIARES GLOBALES
// ========================================

/**
 * Copiar texto al portapapeles
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('Copiado al portapapeles', 'success');
    }).catch(err => {
        console.error('Error al copiar:', err);
    });
}

/**
 * Mostrar toast notification
 */
function showToast(message, type = 'info') {
    // Implementación simple de toast
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} position-fixed top-0 end-0 m-3`;
    toast.style.zIndex = '9999';
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

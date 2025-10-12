<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Camella.com.co - Portal de Empleo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; line-height: 1.6; background: #f8f9fa; }
        .header { background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white; padding: 1rem 0; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        .header-content { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 1.8rem; font-weight: bold; }
        .nav-buttons { display: flex; gap: 10px; }
        .btn { padding: 8px 16px; border: none; border-radius: 5px; text-decoration: none; font-weight: 500; cursor: pointer; transition: all 0.3s; }
        .btn-primary { background: #dc3545; color: white; }
        .btn-secondary { background: transparent; border: 1px solid white; color: white; }
        .hero { background: white; margin: 20px auto; max-width: 1200px; padding: 40px; border-radius: 10px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .hero h1 { color: #007bff; margin-bottom: 15px; font-size: 2.2rem; }
        .hero p { color: #666; margin-bottom: 10px; font-size: 1.1rem; }
        .cta-buttons { margin: 30px 0; display: flex; gap: 15px; justify-content: center; flex-wrap: wrap; }
        .btn-lg { padding: 12px 25px; font-size: 1.1rem; }
        .categorias { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .categorias h2 { text-align: center; margin-bottom: 30px; color: #333; background: #007bff; color: white; padding: 15px; border-radius: 8px; }
        .categorias-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
        .categoria-card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-align: center; transition: transform 0.3s; }
        .categoria-card:hover { transform: translateY(-5px); }
        .categoria-icon { font-size: 2.5rem; color: #007bff; margin-bottom: 15px; }
        .categoria-card h3 { margin-bottom: 10px; color: #333; }
        .categoria-count { color: #666; margin-bottom: 15px; }
        .btn-outline { background: transparent; border: 1px solid #007bff; color: #007bff; }
        .btn-outline:hover { background: #007bff; color: white; }
        .footer { background: #007bff; color: white; text-align: center; padding: 20px 0; margin-top: 40px; }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-briefcase"></i> Camella.com.co
                </div>
                <div class="nav-buttons">
                    <a href="#" class="btn btn-primary">+ Publícate</a>
                    <a href="#" class="btn btn-secondary">Login</a>
                </div>
            </div>
        </div>
    </header>

    <main>
        <div class="hero">
            <h1><i class="fas fa-briefcase"></i> Bienvenido a Camella.com.co</h1>
            <p>Camella.com.co es la bolsa de empleo que conecta a Colombia.</p>
            <p>Si necesitas algo, aquí hay quien te ayude.</p>
            <p>Si puedes hacer algo, aquí hay quien lo necesite.</p>
            
            <div class="cta-buttons">
                <a href="?view=buscar-empleo" class="btn btn-primary btn-lg">
                    <i class="fas fa-search"></i> Buscar Empleo
                </a>
                <a href="?view=publicar-oferta" class="btn btn-secondary btn-lg">
                    <i class="fas fa-plus-circle"></i> Publicar Oferta
                </a>
            </div>
        </div>

        <section class="categorias">
            <h2><i class="fas fa-list"></i> Explora por Categorías</h2>
            
            <div class="categorias-grid">
                <div class="categoria-card">
                    <div class="categoria-icon"><i class="fas fa-laptop-code"></i></div>
                    <h3>Tecnología</h3>
                    <p class="categoria-count">5 oficios disponibles</p>
                    <a href="?view=categoria&id=1" class="btn btn-outline">Ver Ofertas <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <div class="categoria-card">
                    <div class="categoria-icon"><i class="fas fa-heartbeat"></i></div>
                    <h3>Salud</h3>
                    <p class="categoria-count">3 oficios disponibles</p>
                    <a href="?view=categoria&id=2" class="btn btn-outline">Ver Ofertas <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <div class="categoria-card">
                    <div class="categoria-icon"><i class="fas fa-graduation-cap"></i></div>
                    <h3>Educación</h3>
                    <p class="categoria-count">4 oficios disponibles</p>
                    <a href="?view=categoria&id=3" class="btn btn-outline">Ver Ofertas <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <div class="categoria-card">
                    <div class="categoria-icon"><i class="fas fa-chart-line"></i></div>
                    <h3>Ventas</h3>
                    <p class="categoria-count">6 oficios disponibles</p>
                    <a href="?view=categoria&id=4" class="btn btn-outline">Ver Ofertas <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Camella.com.co - Portal de Empleo Líder en Colombia</p>
            <p><a href="?view=privacidad" style="color: white;">Privacidad</a> | <a href="?view=terminos" style="color: white;">Términos</a> | <a href="?view=soporte" style="color: white;">Soporte</a></p>
        </div>
    </footer>
</body>
</html>
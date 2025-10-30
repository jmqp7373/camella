document.addEventListener('DOMContentLoaded',function(){
  const f=document.getElementById('contactForm'); if(!f) return;
  const msg=document.getElementById('formMessage');
  function show(type,txt){ msg.innerHTML=`<div class="alert alert-${type} alert-dismissible fade show" role="alert">${txt}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`; setTimeout(()=>msg.innerHTML='',5000);}
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
});

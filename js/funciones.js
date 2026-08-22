// =============================================
// FUNCIONES MEJORADAS CON ANIMACIONES Y PDF
// SISTEMA DE EVALUACIÓN LOPDP
// =============================================

// Navegación por tabs con animación
function mostrarTab(tabId) {
    document.querySelectorAll('.tab-contenido').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(10px)';
        setTimeout(() => {
            el.classList.remove('activo');
        }, 200);
    });
    
    document.querySelectorAll('.tab').forEach(el => {
        el.classList.remove('activo');
    });
    
    setTimeout(() => {
        const contenido = document.getElementById(tabId);
        contenido.classList.add('activo');
        setTimeout(() => {
            contenido.style.opacity = '1';
            contenido.style.transform = 'translateY(0)';
        }, 50);
    }, 200);
    
    document.querySelector(`.tab[data-tab="${tabId}"]`).classList.add('activo');
}

// Calcular porcentaje de una categoría
function calcularCategoria(categoriaId) {
    const preguntas = document.querySelectorAll(`#categoria-${categoriaId} .pregunta-item`);
    let totalPeso = 0;
    let cumplido = 0;
    
    preguntas.forEach(pregunta => {
        const porcentaje = parseFloat(pregunta.querySelector('.input-porcentaje').value) || 0;
        const estado = pregunta.querySelector('.select-estado').value;
        
        totalPeso += porcentaje;
        
        if (estado === 'Cumple totalmente') {
            cumplido += porcentaje;
        } else if (estado === 'Cumple parcialmente') {
            cumplido += porcentaje * 0.5;
        }
    });
    
    return totalPeso > 0 ? Math.round((cumplido / totalPeso) * 100) : 0;
}

// Actualizar dashboard en tiempo real con animación
function actualizarDashboard() {
    const cat1 = calcularCategoria(1);
    const cat2 = calcularCategoria(2);
    const cat3 = calcularCategoria(3);
    
    animarNumero('porcentaje-cat1', cat1);
    animarNumero('porcentaje-cat2', cat2);
    animarNumero('porcentaje-cat3', cat3);
    
    document.getElementById('barra-cat1').style.width = cat1 + '%';
    document.getElementById('barra-cat2').style.width = cat2 + '%';
    document.getElementById('barra-cat3').style.width = cat3 + '%';
    
    actualizarColor('cat1', cat1);
    actualizarColor('cat2', cat2);
    actualizarColor('cat3', cat3);
}

// Animar números (contador)
function animarNumero(elementId, valorFinal) {
    const elemento = document.getElementById(elementId);
    const valorInicial = parseInt(elemento.textContent) || 0;
    const duracion = 800;
    const inicio = performance.now();
    
    function actualizar(tiempoActual) {
        const progreso = Math.min((tiempoActual - inicio) / duracion, 1);
        const valorActual = Math.round(valorInicial + (valorFinal - valorInicial) * progreso);
        elemento.textContent = valorActual + '%';
        
        if (progreso < 1) {
            requestAnimationFrame(actualizar);
        } else {
            elemento.textContent = valorFinal + '%';
        }
    }
    
    requestAnimationFrame(actualizar);
}

function actualizarColor(categoria, valor) {
    const elemento = document.getElementById(`porcentaje-${categoria}`);
    const card = elemento.closest('.dashboard-card');
    const barra = card.querySelector('.barra-progreso');
    
    barra.classList.remove('barra-verde', 'barra-amarillo', 'barra-rojo');
    
    if (valor >= 80) {
        elemento.className = 'numero color-verde';
        barra.classList.add('barra-verde');
    } else if (valor >= 50) {
        elemento.className = 'numero color-amarillo';
        barra.classList.add('barra-amarillo');
    } else {
        elemento.className = 'numero color-rojo';
        barra.classList.add('barra-rojo');
    }
}

// Generar hallazgos automáticamente
function generarHallazgos() {
    const hallazgos = [];
    const categorias = [1, 2, 3];
    const nombresCategorias = {
        1: 'Políticas institucionales de seguridad y protección de datos personales',
        2: 'Sistema de acceso biométrico (ciberseguridad, flujo y gestión de la información)',
        3: 'Actores que forman parte del sistema (consumidores y operarios)'
    };
    
    categorias.forEach(cat => {
        const preguntas = document.querySelectorAll(`#categoria-${cat} .pregunta-item`);
        preguntas.forEach((pregunta, index) => {
            const estado = pregunta.querySelector('.select-estado').value;
            const texto = pregunta.querySelector('.pregunta-texto').textContent.trim();
            const observacion = pregunta.querySelector('.input-observacion').value;
            
            if (estado === 'Cumple parcialmente' || estado === 'No cumple') {
                hallazgos.push({
                    categoria: cat,
                    pregunta: index + 1,
                    texto: texto,
                    estado: estado,
                    observacion: observacion,
                    nombreCategoria: nombresCategorias[cat]
                });
            }
        });
    });
    
    const container = document.getElementById('hallazgos-container');
    container.innerHTML = '';
    
    if (hallazgos.length === 0) {
        container.innerHTML = `
            <div style="background:#ECFDF5;padding:20px;border-radius:12px;border-left:4px solid #10B981;display:flex;align-items:center;gap:12px;">
                <span style="font-size:2rem;">✅</span>
                <div>
                    <strong style="color:#065F46;">¡Excelente!</strong>
                    <p style="color:#065F46;margin:0;">No se encontraron hallazgos significativos. Todos los requisitos cumplen totalmente.</p>
                </div>
            </div>
        `;
        return;
    }
    
    hallazgos.forEach((h, i) => {
        const div = document.createElement('div');
        div.className = 'hallazgo-item';
        div.innerHTML = `
            <div class="hallazgo-categoria">📌 ${h.nombreCategoria} - Pregunta ${h.pregunta}</div>
            <p><strong>${h.texto}</strong></p>
            <p>Estado: <span class="${h.estado === 'Cumple parcialmente' ? 'estado-parcial' : 'estado-no-cumple'}">${h.estado}</span></p>
            <p style="color:#666;font-size:0.9rem;">📝 Evidencia: ${h.observacion || 'No se registró evidencia'}</p>
            <input type="hidden" name="hallazgo_${i}" value="${h.texto}|${h.estado}|${h.observacion}">
        `;
        container.appendChild(div);
    });
}

// =============================================
// GENERAR PDF CORREGIDO (CON DOM PDF MEJORADO)
// =============================================
function generarReporte() {
    const datos = {
        institucion: document.getElementById('nombre_institucion').value,
        ruc: document.getElementById('ruc').value || 'No registrado',
        sistema: document.getElementById('nombre_sistema').value,
        fecha: document.getElementById('fecha_evaluacion').value,
        evaluador: document.getElementById('evaluador').value,
        cat1: document.getElementById('porcentaje-cat1').textContent,
        cat2: document.getElementById('porcentaje-cat2').textContent,
        cat3: document.getElementById('porcentaje-cat3').textContent,
        conclusiones: document.getElementById('conclusiones').value || 'No se registraron conclusiones',
        recomendaciones: document.getElementById('recomendaciones').value || 'No se registraron recomendaciones'
    };
    
    // Obtener hallazgos
    const hallazgosItems = document.querySelectorAll('#hallazgos-container .hallazgo-item');
    datos.hallazgos = Array.from(hallazgosItems).map(el => el.textContent.trim());
    
    // Mostrar loading
    const btn = document.querySelector('[onclick="generarReporte()"]');
    const textoOriginal = btn.textContent;
    btn.textContent = '⏳ Generando PDF...';
    btn.disabled = true;
    
    // Enviar al servidor para generar PDF
    fetch('generar_reporte.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(datos)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en el servidor');
        }
        return response.blob();
    })
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'informe_evaluacion_lopdp.pdf';
        document.body.appendChild(a);
        a.click();
        a.remove();
        window.URL.revokeObjectURL(url);
        
        btn.textContent = textoOriginal;
        btn.disabled = false;
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Error al generar el PDF. Verifica que el servidor esté funcionando.');
        btn.textContent = textoOriginal;
        btn.disabled = false;
    });
}

// =============================================
// INICIALIZAR
// =============================================
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.input-porcentaje, .select-estado, .input-observacion').forEach(el => {
        el.addEventListener('change', actualizarDashboard);
        el.addEventListener('input', actualizarDashboard);
    });
    
    document.querySelectorAll('.pregunta-item').forEach((item) => {
        const texto = item.querySelector('.pregunta-texto').textContent.trim();
        const catContainer = item.closest('[id^="categoria-"]');
        if (catContainer) {
            const catId = catContainer.id.split('-')[1];
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = `cat${catId}_texto_${Array.from(catContainer.querySelectorAll('.pregunta-item')).indexOf(item) + 1}`;
            hidden.value = texto;
            item.appendChild(hidden);
        }
    });
    
    setTimeout(actualizarDashboard, 300);
});

// Exportar funciones
window.mostrarTab = mostrarTab;
window.actualizarDashboard = actualizarDashboard;
window.generarHallazgos = generarHallazgos;
window.generarReporte = generarReporte;
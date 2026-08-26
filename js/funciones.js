const NOMBRES_CATEGORIAS = {
    1: 'Políticas institucionales',
    2: 'Sistema biométrico',
    3: 'Actores del sistema'
};

const FACTORES_CUMPLIMIENTO = {
    'Cumple totalmente': 1,
    'Cumple parcialmente': 0.5,
    'No cumple': 0,
    'No aplica': null
};

function mostrarTab(tabId) {
    document.querySelectorAll('.tab-contenido').forEach(el => el.classList.remove('activo'));
    document.querySelectorAll('.tab').forEach(el => el.classList.remove('activo'));

    const contenido = document.getElementById(tabId);
    const tab = document.querySelector(`.tab[data-tab="${tabId}"]`);
    if (contenido) contenido.classList.add('activo');
    if (tab) tab.classList.add('activo');

    if (tabId === 'tab-dashboard') actualizarDashboard();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function calcularCategoria(categoriaId) {
    const preguntas = [...document.querySelectorAll(`#categoria-${categoriaId} .pregunta-item`)];
    let pesoAplicable = 0;
    let logrado = 0;
    const estados = {
        'Cumple totalmente': 0,
        'Cumple parcialmente': 0,
        'No cumple': 0,
        'No aplica': 0
    };

    preguntas.forEach(pregunta => {
        const peso = Math.max(0, Number.parseFloat(pregunta.querySelector('.input-porcentaje')?.value) || 0);
        const estado = pregunta.querySelector('.select-estado')?.value || 'No cumple';
        if (Object.prototype.hasOwnProperty.call(estados, estado)) estados[estado]++;

        const factor = FACTORES_CUMPLIMIENTO[estado];
        if (factor === null || factor === undefined) return;
        pesoAplicable += peso;
        logrado += peso * factor;
    });

    const porcentaje = pesoAplicable > 0 ? (logrado / pesoAplicable) * 100 : 0;
    return {
        categoriaId,
        porcentaje: Math.round(porcentaje * 100) / 100,
        totalPreguntas: preguntas.length,
        pesoAplicable,
        logrado,
        estados
    };
}

function obtenerMetricas() {
    const categorias = [1, 2, 3].map(calcularCategoria);
    const aplicables = categorias.filter(c => c.pesoAplicable > 0);
    const promedio = aplicables.length
        ? aplicables.reduce((sum, c) => sum + c.porcentaje, 0) / aplicables.length
        : 0;

    const estados = {
        'Cumple totalmente': 0,
        'Cumple parcialmente': 0,
        'No cumple': 0,
        'No aplica': 0
    };

    categorias.forEach(cat => {
        Object.keys(estados).forEach(estado => {
            estados[estado] += cat.estados[estado];
        });
    });

    return {
        categorias,
        promedio: Math.round(promedio * 100) / 100,
        totalPreguntas: categorias.reduce((sum, c) => sum + c.totalPreguntas, 0),
        estados
    };
}

function formatoPorcentaje(valor) {
    return `${Number(valor).toLocaleString('es-EC', { maximumFractionDigits: 2 })}%`;
}

function actualizarDashboard() {
    const metricas = obtenerMetricas();

    metricas.categorias.forEach(cat => {
        const id = cat.categoriaId;
        setTexto(`porcentaje-cat${id}`, formatoPorcentaje(cat.porcentaje));
        setAncho(`barra-cat${id}`, cat.porcentaje);
        setTexto(`detalle-porcentaje-cat${id}`, formatoPorcentaje(cat.porcentaje));
        setTexto(`barra-detalle-cat${id}`, formatoPorcentaje(cat.porcentaje));
        setAncho(`barra-detalle-cat${id}`, cat.porcentaje);
        actualizarColor(`cat${id}`, cat.porcentaje);
    });

    setTexto('promedio-general', formatoPorcentaje(metricas.promedio));
    setTexto('total-preguntas', metricas.totalPreguntas);
    setTexto('totales-cumple', metricas.estados['Cumple totalmente']);
    setTexto('totales-parcial', metricas.estados['Cumple parcialmente']);
    setTexto('totales-no-cumple', metricas.estados['No cumple']);
    setTexto('totales-no-aplica', metricas.estados['No aplica']);
    setTexto('nivel-general', clasificarNivel(metricas.promedio));

    actualizarDonut(metricas.estados);
    actualizarTotalesPonderacion();
}

function actualizarDonut(estados) {
    const donut = document.getElementById('donut-estados');
    if (!donut) return;

    const valores = [
        estados['Cumple totalmente'],
        estados['Cumple parcialmente'],
        estados['No cumple'],
        estados['No aplica']
    ];
    const total = valores.reduce((a, b) => a + b, 0);
    setTexto('donut-total', total);

    if (total === 0) {
        donut.style.background = '#e2e8f0';
        return;
    }

    const porcentajes = valores.map(v => (v / total) * 100);
    const p1 = porcentajes[0];
    const p2 = p1 + porcentajes[1];
    const p3 = p2 + porcentajes[2];
    donut.style.background = `conic-gradient(
        #16a34a 0 ${p1}%,
        #d97706 ${p1}% ${p2}%,
        #dc2626 ${p2}% ${p3}%,
        #94a3b8 ${p3}% 100%
    )`;
}

function actualizarColor(categoria, valor) {
    const elemento = document.getElementById(`porcentaje-${categoria}`);
    if (!elemento) return;
    elemento.classList.remove('color-verde', 'color-amarillo', 'color-rojo');
    elemento.classList.add(valor >= 80 ? 'color-verde' : valor >= 50 ? 'color-amarillo' : 'color-rojo');
}

function clasificarNivel(valor) {
    if (valor >= 80) return 'Alto';
    if (valor >= 50) return 'Medio';
    return 'Bajo';
}

function setTexto(id, valor) {
    const el = document.getElementById(id);
    if (el) el.textContent = valor;
}

function setAncho(id, valor) {
    const el = document.getElementById(id);
    if (el) el.style.width = `${Math.max(0, Math.min(100, Number(valor) || 0))}%`;
}

function actualizarTotalesPonderacion() {
    [1, 2, 3].forEach(cat => {
        const total = [...document.querySelectorAll(`#categoria-${cat} .input-porcentaje`)]
            .reduce((sum, input) => sum + (Number.parseFloat(input.value) || 0), 0);
        const el = document.getElementById(`peso-total-cat${cat}`);
        if (!el) return;
        el.textContent = `${total.toFixed(2)}%`;
        el.classList.toggle('peso-alerta', Math.abs(total - 100) > 0.05);
    });
}

function generarHallazgos() {
    const container = document.getElementById('hallazgos-container');
    if (!container) return;
    container.replaceChildren();

    let encontrados = 0;
    [1, 2, 3].forEach(cat => {
        const preguntas = [...document.querySelectorAll(`#categoria-${cat} .pregunta-item`)];
        preguntas.forEach((pregunta, index) => {
            const estado = pregunta.querySelector('.select-estado')?.value;
            if (estado !== 'Cumple parcialmente' && estado !== 'No cumple') return;

            encontrados++;
            const texto = pregunta.querySelector('.pregunta-texto')?.textContent.trim() || `Pregunta ${index + 1}`;
            const observacion = pregunta.querySelector('.input-observacion')?.value.trim() || 'No se registró evidencia.';

            const item = document.createElement('div');
            item.className = `hallazgo-item ${estado === 'No cumple' ? 'hallazgo-critico' : ''}`;

            const cabecera = document.createElement('div');
            cabecera.className = 'hallazgo-categoria';
            cabecera.textContent = `${NOMBRES_CATEGORIAS[cat]} · Pregunta ${index + 1}`;

            const pTexto = document.createElement('p');
            const strong = document.createElement('strong');
            strong.textContent = texto;
            pTexto.appendChild(strong);

            const pEstado = document.createElement('p');
            pEstado.textContent = `Estado: ${estado}`;

            const pObs = document.createElement('p');
            pObs.className = 'hallazgo-evidencia';
            pObs.textContent = `Evidencia/observación: ${observacion}`;

            item.append(cabecera, pTexto, pEstado, pObs);
            container.appendChild(item);
        });
    });

    if (encontrados === 0) {
        const ok = document.createElement('div');
        ok.className = 'mensaje-exito';
        ok.textContent = 'No se identificaron hallazgos de incumplimiento o cumplimiento parcial.';
        container.appendChild(ok);
    }
}

function prepararTextosOcultos() {
    document.querySelectorAll('.pregunta-item').forEach(item => {
        if (item.querySelector('input[data-pregunta-texto]')) return;
        const catContainer = item.closest('[id^="categoria-"]');
        if (!catContainer) return;
        const catId = catContainer.id.split('-')[1];
        const index = [...catContainer.querySelectorAll('.pregunta-item')].indexOf(item) + 1;
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.dataset.preguntaTexto = '1';
        hidden.name = `cat${catId}_texto_${index}`;
        hidden.value = item.querySelector('.pregunta-texto')?.textContent.trim() || '';
        item.appendChild(hidden);
    });
}

function validarPonderacionesAntesDeGuardar(event) {
    const incorrectas = [];
    [1, 2, 3].forEach(cat => {
        const total = [...document.querySelectorAll(`#categoria-${cat} .input-porcentaje`)]
            .reduce((sum, input) => sum + (Number.parseFloat(input.value) || 0), 0);
        if (Math.abs(total - 100) > 0.05) incorrectas.push(`Categoría ${cat}: ${total.toFixed(2)}%`);
    });

    if (incorrectas.length) {
        event.preventDefault();
        alert(`Antes de guardar, la ponderación de cada categoría debe sumar 100%.\n\n${incorrectas.join('\n')}`);
        return false;
    }
    return true;
}

document.addEventListener('DOMContentLoaded', () => {
    prepararTextosOcultos();
    document.querySelectorAll('.input-porcentaje, .select-estado, .input-observacion').forEach(el => {
        el.addEventListener('change', actualizarDashboard);
        el.addEventListener('input', actualizarDashboard);
    });

    const form = document.getElementById('form-evaluacion');
    if (form) form.addEventListener('submit', validarPonderacionesAntesDeGuardar);

    actualizarDashboard();
});

window.mostrarTab = mostrarTab;
window.actualizarDashboard = actualizarDashboard;
window.generarHallazgos = generarHallazgos;

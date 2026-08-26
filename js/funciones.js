const STEP_ORDER = ['tab-general','tab-cat1','tab-cat2','tab-cat3','tab-hallazgos','tab-cierre','tab-dashboard'];
const STEP_TITLES = {
  'tab-general':'Información general',
  'tab-cat1':'Categoría 1 · Políticas institucionales',
  'tab-cat2':'Categoría 2 · Sistema biométrico',
  'tab-cat3':'Categoría 3 · Actores del sistema',
  'tab-hallazgos':'Hallazgos identificados',
  'tab-cierre':'Conclusiones y recomendaciones',
  'tab-dashboard':'Dashboard de resultados'
};
const NOMBRES_CATEGORIAS = {1:'Políticas institucionales',2:'Sistema biométrico',3:'Actores del sistema'};
const COLORES_CATEGORIA = {1:'#2563eb',2:'#16a34a',3:'#7c3aed'};
const FACTORES_CUMPLIMIENTO = {'Cumple totalmente':1,'Cumple parcialmente':0.5,'No cumple':0,'No aplica':null};
let maxStepUnlocked = 1;
let currentTab = 'tab-general';
let previousSimulationTab = 'tab-general';

function mostrarTab(tabId, force = false) {
  if (tabId === 'tab-acerca') {
    previousSimulationTab = currentTab;
  } else {
    const idx = STEP_ORDER.indexOf(tabId);
    if (!force && idx >= 0 && idx + 1 > maxStepUnlocked) return;
    if (idx >= 0) currentTab = tabId;
  }

  document.querySelectorAll('.tab-contenido').forEach(el => el.classList.remove('activo'));
  const contenido = document.getElementById(tabId);
  if (contenido) contenido.classList.add('activo');

  document.querySelectorAll('.step-tab').forEach(btn => {
    const step = Number(btn.dataset.step || 0);
    btn.classList.toggle('activo', btn.dataset.tab === tabId);
    btn.classList.toggle('completado', step > 0 && step < (STEP_ORDER.indexOf(currentTab) + 1));
    btn.classList.toggle('bloqueado', step > maxStepUnlocked);
    btn.disabled = step > maxStepUnlocked;
  });

  if (tabId !== 'tab-acerca') actualizarIndicadorPaso(tabId);
  if (tabId === 'tab-hallazgos') generarHallazgos();
  actualizarDashboard();
  window.scrollTo({top:0,behavior:'smooth'});
}

function volverPasoActual(){ mostrarTab(previousSimulationTab || currentTab, true); }

function desbloquearHasta(tabId) {
  const idx = STEP_ORDER.indexOf(tabId);
  if (idx >= 0) maxStepUnlocked = Math.max(maxStepUnlocked, idx + 1);
}

function avanzarPaso(actual, siguiente) {
  if (!validarPaso(actual)) return false;
  desbloquearHasta(siguiente);
  mostrarTab(siguiente, true);
  return true;
}

function validarPaso(tabId) {
  if (tabId === 'tab-general') {
    const campos = ['nombre_institucion','nombre_sistema','fecha_evaluacion','evaluador'];
    for (const id of campos) {
      const el = document.getElementById(id);
      if (!el || !String(el.value).trim()) {
        if (el) { el.focus(); el.classList.add('campo-error'); }
        alert('Complete los campos obligatorios antes de continuar.');
        return false;
      }
      el.classList.remove('campo-error');
    }
    return true;
  }

  const match = tabId.match(/^tab-cat([123])$/);
  if (match) {
    const cat = Number(match[1]);
    const selects = [...document.querySelectorAll(`#categoria-${cat} .select-estado`)];
    const faltantes = selects.filter(s => !s.value);
    if (faltantes.length) {
      faltantes[0].focus();
      faltantes[0].classList.add('campo-error');
      alert(`Faltan ${faltantes.length} respuesta(s) en la Categoría ${cat}.`);
      return false;
    }
    const total = [...document.querySelectorAll(`#categoria-${cat} .input-porcentaje`)]
      .reduce((sum,input)=>sum+(Number.parseFloat(input.value)||0),0);
    if (Math.abs(total - 100) > 0.05) {
      alert(`La ponderación de la Categoría ${cat} debe sumar 100%. Actualmente suma ${total.toFixed(2)}%.`);
      return false;
    }
  }
  return true;
}

function actualizarIndicadorPaso(tabId) {
  const idx = STEP_ORDER.indexOf(tabId);
  if (idx < 0) return;
  const step = idx + 1;
  const porcentaje = (step / STEP_ORDER.length) * 100;
  setTexto('paso-actual-label', `Paso ${step} de ${STEP_ORDER.length}`);
  setTexto('paso-actual-titulo', STEP_TITLES[tabId]);
  setTexto('avance-pasos-num', Math.round(porcentaje));
  setAncho('avance-pasos-bar', porcentaje);
}

function calcularCategoria(categoriaId) {
  const preguntas = [...document.querySelectorAll(`#categoria-${categoriaId} .pregunta-item`)];
  let pesoAplicable = 0;
  let logrado = 0;
  let respondidas = 0;
  const estados = {'Cumple totalmente':0,'Cumple parcialmente':0,'No cumple':0,'No aplica':0};

  preguntas.forEach(pregunta => {
    const peso = Math.max(0, Number.parseFloat(pregunta.querySelector('.input-porcentaje')?.value) || 0);
    const estado = pregunta.querySelector('.select-estado')?.value || '';
    if (!estado) return;
    respondidas++;
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
    respondidas,
    pesoAplicable,
    logrado,
    estados
  };
}

function obtenerMetricas() {
  const categorias = [1,2,3].map(calcularCategoria);
  const aplicables = categorias.filter(c => c.respondidas > 0 && c.pesoAplicable > 0);
  const promedio = aplicables.length ? aplicables.reduce((s,c)=>s+c.porcentaje,0)/aplicables.length : 0;
  const estados = {'Cumple totalmente':0,'Cumple parcialmente':0,'No cumple':0,'No aplica':0};
  categorias.forEach(cat => Object.keys(estados).forEach(e => estados[e] += cat.estados[e]));
  return {
    categorias,
    promedio: Math.round(promedio * 100) / 100,
    totalPreguntas: categorias.reduce((s,c)=>s+c.totalPreguntas,0),
    respondidas: categorias.reduce((s,c)=>s+c.respondidas,0),
    estados
  };
}

function formatoPorcentaje(valor){ return `${Number(valor).toLocaleString('es-EC',{maximumFractionDigits:2})}%`; }

function actualizarDashboard() {
  const metricas = obtenerMetricas();
  metricas.categorias.forEach(cat => {
    const id = cat.categoriaId;
    const value = formatoPorcentaje(cat.porcentaje);
    setTexto(`ring-value-cat${id}`, value);
    setTexto(`respondidas-cat${id}`, cat.respondidas);
    setAncho(`avance-cat${id}`, cat.totalPreguntas ? (cat.respondidas/cat.totalPreguntas)*100 : 0);
    setRing(`ring-cat${id}`, cat.porcentaje, COLORES_CATEGORIA[id]);
    setTexto(`porcentaje-cat${id}`, value);
    setTexto(`detalle-porcentaje-cat${id}`, value);
    setAncho(`barra-detalle-cat${id}`, cat.porcentaje);
    setRing(`dash-ring-cat${id}`, cat.porcentaje, COLORES_CATEGORIA[id]);
  });

  setTexto('promedio-general', formatoPorcentaje(metricas.promedio));
  setTexto('nivel-general', metricas.respondidas === 0 ? 'Sin evaluar' : `Nivel ${clasificarNivel(metricas.promedio)}`);
  setRing('dash-ring-general', metricas.promedio, '#06b6d4');
  setTexto('totales-cumple', metricas.estados['Cumple totalmente']);
  setTexto('totales-parcial', metricas.estados['Cumple parcialmente']);
  setTexto('totales-no-cumple', metricas.estados['No cumple']);
  setTexto('totales-no-aplica', metricas.estados['No aplica']);
  actualizarDonut(metricas.estados);
  actualizarTotalesPonderacion();
}

function setRing(id, value, color) {
  const el = document.getElementById(id);
  if (!el) return;
  const safe = Math.max(0, Math.min(100, Number(value)||0));
  el.style.setProperty('--score', safe);
  el.style.setProperty('--ring', color);
  el.style.background = `conic-gradient(${color} 0 ${safe}%, #e5e7eb ${safe}% 100%)`;
}

function actualizarDonut(estados) {
  const donut = document.getElementById('donut-estados');
  if (!donut) return;
  const valores = [estados['Cumple totalmente'],estados['Cumple parcialmente'],estados['No cumple'],estados['No aplica']];
  const total = valores.reduce((a,b)=>a+b,0);
  setTexto('donut-total', total);
  if (!total) { donut.style.background = '#e5e7eb'; return; }
  const pct = valores.map(v => (v/total)*100);
  const p1=pct[0], p2=p1+pct[1], p3=p2+pct[2];
  donut.style.background = `conic-gradient(#16a34a 0 ${p1}%,#f59e0b ${p1}% ${p2}%,#ef4444 ${p2}% ${p3}%,#94a3b8 ${p3}% 100%)`;
}

function clasificarNivel(valor){ if(valor>=80)return 'Alto'; if(valor>=50)return 'Medio'; return 'Bajo'; }
function setTexto(id,valor){ const el=document.getElementById(id); if(el) el.textContent=valor; }
function setAncho(id,valor){ const el=document.getElementById(id); if(el) el.style.width=`${Math.max(0,Math.min(100,Number(valor)||0))}%`; }

function actualizarTotalesPonderacion(){
  [1,2,3].forEach(cat => {
    const total=[...document.querySelectorAll(`#categoria-${cat} .input-porcentaje`)].reduce((s,i)=>s+(Number.parseFloat(i.value)||0),0);
    const el=document.getElementById(`peso-total-cat${cat}`);
    if(!el)return;
    el.textContent=`${total.toFixed(2)}%`;
    el.classList.toggle('peso-alerta',Math.abs(total-100)>0.05);
  });
}

function generarHallazgos(){
  const container=document.getElementById('hallazgos-container');
  if(!container)return;
  container.replaceChildren();
  let encontrados=0;
  [1,2,3].forEach(cat => {
    [...document.querySelectorAll(`#categoria-${cat} .pregunta-item`)].forEach((pregunta,index)=>{
      const estado=pregunta.querySelector('.select-estado')?.value;
      if(estado!=='Cumple parcialmente'&&estado!=='No cumple')return;
      encontrados++;
      const item=document.createElement('article');
      item.className=`hallazgo-item ${estado==='No cumple'?'hallazgo-critico':''}`;
      const head=document.createElement('div'); head.className='hallazgo-categoria'; head.textContent=`${NOMBRES_CATEGORIAS[cat]} · Pregunta ${index+1}`;
      const req=document.createElement('p'); const strong=document.createElement('strong'); strong.textContent=pregunta.querySelector('.pregunta-texto')?.textContent.trim()||''; req.appendChild(strong);
      const est=document.createElement('p'); est.textContent=`Estado: ${estado}`;
      const obs=document.createElement('p'); obs.className='hallazgo-evidencia'; obs.textContent=`Evidencia/observación: ${pregunta.querySelector('.input-observacion')?.value.trim() || 'No se registró evidencia.'}`;
      item.append(head,req,est,obs); container.appendChild(item);
    });
  });
  if(!encontrados){ const ok=document.createElement('div'); ok.className='mensaje-exito'; ok.textContent='No se identificaron hallazgos de incumplimiento o cumplimiento parcial.'; container.appendChild(ok); }
}

function prepararTextosOcultos(){
  document.querySelectorAll('.pregunta-item').forEach(item=>{
    if(item.querySelector('input[data-pregunta-texto]'))return;
    const catContainer=item.closest('[id^="categoria-"]'); if(!catContainer)return;
    const catId=catContainer.id.split('-')[1];
    const index=[...catContainer.querySelectorAll('.pregunta-item')].indexOf(item)+1;
    const hidden=document.createElement('input'); hidden.type='hidden'; hidden.dataset.preguntaTexto='1'; hidden.name=`cat${catId}_texto_${index}`; hidden.value=item.querySelector('.pregunta-texto')?.textContent.trim()||''; item.appendChild(hidden);
  });
}

function validarFormularioFinal(event){
  for(const tab of ['tab-general','tab-cat1','tab-cat2','tab-cat3']){
    if(!validarPaso(tab)){ event.preventDefault(); desbloquearHasta(tab); mostrarTab(tab,true); return false; }
  }
  return true;
}

document.addEventListener('DOMContentLoaded',()=>{
  prepararTextosOcultos();
  document.querySelectorAll('.input-porcentaje,.select-estado,.input-observacion').forEach(el=>{
    el.addEventListener('change',()=>{el.classList.remove('campo-error');actualizarDashboard();});
    el.addEventListener('input',actualizarDashboard);
  });
  document.querySelectorAll('.step-tab').forEach(btn=>btn.addEventListener('click',()=>mostrarTab(btn.dataset.tab)));
  const help=document.querySelector('.step-help'); if(help) help.addEventListener('click',()=>mostrarTab('tab-acerca'));
  const form=document.getElementById('form-evaluacion'); if(form) form.addEventListener('submit',validarFormularioFinal);
  actualizarDashboard(); actualizarIndicadorPaso('tab-general'); mostrarTab('tab-general',true);
});

window.mostrarTab=mostrarTab;
window.avanzarPaso=avanzarPaso;
window.volverPasoActual=volverPasoActual;
window.actualizarDashboard=actualizarDashboard;
window.generarHallazgos=generarHallazgos;

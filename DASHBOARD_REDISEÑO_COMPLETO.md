# REDISEÑO COMPLETO DEL DASHBOARD
## Sistema de Seguimiento Contractual - Gobernación de Caldas

**Versión:** 2.0
**Fecha:** Marzo 2026
**Arquitecto:** Senior Software Architect
**Estado:** Fase 2 - Rediseño Dashboard

---

## 📊 ANÁLISIS DEL DASHBOARD ACTUAL

### ✅ Funcionalidades Existentes
- Motor BI-style con drag & drop básico
- Asignaciones por rol, secretaría, unidad, usuario
- Widgets KPI y Chart (9 métricas disponibles)
- Scopes de datos (usuario, unidad, secretaría, global)
- Historial de cambios (auditoría)
- Búsqueda de usuarios
- Backend robusto con validaciones

### ❌ Limitaciones Identificadas
1. **UI/UX:** No totalmente responsivo, interfaz técnica
2. **Filtros:** Solo búsqueda de texto, sin filtros temporales
3. **Widgets:** Solo KPI y Charts básicos, sin tablas ni widgets avanzados
4. **Templates:** No hay plantillas predefinidas por rol
5. **Herencia:** Sistema funcional pero no visual
6. **Drag & Drop:** Básico, no permite grid layout
7. **Tiempo Real:** Sin actualizaciones automáticas
8. **Exportación:** No disponible

---

## 🎯 DISEÑO DE VISTAS POR ROL

### 👑 VISTA EJECUTIVA - GOBERNADOR

**Objetivo:** Supervisión estratégica y toma de decisiones

**Dashboard Template: "Ejecutivo Global"**

```javascript
const DashboardGobernador = {
  layout: {
    tipo: 'executive',
    columnas: 4,
    espaciado: 'amplio',
    tema: 'verde-institucional'
  },

  widgets: [
    // Primera fila: KPIs estratégicos
    {
      id: 'kpi-presupuesto-total',
      tipo: 'kpi',
      metrica: 'presupuesto_total_ejecutado',
      titulo: 'Presupuesto Total Ejecutado',
      columnas: 1,
      formato: 'pesos',
      icono: 'currency-dollar',
      comparativo: 'mes_anterior',
      objetivo: '95%'
    },
    {
      id: 'kpi-procesos-activos',
      tipo: 'kpi',
      metrica: 'procesos_en_curso_global',
      titulo: 'Procesos Contractuales Activos',
      columnas: 1,
      icono: 'document-text',
      tendencia: 'trimestre'
    },
    {
      id: 'kpi-contratos-vigentes',
      tipo: 'kpi',
      metrica: 'contratos_vigentes_valor',
      titulo: 'Valor Contratos Vigentes',
      columnas: 1,
      formato: 'pesos',
      icono: 'briefcase'
    },
    {
      id: 'kpi-eficiencia',
      tipo: 'kpi',
      metrica: 'eficiencia_promedio_secretarias',
      titulo: 'Eficiencia Promedio',
      columnas: 1,
      formato: 'porcentaje',
      icono: 'chart-bar',
      meta: '90%'
    },

    // Segunda fila: Charts estratégicos
    {
      id: 'chart-secretarias',
      tipo: 'chart',
      subtipo: 'bar-horizontal',
      metrica: 'presupuesto_por_secretaria',
      titulo: 'Ejecución Presupuestal por Secretaría',
      columnas: 2,
      opciones: {
        comparativo: true,
        periodo: 'año_anterior',
        top_n: 10
      }
    },
    {
      id: 'chart-evolucion',
      tipo: 'chart',
      subtipo: 'line',
      metrica: 'evolucion_proceso_mes',
      titulo: 'Evolución de Procesos (12 meses)',
      columnas: 2,
      opciones: {
        prediccion: true,
        bandas_confianza: true
      }
    },

    // Tercera fila: Monitoring y alertas
    {
      id: 'mapa-calor',
      tipo: 'heatmap',
      metrica: 'procesos_por_secretaria_mes',
      titulo: 'Mapa de Calor - Actividad por Secretaría',
      columnas: 2,
      opciones: {
        granularidad: 'semanal'
      }
    },
    {
      id: 'alertas-ejecutivas',
      tipo: 'alertas',
      metrica: 'alertas_alta_prioridad',
      titulo: 'Alertas Críticas',
      columnas: 2,
      filtros: {
        prioridad: ['critica', 'alta'],
        antiguedad: '48_horas'
      }
    }
  ],

  filtros: {
    periodo: {
      tipo: 'daterange',
      default: 'ultimo_trimestre',
      opciones: ['ultimo_mes', 'ultimo_trimestre', 'ultimo_año', 'personalizado']
    },
    secretaria: {
      tipo: 'multiselect',
      default: 'todas'
    },
    tipo_proceso: {
      tipo: 'dropdown',
      default: 'todos'
    }
  },

  actualizacion: {
    frecuencia: '30_minutos',
    tiempo_real: ['alertas-ejecutivas']
  }
}
```

### 📋 VISTA SECRETARIAL - SECRETARIOS DE DESPACHO

**Objetivo:** Supervisión operativa de la secretaría

**Dashboard Template: "Operativo Secretaría"**

```javascript
const DashboardSecretario = {
  layout: {
    tipo: 'operational',
    columnas: 3,
    espaciado: 'normal',
    tema: 'azul-operativo'
  },

  widgets: [
    // KPIs de secretaría
    {
      id: 'kpi-procesos-secretaria',
      tipo: 'kpi',
      metrica: 'procesos_en_curso_secretaria',
      titulo: 'Mis Procesos en Curso',
      columnas: 1,
      scope: 'secretaria'
    },
    {
      id: 'kpi-tiempo-promedio',
      tipo: 'kpi',
      metrica: 'tiempo_promedio_tramite',
      titulo: 'Tiempo Promedio de Trámite',
      columnas: 1,
      formato: 'dias',
      scope: 'secretaria'
    },
    {
      id: 'kpi-pendientes-firma',
      tipo: 'kpi',
      metrica: 'documentos_pendientes_firma',
      titulo: 'Pendientes de Firma',
      columnas: 1,
      prioridad: 'alta',
      scope: 'secretaria'
    },

    // Charts operativos
    {
      id: 'chart-unidades',
      tipo: 'chart',
      subtipo: 'doughnut',
      metrica: 'procesos_por_unidad',
      titulo: 'Distribución por Unidad',
      columnas: 1,
      scope: 'secretaria'
    },
    {
      id: 'chart-estados',
      tipo: 'chart',
      subtipo: 'bar',
      metrica: 'procesos_por_estado',
      titulo: 'Estados de Procesos',
      columnas: 2,
      scope: 'secretaria'
    },

    // Tabla de procesos críticos
    {
      id: 'tabla-criticos',
      tipo: 'table',
      metrica: 'procesos_criticos_secretaria',
      titulo: 'Procesos Críticos Requieren Atención',
      columnas: 3,
      opciones: {
        paginacion: true,
        ordenamiento: true,
        acciones: ['ver', 'editar', 'priorizar']
      },
      scope: 'secretaria'
    }
  ],

  filtros: {
    unidad: {
      tipo: 'multiselect',
      default: 'todas',
      scope: 'secretaria'
    },
    estado: {
      tipo: 'checkbox_group',
      default: ['en_curso', 'pausado', 'observaciones']
    },
    periodo: {
      tipo: 'daterange',
      default: 'ultimo_mes'
    }
  }
}
```

### 👥 VISTA OPERATIVA - JEFES DE UNIDAD

**Objetivo:** Gestión directa del equipo y procesos asignados

**Dashboard Template: "Gestión de Unidad"**

```javascript
const DashboardJefeUnidad = {
  layout: {
    tipo: 'management',
    columnas: 4,
    espaciado: 'compacto',
    tema: 'verde-gestion'
  },

  widgets: [
    // KPIs de unidad
    {
      id: 'kpi-team-workload',
      tipo: 'kpi',
      metrica: 'carga_trabajo_equipo',
      titulo: 'Carga de Trabajo del Equipo',
      columnas: 1,
      formato: 'porcentaje',
      scope: 'unidad'
    },
    {
      id: 'kpi-procesos-asignados',
      tipo: 'kpi',
      metrica: 'procesos_asignados_unidad',
      titulo: 'Procesos Asignados',
      columnas: 1,
      scope: 'unidad'
    },
    {
      id: 'kpi-tiempo-respuesta',
      tipo: 'kpi',
      metrica: 'tiempo_respuesta_promedio',
      titulo: 'Tiempo de Respuesta Promedio',
      columnas: 1,
      formato: 'horas',
      scope: 'unidad'
    },
    {
      id: 'kpi-documentos-pendientes',
      tipo: 'kpi',
      metrica: 'documentos_pendientes_unidad',
      titulo: 'Documentos Pendientes',
      columnas: 1,
      scope: 'unidad'
    },

    // Gestión de equipo
    {
      id: 'chart-equipo-performance',
      tipo: 'chart',
      subtipo: 'radar',
      metrica: 'performance_por_usuario',
      titulo: 'Performance del Equipo',
      columnas: 2,
      scope: 'unidad'
    },

    // Timeline de procesos
    {
      id: 'timeline-procesos',
      tipo: 'timeline',
      metrica: 'cronologia_procesos_unidad',
      titulo: 'Timeline de Procesos',
      columnas: 2,
      opciones: {
        vista: 'semanal',
        interactivo: true
      },
      scope: 'unidad'
    },

    // Tabla de asignaciones
    {
      id: 'tabla-asignaciones',
      tipo: 'table',
      metrica: 'asignaciones_equipo',
      titulo: 'Asignaciones del Equipo',
      columnas: 4,
      opciones: {
        agrupable: true,
        filtrable: true,
        acciones: ['reasignar', 'priorizar', 'comentar']
      },
      scope: 'unidad'
    }
  ],

  filtros: {
    usuario: {
      tipo: 'multiselect',
      default: 'todos',
      scope: 'unidad'
    },
    prioridad: {
      tipo: 'radio',
      opciones: ['todas', 'alta', 'media', 'baja'],
      default: 'todas'
    }
  }
}
```

---

## 🔄 SISTEMA DE HERENCIA MEJORADO

### Jerarquía Visual de Herencia

```
┌─────────────────┐
│   PLANTILLA     │
│     GLOBAL      │ ← Base institucional
│   (Gobernador)  │
└─────────┬───────┘
          │
┌─────────▼───────┐
│   PLANTILLA     │
│   SECRETARÍA    │ ← Personalización por secretaría
│                 │
└─────────┬───────┘
          │
┌─────────▼───────┐
│   PLANTILLA     │
│    UNIDAD       │ ← Especialización por unidad
│                 │
└─────────┬───────┘
          │
┌─────────▼───────┐
│   CONFIGURACIÓN │
│     USUARIO     │ ← Ajustes personales
│                 │
└─────────────────┘
```

### Algoritmo de Resolución

```javascript
const resolverDashboard = (usuario) => {
  let dashboard = {};

  // 1. Cargar plantilla global (base)
  const plantillaGlobal = getPlatillaGlobal();
  dashboard = { ...plantillaGlobal };

  // 2. Aplicar configuración de rol
  const configRole = getConfigByRole(usuario.roles[0]);
  if (configRole) {
    dashboard = mergeConfigs(dashboard, configRole);
  }

  // 3. Aplicar configuración de secretaría
  const configSecretaria = getConfigBySecretaria(usuario.secretaria_id);
  if (configSecretaria) {
    dashboard = mergeConfigs(dashboard, configSecretaria);
  }

  // 4. Aplicar configuración de unidad
  const configUnidad = getConfigByUnidad(usuario.unidad_id);
  if (configUnidad) {
    dashboard = mergeConfigs(dashboard, configUnidad);
  }

  // 5. Aplicar configuración personal (mayor prioridad)
  const configUsuario = getConfigByUsuario(usuario.id);
  if (configUsuario) {
    dashboard = mergeConfigs(dashboard, configUsuario);
  }

  // 6. Aplicar filtros de permisos
  dashboard = filterByPermissions(dashboard, usuario);

  return dashboard;
}
```

---

## 🎛️ FILTROS DINÁMICOS AVANZADOS

### Sistema de Filtros Inteligentes

```javascript
const FiltrosAvanzados = {
  // Filtros temporales
  temporal: {
    periodo_predefinido: {
      tipo: 'dropdown',
      opciones: [
        'hoy', 'ayear', 'ultima_semana', 'ultimo_mes',
        'ultimo_trimestre', 'ultimo_año', 'personalizado'
      ]
    },
    rango_personalizado: {
      tipo: 'daterange',
      formato: 'DD/MM/YYYY',
      max_rango: '2_años'
    },
    comparacion: {
      tipo: 'toggle',
      opciones: ['periodo_anterior', 'año_anterior']
    }
  },

  // Filtros organizacionales
  organizacional: {
    secretaria: {
      tipo: 'hierarchical_multiselect',
      jerarquia: 'secretaria > unidad > usuario',
      busqueda: true
    },
    rol: {
      tipo: 'multiselect_chips',
      agrupacion: 'categoria'
    },
    estado_proceso: {
      tipo: 'button_group',
      multiple: true
    }
  },

  // Filtros financieros
  financiero: {
    rango_valor: {
      tipo: 'range_slider',
      formato: 'pesos',
      min: 0,
      max: 'auto_calculated'
    },
    fuente_presupuestal: {
      tipo: 'dropdown',
      searchable: true
    }
  },

  // Filtros contextuales (por widget)
  contextual: {
    prioridad: {
      aplicable_a: ['alertas', 'procesos_criticos'],
      tipo: 'checkbox_group'
    },
    completitud: {
      aplicable_a: ['procesos_en_curso'],
      tipo: 'range_slider',
      formato: 'porcentaje'
    }
  }
}
```

### UI de Filtros Responsiva

```jsx
const FiltrosPanel = ({ filtros, onUpdate, isMobile }) => {
  const [filtrosAbiertos, setFiltrosAbiertos] = useState(false);

  return (
    <div className={`filtros-panel ${isMobile ? 'mobile' : 'desktop'}`}>
      {/* Filtros rápidos siempre visibles */}
      <div className="filtros-rapidos">
        <DateRangePicker
          value={filtros.periodo}
          onChange={v => onUpdate('periodo', v)}
          presets={['ultimo_mes', 'ultimo_trimestre']}
        />

        <SecretariaSelector
          value={filtros.secretaria}
          onChange={v => onUpdate('secretaria', v)}
          multiple={true}
        />
      </div>

      {/* Filtros avanzados colapsables */}
      {isMobile ? (
        <Drawer open={filtrosAbiertos}>
          <FiltrosAvanzadosContent filtros={filtros} onUpdate={onUpdate} />
        </Drawer>
      ) : (
        <Collapsible open={filtrosAbiertos}>
          <FiltrosAvanzadosContent filtros={filtros} onUpdate={onUpdate} />
        </Collapsible>
      )}

      <button onClick={() => setFiltrosAbiertos(!filtrosAbiertos)}>
        Filtros Avanzados {filtrosAbiertos ? '−' : '+'}
      </button>
    </div>
  );
}
```

---

## 🎨 MEJORAS EN DRAG & DROP

### Sistema Grid Responsivo Avanzado

```javascript
const DragDropGrid = {
  // Configuración de grilla
  config: {
    breakpoints: {
      mobile: { cols: 1, maxWidth: 768 },
      tablet: { cols: 2, maxWidth: 1024 },
      desktop: { cols: 3, maxWidth: 1440 },
      widescreen: { cols: 4, maxWidth: Infinity }
    },

    rowHeight: 120,
    margin: [16, 16],
    padding: [16, 16],

    collision: 'compact',
    resizable: true,
    draggable: true
  },

  // Restricciones por tipo de widget
  constraints: {
    kpi: {
      minW: 1, maxW: 2,
      minH: 1, maxH: 2
    },
    chart: {
      minW: 2, maxW: 4,
      minH: 2, maxH: 4
    },
    table: {
      minW: 3, maxW: 4,
      minH: 3, maxH: 6
    },
    timeline: {
      minW: 3, maxW: 4,
      minH: 2, maxH: 3
    }
  },

  // Auto-layout inteligente
  autoLayout: {
    kpis_arriba: true,
    charts_centro: true,
    tables_abajo: true,
    preservar_grupos: true
  }
}
```

### Componente Drag & Drop Mejorado

```jsx
import { Responsive, WidthProvider } from 'react-grid-layout';
const ResponsiveGridLayout = WidthProvider(Responsive);

const DashboardGrid = ({ widgets, onLayoutChange, isEditing }) => {
  const [layouts, setLayouts] = useState({});

  const handleLayoutChange = (currentLayout, allLayouts) => {
    setLayouts(allLayouts);
    onLayoutChange(allLayouts);
  };

  const renderWidget = (widget) => {
    return (
      <div key={widget.id} className="widget-container">
        <WidgetHeader
          title={widget.titulo}
          editable={isEditing}
          onEdit={() => openWidgetConfig(widget)}
          onDelete={() => deleteWidget(widget.id)}
        />
        <WidgetContent widget={widget} />
        {isEditing && <ResizeHandle />}
      </div>
    );
  };

  return (
    <ResponsiveGridLayout
      className="dashboard-grid"
      layouts={layouts}
      onLayoutChange={handleLayoutChange}
      breakpoints={{ lg: 1200, md: 996, sm: 768, xs: 480, xxs: 0 }}
      cols={{ lg: 4, md: 3, sm: 2, xs: 1, xxs: 1 }}
      rowHeight={120}
      isDraggable={isEditing}
      isResizable={isEditing}
      margin={[16, 16]}
    >
      {widgets.map(renderWidget)}
    </ResponsiveGridLayout>
  );
};
```

---

## 🧩 NUEVOS TIPOS DE WIDGETS

### 1. Widget Tabla Dinámica

```jsx
const TableWidget = ({ metrica, configuracion, filtros }) => {
  const [data, setData] = useState([]);
  const [ordenamiento, setOrdenamiento] = useState({});
  const [paginacion, setPaginacion] = useState({ pagina: 1, tamaño: 10 });

  const columnas = [
    { key: 'proceso_id', titulo: 'ID Proceso', sortable: true },
    { key: 'nombre', titulo: 'Nombre', sortable: true, searchable: true },
    { key: 'estado', titulo: 'Estado', filterable: true },
    { key: 'fecha_inicio', titulo: 'Fecha Inicio', sortable: true, tipo: 'fecha' },
    { key: 'valor', titulo: 'Valor', sortable: true, tipo: 'pesos' },
    { key: 'acciones', titulo: 'Acciones', tipo: 'acciones' }
  ];

  const acciones = [
    { key: 'ver', titulo: 'Ver Detalle', icono: 'eye', url: '/procesos/{id}' },
    { key: 'editar', titulo: 'Editar', icono: 'pencil', permiso: 'procesos.edit' },
    { key: 'comentario', titulo: 'Comentar', icono: 'comment', modal: true }
  ];

  return (
    <DataTable
      columnas={columnas}
      data={data}
      acciones={acciones}
      ordenamiento={ordenamiento}
      paginacion={paginacion}
      filtros={filtros}
      exportable={true}
      seleccionMultiple={true}
      onRowSelect={handleRowSelect}
      onExport={handleExport}
    />
  );
};
```

### 2. Widget Timeline Interactivo

```jsx
const TimelineWidget = ({ metrica, configuracion }) => {
  const [eventos, setEventos] = useState([]);
  const [vista, setVista] = useState('month'); // day, week, month, year
  const [fechaActual, setFechaActual] = useState(new Date());

  const tiposEvento = {
    inicio_proceso: { color: '#10b981', icono: 'play' },
    aprobacion: { color: '#3b82f6', icono: 'check' },
    observacion: { color: '#f59e0b', icono: 'exclamation' },
    finalizacion: { color: '#6366f1', icono: 'flag' }
  };

  return (
    <Timeline
      eventos={eventos}
      vista={vista}
      fechaActual={fechaActual}
      tiposEvento={tiposEvento}
      interactivo={true}
      onEventClick={handleEventClick}
      onDateChange={setFechaActual}
      onViewChange={setVista}
      zoom={true}
      filtros={['tipo_evento', 'responsable', 'prioridad']}
    />
  );
};
```

### 3. Widget Mapa de Calor

```jsx
const HeatmapWidget = ({ metrica, configuracion, filtros }) => {
  const [data, setData] = useState([]);
  const [escalaTiempo, setEscalaTiempo] = useState('week');

  const dimensiones = {
    x: 'fecha', // Temporal
    y: 'secretaria', // Categórica
    valor: 'cantidad_procesos' // Numérica
  };

  return (
    <div className="heatmap-widget">
      <HeatmapControls
        escalaTiempo={escalaTiempo}
        onEscalaChange={setEscalaTiempo}
      />

      <HeatmapViz
        data={data}
        dimensiones={dimensiones}
        colorScale={['#f0f9ff', '#0369a1']}
        tooltips={true}
        onCellClick={handleCellClick}
        responsive={true}
      />

      <HeatmapLegend />
    </div>
  );
};
```

### 4. Widget Alertas Inteligentes

```jsx
const AlertasWidget = ({ configuracion, tiempo_real = false }) => {
  const [alertas, setAlertas] = useState([]);
  const [filtrosPrioridad, setFiltrosPrioridad] = useState(['alta', 'critica']);

  useEffect(() => {
    if (tiempo_real) {
      const channel = pusher.subscribe('alertas-tiempo-real');
      channel.bind('nueva-alerta', handleNuevaAlerta);

      return () => {
        pusher.unsubscribe('alertas-tiempo-real');
      };
    }
  }, [tiempo_real]);

  const tiposAlerta = {
    critica: { color: '#dc2626', icono: 'exclamation-triangle' },
    alta: { color: '#ea580c', icono: 'exclamation' },
    media: { color: '#ca8a04', icono: 'information-circle' },
    baja: { color: '#16a34a', icono: 'check-circle' }
  };

  return (
    <AlertasList
      alertas={alertas}
      tiposAlerta={tiposAlerta}
      tiempoReal={tiempo_real}
      filtros={filtrosPrioridad}
      agrupable={true}
      acciones={['marcar_leida', 'asignar', 'escalar']}
      onAction={handleAction}
      maxVisible={10}
    />
  );
};
```

---

## 📱 DISEÑO RESPONSIVO COMPLETO

### Breakpoints y Adaptaciones

```css
/* Mobile First Approach */
.dashboard-container {
  /* Mobile (< 768px) */
  @media (max-width: 767px) {
    padding: 8px;

    .widget {
      margin-bottom: 12px;
      border-radius: 8px;
    }

    .filtros-panel {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      background: white;
      box-shadow: 0 -4px 12px rgba(0,0,0,0.1);
    }

    .charts {
      height: 200px; /* Más compacto en móvil */
    }
  }

  /* Tablet (768px - 1023px) */
  @media (min-width: 768px) and (max-width: 1023px) {
    padding: 16px;

    .dashboard-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  /* Desktop (1024px+) */
  @media (min-width: 1024px) {
    padding: 24px;

    .dashboard-grid {
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    }

    .sidebar {
      position: sticky;
      top: 20px;
    }
  }
}
```

### Componentes Responsivos

```jsx
const ResponsiveDashboard = () => {
  const { width } = useWindowSize();
  const isMobile = width < 768;
  const isTablet = width >= 768 && width < 1024;
  const isDesktop = width >= 1024;

  const layoutConfig = {
    mobile: { cols: 1, sidebar: 'drawer' },
    tablet: { cols: 2, sidebar: 'collapsible' },
    desktop: { cols: 3, sidebar: 'fixed' }
  };

  const currentConfig = isMobile ? layoutConfig.mobile
                      : isTablet ? layoutConfig.tablet
                      : layoutConfig.desktop;

  return (
    <DashboardLayout config={currentConfig}>
      <DashboardHeader mobile={isMobile} />

      <DashboardFilters
        mode={currentConfig.sidebar}
        filters={filters}
        onFilterChange={handleFilterChange}
      />

      <DashboardGrid
        columns={currentConfig.cols}
        widgets={widgets}
        responsive={true}
      />
    </DashboardLayout>
  );
};
```

---

## 🔄 SISTEMA DE TIEMPO REAL

### WebSocket Integration

```javascript
const TiempoRealManager = {
  connection: null,
  subscriptions: new Map(),

  init() {
    if (window.Echo) {
      this.connection = window.Echo;
      this.setupChannels();
    }
  },

  setupChannels() {
    // Canal global para alertas críticas
    this.connection.channel('alertas-globales')
      .listen('AlertaCriticaCreada', (e) => {
        this.notifyWidgets('alertas', e.data);
      });

    // Canal por usuario para notificaciones personales
    this.connection.private(`user.${userId}`)
      .listen('ProcesoAsignado', (e) => {
        this.notifyWidgets('procesos_asignados', e.data);
      });

    // Canal por secretaría
    this.connection.join(`secretaria.${secretariaId}`)
      .here((users) => {
        console.log('Usuarios conectados:', users);
      })
      .joining((user) => {
        console.log(`${user.name} se conectó`);
      })
      .leaving((user) => {
        console.log(`${user.name} se desconectó`);
      })
      .listen('ProcesoActualizado', (e) => {
        this.notifyWidgets('procesos_secretaria', e.data);
      });
  },

  subscribe(widgetId, channel, callback) {
    if (!this.subscriptions.has(channel)) {
      this.subscriptions.set(channel, new Set());
    }
    this.subscriptions.get(channel).add({ widgetId, callback });
  },

  notifyWidgets(channel, data) {
    const widgets = this.subscriptions.get(channel);
    if (widgets) {
      widgets.forEach(({ callback }) => callback(data));
    }
  }
};
```

---

## 📊 IMPLEMENTACIÓN TÉCNICA

### Nuevos Archivos a Crear

```
/resources/js/
├── dashboard-v2/
│   ├── DashboardMotorV2.jsx          # Componente principal
│   ├── components/
│   │   ├── widgets/
│   │   │   ├── KpiWidget.jsx
│   │   │   ├── ChartWidget.jsx
│   │   │   ├── TableWidget.jsx
│   │   │   ├── TimelineWidget.jsx
│   │   │   ├── HeatmapWidget.jsx
│   │   │   └── AlertasWidget.jsx
│   │   ├── filters/
│   │   │   ├── FiltrosPanel.jsx
│   │   │   ├── DateRangePicker.jsx
│   │   │   ├── SecretariaSelector.jsx
│   │   │   └── MultiSelectChips.jsx
│   │   ├── grid/
│   │   │   ├── DashboardGrid.jsx
│   │   │   ├── WidgetContainer.jsx
│   │   │   └── ResponsiveLayout.jsx
│   │   └── templates/
│   │       ├── EjecutivoTemplate.jsx
│   │       ├── SecretarialTemplate.jsx
│   │       └── OperativoTemplate.jsx
│   ├── hooks/
│   │   ├── useDashboardData.js
│   │   ├── useRealtimeUpdates.js
│   │   ├── useResponsiveLayout.js
│   │   └── useWidgetConfig.js
│   ├── services/
│   │   ├── DashboardAPI.js
│   │   ├── DataFormatter.js
│   │   └── ExportService.js
│   └── utils/
│       ├── layoutAlgorithm.js
│       ├── widgetRenderer.js
│       └── permissionChecker.js
```

### Controlador Mejorado

```php
<?php
// app/Http/Controllers/DashboardV2Controller.php

class DashboardV2Controller extends Controller
{
    public function index(Request $request)
    {
        $usuario = auth()->user();

        // Resolver dashboard usando nuevo algoritmo de herencia
        $dashboard = app(DashboardResolverService::class)
            ->resolverPorUsuario($usuario);

        // Obtener plantillas disponibles por rol
        $plantillasDisponibles = app(PlantillaService::class)
            ->getPorRoles($usuario->roles->pluck('name'));

        // Obtener datos para widgets
        $widgetData = app(WidgetDataService::class)
            ->getDataPorUsuario($usuario, $request->get('filtros', []));

        return view('dashboards.v2.index', compact(
            'dashboard',
            'plantillasDisponibles',
            'widgetData'
        ));
    }

    public function getWidgetData(Request $request, $widgetId)
    {
        $widget = DashboardWidget::findOrFail($widgetId);

        // Validar permisos
        $this->authorize('view', $widget);

        $filtros = $request->validate([
            'periodo' => 'nullable|array',
            'secretaria_id' => 'nullable|array',
            'unidad_id' => 'nullable|array',
            'estado' => 'nullable|array'
        ]);

        $data = app(WidgetDataService::class)
            ->getDataPorWidget($widget, $filtros, auth()->user());

        return response()->json([
            'data' => $data,
            'meta' => [
                'widget_id' => $widgetId,
                'ultima_actualizacion' => now(),
                'cache_ttl' => 300 // 5 minutos
            ]
        ]);
    }

    public function exportDashboard(Request $request)
    {
        $formato = $request->get('formato', 'pdf'); // pdf, excel, png

        $dashboard = app(DashboardResolverService::class)
            ->resolverPorUsuario(auth()->user());

        return app(DashboardExportService::class)
            ->export($dashboard, $formato, $request->get('filtros', []));
    }
}
```

### Servicios de Negocio

```php
<?php
// app/Services/DashboardResolverService.php

class DashboardResolverService
{
    public function resolverPorUsuario(User $usuario): array
    {
        // 1. Plantilla base por rol principal
        $config = $this->getConfigBase($usuario->roles->first());

        // 2. Sobrescribir con config de secretaría
        if ($usuario->secretaria_id) {
            $configSecretaria = $this->getConfigSecretaria($usuario->secretaria_id);
            $config = $this->mergeConfigs($config, $configSecretaria);
        }

        // 3. Sobrescribir con config de unidad
        if ($usuario->unidad_id) {
            $configUnidad = $this->getConfigUnidad($usuario->unidad_id);
            $config = $this->mergeConfigs($config, $configUnidad);
        }

        // 4. Sobrescribir con config personal
        $configPersonal = $this->getConfigUsuario($usuario->id);
        $config = $this->mergeConfigs($config, $configPersonal);

        // 5. Filtrar widgets por permisos
        $config = $this->filtrarPorPermisos($config, $usuario);

        return $config;
    }

    private function mergeConfigs(array $base, ?array $override): array
    {
        if (!$override) return $base;

        // Merge inteligente preservando orden y prioridades
        $widgets = collect($base['widgets'] ?? [])
            ->merge(collect($override['widgets'] ?? []))
            ->unique('id')
            ->sortBy('orden')
            ->values()
            ->toArray();

        return array_merge($base, $override, ['widgets' => $widgets]);
    }
}
```

---

## 🎯 BENEFICIOS DEL REDISEÑO

### Para Usuarios Finales

1. **Experiencia Mejorada**
   - Dashboards específicos por rol
   - Interfaz intuitiva y responsiva
   - Filtros inteligentes y contextuales

2. **Productividad Aumentada**
   - Widgets de tabla para acciones directas
   - Timeline interactivo para seguimiento
   - Actualizaciones en tiempo real

3. **Flexibilidad Total**
   - Personalización completa per usuario
   - Templates predefinidos pero customizables
   - Drag & drop avanzado

### Para Administradores

1. **Gestión Simplificada**
   - Templates reutilizables por rol
   - Sistema de herencia visual
   - Auditoría completa de cambios

2. **Performance Optimizada**
   - Cache inteligente
   - Datos bajo demanda
   - Consultas optimizadas

3. **Escalabilidad**
   - Nuevos widgets extensibles
   - APIs bien definidas
   - Arquitectura modular

---

**Documento técnico completado**
**Próxima fase:** Implementación de mejoras identificadas

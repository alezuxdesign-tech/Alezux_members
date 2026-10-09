# Crezca (v2.0.0)

Plugin modular integral para gestión de academias, membresías, cursos en línea, estudiantes, finanzas, marketing y control de accesos, con **Panel de Administración Moderno impulsado por la librería Arc UI ([uiarc.dev](https://uiarc.dev/))**.

---

## 🚀 Instalación en WordPress (Sin Comprimir)

Para instalarlo en tu WordPress sin necesidad de archivos ZIP ni conflictos con versiones anteriores:

1. Copia o mueve directamente la carpeta del plugin a tu directorio de plugins de WordPress:
   ```text
   wp-content/plugins/crezca/
   ```
2. Entra a tu panel de **WordPress > Plugins Instalados**.
3. Verás listado **Crezca**. Haz clic en **Activar**.
4. En el menú lateral izquierdo aparecerá la sección **Crezca** con el nuevo Dashboard interactivo de Arc UI.

> [!NOTE]
> El plugin ha sido renombrado a **Crezca** (`crezca.php`, Text Domain `crezca`, namespace `crezca/v1` y slug de menú `crezca`), por lo que no colisiona ni genera conflictos con instalaciones previas de otros plugins o `Members-Beta`.

---

## 💎 Novedades de la Versión 2.0.0

- **Integración de Arc UI (Free Tier)**:
  - Sistema de diseño de tokens semánticos (`foundation.css`) con soporte para Modo Oscuro (`data-theme="dark"` / `"light"`) y acentos de color (`violet`, `blue`, `green`, `amber`, `coral`, `neutral`).
  - Animaciones y microinteracciones fluidas basadas en Motion (`motion/react` y `motion-tokens.ts`).
  - Componentes nativos de Arc: `Button`, `MetricCard`, `AnimatedCounter`, `SegmentedControl`, `Badge`, `Modal/Dialog`.
- **Dashboard Centralizado para el Administrador**:
  - **Métricas & KPIs en tiempo real**: Contador dinámico de estudiantes totales, cursos activos, facturación mensual estimada y la clase/lección más popular.
  - **Gráfico de Flujo de Estudiantes**: Visualización interactiva de actividad semanal/mensual.
  - **Constructor de Cursos Drag & Drop**: Diseña y organiza módulos, lecciones y temas arrastrando elementos en un árbol visual.
  - **Gestión de Estudiantes & Accesos**: Buscador en tiempo real y panel de activación/desactivación de cursos por estudiante con interruptores instantáneos.
  - **Módulo de Finanzas & Links de Pago**: Creación de planes de cuotas recurrentes o pago único, con generación de links protegidos por token listos para compartir (`?crezca_action=checkout&token=...`).
  - **Módulo de Marketing**: Configuración de correos automáticos por eventos (bienvenida, cuotas pendientes, curso terminado) con editor de plantillas.
- **Arquitectura Modular "Lego-Style"**:
  - Se recopilaron y organizaron todos los 14 módulos: `config`, `estudiantes`, `finanzas`, `formaciones`, `lesson-navigator`, `listing`, `logros`, `marketing`, `menu-admin`, `notifications`, `proyectos-agencia`, `slide-lesson`, `smtp` y `demo-block`.

---

## 📁 Estructura del Proyecto

```text
crezca/
├── crezca.php                    # Entrada principal del plugin de WordPress
├── admin-app/                    # Aplicación Frontend React + Vite con Arc UI
│   ├── src/
│   │   ├── components/arc/       # Componentes y tokens de la librería Arc UI
│   │   │   ├── foundation.css    # Tokens CSS globales de Arc (Colores, superficies, radios)
│   │   │   ├── motion-tokens.ts  # Presets de animación de Arc
│   │   │   ├── button/           # Botón con estados morph y loader
│   │   │   ├── metric-card/      # Tarjeta de KPI interactiva
│   │   │   ├── animated-counter/ # Odómetro animado de números
│   │   │   ├── segmented-control/# Selector de pestañas con pill deslizante
│   │   │   └── modal/            # Diálogo emergente accesible
│   │   ├── views/                # Vistas principales del Dashboard
│   │   │   ├── OverviewView.tsx  # Resumen, KPIs y Flujo de Estudiantes
│   │   │   ├── CoursesView.tsx   # Creación de Cursos y Constructor Drag & Drop
│   │   │   ├── StudentsView.tsx  # Listado y Control de Cursos Habilitados
│   │   │   ├── FinanceView.tsx   # Planes de Pago y Generador de Enlaces
│   │   │   ├── MarketingView.tsx # Automatizaciones de Correo
│   │   │   └── SettingsView.tsx  # Tokens, temas y claves de Stripe
│   │   ├── services/api.ts       # Conexión con REST API y datos de respaldo
│   │   ├── App.tsx               # Shell principal de la aplicación
│   │   └── main.tsx              # Punto de montaje (crezca-admin-root)
│   ├── package.json
│   └── vite.config.ts
├── assets/
│   ├── dist/                     # Bundle compilado de React (HTML, CSS y JS)
│   │   ├── alezux-dashboard.js
│   │   ├── alezux-dashboard.css
│   │   └── index.html            # Previsualización directa en el navegador
│   └── css/global.css            # Estilos globales para WordPress
├── core/
│   ├── Admin_Api.php             # Endpoints REST (/wp-json/crezca/v1/...)
│   ├── Admin_Dashboard.php       # Encolador de assets y menú de administración (slug: crezca)
│   ├── Elementor_Widget_Base.php # Base para widgets de Elementor
│   ├── Module_Base.php           # Base abstracta para módulos Lego
│   └── Plugin_Loader.php         # Descubrimiento dinámico de módulos
├── modules/                      # 14 Módulos independientes
└── views/
    └── admin/dashboard.php       # Contenedor raíz donde monta la aplicación
```

---

## 🛠️ Comandos de Desarrollo del Dashboard

Para modificar o ampliar la aplicación de administración construida con Arc UI:

```bash
# Entrar al directorio del frontend
cd admin-app

# Ejecutar el servidor de desarrollo local con Hot Reload
npm run dev

# Compilar para producción (actualiza automáticamente assets/dist/)
npm run build
```

---

## 🌐 Endpoints REST API de WordPress

Todos los endpoints están protegidos por permisos de administrador (`manage_options`) y nonces (`wp_rest`):

- `GET /wp-json/crezca/v1/stats`: Métricas de estudiantes, cursos activos, facturación y flujo.
- `GET /wp-json/crezca/v1/courses`: Listado de formaciones y currículum.
- `POST /wp-json/crezca/v1/courses/{id}/curriculum`: Guarda la estructura reordenada por Drag & Drop.
- `GET /wp-json/crezca/v1/students`: Listado de alumnos con sus cursos habilitados.
- `POST /wp-json/crezca/v1/students/{id}/course-access`: Activa/desactiva acceso a formaciones.
- `GET /wp-json/crezca/v1/finance/plans`: Lista de planes y cuotas.
- `POST /wp-json/crezca/v1/finance/create-plan`: Crea un plan y genera su link de pago.

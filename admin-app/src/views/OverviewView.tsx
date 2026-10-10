import { useEffect, useState, useMemo } from "react";
import { 
  Users, 
  GraduationCap, 
  DollarSign, 
  Award, 
  PlayCircle, 
  Eye, 
  CheckCircle2, 
  TrendingUp, 
  ArrowUpRight,
  Sparkles,
  CreditCard,
  UserPlus,
  SlidersHorizontal
} from "lucide-react";
import { MetricCard } from "../components/arc/metric-card/metric-card";
import { Badge } from "../components/arc/badge/badge";
import { SegmentedControl } from "../components/arc/segmented-control/segmented-control";
import { LineChart, LineChartPoint } from "../components/arc/line-chart/line-chart";
import { api, DashboardStats, FlowDataPoint } from "../services/api";
import { ModuleSkeleton } from "../components/arc/skeleton";
import styles from "./OverviewView.module.css";

interface OverviewViewProps {
  onNavigate: (tab: string) => void;
}

type RangeType = "7d" | "30d" | "90d";

export function OverviewView({ onNavigate }: OverviewViewProps) {
  const [stats, setStats] = useState<DashboardStats | null>(null);
  const [activeRange, setActiveRange] = useState<RangeType>("7d");

  useEffect(() => {
    api.getDashboardStats()
      .then(setStats)
      .catch((err) => console.error("Error al cargar estadísticas del Dashboard:", err));
  }, []);

  // Puntos sin procesar para el rango seleccionado
  const rawPoints: FlowDataPoint[] = useMemo(() => {
    if (!stats) return [];
    if (stats.flowRanges && stats.flowRanges[activeRange]) {
      return stats.flowRanges[activeRange];
    }
    return stats.studentFlow || [];
  }, [stats, activeRange]);

  // Puntos calculados exclusivamente para la Facturación en el LineChart
  const chartPoints: LineChartPoint[] = useMemo(() => {
    return rawPoints.map((pt) => ({
      label: pt.label,
      value: pt.revenue ?? Math.round(pt.students * 12.5),
      secondaryValue: pt.students,
      detail: pt.detail || pt.label,
    }));
  }, [rawPoints]);

  // Cálculos estadísticos rápidos de la facturación en el período seleccionado
  const summaryMetrics = useMemo(() => {
    if (chartPoints.length === 0) return { total: 0, avg: 0, peakLabel: "-", peakVal: 0 };
    const total = chartPoints.reduce((acc, p) => acc + p.value, 0);
    const avg = Math.round(total / chartPoints.length);
    const peak = chartPoints.reduce((prev, curr) => (curr.value > prev.value ? curr : prev), chartPoints[0]);

    return {
      total,
      avg,
      peakLabel: peak?.label || "-",
      peakVal: peak?.value || 0,
    };
  }, [chartPoints]);

  if (!stats) {
    return <ModuleSkeleton type="overview" />;
  }

  return (
    <div className={styles.container}>
      {/* Encabezado limpio estilo Arc PageHeader sin botones redundantes */}
      <div className={styles.pageHeader}>
        <div>
          <h1 className={styles.pageTitle}>Dashboard</h1>
          <p className={styles.pageDescription}>
            Métricas de rendimiento operativo, alumnos activos y facturación en tiempo real.
          </p>
        </div>
      </div>

      {/* Grid de KPIs principales con Arc MetricCard */}
      <div className={styles.metricsGrid}>
        <MetricCard
          label="Estudiantes Inscritos"
          value={stats.totalStudents}
          change={stats.totalStudentsChange}
          context="Alumnos registrados con acceso a la plataforma"
          icon={<Users size={16} />}
        />
        <MetricCard
          label="Cursos Activos"
          value={stats.activeCourses}
          change={stats.activeCoursesChange}
          context="Formaciones publicadas disponibles para alumnos"
          icon={<GraduationCap size={16} />}
        />
        <MetricCard
          label="Facturación del Mes"
          value={stats.monthlyRevenue}
          prefix="$"
          suffix="USD"
          decimals={2}
          change={stats.monthlyRevenueChange}
          context="Cobros de cuotas y membresías recurrentes"
          icon={<DollarSign size={16} />}
        />
        <MetricCard
          label="Vistas Lección Popular"
          value={stats.topClass.views}
          change={stats.topClass.completionRate}
          context={`"${stats.topClass.title.slice(0, 32)}..."`}
          icon={<Award size={16} />}
        />
      </div>

      {/* Grilla Principal: Gráfico de Facturación & Tarjetas Complementarias */}
      <div className={styles.mainGrid}>
        {/* Bloque de Gráfico Line Chart: Exclusivamente Facturación */}
        <section className={styles.cardSection}>
          <div className={styles.cardHeader}>
            <div className={styles.chartHeaderTitles}>
              <div className={styles.chartTitleRow}>
                <DollarSign size={18} className={styles.chartIcon} />
                <h2 className={styles.cardTitle}>Facturación</h2>
              </div>
              <p className={styles.cardSubtitle}>
                Ingresos por ventas de cursos y cobros de cuotas periódicas
              </p>
            </div>

            <div className={styles.controlsGroup}>
              {/* Selector de Rango de Tiempo */}
              <SegmentedControl
                size="sm"
                options={[
                  { value: "7d", label: "7 días" },
                  { value: "30d", label: "30 días" },
                  { value: "90d", label: "90 días" },
                ]}
                value={activeRange}
                onChange={(val) => setActiveRange(val as RangeType)}
              />
            </div>
          </div>

          {/* Gráfico de Línea Arc LineChart */}
          <div className={styles.chartWrapper}>
            <LineChart
              data={chartPoints}
              valuePrefix="$"
              valueSuffix=" USD"
              height={260}
              metricName="Facturación"
              secondaryMetricName="Estudiantes activos"
            />
          </div>

          {/* Ribbon de Resumen de Facturación del Período */}
          <div className={styles.summaryRibbon}>
            <div className={styles.summaryItem}>
              <span className={styles.summaryLabel}>Total en período</span>
              <span className={styles.summaryValue}>
                ${summaryMetrics.total.toLocaleString()} <small>USD</small>
              </span>
            </div>
            <div className={styles.summaryItem}>
              <span className={styles.summaryLabel}>Promedio / día</span>
              <span className={styles.summaryValue}>
                ${summaryMetrics.avg.toLocaleString()} <small>USD</small>
              </span>
            </div>
            <div className={styles.summaryItem}>
              <span className={styles.summaryLabel}>Pico más alto</span>
              <span className={styles.summaryValue}>
                {summaryMetrics.peakLabel}: ${summaryMetrics.peakVal.toLocaleString()}
              </span>
            </div>
            <div className={styles.summaryTrend}>
              <TrendingUp size={15} className={styles.trendIcon} />
              <span>+24.5% tendencia alcista</span>
            </div>
          </div>
        </section>

        {/* Columna Derecha: Lección Más Popular & Accesos Rápidos */}
        <div className={styles.sideColumn}>
          {/* Bloque de Clase / Lección Más Popular */}
          <section className={styles.cardSection}>
            <div className={styles.cardHeader}>
              <div>
                <h2 className={styles.cardTitle}>Lección Más Popular</h2>
                <p className={styles.cardSubtitle}>Mayor retención e impacto entre tus estudiantes</p>
              </div>
              <Badge variant="accent">Top #1</Badge>
            </div>

            <div className={styles.topClassCard}>
              <div className={styles.topClassIconWrapper}>
                <PlayCircle size={28} className={styles.playIcon} />
              </div>

              <div className={styles.topClassInfo}>
                <span className={styles.topCourseTitle}>{stats.topClass.courseTitle}</span>
                <h3 className={styles.topClassTitle}>{stats.topClass.title}</h3>

                <div className={styles.topClassMetrics}>
                  <div className={styles.classStat}>
                    <Eye size={14} />
                    <span>{stats.topClass.views.toLocaleString()} Reproducciones</span>
                  </div>
                  <div className={styles.classStat}>
                    <CheckCircle2 size={14} />
                    <span>{stats.topClass.completions.toLocaleString()} Finalizados ({stats.topClass.completionRate})</span>
                  </div>
                </div>
              </div>
            </div>

            <div className={styles.nextActionsBox}>
              <h4 className={styles.actionBoxTitle}>Acciones rápidas del curso</h4>
              <div className={styles.actionList}>
                <button 
                  type="button" 
                  className={styles.actionItemBtn}
                  onClick={() => onNavigate("students")}
                >
                  <span>Revisar alumnos con este curso habilitado</span>
                  <ArrowUpRight size={14} />
                </button>
                <button 
                  type="button" 
                  className={styles.actionItemBtn}
                  onClick={() => onNavigate("marketing")}
                >
                  <span>Configurar automatización por finalización</span>
                  <ArrowUpRight size={14} />
                </button>
              </div>
            </div>
          </section>

          {/* Accesos Rápidos de Plataforma */}
          <section className={styles.shortcutsCard}>
            <div className={styles.shortcutsHeader}>
              <Sparkles size={16} className={styles.shortcutIcon} />
              <h3 className={styles.shortcutsTitle}>Atajos y Gestión</h3>
            </div>
            <div className={styles.shortcutsGrid}>
              <button 
                type="button" 
                className={styles.shortcutTile}
                onClick={() => onNavigate("finance")}
              >
                <CreditCard size={18} />
                <div className={styles.shortcutInfo}>
                  <span className={styles.shortcutLabel}>Cobros & Cuotas</span>
                  <span className={styles.shortcutSub}>Ver transacciones</span>
                </div>
              </button>

              <button 
                type="button" 
                className={styles.shortcutTile}
                onClick={() => onNavigate("students")}
              >
                <UserPlus size={18} />
                <div className={styles.shortcutInfo}>
                  <span className={styles.shortcutLabel}>Alta de Alumno</span>
                  <span className={styles.shortcutSub}>Registrar nuevo</span>
                </div>
              </button>

              <button 
                type="button" 
                className={styles.shortcutTile}
                onClick={() => onNavigate("settings")}
              >
                <SlidersHorizontal size={18} />
                <div className={styles.shortcutInfo}>
                  <span className={styles.shortcutLabel}>Configuración</span>
                  <span className={styles.shortcutSub}>Logo y colores</span>
                </div>
              </button>
            </div>
          </section>
        </div>
      </div>
    </div>
  );
}

export default OverviewView;

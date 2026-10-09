import { useEffect, useState } from "react";
import { Users, GraduationCap, DollarSign, Award, PlayCircle, Eye, CheckCircle2, TrendingUp, ArrowUpRight } from "lucide-react";
import { MetricCard } from "../components/arc/metric-card/metric-card";
import { Button } from "../components/arc/button/button";
import { Badge } from "../components/arc/badge/badge";
import { SegmentedControl } from "../components/arc/segmented-control/segmented-control";
import { api, DashboardStats } from "../services/api";
import { ModuleSkeleton } from "../components/arc/skeleton";
import styles from "./OverviewView.module.css";

interface OverviewViewProps {
  onNavigate: (tab: string) => void;
}

export function OverviewView({ onNavigate }: OverviewViewProps) {
  const [stats, setStats] = useState<DashboardStats | null>(null);
  const [activeRange, setActiveRange] = useState<string>("7d");

  useEffect(() => {
    api.getDashboardStats().then(setStats);
  }, []);

  if (!stats) {
    return <ModuleSkeleton type="overview" />;
  }

  return (
    <div className={styles.container}>
      {/* Encabezado limpio estilo Arc PageHeader */}
      <div className={styles.pageHeader}>
        <div>
          <h1 className={styles.pageTitle}>Resumen de la Academia</h1>
          <p className={styles.pageDescription}>
            Métricas de rendimiento operativo, alumnos activos y facturación en tiempo real.
          </p>
        </div>
        <div className={styles.pageActions}>
          <Button variant="secondary" size="md" onClick={() => onNavigate("finance")}>
            Generar Enlace de Pago
          </Button>
          <Button variant="primary" size="md" onClick={() => onNavigate("courses")}>
            + Nuevo Curso
          </Button>
        </div>
      </div>

      {/* Grid de KPIs con Arc MetricCard */}
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
          context="Tasa de finalización promedio en el campus"
          icon={<Award size={16} />}
        />
      </div>

      {/* Grilla Principal: Flujo de Actividad & Lección Popular */}
      <div className={styles.mainGrid}>
        {/* Bloque de Flujo de Actividad */}
        <section className={styles.cardSection}>
          <div className={styles.cardHeader}>
            <div>
              <h2 className={styles.cardTitle}>Flujo de Actividad</h2>
              <p className={styles.cardSubtitle}>Estudiantes activos en la plataforma en el período seleccionado</p>
            </div>
            <SegmentedControl
              size="sm"
              options={[
                { value: "7d", label: "7 días" },
                { value: "30d", label: "30 días" },
                { value: "90d", label: "90 días" },
              ]}
              value={activeRange}
              onChange={(val) => setActiveRange(val)}
            />
          </div>

          <div className={styles.chartWrapper}>
            <div className={styles.flowBars}>
              {stats.studentFlow.map((day, idx) => {
                const maxVal = Math.max(...stats.studentFlow.map((d) => d.activity));
                const heightPct = Math.max(14, Math.round((day.activity / maxVal) * 100));
                return (
                  <div key={idx} className={styles.barColumn}>
                    <div className={styles.barTrack}>
                      <div
                        className={styles.barFill}
                        style={{ height: `${heightPct}%` }}
                      >
                        <span className={styles.barTooltip}>
                          {day.students.toLocaleString()} alumnos ({day.activity.toLocaleString()} lecciones)
                        </span>
                      </div>
                    </div>
                    <span className={styles.barLabel}>{day.label}</span>
                  </div>
                );
              })}
            </div>
          </div>

          <div className={styles.chartFooter}>
            <div className={styles.legendItem}>
              <span className={styles.legendDot} />
              <span>Estudiantes activos diarios</span>
            </div>
            <div className={styles.legendStat}>
              <TrendingUp size={13} className={styles.trendIcon} />
              <span>+24% más tiempo de estudio esta semana</span>
            </div>
          </div>
        </section>

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
            <h4 className={styles.actionBoxTitle}>Acciones rápidas</h4>
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
      </div>
    </div>
  );
}

export default OverviewView;

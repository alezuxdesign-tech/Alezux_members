import { useEffect, useState } from "react";
import { Users, GraduationCap, DollarSign, Award, PlayCircle, Eye, CheckCircle2, TrendingUp, Sparkles } from "lucide-react";
import { MetricCard } from "../components/arc/metric-card/metric-card";
import { Button } from "../components/arc/button/button";
import { Badge } from "../components/arc/badge/badge";
import { api, DashboardStats } from "../services/api";
import styles from "./OverviewView.module.css";

interface OverviewViewProps {
  onNavigate: (tab: string) => void;
}

export function OverviewView({ onNavigate }: OverviewViewProps) {
  const [stats, setStats] = useState<DashboardStats | null>(null);
  const [activeRange, setActiveRange] = useState<"7d" | "30d" | "90d">("7d");

  useEffect(() => {
    api.getDashboardStats().then(setStats);
  }, []);

  if (!stats) return null;

  return (
    <div className={styles.container}>
      {/* Encabezado con Bienvenida y Acciones Rápidas */}
      <div className={styles.welcomeBanner}>
        <div>
          <div className={styles.tagline}>
            <Sparkles size={14} className={styles.sparkle} />
            <span>Panel de Rendimiento Académico</span>
          </div>
          <h1 className={styles.heading}>Panel de Control Principal</h1>
          <p className={styles.subheading}>
            Supervisa el crecimiento de tus estudiantes, el rendimiento de tus formaciones y la facturación en tiempo real.
          </p>
        </div>
        <div className={styles.quickActions}>
          <Button variant="secondary" onClick={() => onNavigate("finance")}>
            Generar Link de Pago
          </Button>
          <Button variant="primary" onClick={() => onNavigate("courses")}>
            + Nuevo Curso
          </Button>
        </div>
      </div>

      {/* Fila de Métricas Clave (KPIs con Arc MetricCard) */}
      <div className={styles.metricsGrid}>
        <MetricCard
          label="Estudiantes Totales"
          value={stats.totalStudents}
          suffix=" alumnos"
          change={stats.totalStudentsChange}
          context="Alumnos registrados con acceso a la plataforma"
          icon={<Users size={18} />}
        />
        <MetricCard
          label="Cursos Activos"
          value={stats.activeCourses}
          suffix=" cursos"
          change={stats.activeCoursesChange}
          context="Formaciones publicadas y disponibles"
          icon={<GraduationCap size={18} />}
        />
        <MetricCard
          label="Facturación del Mes"
          value={stats.monthlyRevenue}
          prefix="$"
          suffix=" USD"
          change={stats.monthlyRevenueChange}
          context="Planes recurrentes y pagos completos"
          icon={<DollarSign size={18} />}
        />
        <MetricCard
          label="Clase Más Vista"
          value={stats.topClass.views}
          suffix=" vistas"
          change={stats.topClass.completionRate}
          context="Tasa de finalización promedio"
          icon={<Award size={18} />}
        />
      </div>

      {/* Grilla Principal: Flujo de Estudiantes & Clase Más Vista */}
      <div className={styles.mainGrid}>
        {/* Gráfico / Visualización de Flujo de Estudiantes */}
        <section className={styles.cardSection}>
          <div className={styles.cardHeader}>
            <div>
              <h2 className={styles.cardTitle}>Flujo de Actividad de Estudiantes</h2>
              <p className={styles.cardSubtitle}>Estudiantes activos en la plataforma a lo largo del tiempo</p>
            </div>
            <div className={styles.rangeButtons}>
              {(["7d", "30d", "90d"] as const).map((range) => (
                <button
                  key={range}
                  type="button"
                  className={[styles.rangeBtn, activeRange === range ? styles.rangeBtnActive : ""].join(" ")}
                  onClick={() => setActiveRange(range)}
                >
                  {range.toUpperCase()}
                </button>
              ))}
            </div>
          </div>

          <div className={styles.chartWrapper}>
            <div className={styles.flowBars}>
              {stats.studentFlow.map((day, idx) => {
                const maxVal = Math.max(...stats.studentFlow.map((d) => d.activity));
                const heightPct = Math.round((day.activity / maxVal) * 100);
                return (
                  <div key={idx} className={styles.barColumn}>
                    <div className={styles.barTrack}>
                      <div
                        className={styles.barFill}
                        style={{ height: `${heightPct}%` }}
                        title={`${day.students} estudiantes activos (${day.activity} lecciones completadas)`}
                      >
                        <span className={styles.barTooltip}>{day.students} al.</span>
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
              <TrendingUp size={14} className={styles.trendIcon} />
              <span>+24% más tiempo de estudio esta semana</span>
            </div>
          </div>
        </section>

        {/* Módulo de la Clase / Lección Más Vista */}
        <section className={styles.cardSection}>
          <div className={styles.cardHeader}>
            <div>
              <h2 className={styles.cardTitle}>Clase Más Popular</h2>
              <p className={styles.cardSubtitle}>Mayor retención e impacto entre tus estudiantes</p>
            </div>
            <Badge variant="accent">Top #1</Badge>
          </div>

          <div className={styles.topClassCard}>
            <div className={styles.topClassIconWrapper}>
              <PlayCircle size={32} className={styles.playIcon} />
            </div>

            <div className={styles.topClassInfo}>
              <span className={styles.topCourseTitle}>{stats.topClass.courseTitle}</span>
              <h3 className={styles.topClassTitle}>{stats.topClass.title}</h3>

              <div className={styles.topClassMetrics}>
                <div className={styles.classStat}>
                  <Eye size={15} />
                  <span>{stats.topClass.views.toLocaleString()} Reproducciones</span>
                </div>
                <div className={styles.classStat}>
                  <CheckCircle2 size={15} />
                  <span>{stats.topClass.completions.toLocaleString()} Completaron ({stats.topClass.completionRate})</span>
                </div>
              </div>
            </div>
          </div>

          <div className={styles.nextActionsBox}>
            <h4 className={styles.actionBoxTitle}>Acciones recomendadas</h4>
            <ul className={styles.actionList}>
              <li onClick={() => onNavigate("students")}>
                <span>Revisar estudiantes que han completado este módulo</span>
                <span className={styles.arrow}>→</span>
              </li>
              <li onClick={() => onNavigate("marketing")}>
                <span>Enviar felicitación automática a los graduados</span>
                <span className={styles.arrow}>→</span>
              </li>
            </ul>
          </div>
        </section>
      </div>
    </div>
  );
}

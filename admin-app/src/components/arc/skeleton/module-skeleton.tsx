import React from "react";
import { Skeleton } from "./skeleton";
import styles from "./module-skeleton.module.css";

export type ModuleSkeletonType =
  | "overview"
  | "students"
  | "courses"
  | "finance"
  | "marketing"
  | "table"
  | "cards"
  | "generic";

export interface ModuleSkeletonProps {
  type?: ModuleSkeletonType;
  className?: string;
}

export function ModuleSkeleton({ type = "generic", className = "" }: ModuleSkeletonProps) {
  // 1. OVERVIEW SKELETON
  if (type === "overview") {
    return (
      <div className={[styles.container, className].join(" ")}>
        {/* Cabecera */}
        <div className={styles.pageHeader}>
          <div className={styles.headerLeft}>
            <Skeleton width={260} height={28} />
            <Skeleton width={380} height={16} />
          </div>
          <div className={styles.headerRight}>
            <Skeleton width={160} height={40} />
            <Skeleton width={130} height={40} />
          </div>
        </div>

        {/* 4 Tarjetas de Métricas */}
        <div className={styles.metricsGrid}>
          {[1, 2, 3, 4].map((i) => (
            <div key={i} className={styles.metricCardSkeleton}>
              <div className={styles.metricCardTop}>
                <Skeleton width={110} height={14} />
                <Skeleton width={28} height={28} variant="circular" />
              </div>
              <Skeleton width={140} height={32} />
              <div style={{ display: "flex", gap: "8px", alignItems: "center" }}>
                <Skeleton width={60} height={18} />
                <Skeleton width={100} height={12} />
              </div>
            </div>
          ))}
        </div>

        {/* 2 Tarjetas Grandes (Clase más vista y Gráfico de flujo) */}
        <div className={styles.twoColGrid}>
          <div className={styles.largeCardSkeleton}>
            <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center" }}>
              <Skeleton width={160} height={20} />
              <Skeleton width={80} height={22} />
            </div>
            <Skeleton width="100%" height={140} variant="rounded" />
            <div style={{ display: "flex", flexDirection: "column", gap: "8px" }}>
              <Skeleton width={220} height={18} />
              <Skeleton width={160} height={14} />
            </div>
            <div style={{ display: "flex", gap: "12px", marginTop: "auto" }}>
              <Skeleton width={90} height={24} />
              <Skeleton width={90} height={24} />
            </div>
          </div>

          <div className={styles.largeCardSkeleton}>
            <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center" }}>
              <Skeleton width={180} height={20} />
              <Skeleton width={140} height={32} />
            </div>
            <Skeleton width={260} height={14} />
            <div className={styles.chartBarsWrapper}>
              {[45, 65, 80, 95, 75, 110, 130].map((h, idx) => (
                <div key={idx} style={{ display: "flex", flexDirection: "column", alignItems: "center", gap: "8px", flex: 1 }}>
                  <Skeleton width="75%" height={h} variant="rounded" />
                  <Skeleton width={24} height={12} />
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>
    );
  }

  // 2. STUDENTS / TABLE SKELETON
  if (type === "students" || type === "table") {
    return (
      <div className={[styles.container, className].join(" ")}>
        {/* Cabecera */}
        <div className={styles.pageHeader}>
          <div className={styles.headerLeft}>
            <Skeleton width={240} height={28} />
            <Skeleton width={380} height={16} />
          </div>
          <div className={styles.headerRight}>
            <Skeleton width={240} height={36} />
            <Skeleton width={140} height={36} />
            <Skeleton width={130} height={36} />
          </div>
        </div>

        {/* Tabla */}
        <div className={styles.tableWrapper}>
          <div className={styles.tableHeaderRow}>
            <div style={{ flex: 2 }}><Skeleton width={100} height={14} /></div>
            <div style={{ flex: 1.2 }}><Skeleton width={90} height={14} /></div>
            <div style={{ flex: 1.5 }}><Skeleton width={110} height={14} /></div>
            <div style={{ flex: 1.2 }}><Skeleton width={80} height={14} /></div>
            <div style={{ flex: 1 }}><Skeleton width={70} height={14} /></div>
            <div style={{ width: 100, textAlign: "right" }}><Skeleton width={60} height={14} /></div>
          </div>

          {[1, 2, 3, 4, 5, 6].map((i) => (
            <div key={i} className={styles.tableRow}>
              {/* Estudiante con avatar */}
              <div style={{ flex: 2, display: "flex", alignItems: "center", gap: "12px" }}>
                <Skeleton width={36} height={36} variant="circular" />
                <div style={{ display: "flex", flexDirection: "column", gap: "6px" }}>
                  <Skeleton width={130} height={15} />
                  <Skeleton width={170} height={12} />
                </div>
              </div>
              {/* Fecha */}
              <div style={{ flex: 1.2 }}><Skeleton width={85} height={14} /></div>
              {/* Plan */}
              <div style={{ flex: 1.5 }}><Skeleton width={130} height={14} /></div>
              {/* Cursos */}
              <div style={{ flex: 1.2 }}><Skeleton width={80} height={24} variant="rounded" /></div>
              {/* Estado */}
              <div style={{ flex: 1 }}><Skeleton width={65} height={24} variant="rounded" /></div>
              {/* Acciones */}
              <div style={{ width: 100, display: "flex", justifyContent: "flex-end" }}>
                <Skeleton width={85} height={32} variant="rounded" />
              </div>
            </div>
          ))}
        </div>
      </div>
    );
  }

  // 3. COURSES / BUILDER SKELETON
  if (type === "courses") {
    return (
      <div className={[styles.container, className].join(" ")}>
        {/* Cabecera */}
        <div className={styles.pageHeader}>
          <div className={styles.headerLeft}>
            <Skeleton width={270} height={28} />
            <Skeleton width={400} height={16} />
          </div>
          <div className={styles.headerRight}>
            <Skeleton width={160} height={40} />
          </div>
        </div>

        {/* Layout de dos columnas: Barra lateral + Constructor */}
        <div className={styles.builderLayout}>
          {/* Barra lateral */}
          <div className={styles.builderSidebar}>
            <Skeleton width={140} height={16} style={{ marginBottom: "8px" }} />
            {[1, 2, 3, 4].map((i) => (
              <div
                key={i}
                style={{
                  display: "flex",
                  gap: "10px",
                  alignItems: "center",
                  padding: "8px",
                  background: "var(--surface-muted)",
                  borderRadius: "8px",
                }}
              >
                <Skeleton width={56} height={40} variant="rounded" />
                <div style={{ display: "flex", flexDirection: "column", gap: "6px", flex: 1 }}>
                  <Skeleton width="85%" height={14} />
                  <Skeleton width={70} height={11} />
                </div>
              </div>
            ))}
          </div>

          {/* Área principal del Builder */}
          <div className={styles.builderMain}>
            <div
              style={{
                display: "flex",
                justifyContent: "space-between",
                alignItems: "center",
                padding: "16px",
                background: "var(--surface)",
                borderRadius: "12px",
                border: "1px solid var(--border)",
              }}
            >
              <div style={{ display: "flex", flexDirection: "column", gap: "8px" }}>
                <Skeleton width={260} height={22} />
                <Skeleton width={180} height={14} />
              </div>
              <Skeleton width={140} height={36} />
            </div>

            {[1, 2].map((sec) => (
              <div key={sec} className={styles.builderSectionCard}>
                <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center" }}>
                  <Skeleton width={200} height={18} />
                  <Skeleton width={90} height={28} />
                </div>
                {[1, 2, 3].map((les) => (
                  <div
                    key={les}
                    style={{
                      display: "flex",
                      alignItems: "center",
                      justifyContent: "space-between",
                      padding: "10px 14px",
                      background: "var(--surface-muted)",
                      borderRadius: "6px",
                      border: "1px solid var(--border)",
                    }}
                  >
                    <div style={{ display: "flex", alignItems: "center", gap: "10px" }}>
                      <Skeleton width={18} height={18} />
                      <Skeleton width={220} height={14} />
                    </div>
                    <Skeleton width={50} height={14} />
                  </div>
                ))}
              </div>
            ))}
          </div>
        </div>
      </div>
    );
  }

  // 4. FINANCE SKELETON
  if (type === "finance") {
    return (
      <div className={[styles.container, className].join(" ")}>
        {/* Cabecera */}
        <div className={styles.pageHeader}>
          <div className={styles.headerLeft}>
            <Skeleton width={230} height={28} />
            <Skeleton width={380} height={16} />
          </div>
          <div className={styles.headerRight}>
            <Skeleton width={130} height={36} />
            <Skeleton width={140} height={36} />
          </div>
        </div>

        {/* 4 KPIs */}
        <div className={styles.metricsGrid}>
          {[1, 2, 3, 4].map((i) => (
            <div key={i} className={styles.metricCardSkeleton}>
              <div className={styles.metricCardTop}>
                <Skeleton width={100} height={14} />
                <Skeleton width={26} height={26} variant="circular" />
              </div>
              <Skeleton width={130} height={30} />
              <Skeleton width={90} height={14} />
            </div>
          ))}
        </div>

        {/* Tabs */}
        <div style={{ display: "flex", gap: "8px", width: 340 }}>
          <Skeleton width="100%" height={40} variant="rounded" />
        </div>

        {/* Grid de tarjetas de planes */}
        <div className={styles.cardsGrid}>
          {[1, 2, 3, 4, 5, 6].map((i) => (
            <div key={i} className={styles.cardItemSkeleton}>
              <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center" }}>
                <Skeleton width={140} height={18} />
                <Skeleton width={70} height={22} />
              </div>
              <Skeleton width="80%" height={14} />
              <div style={{ display: "flex", alignItems: "baseline", gap: "8px" }}>
                <Skeleton width={110} height={32} />
                <Skeleton width={60} height={14} />
              </div>
              <div style={{ display: "flex", gap: "8px", marginTop: "auto" }}>
                <Skeleton width="100%" height={36} variant="rounded" />
              </div>
            </div>
          ))}
        </div>
      </div>
    );
  }

  // 5. MARKETING / CARDS SKELETON
  if (type === "marketing" || type === "cards") {
    return (
      <div className={[styles.container, className].join(" ")}>
        {/* Cabecera */}
        <div className={styles.pageHeader}>
          <div className={styles.headerLeft}>
            <Skeleton width={260} height={28} />
            <Skeleton width={400} height={16} />
          </div>
          <div className={styles.headerRight}>
            <Skeleton width={180} height={38} />
          </div>
        </div>

        {/* Filtros de Categoría */}
        <div style={{ display: "flex", gap: "8px", width: 440 }}>
          <Skeleton width="100%" height={40} variant="rounded" />
        </div>

        {/* Tarjetas de Marketing */}
        <div className={styles.cardsGrid}>
          {[1, 2, 3, 4, 5, 6].map((i) => (
            <div key={i} className={styles.cardItemSkeleton}>
              <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center" }}>
                <Skeleton width={90} height={20} variant="rounded" />
                <Skeleton width={44} height={24} variant="rounded" />
              </div>
              <Skeleton width={180} height={18} />
              <Skeleton width="90%" height={14} />
              <Skeleton width="75%" height={14} />
              <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginTop: "auto", paddingTop: "12px", borderTop: "1px solid var(--border)" }}>
                <Skeleton width={100} height={14} />
                <Skeleton width={70} height={30} variant="rounded" />
              </div>
            </div>
          ))}
        </div>
      </div>
    );
  }

  // 6. GENERIC SKELETON
  return (
    <div className={[styles.container, className].join(" ")}>
      <div className={styles.pageHeader}>
        <div className={styles.headerLeft}>
          <Skeleton width={240} height={28} />
          <Skeleton width={360} height={16} />
        </div>
        <div className={styles.headerRight}>
          <Skeleton width={120} height={36} />
        </div>
      </div>

      <div className={styles.metricsGrid}>
        {[1, 2, 3, 4].map((i) => (
          <div key={i} className={styles.metricCardSkeleton}>
            <Skeleton width={100} height={14} />
            <Skeleton width={120} height={28} />
          </div>
        ))}
      </div>

      <div className={styles.tableWrapper}>
        {[1, 2, 3, 4, 5].map((i) => (
          <div key={i} className={styles.tableRow}>
            <div style={{ flex: 1 }}><Skeleton width="70%" height={16} /></div>
            <div style={{ flex: 1 }}><Skeleton width="50%" height={16} /></div>
            <div style={{ width: 90 }}><Skeleton width={80} height={30} /></div>
          </div>
        ))}
      </div>
    </div>
  );
}

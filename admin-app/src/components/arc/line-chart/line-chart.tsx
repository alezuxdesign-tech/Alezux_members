import React, { useState, useRef, useId } from "react";
import styles from "./line-chart.module.css";

export interface LineChartPoint {
  label: string;
  value: number;
  secondaryValue?: number;
  detail?: string;
}

export interface LineChartProps {
  data: LineChartPoint[];
  valuePrefix?: string;
  valueSuffix?: string;
  height?: number;
  metricName?: string;
  secondaryMetricName?: string;
  className?: string;
}

/**
 * Genera un trazado SVG cúbico suave (Monotone Spline) para que la línea fluya orgánicamente.
 */
function generateSmoothPath(points: { x: number; y: number }[]): string {
  if (points.length === 0) return "";
  if (points.length === 1) return `M ${points[0].x} ${points[0].y}`;

  let d = `M ${points[0].x.toFixed(2)},${points[0].y.toFixed(2)}`;
  for (let i = 0; i < points.length - 1; i++) {
    const p0 = points[Math.max(0, i - 1)];
    const p1 = points[i];
    const p2 = points[i + 1];
    const p3 = points[Math.min(points.length - 1, i + 2)];

    const cp1x = p1.x + (p2.x - p0.x) / 6;
    const cp1y = p1.y + (p2.y - p0.y) / 6;
    const cp2x = p2.x - (p3.x - p1.x) / 6;
    const cp2y = p2.y - (p3.y - p1.y) / 6;

    d += ` C ${cp1x.toFixed(2)},${cp1y.toFixed(2)} ${cp2x.toFixed(2)},${cp2y.toFixed(2)} ${p2.x.toFixed(2)},${p2.y.toFixed(2)}`;
  }
  return d;
}

function formatAxisNumber(val: number): string {
  if (val >= 1000000) return `${(val / 1000000).toFixed(1)}M`;
  if (val >= 1000) return `${(val / 1000).toFixed(val % 1000 === 0 ? 0 : 1)}k`;
  return String(Math.round(val));
}

export const LineChart: React.FC<LineChartProps> = ({
  data,
  valuePrefix = "",
  valueSuffix = "",
  height = 240,
  metricName = "Estudiantes activos",
  secondaryMetricName = "Lecciones completadas",
  className,
}) => {
  const containerRef = useRef<HTMLDivElement>(null);
  const [activeIndex, setActiveIndex] = useState<number | null>(null);
  const reactId = useId().replace(/:/g, "_");
  const gradientId = `arc_line_grad_${reactId}`;

  if (!data || data.length === 0) {
    return <div style={{ height }} className={styles.lineChartContainer} />;
  }

  const svgWidth = 800;
  const svgHeight = 240;
  const paddingLeft = 52;
  const paddingRight = 24;
  const paddingTop = 26;
  const paddingBottom = 34;

  const plotWidth = svgWidth - paddingLeft - paddingRight;
  const plotHeight = svgHeight - paddingTop - paddingBottom;

  const rawValues = data.map((d) => d.value);
  const rawMax = Math.max(...rawValues, 10);
  const maxVal = Math.ceil(rawMax * 1.15); // 15% de margen superior para respirar
  const minVal = 0;

  // Mapear cada punto a coordenadas dentro de la caja de trazado
  const points = data.map((item, i) => {
    const x = paddingLeft + (i / Math.max(1, data.length - 1)) * plotWidth;
    const valClamped = Math.max(minVal, Math.min(maxVal, item.value));
    const y = paddingTop + (1 - (valClamped - minVal) / (maxVal - minVal)) * plotHeight;
    return { x, y, item, i };
  });

  const linePath = generateSmoothPath(points);
  const lastX = points[points.length - 1]?.x ?? svgWidth;
  const firstX = points[0]?.x ?? paddingLeft;
  const baselineY = paddingTop + plotHeight;
  const areaPath = `${linePath} L ${lastX.toFixed(2)},${baselineY} L ${firstX.toFixed(2)},${baselineY} Z`;

  // 4 líneas de referencia horizontal (Y-Axis)
  const yTicks = [
    { val: maxVal, y: paddingTop },
    { val: maxVal * 0.66, y: paddingTop + plotHeight * 0.33 },
    { val: maxVal * 0.33, y: paddingTop + plotHeight * 0.66 },
    { val: 0, y: baselineY },
  ];

  // Interacción de puntero
  const handlePointerMove = (e: React.PointerEvent<HTMLDivElement>) => {
    if (!containerRef.current) return;
    const rect = containerRef.current.getBoundingClientRect();
    const relX = ((e.clientX - rect.left) / rect.width) * svgWidth;

    const clampedX = Math.max(paddingLeft, Math.min(svgWidth - paddingRight, relX));
    const closestIdx = Math.round(
      ((clampedX - paddingLeft) / plotWidth) * (data.length - 1)
    );
    const validIdx = Math.max(0, Math.min(data.length - 1, closestIdx));
    setActiveIndex(validIdx);
  };

  const handlePointerLeave = () => {
    setActiveIndex(null);
  };

  const activePoint = activeIndex !== null ? points[activeIndex] : null;

  return (
    <div
      ref={containerRef}
      className={[styles.lineChartContainer, className].filter(Boolean).join(" ")}
      style={{ height }}
      onPointerMove={handlePointerMove}
      onPointerLeave={handlePointerLeave}
    >
      <svg
        viewBox={`0 0 ${svgWidth} ${svgHeight}`}
        className={styles.svgRoot}
        preserveAspectRatio="none"
      >
        <defs>
          <linearGradient id={gradientId} x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stopColor="var(--accent)" stopOpacity="0.35" />
            <stop offset="55%" stopColor="var(--accent)" stopOpacity="0.10" />
            <stop offset="100%" stopColor="var(--accent)" stopOpacity="0.00" />
          </linearGradient>
        </defs>

        {/* 1. Cuadrícula horizontal y etiquetas de escala Y */}
        {yTicks.map((tick, idx) => (
          <g key={idx}>
            <line
              x1={paddingLeft}
              y1={tick.y}
              x2={svgWidth - paddingRight}
              y2={tick.y}
              className={styles.gridLine}
            />
            <text
              x={paddingLeft - 10}
              y={tick.y + 4}
              textAnchor="end"
              className={styles.axisText}
            >
              {valuePrefix}
              {formatAxisNumber(tick.val)}
            </text>
          </g>
        ))}

        {/* 2. Área con degradado dinámico bajo la curva */}
        <path d={areaPath} fill={`url(#${gradientId})`} className={styles.chartArea} />

        {/* 3. Línea suavizada principal */}
        <path d={linePath} className={styles.chartLine} />

        {/* 4. Etiquetas del eje X (Fechas / Días) */}
        {points.map((p, idx) => {
          const isSelected = activeIndex === idx;
          return (
            <text
              key={idx}
              x={p.x}
              y={svgHeight - 8}
              className={[
                styles.xLabel,
                isSelected ? styles.xLabelActive : "",
              ].join(" ")}
            >
              {p.item.label}
            </text>
          );
        })}

        {/* 5. Guía vertical y punto activo interactivo */}
        {activePoint && (
          <g>
            <line
              x1={activePoint.x}
              y1={paddingTop}
              x2={activePoint.x}
              y2={baselineY}
              className={styles.verticalGuide}
            />
            <circle
              cx={activePoint.x}
              cy={activePoint.y}
              r={12}
              className={styles.activeGlowRing}
            />
            <circle
              cx={activePoint.x}
              cy={activePoint.y}
              r={5.5}
              className={styles.activeDot}
            />
          </g>
        )}
      </svg>

      {/* 6. Tooltip flotante interactivo */}
      {activePoint && (
        <div
          className={styles.tooltipCard}
          style={{
            left: `${(activePoint.x / svgWidth) * 100}%`,
            top: `${(activePoint.y / svgHeight) * 100}%`,
          }}
        >
          <span className={styles.tooltipLabel}>
            {activePoint.item.detail || activePoint.item.label}
          </span>
          <div className={styles.tooltipPrimaryRow}>
            <span className={styles.tooltipDot} />
            <span className={styles.tooltipValue}>
              {valuePrefix}
              {activePoint.item.value.toLocaleString()}
              {valueSuffix ? ` ${valueSuffix}` : ""}
            </span>
          </div>
          {activePoint.item.secondaryValue !== undefined && (
            <div className={styles.tooltipSecondaryRow}>
              <span>
                {secondaryMetricName}:{" "}
                <strong>{activePoint.item.secondaryValue.toLocaleString()}</strong>
              </span>
            </div>
          )}
        </div>
      )}
    </div>
  );
};

export default LineChart;

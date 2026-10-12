"use client";

import React, { useState, useRef, useEffect } from "react";
import { createPortal } from "react-dom";
import { Calendar as CalendarIcon, ChevronLeft, ChevronRight } from "lucide-react";
import styles from "./date-picker.module.css";

export interface DatePickerProps {
  value: string; // YYYY-MM-DD
  onChange: (value: string) => void;
  label?: string;
  placeholder?: string;
  disabled?: boolean;
}

const MONTH_NAMES = [
  "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
  "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
];

const WEEKDAY_NAMES = ["Lu", "Ma", "Mi", "Ju", "Vi", "Sá", "Do"];

export const DatePicker: React.FC<DatePickerProps> = ({
  value,
  onChange,
  label,
  placeholder = "Seleccionar fecha...",
  disabled = false,
}) => {
  const [isOpen, setIsOpen] = useState(false);
  const triggerRef = useRef<HTMLButtonElement>(null);
  const popoverRef = useRef<HTMLDivElement>(null);

  // Fecha visible en el calendario (año y mes)
  const initialDate = value ? new Date(value + "T12:00:00") : new Date();
  const [viewYear, setViewYear] = useState(initialDate.getFullYear());
  const [viewMonth, setViewMonth] = useState(initialDate.getMonth()); // 0-11

  const [coords, setCoords] = useState<{ top: number; left: number; placeAbove: boolean }>({
    top: 0,
    left: 0,
    placeAbove: false,
  });

  const updatePosition = () => {
    if (!triggerRef.current) return;
    const rect = triggerRef.current.getBoundingClientRect();
    const popoverHeight = 310;
    const spaceBelow = window.innerHeight - rect.bottom;
    const placeAbove = spaceBelow < popoverHeight && rect.top > popoverHeight;

    setCoords({
      top: placeAbove ? rect.top - popoverHeight - 4 : rect.bottom + 4,
      left: Math.max(8, Math.min(rect.left, window.innerWidth - 300)),
      placeAbove,
    });
  };

  const handleToggle = () => {
    if (disabled) return;
    if (!isOpen) {
      if (value) {
        const d = new Date(value + "T12:00:00");
        if (!isNaN(d.getTime())) {
          setViewYear(d.getFullYear());
          setViewMonth(d.getMonth());
        }
      }
      updatePosition();
      setIsOpen(true);
    } else {
      setIsOpen(false);
    }
  };

  useEffect(() => {
    if (!isOpen) return;

    const handleScroll = (e: Event) => {
      if (popoverRef.current && popoverRef.current.contains(e.target as Node)) return;
      updatePosition();
    };

    const handleClickOutside = (e: MouseEvent) => {
      if (
        triggerRef.current &&
        !triggerRef.current.contains(e.target as Node) &&
        popoverRef.current &&
        !popoverRef.current.contains(e.target as Node)
      ) {
        setIsOpen(false);
      }
    };

    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === "Escape") setIsOpen(false);
    };

    window.addEventListener("scroll", handleScroll, true);
    window.addEventListener("resize", updatePosition);
    document.addEventListener("mousedown", handleClickOutside);
    document.addEventListener("keydown", handleKeyDown);

    return () => {
      window.removeEventListener("scroll", handleScroll, true);
      window.removeEventListener("resize", updatePosition);
      document.removeEventListener("mousedown", handleClickOutside);
      document.removeEventListener("keydown", handleKeyDown);
    };
  }, [isOpen]);

  // Navegación de mes
  const handlePrevMonth = (e: React.MouseEvent) => {
    e.stopPropagation();
    if (viewMonth === 0) {
      setViewMonth(11);
      setViewYear((y) => y - 1);
    } else {
      setViewMonth((m) => m - 1);
    }
  };

  const handleNextMonth = (e: React.MouseEvent) => {
    e.stopPropagation();
    if (viewMonth === 11) {
      setViewMonth(0);
      setViewYear((y) => y + 1);
    } else {
      setViewMonth((m) => m + 1);
    }
  };

  // Cálculo de los días a mostrar en el mes (con relleno de semanas completas de Lunes a Domingo)
  const getDaysArray = () => {
    const firstDayOfMonth = new Date(viewYear, viewMonth, 1);
    const lastDayOfMonth = new Date(viewYear, viewMonth + 1, 0);

    // En JS 0 = Domingo, 1 = Lunes. Queremos 0 = Lunes, 6 = Domingo
    let startDayOfWeek = firstDayOfMonth.getDay() - 1;
    if (startDayOfWeek === -1) startDayOfWeek = 6;

    const daysInPrevMonth = new Date(viewYear, viewMonth, 0).getDate();
    const daysInCurrentMonth = lastDayOfMonth.getDate();

    const items: Array<{
      dayNumber: number;
      isCurrentMonth: boolean;
      dateString: string;
      isToday: boolean;
      isSelected: boolean;
    }> = [];

    const todayStr = new Date().toISOString().split("T")[0];

    // Días del mes anterior
    for (let i = startDayOfWeek - 1; i >= 0; i--) {
      const day = daysInPrevMonth - i;
      const prevMonth = viewMonth === 0 ? 11 : viewMonth - 1;
      const prevYear = viewMonth === 0 ? viewYear - 1 : viewYear;
      const mStr = String(prevMonth + 1).padStart(2, "0");
      const dStr = String(day).padStart(2, "0");
      const str = `${prevYear}-${mStr}-${dStr}`;
      items.push({
        dayNumber: day,
        isCurrentMonth: false,
        dateString: str,
        isToday: str === todayStr,
        isSelected: str === value,
      });
    }

    // Días del mes actual
    for (let d = 1; d <= daysInCurrentMonth; d++) {
      const mStr = String(viewMonth + 1).padStart(2, "0");
      const dStr = String(d).padStart(2, "0");
      const str = `${viewYear}-${mStr}-${dStr}`;
      items.push({
        dayNumber: d,
        isCurrentMonth: true,
        dateString: str,
        isToday: str === todayStr,
        isSelected: str === value,
      });
    }

    // Días del mes siguiente para completar múltiplos de 7
    const remaining = (7 - (items.length % 7)) % 7;
    for (let d = 1; d <= remaining; d++) {
      const nextMonth = viewMonth === 11 ? 0 : viewMonth + 1;
      const nextYear = viewMonth === 11 ? viewYear + 1 : viewYear;
      const mStr = String(nextMonth + 1).padStart(2, "0");
      const dStr = String(d).padStart(2, "0");
      const str = `${nextYear}-${mStr}-${dStr}`;
      items.push({
        dayNumber: d,
        isCurrentMonth: false,
        dateString: str,
        isToday: str === todayStr,
        isSelected: str === value,
      });
    }

    return items;
  };

  const handleSelectDay = (dateStr: string) => {
    onChange(dateStr);
    setIsOpen(false);
  };

  const handleSelectToday = (e: React.MouseEvent) => {
    e.stopPropagation();
    const today = new Date().toISOString().split("T")[0];
    onChange(today);
    setIsOpen(false);
  };

  // Formato visual legible para el botón
  const formatDisplayValue = (val: string) => {
    if (!val) return "";
    try {
      const [y, m, d] = val.split("-").map(Number);
      const date = new Date(y, m - 1, d);
      return `${d} de ${MONTH_NAMES[date.getMonth()]}, ${y}`;
    } catch {
      return val;
    }
  };

  const popoverContent = isOpen && (
    <div
      ref={popoverRef}
      className={styles.calendarPopover}
      style={{
        top: `${coords.top}px`,
        left: `${coords.left}px`,
      }}
      onClick={(e) => e.stopPropagation()}
    >
      <div className={styles.navHeader}>
        <button type="button" className={styles.navBtn} onClick={handlePrevMonth} title="Mes anterior">
          <ChevronLeft size={16} />
        </button>
        <span className={styles.monthTitle}>
          {MONTH_NAMES[viewMonth]} {viewYear}
        </span>
        <button type="button" className={styles.navBtn} onClick={handleNextMonth} title="Mes siguiente">
          <ChevronRight size={16} />
        </button>
      </div>

      <div className={styles.weekdaysGrid}>
        {WEEKDAY_NAMES.map((wd) => (
          <span key={wd} className={styles.weekdayLabel}>
            {wd}
          </span>
        ))}
      </div>

      <div className={styles.daysGrid}>
        {getDaysArray().map((item, idx) => (
          <button
            key={idx}
            type="button"
            className={`${styles.dayBtn} ${!item.isCurrentMonth ? styles.otherMonth : ""} ${item.isToday ? styles.today : ""} ${item.isSelected ? styles.selected : ""}`}
            onClick={() => handleSelectDay(item.dateString)}
          >
            {item.dayNumber}
          </button>
        ))}
      </div>

      <div className={styles.footerRow}>
        <button type="button" className={styles.todayBtn} onClick={handleSelectToday}>
          Hoy
        </button>
      </div>
    </div>
  );

  return (
    <div className={styles.container}>
      {label && <label className={styles.label}>{label}</label>}
      <button
        ref={triggerRef}
        type="button"
        className={`${styles.trigger} ${isOpen ? styles.open : ""} ${disabled ? styles.disabled : ""}`}
        onClick={handleToggle}
        disabled={disabled}
      >
        <div className={styles.triggerContent}>
          <CalendarIcon size={15} className={styles.icon} />
          {value ? (
            <span>{formatDisplayValue(value)}</span>
          ) : (
            <span className={styles.placeholder}>{placeholder}</span>
          )}
        </div>
      </button>

      {typeof document !== "undefined" && createPortal(popoverContent, document.body)}
    </div>
  );
};

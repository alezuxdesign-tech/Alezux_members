"use client";

import React, { useState, useRef, useEffect } from "react";
import { createPortal } from "react-dom";
import { Clock, Check } from "lucide-react";
import styles from "./time-picker.module.css";

export interface TimePickerProps {
  value: string; // e.g. "19:00" or "07:00 PM"
  onChange: (value: string) => void;
  label?: string;
  placeholder?: string;
  disabled?: boolean;
}

const HOURS_12 = ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"];
const MINUTES_5 = ["00", "05", "10", "15", "20", "25", "30", "35", "40", "45", "50", "55"];
const COMMON_PRESETS = [
  { label: "09:00 AM", h: "09", m: "00", p: "AM" },
  { label: "11:00 AM", h: "11", m: "00", p: "AM" },
  { label: "02:00 PM", h: "02", m: "00", p: "PM" },
  { label: "04:00 PM", h: "04", m: "00", p: "PM" },
  { label: "07:00 PM", h: "07", m: "00", p: "PM" },
  { label: "08:30 PM", h: "08", m: "30", p: "PM" },
];

export const TimePicker: React.FC<TimePickerProps> = ({
  value,
  onChange,
  label,
  placeholder = "Seleccionar hora...",
  disabled = false,
}) => {
  const [isOpen, setIsOpen] = useState(false);
  const triggerRef = useRef<HTMLButtonElement>(null);
  const popoverRef = useRef<HTMLDivElement>(null);

  // Parsear valor inicial
  const parseTime = (val: string) => {
    if (!val) return { hour: "07", minute: "00", period: "PM" };

    if (val.toUpperCase().includes("AM") || val.toUpperCase().includes("PM")) {
      const parts = val.trim().split(/[:\s]+/);
      return {
        hour: parts[0]?.padStart(2, "0") || "07",
        minute: parts[1]?.padStart(2, "0") || "00",
        period: val.toUpperCase().includes("AM") ? "AM" : "PM",
      };
    }

    // Formato 24h e.g. "19:00"
    const [hStr, mStr] = val.split(":");
    let hNum = parseInt(hStr, 10);
    if (isNaN(hNum)) hNum = 19;
    const period = hNum >= 12 ? "PM" : "AM";
    const h12 = hNum % 12 === 0 ? 12 : hNum % 12;
    return {
      hour: String(h12).padStart(2, "0"),
      minute: (mStr || "00").padStart(2, "0"),
      period,
    };
  };

  const initial = parseTime(value);
  const [selectedHour, setSelectedHour] = useState(initial.hour);
  const [selectedMinute, setSelectedMinute] = useState(initial.minute);
  const [selectedPeriod, setSelectedPeriod] = useState<"AM" | "PM">(initial.period as "AM" | "PM");

  const [coords, setCoords] = useState<{ top: number; left: number; placeAbove: boolean }>({
    top: 0,
    left: 0,
    placeAbove: false,
  });

  const updatePosition = () => {
    if (!triggerRef.current) return;
    const rect = triggerRef.current.getBoundingClientRect();
    const popoverHeight = 330;
    const spaceBelow = window.innerHeight - rect.bottom;
    const placeAbove = spaceBelow < popoverHeight && rect.top > popoverHeight;

    setCoords({
      top: placeAbove ? rect.top - popoverHeight - 4 : rect.bottom + 4,
      left: Math.max(8, Math.min(rect.left, window.innerWidth - 280)),
      placeAbove,
    });
  };

  const handleToggle = () => {
    if (disabled) return;
    if (!isOpen) {
      const parsed = parseTime(value);
      setSelectedHour(parsed.hour);
      setSelectedMinute(parsed.minute);
      setSelectedPeriod(parsed.period as "AM" | "PM");
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

  const commitTime = (h: string, m: string, p: "AM" | "PM") => {
    // Convertir a formato 24h estándar (HH:MM) para compatibilidad con backend
    let hNum = parseInt(h, 10);
    if (p === "PM" && hNum < 12) hNum += 12;
    if (p === "AM" && hNum === 12) hNum = 0;
    const formatted24 = `${String(hNum).padStart(2, "0")}:${m}`;
    onChange(formatted24);
  };

  const handleApply = () => {
    commitTime(selectedHour, selectedMinute, selectedPeriod);
    setIsOpen(false);
  };

  const handleSelectPreset = (p: typeof COMMON_PRESETS[0]) => {
    setSelectedHour(p.h);
    setSelectedMinute(p.m);
    setSelectedPeriod(p.p as "AM" | "PM");
    commitTime(p.h, p.m, p.p as "AM" | "PM");
    setIsOpen(false);
  };

  // Formato visual legible para el trigger (ej: "07:00 PM")
  const formatDisplay = (val: string) => {
    if (!val) return "";
    const p = parseTime(val);
    return `${p.hour}:${p.minute} ${p.period}`;
  };

  const popoverContent = isOpen && (
    <div
      ref={popoverRef}
      className={styles.timePopover}
      style={{
        top: `${coords.top}px`,
        left: `${coords.left}px`,
      }}
      onClick={(e) => e.stopPropagation()}
    >
      {/* Visualización de dígitos y Toggle AM / PM */}
      <div className={styles.headerDisplay}>
        <span className={styles.displayDigits}>
          {selectedHour}:{selectedMinute}
        </span>
        <div className={styles.periodToggle}>
          <button
            type="button"
            className={`${styles.periodBtn} ${selectedPeriod === "AM" ? styles.periodBtnActive : ""}`}
            onClick={() => {
              setSelectedPeriod("AM");
              commitTime(selectedHour, selectedMinute, "AM");
            }}
          >
            AM
          </button>
          <button
            type="button"
            className={`${styles.periodBtn} ${selectedPeriod === "PM" ? styles.periodBtnActive : ""}`}
            onClick={() => {
              setSelectedPeriod("PM");
              commitTime(selectedHour, selectedMinute, "PM");
            }}
          >
            PM
          </button>
        </div>
      </div>

      {/* Columnas para Hora y Minutos */}
      <div className={styles.columnsWrap}>
        <div className={styles.columnBox}>
          <span className={styles.columnTitle}>Hora</span>
          <div className={styles.scrollList}>
            {HOURS_12.map((h) => (
              <button
                key={h}
                type="button"
                className={`${styles.timeOptionBtn} ${selectedHour === h ? styles.selected : ""}`}
                onClick={() => {
                  setSelectedHour(h);
                  commitTime(h, selectedMinute, selectedPeriod);
                }}
              >
                {h}
              </button>
            ))}
          </div>
        </div>

        <div className={styles.columnBox}>
          <span className={styles.columnTitle}>Minuto</span>
          <div className={styles.scrollList}>
            {MINUTES_5.map((m) => (
              <button
                key={m}
                type="button"
                className={`${styles.timeOptionBtn} ${selectedMinute === m ? styles.selected : ""}`}
                onClick={() => {
                  setSelectedMinute(m);
                  commitTime(selectedHour, m, selectedPeriod);
                }}
              >
                {m}
              </button>
            ))}
          </div>
        </div>
      </div>

      {/* Preajustes rápidos */}
      <div className={styles.presetsRow}>
        {COMMON_PRESETS.map((p) => (
          <button
            key={p.label}
            type="button"
            className={styles.presetPill}
            onClick={() => handleSelectPreset(p)}
          >
            {p.label}
          </button>
        ))}
      </div>

      <button type="button" className={styles.doneBtn} onClick={handleApply}>
        <Check size={14} /> Aplicar Hora
      </button>
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
          <Clock size={15} className={styles.icon} />
          {value ? (
            <span>{formatDisplay(value)}</span>
          ) : (
            <span className={styles.placeholder}>{placeholder}</span>
          )}
        </div>
      </button>

      {typeof document !== "undefined" && createPortal(popoverContent, document.body)}
    </div>
  );
};

"use client";

import React, { useState, useEffect, useId, forwardRef } from "react";
import styles from "./money-input.module.css";

export interface MoneyInputProps {
  label?: string;
  hint?: string;
  error?: string;
  value: number;
  onChange: (value: number) => void;
  currency?: string;
  symbol?: string;
  placeholder?: string;
  disabled?: boolean;
  min?: number;
  max?: number;
  className?: string;
  id?: string;
}

const formatDisplayValue = (val: number): string => {
  if (val === undefined || val === null || isNaN(val)) {
    return "0.00";
  }
  return val.toLocaleString("en-US", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

export const MoneyInput = forwardRef<HTMLInputElement, MoneyInputProps>(function MoneyInput(
  {
    label,
    hint,
    error,
    value,
    onChange,
    currency = "USD",
    symbol = "$",
    placeholder = "0.00",
    disabled = false,
    min = 0,
    max,
    className,
    id: customId,
  },
  ref
) {
  const generatedId = useId();
  const inputId = customId || generatedId;

  const [isFocused, setIsFocused] = useState(false);
  const [displayValue, setDisplayValue] = useState<string>(() => formatDisplayValue(value));

  // Sincronizar valor numérico externo cuando no está enfocado
  useEffect(() => {
    if (!isFocused) {
      setDisplayValue(formatDisplayValue(value));
    }
  }, [value, isFocused]);

  const handleFocus = (e: React.FocusEvent<HTMLInputElement>) => {
    setIsFocused(true);
    // Al hacer foco seleccionamos todo el texto para facilitar reemplazarlo
    e.target.select();
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const raw = e.target.value;

    // Normalizar comas a puntos decimales
    let clean = raw.replace(/,/g, ".");
    // Remover todo lo que no sea dígito o punto
    clean = clean.replace(/[^0-9.]/g, "");

    // Permitir sólo un punto decimal
    const parts = clean.split(".");
    if (parts.length > 2) {
      clean = `${parts[0]}.${parts.slice(1).join("")}`;
    }

    // Limitar a 2 decimales si ya hay punto
    if (parts.length === 2 && parts[1].length > 2) {
      clean = `${parts[0]}.${parts[1].slice(0, 2)}`;
    }

    setDisplayValue(clean);

    let parsed = parseFloat(clean);
    if (isNaN(parsed)) {
      parsed = 0;
    }

    if (min !== undefined && parsed < min) {
      // No forzamos min mientras escribe para no trabar escritura, pero notificamos min si terminó
    }
    if (max !== undefined && parsed > max) {
      parsed = max;
    }

    onChange(parsed);
  };

  const handleBlur = () => {
    setIsFocused(false);
    const cleaned = displayValue.replace(/,/g, "");
    let num = parseFloat(cleaned);
    if (isNaN(num) || num < (min ?? 0)) {
      num = min ?? 0;
    }
    if (max !== undefined && num > max) {
      num = max;
    }
    onChange(num);
    setDisplayValue(formatDisplayValue(num));
  };

  return (
    <div className={[styles.wrapper, className].filter(Boolean).join(" ")}>
      {label && (
        <label htmlFor={inputId} className={styles.label}>
          {label}
        </label>
      )}

      <div
        className={[
          styles.container,
          disabled ? styles.disabled : "",
        ]
          .filter(Boolean)
          .join(" ")}
      >
        <span className={styles.symbol} aria-hidden="true">
          {symbol}
        </span>

        <input
          ref={ref}
          id={inputId}
          type="text"
          inputMode="decimal"
          disabled={disabled}
          placeholder={placeholder}
          value={displayValue}
          onFocus={handleFocus}
          onChange={handleChange}
          onBlur={handleBlur}
          className={styles.input}
          autoComplete="off"
          spellCheck={false}
        />

        {currency && (
          <span className={styles.currencyBadge} aria-label={`Moneda ${currency}`}>
            {currency}
          </span>
        )}
      </div>

      {hint && !error && <span className={styles.hint}>{hint}</span>}
      {error && <span className={styles.error}>{error}</span>}
    </div>
  );
});

MoneyInput.displayName = "MoneyInput";
export default MoneyInput;

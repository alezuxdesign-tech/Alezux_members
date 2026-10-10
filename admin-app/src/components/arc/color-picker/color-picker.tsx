import React, { useState, useEffect, useRef, useCallback } from "react";
import { Pipette, Check, Sparkles } from "lucide-react";
import {
  hexToRgb,
  rgbToHex,
  rgbToHsv,
  hsvToRgb,
  getContrastForeground,
  getContrastRatio,
  HSV,
} from "./color-utils";
import styles from "./color-picker.module.css";

export interface ArcColorPickerProps {
  value: string;
  onChange: (hex: string) => void;
  className?: string;
}

const PRESET_SWATCHES = [
  { hex: "#C7F804", name: "Lima Neón" },
  { hex: "#7747FF", name: "Violeta Crezca" },
  { hex: "#0562EF", name: "Azul Eléctrico" },
  { hex: "#0DB879", name: "Esmeralda" },
  { hex: "#F3AD20", name: "Ámbar Dorado" },
  { hex: "#F15F55", name: "Coral Sunset" },
  { hex: "#00E5FF", name: "Cian Cibernético" },
  { hex: "#EC4899", name: "Rosa Neón" },
  { hex: "#E11D48", name: "Rubí Intenso" },
  { hex: "#71717A", name: "Neutral Minimal" },
];

export const ColorPicker: React.FC<ArcColorPickerProps> = ({
  value,
  onChange,
  className,
}) => {
  const [hsv, setHsv] = useState<HSV>(() => {
    const rgb = hexToRgb(value || "#7747FF");
    return rgbToHsv(rgb.r, rgb.g, rgb.b);
  });

  const [hexInput, setHexInput] = useState<string>(() => {
    return (value || "#7747FF").toUpperCase();
  });

  const saturationRef = useRef<HTMLDivElement>(null);
  const hueRef = useRef<HTMLDivElement>(null);
  const isDraggingSat = useRef(false);
  const isDraggingHue = useRef(false);

  // Sincronizar estado cuando la prop externa cambie
  useEffect(() => {
    if (value) {
      const rgb = hexToRgb(value);
      const newHsv = rgbToHsv(rgb.r, rgb.g, rgb.b);
      setHsv(newHsv);
      setHexInput(value.toUpperCase());
    }
  }, [value]);

  const updateFromHsv = useCallback(
    (newHsv: HSV) => {
      setHsv(newHsv);
      const rgb = hsvToRgb(newHsv.h, newHsv.s, newHsv.v);
      const hex = rgbToHex(rgb.r, rgb.g, rgb.b);
      setHexInput(hex);
      onChange(hex);
    },
    [onChange]
  );

  // Manejo de puntero para la caja 2D de saturación y valor
  const handleSatPointer = useCallback(
    (e: React.PointerEvent | PointerEvent) => {
      if (!saturationRef.current) return;
      const rect = saturationRef.current.getBoundingClientRect();
      const x = Math.max(0, Math.min(rect.width, e.clientX - rect.left));
      const y = Math.max(0, Math.min(rect.height, e.clientY - rect.top));

      const s = Math.round((x / rect.width) * 100);
      const v = Math.round((1 - y / rect.height) * 100);

      updateFromHsv({ ...hsv, s, v });
    },
    [hsv, updateFromHsv]
  );

  const onSatPointerDown = (e: React.PointerEvent<HTMLDivElement>) => {
    e.currentTarget.setPointerCapture(e.pointerId);
    isDraggingSat.current = true;
    handleSatPointer(e);
  };

  const onSatPointerMove = (e: React.PointerEvent<HTMLDivElement>) => {
    if (isDraggingSat.current) {
      handleSatPointer(e);
    }
  };

  const onSatPointerUp = (e: React.PointerEvent<HTMLDivElement>) => {
    if (isDraggingSat.current) {
      isDraggingSat.current = false;
      try {
        e.currentTarget.releasePointerCapture(e.pointerId);
      } catch {
        // Ignorar si el puntero ya no está capturado
      }
    }
  };

  // Manejo de puntero para el slider de Tono (Hue)
  const handleHuePointer = useCallback(
    (e: React.PointerEvent | PointerEvent) => {
      if (!hueRef.current) return;
      const rect = hueRef.current.getBoundingClientRect();
      const x = Math.max(0, Math.min(rect.width, e.clientX - rect.left));
      const h = Math.round((x / rect.width) * 360);

      updateFromHsv({ ...hsv, h: h >= 360 ? 0 : h });
    },
    [hsv, updateFromHsv]
  );

  const onHuePointerDown = (e: React.PointerEvent<HTMLDivElement>) => {
    e.currentTarget.setPointerCapture(e.pointerId);
    isDraggingHue.current = true;
    handleHuePointer(e);
  };

  const onHuePointerMove = (e: React.PointerEvent<HTMLDivElement>) => {
    if (isDraggingHue.current) {
      handleHuePointer(e);
    }
  };

  const onHuePointerUp = (e: React.PointerEvent<HTMLDivElement>) => {
    if (isDraggingHue.current) {
      isDraggingHue.current = false;
      try {
        e.currentTarget.releasePointerCapture(e.pointerId);
      } catch {
        // Ignorar
      }
    }
  };

  // Manejo de entrada directa de HEX
  const handleHexInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    let inputVal = e.target.value.trim().toUpperCase();
    if (!inputVal.startsWith("#")) {
      inputVal = "#" + inputVal;
    }
    setHexInput(inputVal);

    if (/^#([A-F0-9]{6}|[A-F0-9]{3})$/i.test(inputVal)) {
      const rgb = hexToRgb(inputVal);
      const newHsv = rgbToHsv(rgb.r, rgb.g, rgb.b);
      setHsv(newHsv);
      onChange(inputVal);
    }
  };

  // Cuentagotas nativo (EyeDropper API si está disponible en Chromium)
  const handleEyeDropper = async () => {
    if ("EyeDropper" in window) {
      try {
        const eyeDropper = new (window as any).EyeDropper();
        const result = await eyeDropper.open();
        if (result?.sRGBHex) {
          const hex = result.sRGBHex.toUpperCase();
          const rgb = hexToRgb(hex);
          const newHsv = rgbToHsv(rgb.r, rgb.g, rgb.b);
          setHsv(newHsv);
          setHexInput(hex);
          onChange(hex);
        }
      } catch {
        // Usuario canceló la selección con ESC
      }
    }
  };

  // Cálculos de contraste dinámicos
  const currentHex = hexInput.startsWith("#") ? hexInput : `#${hexInput}`;
  const contrastFg = getContrastForeground(currentHex);
  const contrastRatio = getContrastRatio(currentHex, contrastFg);
  const isLightForeground = contrastFg === "#ffffff";

  return (
    <div className={[styles.colorPickerRoot, className].filter(Boolean).join(" ")}>
      {/* 1. Área 2D de Saturación y Valor */}
      <div
        ref={saturationRef}
        className={styles.saturationArea}
        style={{ backgroundColor: `hsl(${hsv.h}, 100%, 50%)` }}
        onPointerDown={onSatPointerDown}
        onPointerMove={onSatPointerMove}
        onPointerUp={onSatPointerUp}
      >
        <div className={styles.saturationWhite} />
        <div className={styles.saturationBlack} />
        <div
          className={styles.saturationHandle}
          style={{
            left: `${hsv.s}%`,
            top: `${100 - hsv.v}%`,
            backgroundColor: currentHex,
          }}
        />
      </div>

      {/* 2. Slider de Hue (Tono espectral) */}
      <div
        ref={hueRef}
        className={styles.hueSliderWrap}
        onPointerDown={onHuePointerDown}
        onPointerMove={onHuePointerMove}
        onPointerUp={onHuePointerUp}
      >
        <div
          className={styles.hueThumb}
          style={{
            left: `${(hsv.h / 360) * 100}%`,
            backgroundColor: `hsl(${hsv.h}, 100%, 50%)`,
          }}
        />
      </div>

      {/* 3. Fila de Input HEX y Cuentagotas */}
      <div className={styles.controlsRow}>
        <div
          className={styles.previewSwatch}
          style={{ backgroundColor: currentHex }}
          title={`Color activo: ${currentHex}`}
        />

        <div className={styles.hexInputGroup}>
          <span className={styles.hexLabel}>HEX</span>
          <input
            type="text"
            className={styles.hexInputField}
            value={hexInput}
            onChange={handleHexInputChange}
            maxLength={7}
            placeholder="#C7F804"
            spellCheck={false}
          />
        </div>

        {typeof window !== "undefined" && "EyeDropper" in window && (
          <button
            type="button"
            className={styles.eyedropperBtn}
            onClick={handleEyeDropper}
            title="Cuentagotas: Tomar color de cualquier elemento de la pantalla"
          >
            <Pipette size={16} />
          </button>
        )}
      </div>

      {/* 4. Tarjeta de Verificación de Contraste Inteligente (WCAG) */}
      <div className={styles.contrastPreviewCard}>
        <div className={styles.contrastInfoText}>
          <span className={styles.contrastTitle}>
            <Sparkles size={13} style={{ color: "var(--accent)" }} />
            Contraste de Botones & Texto
          </span>
          <span className={styles.contrastSubtitle}>
            {isLightForeground
              ? "Color oscuro/saturado: Texto blanco aplicado"
              : "Color claro/neón: Texto oscuro aplicado automáticamente"}
          </span>
        </div>

        <button
          type="button"
          className={styles.contrastDemoButton}
          style={{
            backgroundColor: currentHex,
            color: contrastFg,
          }}
          title={`Ratio de contraste calculado: ${contrastRatio.toFixed(1)}:1`}
        >
          <span>Ejemplo</span>
          <span className={styles.contrastBadge}>
            {contrastRatio.toFixed(1)}:1
          </span>
        </button>
      </div>

      {/* 5. Muestras Rápidas (Swatches) */}
      <div className={styles.swatchesSection}>
        <span className={styles.swatchesLabel}>Paleta de sugerencias rápidas:</span>
        <div className={styles.swatchesGrid}>
          {PRESET_SWATCHES.map((swatch) => {
            const isActive = currentHex.toUpperCase() === swatch.hex.toUpperCase();
            return (
              <button
                key={swatch.hex}
                type="button"
                className={[
                  styles.swatchBtn,
                  isActive ? styles.swatchActive : "",
                ].join(" ")}
                style={{ backgroundColor: swatch.hex }}
                onClick={() => {
                  const rgb = hexToRgb(swatch.hex);
                  const newHsv = rgbToHsv(rgb.r, rgb.g, rgb.b);
                  updateFromHsv(newHsv);
                }}
                title={`${swatch.name} (${swatch.hex})`}
              >
                {isActive && (
                  <Check
                    size={13}
                    color={getContrastForeground(swatch.hex)}
                    style={{ margin: "auto", display: "block" }}
                  />
                )}
              </button>
            );
          })}
        </div>
      </div>
    </div>
  );
};

export default ColorPicker;

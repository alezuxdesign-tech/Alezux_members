import { useState } from "react";
import { Key, Shield, Check, Palette, Moon, Sun } from "lucide-react";
import { Button } from "../components/arc/button/button";
import { SegmentedControl } from "../components/arc/segmented-control/segmented-control";
import { Input } from "../components/arc/input/input";
import styles from "./SettingsView.module.css";

interface SettingsViewProps {
  currentAccent: string;
  onAccentChange: (accent: string) => void;
  currentTheme: "dark" | "light";
  onThemeToggle: () => void;
}

export function SettingsView({ currentAccent, onAccentChange, currentTheme, onThemeToggle }: SettingsViewProps) {
  const [stripePublic, setStripePublic] = useState("");
  const [stripeSecret, setStripeSecret] = useState("");
  const [adminCssClasses, setAdminCssClasses] = useState(".elementor-admin-only, .admin-only");
  const [saved, setSaved] = useState(false);

  const accents = [
    { id: "violet", name: "Violeta (Crezca Default)", color: "#7747ff" },
    { id: "blue", name: "Azul Eléctrico", color: "#0562ef" },
    { id: "green", name: "Esmeralda", color: "#0db879" },
    { id: "amber", name: "Ámbar Dorado", color: "#f3ad20" },
    { id: "coral", name: "Coral Sunset", color: "#f15f55" },
    { id: "neutral", name: "Neutral Minimal", color: "#71717a" },
  ];

  const handleSave = () => {
    setSaved(true);
    setTimeout(() => setSaved(false), 2500);
  };

  const themeOptions = [
    { value: "dark", label: "Modo Oscuro" },
    { value: "light", label: "Modo Claro" },
  ];

  return (
    <div className={styles.container}>
      <div className={styles.header}>
        <div>
          <h1 className={styles.title}>Ajustes y Personalización de Plataforma</h1>
          <p className={styles.subtitle}>
            Configura la apariencia de Arc UI, pasarelas de pago Stripe y reglas de acceso.
          </p>
        </div>
      </div>

      <div className={styles.sections}>
        {/* Sección: Apariencia & Tokens Arc */}
        <div className={styles.card}>
          <div className={styles.cardHeader}>
            <Palette size={18} className={styles.cardIcon} />
            <div>
              <h3 className={styles.cardTitle}>Color de Acento de la Interfaz (Arc Tokens)</h3>
              <p className={styles.cardDesc}>Selecciona la tonalidad que regirá los botones, gráficos y elementos activos.</p>
            </div>
          </div>

          <div className={styles.accentsGrid}>
            {accents.map((acc) => {
              const isSelected = currentAccent === acc.id;
              return (
                <div
                  key={acc.id}
                  className={[styles.accentItem, isSelected ? styles.accentActive : ""].join(" ")}
                  onClick={() => onAccentChange(acc.id)}
                >
                  <span className={styles.colorCircle} style={{ background: acc.color }} />
                  <span className={styles.accentName}>{acc.name}</span>
                  {isSelected && <Check size={14} className={styles.checkIcon} />}
                </div>
              );
            })}
          </div>

          <div className={styles.themeToggleRow}>
            <div className={styles.themeLabelBox}>
              <span className={styles.themeLabel}>Tema Visual:</span>
              <span className={styles.themeSub}>Paleta de color activa del panel</span>
            </div>
            <SegmentedControl
              options={themeOptions}
              value={currentTheme}
              onChange={(val) => {
                if (val !== currentTheme) onThemeToggle();
              }}
              size="sm"
            />
          </div>
        </div>

        {/* Sección: Integración con Stripe */}
        <div className={styles.card}>
          <div className={styles.cardHeader}>
            <Key size={18} className={styles.cardIcon} />
            <div>
              <h3 className={styles.cardTitle}>Credenciales de Stripe</h3>
              <p className={styles.cardDesc}>Utilizadas para procesar suscripciones, pagos únicos y cuotas automatizadas.</p>
            </div>
          </div>

          <div className={styles.formGroup}>
            <Input
              label="Publishable Key"
              placeholder="pk_live_..."
              value={stripePublic}
              onChange={(e) => setStripePublic(e.target.value)}
            />
          </div>

          <div className={styles.formGroup}>
            <Input
              label="Secret Key"
              type="password"
              placeholder="sk_live_..."
              value={stripeSecret}
              onChange={(e) => setStripeSecret(e.target.value)}
            />
          </div>
        </div>

        {/* Sección: Restricciones de Visibilidad */}
        <div className={styles.card}>
          <div className={styles.cardHeader}>
            <Shield size={18} className={styles.cardIcon} />
            <div>
              <h3 className={styles.cardTitle}>Clases CSS 'Solo Administrador'</h3>
              <p className={styles.cardDesc}>Elementos que se ocultarán automáticamente para los estudiantes en el Frontend.</p>
            </div>
          </div>

          <div className={styles.formGroup}>
            <Input
              label="Clases separadas por comas"
              value={adminCssClasses}
              onChange={(e) => setAdminCssClasses(e.target.value)}
              hint="Cualquier bloque con estas clases no será visible para los alumnos."
            />
          </div>
        </div>

        <div className={styles.footerActions}>
          {saved && (
            <span className={styles.saveNotice}>
              <Check size={16} /> Ajustes guardados correctamente
            </span>
          )}
          <Button variant="primary" onClick={handleSave}>
            Guardar Ajustes
          </Button>
        </div>
      </div>
    </div>
  );
}

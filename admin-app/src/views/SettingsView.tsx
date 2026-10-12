import { useState, useEffect } from "react";
import { 
  GraduationCap, 
  Palette, 
  Check, 
  Trash2, 
  Cpu, 
  Database, 
  ArrowRightLeft, 
  Sparkles, 
  AlertCircle,
  RefreshCw
} from "lucide-react";
import { Button } from "../components/arc/button/button";
import { SegmentedControl } from "../components/arc/segmented-control/segmented-control";
import { Input } from "../components/arc/input/input";
import { FileDropzone } from "../components/arc/file-dropzone/file-dropzone";
import { Alert } from "../components/arc/alert/alert";
import { ColorPicker } from "../components/arc/color-picker/color-picker";
import { getContrastForeground } from "../components/arc/color-picker/color-utils";
import { api, LMSSettingsResponse, LMSMigrationResponse } from "../services/api";
import styles from "./SettingsView.module.css";

interface SettingsViewProps {
  currentAccent: string;
  onAccentChange: (accent: string) => void;
  currentTheme: "dark" | "light";
  onThemeToggle: () => void;
  academyName: string;
  onAcademyNameChange: (name: string) => void;
  academyLogo: string;
  onAcademyLogoChange: (logo: string) => void;
}

interface ToastState {
  id: number;
  tone: "info" | "success" | "warning" | "danger";
  title: string;
  description?: string;
}

export function SettingsView({
  currentAccent,
  onAccentChange,
  currentTheme,
  onThemeToggle,
  academyName,
  onAcademyNameChange,
  academyLogo,
  onAcademyLogoChange,
}: SettingsViewProps) {
  const [localName, setLocalName] = useState(academyName);
  const [localLogo, setLocalLogo] = useState(academyLogo);
  const [isSaving, setIsSaving] = useState(false);
  const [toast, setToast] = useState<ToastState | null>(null);

  // Estado del Motor LMS y Migración
  const [lmsSettings, setLmsSettings] = useState<LMSSettingsResponse | null>(null);
  const [selectedLmsMode, setSelectedLmsMode] = useState<"auto" | "native" | "learndash">("auto");
  const [isMigrating, setIsMigrating] = useState(false);
  const [migrationResult, setMigrationResult] = useState<LMSMigrationResponse | null>(null);

  useEffect(() => {
    api.getLMSSettings().then((data) => {
      setLmsSettings(data);
      setSelectedLmsMode(data.mode);
    });
  }, []);

  const handleLmsModeChange = async (mode: "auto" | "native" | "learndash") => {
    setSelectedLmsMode(mode);
    try {
      const res = await api.saveLMSSettings({ mode });
      if (res.success) {
        setLmsSettings((prev) => prev ? { 
          ...prev, 
          mode, 
          active_mode: mode === "learndash" ? "learndash" : "native" 
        } : null);
        showToast(
          "success", 
          "Modo LMS actualizado", 
          `El motor preferido ahora es: ${mode === "native" ? "Crezca LMS Nativo" : mode === "learndash" ? "LearnDash LMS" : "Detección Automática"}.`
        );
      }
    } catch {
      showToast("danger", "Error", "No se pudo guardar la preferencia del motor LMS.");
    }
  };

  const handleMigrate = async () => {
    const confirm = window.confirm(
      "¿Deseas migrar todos los cursos de LearnDash al motor nativo de Crezca?\n\nSe crearán copias nativas en Crezca con sus módulos, lecciones, temas y matrículas de estudiantes. Los cursos originales de LearnDash no se borrarán."
    );
    if (!confirm) return;

    setIsMigrating(true);
    try {
      const res = await api.migrateFromLearnDash(true);
      setMigrationResult(res);
      if (res.success) {
        showToast("success", "Migración completada", res.message);
        const updated = await api.getLMSSettings();
        setLmsSettings(updated);
        setSelectedLmsMode(updated.mode);
      } else {
        showToast("warning", "Aviso de migración", res.message);
      }
    } catch {
      showToast("danger", "Error", "Ocurrió un fallo durante la migración.");
    } finally {
      setIsMigrating(false);
    }
  };

  useEffect(() => {
    setLocalName(academyName);
  }, [academyName]);

  useEffect(() => {
    setLocalLogo(academyLogo);
  }, [academyLogo]);

  // Limpieza automática de Toast
  useEffect(() => {
    if (!toast) return;
    const timer = setTimeout(() => {
      setToast(null);
    }, 4000);
    return () => clearTimeout(timer);
  }, [toast]);

  const showToast = (tone: "info" | "success" | "warning" | "danger", title: string, description?: string) => {
    setToast({ id: Date.now(), tone, title, description });
  };

  const accents = [
    { id: "violet", name: "Violeta (Crezca Default)", color: "#7747ff" },
    { id: "blue", name: "Azul Eléctrico", color: "#0562ef" },
    { id: "green", name: "Esmeralda", color: "#0db879" },
    { id: "amber", name: "Ámbar Dorado", color: "#f3ad20" },
    { id: "coral", name: "Coral Sunset", color: "#f15f55" },
    { id: "neutral", name: "Neutral Minimal", color: "#71717a" },
  ];

  const isPreset = accents.some((a) => a.id === currentAccent);
  const [customHex, setCustomHex] = useState(() => {
    return isPreset ? "#6366f1" : currentAccent;
  });

  useEffect(() => {
    if (!accents.some((a) => a.id === currentAccent) && currentAccent) {
      setCustomHex(currentAccent);
    }
  }, [currentAccent]);


  const themeOptions = [
    { value: "dark", label: "Modo Oscuro" },
    { value: "light", label: "Modo Claro" },
  ];

  const handleNameChange = (val: string) => {
    setLocalName(val);
    onAcademyNameChange(val);
  };

  const handleLogoChange = (val: string) => {
    setLocalLogo(val);
    onAcademyLogoChange(val);
  };

  const handleRemoveLogo = () => {
    handleLogoChange("");
    showToast("info", "Logo removido", "Se ha restablecido el icono por defecto en el menú lateral.");
  };

  const handleSave = async () => {
    setIsSaving(true);
    try {
      const ok = await api.savePlatformSettings({
        academy_name: localName,
        academy_logo: localLogo,
        theme: currentTheme,
        accent: currentAccent,
      });

      if (ok) {
        localStorage.setItem("crezca_academy_name", localName);
        localStorage.setItem("crezca_academy_logo", localLogo);
        localStorage.setItem("crezca_theme", currentTheme);
        localStorage.setItem("crezca_accent", currentAccent);

        showToast(
          "success",
          "Ajustes guardados correctamente",
          "El nombre, logotipo de la academia y las preferencias de apariencia se han guardado."
        );
      } else {
        showToast("warning", "Aviso", "Los ajustes se aplicaron localmente en el navegador.");
      }
    } catch {
      showToast("danger", "Error", "No se pudo sincronizar con el servidor de WordPress.");
    } finally {
      setIsSaving(false);
    }
  };

  return (
    <div className={styles.container}>
      <div className={styles.header}>
        <div>
          <h1 className={styles.title}>Ajustes y Personalización de Plataforma</h1>
          <p className={styles.subtitle}>
            Personaliza el nombre, logotipo de tu academia y el tema visual del panel.
          </p>
        </div>
      </div>

      <div className={styles.sections}>
        {/* ============================================================== */}
        {/* SECCIÓN 1: IDENTIDAD DE LA ACADEMIA */}
        {/* ============================================================== */}
        <div className={styles.card}>
          <div className={styles.cardHeader}>
            <GraduationCap size={20} className={styles.cardIcon} />
            <div>
              <h3 className={styles.cardTitle}>Identidad de la Academia</h3>
              <p className={styles.cardDesc}>
                Define el nombre y el logotipo oficial que se mostrarán en la barra lateral y en toda la plataforma.
              </p>
            </div>
          </div>

          <div className={styles.formGroup}>
            <Input
              label="Nombre de la Academia"
              placeholder="Ej: Next Level Trading Academy"
              value={localName}
              onChange={(e) => handleNameChange(e.target.value)}
              hint="Este nombre aparecerá en la cabecera del menú lateral y encabezados de la app."
            />
          </div>

          <div className={styles.logoDropzoneContainer}>
            <label className={styles.label}>Logotipo de la Academia (Icono de la App en el Menú)</label>
            <FileDropzone
              accept="image/png,image/jpeg,image/svg+xml,image/webp,image/gif"
              multiple={false}
              maxFiles={1}
              label="Arrastra el logotipo de tu academia aquí"
              description="PNG, JPG, SVG o WebP (cuadrado recomendado, máx. 5 MB)"
              dropLabel="Suelta el archivo para subir el logotipo"
              compactAt={1}
              listPlacement="inside"
              defaultItems={
                localLogo
                  ? [
                      {
                        id: "academy-logo",
                        name: localLogo.split("/").pop() || "logo.png",
                        size: 32 * 1024,
                        status: "uploaded",
                        preview: localLogo,
                      },
                    ]
                  : []
              }
              onUpload={async (item, { onProgress }) => {
                onProgress(20);
                if (item.file) {
                  onProgress(50);
                  const uploadedUrl = await api.uploadLogo(item.file);
                  onProgress(100);
                  if (uploadedUrl) {
                    handleLogoChange(uploadedUrl);
                    showToast(
                      "success",
                      "Logo subido con éxito",
                      "El logotipo se actualizó en la barra lateral."
                    );
                  } else {
                    throw new Error("No se pudo subir la imagen.");
                  }
                }
              }}
            />

            {/* Vista previa o URL directa */}
            {localLogo ? (
              <div className={styles.logoPreviewCard}>
                <div className={styles.logoPreviewInfo}>
                  <img src={localLogo} alt="Logo de la Academia" className={styles.logoImgThumb} />
                  <div className={styles.logoDetails}>
                    <span className={styles.logoTitle}>Logotipo activo</span>
                    <span className={styles.logoUrlText} title={localLogo}>
                      {localLogo}
                    </span>
                  </div>
                </div>
                <button
                  type="button"
                  className={styles.removeLogoBtn}
                  onClick={handleRemoveLogo}
                  title="Eliminar este logotipo y usar el icono por defecto"
                >
                  <Trash2 size={13} />
                  <span>Quitar logo</span>
                </button>
              </div>
            ) : (
              <Input
                label="O pega una URL directa del logotipo:"
                placeholder="https://tu-dominio.com/logo.png"
                value={localLogo}
                onChange={(e) => handleLogoChange(e.target.value)}
              />
            )}
          </div>
        </div>

        {/* ============================================================== */}
        {/* SECCIÓN 2: MOTOR DE CURSOS (LMS ENGINE) & MIGRACIÓN */}
        {/* ============================================================== */}
        <div className={styles.card}>
          <div className={styles.cardHeader}>
            <Cpu size={20} className={styles.cardIcon} />
            <div>
              <h3 className={styles.cardTitle}>Motor de Formaciones (LMS Engine) & Migración</h3>
              <p className={styles.cardDesc}>
                Elige el motor que gestionará tus cursos. Puedes operar con nuestro LMS nativo (100% independiente en tu base de datos) o sincronizar con LearnDash si lo tienes instalado.
              </p>
            </div>
          </div>

          {/* Estadísticas de diagnóstico */}
          <div className={styles.lmsStatsGrid}>
            <div className={styles.lmsStatCard}>
              <span className={styles.lmsStatLabel}>LearnDash LMS</span>
              <div className={styles.lmsStatValueRow}>
                {lmsSettings?.is_learndash_active ? (
                  <span className={[styles.lmsBadge, styles.lmsBadgeGreen].join(" ")}>
                    Activo en WordPress
                  </span>
                ) : (
                  <span className={[styles.lmsBadge, styles.lmsBadgeGray].join(" ")}>
                    No detectado
                  </span>
                )}
              </div>
            </div>

            <div className={styles.lmsStatCard}>
              <span className={styles.lmsStatLabel}>Cursos LearnDash</span>
              <div className={styles.lmsStatValueRow}>
                <span className={styles.lmsStatValue}>
                  {lmsSettings?.learndash_courses_count ?? 0}
                </span>
                <span style={{ fontSize: 12, color: "var(--text-muted)" }}>cursos (sfwd-courses)</span>
              </div>
            </div>

            <div className={styles.lmsStatCard}>
              <span className={styles.lmsStatLabel}>Cursos Crezca LMS</span>
              <div className={styles.lmsStatValueRow}>
                <span className={styles.lmsStatValue} style={{ color: "var(--accent)" }}>
                  {lmsSettings?.native_courses_count ?? 0}
                </span>
                <span style={{ fontSize: 12, color: "var(--text-muted)" }}>cursos nativos</span>
              </div>
            </div>
          </div>

          {/* Opciones de Selector de Modo */}
          <div className={styles.formGroup}>
            <label className={styles.label}>Modo de Operación del Motor LMS:</label>
            <div className={styles.lmsModeGrid}>
              <div
                className={[styles.lmsModeItem, selectedLmsMode === "native" ? styles.lmsModeItemActive : ""].join(" ")}
                onClick={() => handleLmsModeChange("native")}
              >
                <div className={styles.lmsModeRadio}>
                  {selectedLmsMode === "native" && <div className={styles.lmsModeRadioDot} />}
                </div>
                <div className={styles.lmsModeContent}>
                  <div className={styles.lmsModeTitle}>
                    <span>Motor Nativo Crezca LMS</span>
                    <span className={[styles.lmsBadge, styles.lmsBadgeGreen].join(" ")}>Recomendado</span>
                  </div>
                  <p className={styles.lmsModeDesc}>
                    Crea y administra tus cursos de forma 100% independiente en tu propia base de datos MySQL (CPT crezca_course). Cero dependencias de plugins externos, ultra rápido y compatible con sub-clases y preguntas.
                  </p>
                </div>
              </div>

              <div
                className={[styles.lmsModeItem, selectedLmsMode === "auto" ? styles.lmsModeItemActive : ""].join(" ")}
                onClick={() => handleLmsModeChange("auto")}
              >
                <div className={styles.lmsModeRadio}>
                  {selectedLmsMode === "auto" && <div className={styles.lmsModeRadioDot} />}
                </div>
                <div className={styles.lmsModeContent}>
                  <div className={styles.lmsModeTitle}>
                    <span>Detección Automática (Híbrido)</span>
                  </div>
                  <p className={styles.lmsModeDesc}>
                    Si LearnDash está activo en WordPress, Crezca se adapta automáticamente para leer sus cursos; de lo contrario, activa el motor nativo sin interrumpir el funcionamiento.
                  </p>
                </div>
              </div>

              <div
                className={[styles.lmsModeItem, selectedLmsMode === "learndash" ? styles.lmsModeItemActive : ""].join(" ")}
                onClick={() => handleLmsModeChange("learndash")}
              >
                <div className={styles.lmsModeRadio}>
                  {selectedLmsMode === "learndash" && <div className={styles.lmsModeRadioDot} />}
                </div>
                <div className={styles.lmsModeContent}>
                  <div className={styles.lmsModeTitle}>
                    <span>Forzar LearnDash LMS</span>
                  </div>
                  <p className={styles.lmsModeDesc}>
                    Opera exclusivamente sobre las tablas y cursos legados de LearnDash (sfwd-courses). Solo recomendado si requieres plugins de terceros que dependen estrictamente de LearnDash.
                  </p>
                </div>
              </div>
            </div>
          </div>

          {/* Banner de Migración de Cursos con 1 clic */}
          {(lmsSettings?.learndash_courses_count ?? 0) > 0 && (
            <div className={styles.migrationBannerBox}>
              <div className={styles.migrationBannerLeft}>
                <Sparkles size={24} className={styles.migrationIcon} />
                <div>
                  <h4 className={styles.migrationTextTitle}>
                    ¿Quieres independizarte de LearnDash?
                  </h4>
                  <p className={styles.migrationTextSub}>
                    Hemos detectado {lmsSettings?.learndash_courses_count} curso(s) en LearnDash. Puedes convertirlos a cursos nativos de Crezca con 1 solo clic, copiando sus videos, descripciones, temas y alumnos matriculados sin perder ningún dato.
                  </p>
                </div>
              </div>

              <Button
                variant="primary"
                onClick={handleMigrate}
                disabled={isMigrating}
              >
                {isMigrating ? (
                  <>
                    <RefreshCw size={14} /> Migrando cursos...
                  </>
                ) : (
                  <>
                    <ArrowRightLeft size={14} /> Migrar Cursos a Crezca LMS
                  </>
                )}
              </Button>
            </div>
          )}

          {migrationResult && (
            <Alert
              tone={migrationResult.success ? "success" : "warning"}
              title="Resultado de la Migración"
              onDismiss={() => setMigrationResult(null)}
            >
              {migrationResult.message}
            </Alert>
          )}
        </div>

        {/* ============================================================== */}
        {/* SECCIÓN 3: APARIENCIA & TEMA VISUAL (ARC TOKENS) */}
        {/* ============================================================== */}
        <div className={styles.card}>
          <div className={styles.cardHeader}>
            <Palette size={20} className={styles.cardIcon} />
            <div>
              <h3 className={styles.cardTitle}>Color de Acento de la Interfaz</h3>
              <p className={styles.cardDesc}>
                Selecciona la tonalidad que regirá los botones, gráficos, focos y elementos interactivos activos.
              </p>
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

            {/* Opción Personalizada */}
            <div
              className={[styles.accentItem, !isPreset ? styles.accentActive : ""].join(" ")}
              onClick={() => onAccentChange(customHex)}
            >
              <span
                className={styles.colorCircle}
                style={{
                  background: !isPreset
                    ? customHex
                    : "conic-gradient(from 180deg at 50% 50%, #f43f5e 0deg, #ec4899 72deg, #8b5cf6 144deg, #3b82f6 216deg, #10b981 288deg, #f59e0b 360deg)",
                }}
              />
              <span className={styles.accentName}>
                {!isPreset ? `Personalizado (${customHex.toUpperCase()})` : "Color Personalizado"}
              </span>
              {!isPreset && (
                <Check
                  size={14}
                  className={styles.checkIcon}
                  style={{ color: "var(--accent)" }}
                />
              )}
            </div>
          </div>

          {/* Panel de Configuración de Color Personalizado con Arc ColorPicker */}
          <div className={styles.customColorBox}>
            <div className={styles.customColorHeader}>
              <Palette size={18} className={styles.customColorIcon} />
              <div className={styles.customColorTextWrap}>
                <span className={styles.customColorTitle}>Selector de Color de Arc UI</span>
                <span className={styles.customColorSub}>
                  Ajusta el tono exacto con el lienzo 2D, el slider de espectro o códigos HEX. El sistema calcula automáticamente el contraste óptimo (blanco o negro) en botones y textos para evitar que rechinen.
                </span>
              </div>
            </div>

            <ColorPicker
              value={customHex}
              onChange={(newHex) => {
                setCustomHex(newHex);
                onAccentChange(newHex);
              }}
            />
          </div>

          <div className={styles.themeToggleRow}>
            <div className={styles.themeLabelBox}>
              <span className={styles.themeLabel}>Tema Visual:</span>
              <span className={styles.themeSub}>Paleta de color activa del panel de control</span>
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

        {/* ============================================================== */}
        {/* BOTÓN DE GUARDADO */}
        {/* ============================================================== */}
        <div className={styles.footerActions}>
          <Button variant="primary" onClick={handleSave} disabled={isSaving}>
            {isSaving ? "Guardando..." : "Guardar Ajustes"}
          </Button>
        </div>
      </div>

      {/* Alerta flotante tipo Toast de Arc */}
      {toast && (
        <div className={styles.toastContainer}>
          <Alert tone={toast.tone} title={toast.title} onDismiss={() => setToast(null)}>
            {toast.description}
          </Alert>
        </div>
      )}
    </div>
  );
}

export default SettingsView;

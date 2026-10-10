import { useState, useEffect } from "react";
import { GraduationCap, Palette, Check, Trash2, Image as ImageIcon } from "lucide-react";
import { Button } from "../components/arc/button/button";
import { SegmentedControl } from "../components/arc/segmented-control/segmented-control";
import { Input } from "../components/arc/input/input";
import { FileDropzone } from "../components/arc/file-dropzone/file-dropzone";
import { Alert } from "../components/arc/alert/alert";
import { api } from "../services/api";
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
        {/* SECCIÓN 2: APARIENCIA & TEMA VISUAL (ARC TOKENS) */}
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

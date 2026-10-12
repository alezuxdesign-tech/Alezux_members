import React, { useState, useEffect, useRef } from "react";
import { 
  Check, 
  ArrowRightLeft, 
  RefreshCw 
} from "lucide-react";
import { Button } from "../components/arc/button/button";
import { Input } from "../components/arc/input/input";
import { Switch } from "../components/arc/switch/switch";
import { Alert } from "../components/arc/alert/alert";
import { Modal } from "../components/arc/modal/modal";
import { ColorPicker } from "../components/arc/color-picker/color-picker";
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

// Preset de colores idéntico a la paleta de la referencia visual + Crezca
const PRESET_COLORS = [
  { id: "dark", name: "Negro Carbón", hex: "#18181b" },
  { id: "violet", name: "Violeta Crezca", hex: "#7747ff" },
  { id: "blue", name: "Azul Eléctrico", hex: "#0562ef" },
  { id: "ocean", name: "Azul Océano", hex: "#0284c7" },
  { id: "sky", name: "Azul Cielo", hex: "#0ea5e9" },
  { id: "teal", name: "Turquesa", hex: "#0d9488" },
  { id: "emerald", name: "Esmeralda", hex: "#0db879" },
  { id: "amber", name: "Ámbar Dorado", hex: "#f3ad20" },
  { id: "coral", name: "Coral Sunset", hex: "#f15f55" },
];

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
  const [isUploadingLogo, setIsUploadingLogo] = useState(false);
  const [showUrlInput, setShowUrlInput] = useState(false);
  const [showAdvancedPicker, setShowAdvancedPicker] = useState(false);
  const [toast, setToast] = useState<ToastState | null>(null);

  const fileInputRef = useRef<HTMLInputElement | null>(null);

  // Preferencia de tema: sistema, claro u oscuro
  const [themePref, setThemePref] = useState<"system" | "light" | "dark">(() => {
    const saved = localStorage.getItem("crezca_theme_pref") as "system" | "light" | "dark" | null;
    return saved || currentTheme;
  });

  // Preferencia de barra lateral translúcida
  const [transparentSidebar, setTransparentSidebar] = useState<boolean>(() => {
    return localStorage.getItem("crezca_transparent_sidebar") === "true";
  });

  // Preferencia de elementos del menú lateral
  const [sidebarFeature, setSidebarFeature] = useState<string>(() => {
    return localStorage.getItem("crezca_sidebar_feature") || "recent";
  });

  // Preferencia de densidad de tablas
  const [tableDensity, setTableDensity] = useState<"default" | "compact">(() => {
    const saved = localStorage.getItem("crezca_table_density");
    return saved === "compact" ? "compact" : "default";
  });

  // Color personalizado Hex
  const [customHex, setCustomHex] = useState(() => {
    return currentAccent.startsWith("#") ? currentAccent : "#0562ef";
  });

  // Sincronización de customHex si cambia currentAccent externamente
  useEffect(() => {
    if (currentAccent.startsWith("#")) {
      setCustomHex(currentAccent);
    }
  }, [currentAccent]);

  // Sincronizar props entrantes
  useEffect(() => {
    setLocalName(academyName);
  }, [academyName]);

  useEffect(() => {
    setLocalLogo(academyLogo);
  }, [academyLogo]);

  // Limpieza automática de Toast
  useEffect(() => {
    if (!toast) return;
    const timer = setTimeout(() => setToast(null), 4000);
    return () => clearTimeout(timer);
  }, [toast]);

  const showToast = (tone: "info" | "success" | "warning" | "danger", title: string, description?: string) => {
    setToast({ id: Date.now(), tone, title, description });
  };

  // Motor LMS y Migración
  const [lmsSettings, setLmsSettings] = useState<LMSSettingsResponse | null>(null);
  const [selectedLmsMode, setSelectedLmsMode] = useState<"auto" | "native" | "learndash">("auto");
  const [isMigrating, setIsMigrating] = useState(false);
  const [migrationResult, setMigrationResult] = useState<LMSMigrationResponse | null>(null);
  const [isMigrationModalOpen, setIsMigrationModalOpen] = useState(false);

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
          `Motor activo: ${mode === "native" ? "Crezca LMS Nativo" : mode === "learndash" ? "LearnDash LMS" : "Detección Automática"}.`
        );
      }
    } catch {
      showToast("danger", "Error", "No se pudo guardar la preferencia del motor LMS.");
    }
  };

  const handleOpenMigrateModal = () => {
    setIsMigrationModalOpen(true);
  };

  const confirmAndExecuteMigration = async () => {
    setIsMigrating(true);
    try {
      const res = await api.migrateFromLearnDash(true);
      setMigrationResult(res);
      setIsMigrationModalOpen(false);
      if (res.success) {
        showToast("success", "Migración completada", res.message);
        const updated = await api.getLMSSettings();
        setLmsSettings(updated);
        setSelectedLmsMode(updated.mode);
      } else {
        showToast("warning", "Aviso de migración", res.message);
      }
    } catch {
      setIsMigrationModalOpen(false);
      showToast("danger", "Error", "Ocurrió un fallo durante la migración.");
    } finally {
      setIsMigrating(false);
    }
  };

  // Manejadores de Logotipo
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
    showToast("info", "Logo removido", "Se restableció el icono por defecto.");
  };

  const handleFileUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;
    setIsUploadingLogo(true);
    try {
      const uploadedUrl = await api.uploadLogo(file);
      if (uploadedUrl) {
        handleLogoChange(uploadedUrl);
        showToast("success", "Logotipo subido con éxito", "El nuevo logotipo ya está activo.");
      } else {
        const localUrl = URL.createObjectURL(file);
        handleLogoChange(localUrl);
        showToast("info", "Vista previa local", "El logotipo se guardará al confirmar cambios.");
      }
    } catch {
      showToast("danger", "Error", "No se pudo subir la imagen seleccionada.");
    } finally {
      setIsUploadingLogo(false);
      if (fileInputRef.current) fileInputRef.current.value = "";
    }
  };

  // Manejador de Selección de Tema
  const handleThemeSelect = (pref: "system" | "light" | "dark") => {
    setThemePref(pref);
    localStorage.setItem("crezca_theme_pref", pref);

    let targetTheme: "dark" | "light" = "dark";
    if (pref === "system") {
      targetTheme = window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
    } else {
      targetTheme = pref;
    }

    if (currentTheme !== targetTheme) {
      onThemeToggle();
    }
  };

  // Manejador de Barra Lateral Translúcida
  const handleToggleTransparentSidebar = (checked: boolean) => {
    setTransparentSidebar(checked);
    localStorage.setItem("crezca_transparent_sidebar", String(checked));
    document.body.classList.toggle("transparent-sidebar", checked);
  };

  // Manejador de Densidad de Tablas
  const handleTableDensitySelect = (density: "default" | "compact") => {
    setTableDensity(density);
    localStorage.setItem("crezca_table_density", density);
    document.body.dataset.tableDensity = density;
  };

  // Cancelar y restaurar
  const handleCancel = () => {
    setLocalName(academyName);
    setLocalLogo(academyLogo);
    showToast("info", "Cambios descartados", "Se restablecieron los datos guardados.");
  };

  // Guardar Ajustes en el Servidor
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
          "Tus preferencias de identidad y apariencia se han sincronizado."
        );
      } else {
        showToast("warning", "Aviso", "Los cambios se aplicaron localmente en el navegador.");
      }
    } catch {
      showToast("danger", "Error", "No se pudo sincronizar con el servidor de WordPress.");
    } finally {
      setIsSaving(false);
    }
  };

  return (
    <div className={styles.container}>
      {/* Encabezado */}
      <div className={styles.header}>
        <h1 className={styles.title}>Ajustes y Personalización</h1>
        <p className={styles.subtitle}>
          Configura el logotipo oficial, la paleta de colores, el tema de interfaz y la sincronización del LMS.
        </p>
      </div>

      {/* Contenedor tipo panel con filas e inspectores */}
      <div className={styles.settingsCard}>
        {/* ============================================================== */}
        {/* FILA 1: LOGOTIPO DE LA COMPAÑÍA / ACADEMIA */}
        {/* ============================================================== */}
        <div className={styles.settingsRow}>
          <div className={styles.rowInfo}>
            <h3 className={styles.rowTitle}>Logotipo de la Academia</h3>
            <p className={styles.rowDesc}>Actualiza el logotipo oficial mostrado en el menú y encabezados.</p>
          </div>
          <div className={styles.rowContent}>
            <div className={styles.logoRowWrap}>
              {/* Thumbnail cuadrado redondeado */}
              <div className={styles.logoThumb}>
                {localLogo ? (
                  <img src={localLogo} alt="Logo" className={styles.logoImg} />
                ) : (
                  <div className={styles.logoPlaceholder}>
                    {/* Icono vectorial de radios solares como en la referencia */}
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round">
                      <line x1="12" y1="2" x2="12" y2="6" />
                      <line x1="12" y1="18" x2="12" y2="22" />
                      <line x1="4.93" y1="4.93" x2="7.76" y2="7.76" />
                      <line x1="16.24" y1="16.24" x2="19.07" y2="19.07" />
                      <line x1="2" y1="12" x2="6" y2="12" />
                      <line x1="18" y1="12" x2="22" y2="12" />
                      <line x1="4.93" y1="19.07" x2="7.76" y2="16.24" />
                      <line x1="16.24" y1="7.76" x2="19.07" y2="4.93" />
                    </svg>
                  </div>
                )}
              </div>

              {/* Acciones de logo */}
              <div className={styles.logoActions}>
                <input
                  type="file"
                  ref={fileInputRef}
                  style={{ display: "none" }}
                  accept="image/png,image/jpeg,image/svg+xml,image/webp,image/gif"
                  onChange={handleFileUpload}
                />
                <Button
                  variant="secondary"
                  size="sm"
                  onClick={() => fileInputRef.current?.click()}
                  disabled={isUploadingLogo}
                >
                  {isUploadingLogo ? "Subiendo..." : "Cambiar logo"}
                </Button>

                {localLogo && (
                  <button
                    type="button"
                    className={styles.removeBtn}
                    onClick={handleRemoveLogo}
                  >
                    Quitar
                  </button>
                )}

                <button
                  type="button"
                  className={styles.urlToggleBtn}
                  onClick={() => setShowUrlInput(!showUrlInput)}
                >
                  {showUrlInput ? "Ocultar URL" : "Pegar URL"}
                </button>
              </div>
            </div>

            {showUrlInput && (
              <div className={styles.urlInputBox}>
                <Input
                  placeholder="https://tu-dominio.com/logo.png"
                  value={localLogo}
                  onChange={(e) => handleLogoChange(e.target.value)}
                />
              </div>
            )}
          </div>
        </div>

        {/* ============================================================== */}
        {/* FILA 2: NOMBRE DE LA ACADEMIA */}
        {/* ============================================================== */}
        <div className={styles.settingsRow}>
          <div className={styles.rowInfo}>
            <h3 className={styles.rowTitle}>Nombre de la Academia</h3>
            <p className={styles.rowDesc}>Nombre institucional visible en el menú lateral y correos.</p>
          </div>
          <div className={styles.rowContent}>
            <div className={styles.inputWrapNarrow}>
              <Input
                placeholder="Ej: Next Level Trading Academy"
                value={localName}
                onChange={(e) => handleNameChange(e.target.value)}
              />
            </div>
          </div>
        </div>

        {/* ============================================================== */}
        {/* FILA 3: COLOR DE MARCA (BRAND COLOR) */}
        {/* ============================================================== */}
        <div className={styles.settingsRow}>
          <div className={styles.rowInfo}>
            <h3 className={styles.rowTitle}>Color de marca</h3>
            <p className={styles.rowDesc}>Selecciona o personaliza el color distintivo de la interfaz.</p>
          </div>
          <div className={styles.rowContent}>
            {/* Fila de puntos circulares de color */}
            <div className={styles.colorSwatchesRow}>
              {PRESET_COLORS.map((c) => {
                const isSelected = 
                  currentAccent.toLowerCase() === c.hex.toLowerCase() || 
                  currentAccent.toLowerCase() === c.id;
                return (
                  <button
                    key={c.id}
                    type="button"
                    className={[styles.colorDot, isSelected ? styles.colorDotActive : ""].join(" ")}
                    style={{ backgroundColor: c.hex }}
                    onClick={() => {
                      setCustomHex(c.hex);
                      onAccentChange(c.hex);
                    }}
                    title={c.name}
                  />
                );
              })}
            </div>

            {/* Fila inferior: Color personalizado con Hex y Swatch circular */}
            <div className={styles.customColorInline}>
              <span className={styles.customColorLabel}>Color personalizado:</span>
              <div className={styles.hexInputWrap}>
                <span className={styles.hexHash}>#</span>
                <input
                  type="text"
                  className={styles.hexInput}
                  value={customHex.replace(/^#/, "").toUpperCase()}
                  maxLength={6}
                  onChange={(e) => {
                    const hexVal = "#" + e.target.value.replace(/[^0-9A-Fa-f]/g, "");
                    setCustomHex(hexVal);
                    if (hexVal.length === 7) {
                      onAccentChange(hexVal);
                    }
                  }}
                />
              </div>

              {/* Botón circular con selector nativo invisible */}
              <div className={styles.colorPickerTriggerWrap} title="Haz clic para elegir un color">
                <input
                  type="color"
                  className={styles.nativeColorInput}
                  value={customHex.startsWith("#") && customHex.length === 7 ? customHex : "#0562ef"}
                  onChange={(e) => {
                    setCustomHex(e.target.value);
                    onAccentChange(e.target.value);
                  }}
                />
                <div 
                  className={styles.customColorCircle}
                  style={{ backgroundColor: customHex }}
                />
              </div>

              <button
                type="button"
                className={styles.advancedColorBtn}
                onClick={() => setShowAdvancedPicker(!showAdvancedPicker)}
              >
                {showAdvancedPicker ? "Ocultar lienzo 2D" : "Ajuste fino (Lienzo)"}
              </button>
            </div>

            {showAdvancedPicker && (
              <div className={styles.advancedColorBox}>
                <ColorPicker
                  value={customHex}
                  onChange={(newHex) => {
                    setCustomHex(newHex);
                    onAccentChange(newHex);
                  }}
                />
              </div>
            )}
          </div>
        </div>

        {/* ============================================================== */}
        {/* FILA 4: TEMA DE LA INTERFAZ (INTERFACE THEME) */}
        {/* ============================================================== */}
        <div className={styles.settingsRow}>
          <div className={styles.rowInfo}>
            <h3 className={styles.rowTitle}>Tema de la interfaz</h3>
            <p className={styles.rowDesc}>Selecciona el estilo visual que deseas para el panel.</p>
          </div>
          <div className={styles.rowContent}>
            <div className={styles.themeCardsGrid}>
              {/* Tarjeta 1: Preferencia del sistema */}
              <div
                className={[styles.themeCard, themePref === "system" ? styles.themeCardSelected : ""].join(" ")}
                onClick={() => handleThemeSelect("system")}
              >
                <div className={styles.mockupWrapper}>
                  <SystemPreferenceMockup />
                  {themePref === "system" && (
                    <div className={styles.selectedBadge}>
                      <Check size={11} strokeWidth={3} />
                    </div>
                  )}
                </div>
                <span className={styles.themeCardLabel}>Preferencia del sistema</span>
              </div>

              {/* Tarjeta 2: Claro */}
              <div
                className={[styles.themeCard, themePref === "light" ? styles.themeCardSelected : ""].join(" ")}
                onClick={() => handleThemeSelect("light")}
              >
                <div className={styles.mockupWrapper}>
                  <LightMockup />
                  {themePref === "light" && (
                    <div className={styles.selectedBadge}>
                      <Check size={11} strokeWidth={3} />
                    </div>
                  )}
                </div>
                <span className={styles.themeCardLabel}>Claro</span>
              </div>

              {/* Tarjeta 3: Oscuro */}
              <div
                className={[styles.themeCard, themePref === "dark" ? styles.themeCardSelected : ""].join(" ")}
                onClick={() => handleThemeSelect("dark")}
              >
                <div className={styles.mockupWrapper}>
                  <DarkMockup />
                  {themePref === "dark" && (
                    <div className={styles.selectedBadge}>
                      <Check size={11} strokeWidth={3} />
                    </div>
                  )}
                </div>
                <span className={styles.themeCardLabel}>Oscuro</span>
              </div>
            </div>
          </div>
        </div>

        {/* ============================================================== */}
        {/* FILA 5: BARRA LATERAL TRANSLÚCIDA (TRANSPARENT SIDEBAR) */}
        {/* ============================================================== */}
        <div className={styles.settingsRow}>
          <div className={styles.rowInfo}>
            <h3 className={styles.rowTitle}>Barra lateral translúcida</h3>
            <p className={styles.rowDesc}>Aplica desenfoque translúcido (glassmorphism) al menú lateral de escritorio.</p>
          </div>
          <div className={styles.rowContent}>
            <Switch
              checked={transparentSidebar}
              onCheckedChange={handleToggleTransparentSidebar}
            />
          </div>
        </div>

        {/* ============================================================== */}
        {/* FILA 6: ELEMENTOS DEL MENÚ (SIDEBAR FEATURE) */}
        {/* ============================================================== */}
        <div className={styles.settingsRow}>
          <div className={styles.rowInfo}>
            <h3 className={styles.rowTitle}>Elementos del menú</h3>
            <p className={styles.rowDesc}>Configura qué accesos o estados se priorizan en la barra lateral.</p>
          </div>
          <div className={styles.rowContent}>
            <div className={styles.selectWrap}>
              <div className={styles.selectIndicatorDot} />
              <select
                className={styles.customSelect}
                value={sidebarFeature}
                onChange={(e) => {
                  setSidebarFeature(e.target.value);
                  localStorage.setItem("crezca_sidebar_feature", e.target.value);
                }}
              >
                <option value="recent">Cambios recientes</option>
                <option value="all">Todos los módulos</option>
                <option value="compact">Modo esencial</option>
              </select>
            </div>
          </div>
        </div>

        {/* ============================================================== */}
        {/* FILA 7: VISTA DE TABLAS Y LISTAS (TABLES VIEW) */}
        {/* ============================================================== */}
        <div className={styles.settingsRow}>
          <div className={styles.rowInfo}>
            <h3 className={styles.rowTitle}>Vista de tablas y listas</h3>
            <p className={styles.rowDesc}>Ajusta la densidad visual de las tablas de estudiantes y cursos.</p>
          </div>
          <div className={styles.rowContent}>
            <div className={styles.themeCardsGrid}>
              {/* Opción 1: Predeterminada (Default) */}
              <div
                className={[styles.themeCard, tableDensity === "default" ? styles.themeCardSelected : ""].join(" ")}
                onClick={() => handleTableDensitySelect("default")}
              >
                <div className={styles.mockupWrapper}>
                  <TableDefaultMockup />
                  {tableDensity === "default" && (
                    <div className={styles.selectedBadge}>
                      <Check size={11} strokeWidth={3} />
                    </div>
                  )}
                </div>
                <span className={styles.themeCardLabel}>Predeterminada</span>
              </div>

              {/* Opción 2: Compacta (Compact) */}
              <div
                className={[styles.themeCard, tableDensity === "compact" ? styles.themeCardSelected : ""].join(" ")}
                onClick={() => handleTableDensitySelect("compact")}
              >
                <div className={styles.mockupWrapper}>
                  <TableCompactMockup />
                  {tableDensity === "compact" && (
                    <div className={styles.selectedBadge}>
                      <Check size={11} strokeWidth={3} />
                    </div>
                  )}
                </div>
                <span className={styles.themeCardLabel}>Compacta</span>
              </div>
            </div>
          </div>
        </div>

        {/* ============================================================== */}
        {/* FILA 8: MOTOR DE CURSOS (LMS ENGINE) & MIGRACIÓN */}
        {/* ============================================================== */}
        <div className={styles.settingsRow}>
          <div className={styles.rowInfo}>
            <h3 className={styles.rowTitle}>Motor de Formaciones (LMS)</h3>
            <p className={styles.rowDesc}>
              Configura si Crezca opera de forma 100% nativa o sincronizada con LearnDash.
            </p>
          </div>
          <div className={styles.rowContent}>
            {/* Píldoras de diagnóstico */}
            <div className={styles.lmsPillsRow}>
              <span className={styles.lmsPill}>
                LearnDash: <strong>{lmsSettings?.is_learndash_active ? "Activo" : "No detectado"}</strong>
              </span>
              <span className={styles.lmsPill}>
                Cursos LearnDash: <strong>{lmsSettings?.learndash_courses_count ?? 0}</strong>
              </span>
              <span className={[styles.lmsPill, styles.lmsPillAccent].join(" ")}>
                Cursos Crezca LMS: <strong>{lmsSettings?.native_courses_count ?? 0}</strong>
              </span>
            </div>

            {/* Opciones del motor */}
            <div className={styles.lmsModeGrid}>
              <div
                className={[styles.lmsModeTile, selectedLmsMode === "native" ? styles.lmsModeTileActive : ""].join(" ")}
                onClick={() => handleLmsModeChange("native")}
              >
                <div className={styles.lmsRadioDot}>
                  {selectedLmsMode === "native" && <div className={styles.lmsRadioInner} />}
                </div>
                <div>
                  <div className={styles.lmsModeHeading}>
                    <span>Crezca LMS Nativo</span>
                    <span className={styles.recommendedBadge}>Recomendado</span>
                  </div>
                  <p className={styles.lmsModeText}>
                    Base de datos independiente, ultra rápida y sin dependencias de plugins externos.
                  </p>
                </div>
              </div>

              <div
                className={[styles.lmsModeTile, selectedLmsMode === "auto" ? styles.lmsModeTileActive : ""].join(" ")}
                onClick={() => handleLmsModeChange("auto")}
              >
                <div className={styles.lmsRadioDot}>
                  {selectedLmsMode === "auto" && <div className={styles.lmsRadioInner} />}
                </div>
                <div>
                  <div className={styles.lmsModeHeading}>Detección Automática (Híbrido)</div>
                  <p className={styles.lmsModeText}>
                    Detecta si LearnDash está presente para sincronizarlo; si no, activa el motor nativo sin pausas.
                  </p>
                </div>
              </div>

              <div
                className={[styles.lmsModeTile, selectedLmsMode === "learndash" ? styles.lmsModeTileActive : ""].join(" ")}
                onClick={() => handleLmsModeChange("learndash")}
              >
                <div className={styles.lmsRadioDot}>
                  {selectedLmsMode === "learndash" && <div className={styles.lmsRadioInner} />}
                </div>
                <div>
                  <div className={styles.lmsModeHeading}>Forzar LearnDash LMS</div>
                  <p className={styles.lmsModeText}>
                    Opera exclusivamente sobre las tablas de LearnDash (sfwd-courses).
                  </p>
                </div>
              </div>
            </div>

            {/* Banner de migración con 1 clic */}
            {(lmsSettings?.learndash_courses_count ?? 0) > 0 && (
              <div className={styles.migrationNotice}>
                <div className={styles.migrationNoticeText}>
                  <strong>¿Deseas independizarte de LearnDash?</strong>
                  <p>
                    Detectamos {lmsSettings?.learndash_courses_count} curso(s). Puedes migrarlos a Crezca LMS en 1 clic
                    conservando lecciones, videos y progreso de los alumnos.
                  </p>
                </div>
                <Button
                  variant="secondary"
                  size="sm"
                  onClick={handleOpenMigrateModal}
                  disabled={isMigrating}
                >
                  <ArrowRightLeft size={13} /> Migrar Cursos
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
        </div>
      </div>

      {/* ============================================================== */}
      {/* BARRA DE ACCIONES INFERIOR (CANCELAR Y GUARDAR CAMBIOS) */}
      {/* ============================================================== */}
      <div className={styles.footerRow}>
        <Button variant="ghost" onClick={handleCancel}>
          Cancelar
        </Button>
        <Button variant="primary" onClick={handleSave} disabled={isSaving}>
          {isSaving ? "Guardando..." : "Guardar cambios"}
        </Button>
      </div>

      {/* Alerta flotante tipo Toast de Arc */}
      {toast && (
        <div className={styles.toastContainer}>
          <Alert tone={toast.tone} title={toast.title} onDismiss={() => setToast(null)}>
            {toast.description}
          </Alert>
        </div>
      )}

      {/* Modal Arc de Confirmación de Migración */}
      {isMigrationModalOpen && (
        <Modal
          isOpen={true}
          onClose={() => {
            if (!isMigrating) setIsMigrationModalOpen(false);
          }}
          title="Migrar Cursos a Crezca LMS"
          maxWidth="520px"
          footer={
            <>
              <Button
                variant="ghost"
                onClick={() => setIsMigrationModalOpen(false)}
                disabled={isMigrating}
              >
                Cancelar
              </Button>
              <Button
                variant="primary"
                onClick={confirmAndExecuteMigration}
                disabled={isMigrating}
              >
                {isMigrating ? (
                  <>
                    <RefreshCw size={14} /> Migrando cursos...
                  </>
                ) : (
                  <>
                    <ArrowRightLeft size={14} /> Confirmar e Iniciar
                  </>
                )}
              </Button>
            </>
          }
        >
          <div className={styles.migrationConfirmBody}>
            <div className={styles.migrationConfirmIconWrap}>
              <ArrowRightLeft size={22} />
            </div>
            <div className={styles.migrationConfirmContent}>
              <h4 className={styles.migrationConfirmTitle}>
                ¿Deseas migrar todos tus cursos de LearnDash a Crezca LMS?
              </h4>
              <p className={styles.migrationConfirmDesc}>
                Se crearán copias nativas en Crezca con sus módulos, lecciones, temas y alumnos matriculados. Los cursos
                originales de LearnDash <strong>no se verán afectados ni se borrarán</strong>.
              </p>
              <div className={styles.migrationConfirmList}>
                <div className={styles.migrationConfirmItem}>
                  <Check size={14} className={styles.migrationConfirmItemIcon} />
                  <span>Conserva videos, descripciones y archivos descargables</span>
                </div>
                <div className={styles.migrationConfirmItem}>
                  <Check size={14} className={styles.migrationConfirmItemIcon} />
                  <span>Transfiere el progreso de lecciones de los estudiantes</span>
                </div>
                <div className={styles.migrationConfirmItem}>
                  <Check size={14} className={styles.migrationConfirmItemIcon} />
                  <span>Permite operar de forma 100% independiente sin LearnDash</span>
                </div>
              </div>
            </div>
          </div>
        </Modal>
      )}
    </div>
  );
}

/* ==============================================================
   COMPONENTES SVG ILUSTRATIVOS (MOCKUPS IDÉNTICOS A LA REFERENCIA)
   ============================================================== */

function SystemPreferenceMockup() {
  return (
    <svg viewBox="0 0 160 100" fill="none" xmlns="http://www.w3.org/2000/svg" className={styles.mockupSvg}>
      {/* Fondo marco general */}
      <rect width="160" height="100" rx="8" fill="#f8fafc" />

      {/* Mitad izquierda (Claro) */}
      <g>
        {/* Puntos ventana */}
        <circle cx="10" cy="10" r="2.2" fill="#ef4444" />
        <circle cx="17" cy="10" r="2.2" fill="#f59e0b" />
        <circle cx="24" cy="10" r="2.2" fill="#10b981" />
        <line x1="0" y1="18" x2="80" y2="18" stroke="#e2e8f0" strokeWidth="1" />

        {/* Sidebar claro */}
        <circle cx="12" cy="27" r="3" fill="#cbd5e1" />
        <rect x="18" y="25.5" width="16" height="3" rx="1.5" fill="#94a3b8" />
        <rect x="8" y="35" width="26" height="2.5" rx="1.25" fill="#cbd5e1" />
        <rect x="8" y="42" width="22" height="2.5" rx="1.25" fill="#e2e8f0" />
        <rect x="8" y="49" width="24" height="2.5" rx="1.25" fill="#e2e8f0" />
        <rect x="8" y="56" width="20" height="2.5" rx="1.25" fill="#e2e8f0" />
        <line x1="38" y1="18" x2="38" y2="100" stroke="#e2e8f0" strokeWidth="1" />

        {/* Header y contenido claro */}
        <text x="44" y="28" fontSize="5.5" fontWeight="600" fill="#64748b" fontFamily="system-ui">Your dashboard</text>
        <rect x="44" y="34" width="32" height="58" rx="4" fill="#ffffff" stroke="#e2e8f0" strokeWidth="1" />
        <rect x="48" y="40" width="18" height="3" rx="1.5" fill="#cbd5e1" />
        <rect x="48" y="47" width="24" height="2" rx="1" fill="#f1f5f9" />
      </g>

      {/* Mitad derecha (Oscuro) */}
      <g>
        <rect x="80" y="0" width="80" height="100" fill="#1e293b" />
        <line x1="80" y1="18" x2="160" y2="18" stroke="#334155" strokeWidth="1" />

        {/* Header y contenido oscuro */}
        <text x="86" y="28" fontSize="5.5" fontWeight="600" fill="#94a3b8" fontFamily="system-ui">Your dashboard</text>
        <rect x="86" y="34" width="34" height="58" rx="4" fill="#0f172a" stroke="#334155" strokeWidth="1" />
        <rect x="91" y="40" width="18" height="3" rx="1.5" fill="#475569" />
        <rect x="91" y="47" width="24" height="2" rx="1" fill="#1e293b" />

        {/* Sidebar oscuro a la derecha simulación */}
        <line x1="126" y1="18" x2="126" y2="100" stroke="#334155" strokeWidth="1" />
        <rect x="132" y="26" width="20" height="2.5" rx="1.25" fill="#475569" />
        <rect x="132" y="33" width="16" height="2.5" rx="1.25" fill="#334155" />
        <rect x="132" y="40" width="18" height="2.5" rx="1.25" fill="#334155" />
      </g>

      {/* Línea divisoria central */}
      <line x1="80" y1="0" x2="80" y2="100" stroke="#94a3b8" strokeWidth="0.75" />
    </svg>
  );
}

function LightMockup() {
  return (
    <svg viewBox="0 0 160 100" fill="none" xmlns="http://www.w3.org/2000/svg" className={styles.mockupSvg}>
      <rect width="160" height="100" rx="8" fill="#f8fafc" />

      {/* Puntos ventana */}
      <circle cx="10" cy="10" r="2.2" fill="#ef4444" />
      <circle cx="17" cy="10" r="2.2" fill="#f59e0b" />
      <circle cx="24" cy="10" r="2.2" fill="#10b981" />
      <line x1="0" y1="18" x2="160" y2="18" stroke="#e2e8f0" strokeWidth="1" />

      {/* Sidebar */}
      <circle cx="12" cy="27" r="3" fill="#cbd5e1" />
      <rect x="18" y="25.5" width="18" height="3" rx="1.5" fill="#94a3b8" />
      <rect x="8" y="35" width="28" height="2.5" rx="1.25" fill="#cbd5e1" />
      <rect x="8" y="42" width="22" height="2.5" rx="1.25" fill="#e2e8f0" />
      <rect x="8" y="49" width="26" height="2.5" rx="1.25" fill="#e2e8f0" />
      <rect x="8" y="56" width="20" height="2.5" rx="1.25" fill="#e2e8f0" />
      <rect x="8" y="63" width="24" height="2.5" rx="1.25" fill="#e2e8f0" />
      <line x1="42" y1="18" x2="42" y2="100" stroke="#e2e8f0" strokeWidth="1" />

      {/* Header */}
      <text x="50" y="28" fontSize="5.5" fontWeight="600" fill="#475569" fontFamily="system-ui">Your dashboard</text>
      <rect x="124" y="23" width="26" height="7" rx="3.5" fill="#e2e8f0" />

      {/* Tarjeta de contenido */}
      <rect x="50" y="34" width="100" height="58" rx="5" fill="#ffffff" stroke="#e2e8f0" strokeWidth="1" />
      <rect x="58" y="42" width="40" height="3.5" rx="1.75" fill="#cbd5e1" />
      <rect x="58" y="50" width="65" height="2.5" rx="1.25" fill="#f1f5f9" />
      <rect x="58" y="57" width="80" height="2.5" rx="1.25" fill="#f1f5f9" />
    </svg>
  );
}

function DarkMockup() {
  return (
    <svg viewBox="0 0 160 100" fill="none" xmlns="http://www.w3.org/2000/svg" className={styles.mockupSvg}>
      <rect width="160" height="100" rx="8" fill="#18181b" />

      {/* Puntos ventana */}
      <circle cx="10" cy="10" r="2.2" fill="#ef4444" />
      <circle cx="17" cy="10" r="2.2" fill="#f59e0b" />
      <circle cx="24" cy="10" r="2.2" fill="#10b981" />
      <line x1="0" y1="18" x2="160" y2="18" stroke="#27272a" strokeWidth="1" />

      {/* Sidebar */}
      <circle cx="12" cy="27" r="3" fill="#71717a" />
      <rect x="18" y="25.5" width="18" height="3" rx="1.5" fill="#a1a1aa" />
      <rect x="8" y="35" width="28" height="2.5" rx="1.25" fill="#52525b" />
      <rect x="8" y="42" width="22" height="2.5" rx="1.25" fill="#3f3f46" />
      <rect x="8" y="49" width="26" height="2.5" rx="1.25" fill="#3f3f46" />
      <rect x="8" y="56" width="20" height="2.5" rx="1.25" fill="#3f3f46" />
      <rect x="8" y="63" width="24" height="2.5" rx="1.25" fill="#3f3f46" />
      <line x1="42" y1="18" x2="42" y2="100" stroke="#27272a" strokeWidth="1" />

      {/* Header */}
      <text x="50" y="28" fontSize="5.5" fontWeight="600" fill="#a1a1aa" fontFamily="system-ui">Your dashboard</text>
      <rect x="124" y="23" width="26" height="7" rx="3.5" fill="#27272a" />

      {/* Tarjeta de contenido */}
      <rect x="50" y="34" width="100" height="58" rx="5" fill="#09090b" stroke="#27272a" strokeWidth="1" />
      <rect x="58" y="42" width="40" height="3.5" rx="1.75" fill="#52525b" />
      <rect x="58" y="50" width="65" height="2.5" rx="1.25" fill="#27272a" />
      <rect x="58" y="57" width="80" height="2.5" rx="1.25" fill="#27272a" />
    </svg>
  );
}

function TableDefaultMockup() {
  return (
    <svg viewBox="0 0 160 100" fill="none" xmlns="http://www.w3.org/2000/svg" className={styles.mockupSvg}>
      <rect width="160" height="100" rx="8" fill="#f8fafc" />
      <text x="12" y="15" fontSize="5.5" fontWeight="600" fill="#475569" fontFamily="system-ui">Customer</text>
      <rect x="114" y="9" width="34" height="7" rx="3.5" fill="#e2e8f0" />
      <line x1="0" y1="22" x2="160" y2="22" stroke="#e2e8f0" strokeWidth="1" />

      {/* Fila 1 */}
      <circle cx="16" cy="34" r="4" fill="#cbd5e1" />
      <rect x="25" y="32" width="26" height="4" rx="2" fill="#94a3b8" />
      <rect x="68" y="33" width="22" height="3" rx="1.5" fill="#cbd5e1" />
      <rect x="104" y="33" width="18" height="3" rx="1.5" fill="#cbd5e1" />
      <rect x="134" y="32" width="14" height="4" rx="2" fill="#bbf7d0" />
      <line x1="12" y1="44" x2="148" y2="44" stroke="#f1f5f9" strokeWidth="1" />

      {/* Fila 2 */}
      <circle cx="16" cy="54" r="4" fill="#cbd5e1" />
      <rect x="25" y="52" width="30" height="4" rx="2" fill="#94a3b8" />
      <rect x="68" y="53" width="24" height="3" rx="1.5" fill="#cbd5e1" />
      <rect x="104" y="53" width="16" height="3" rx="1.5" fill="#cbd5e1" />
      <rect x="134" y="52" width="14" height="4" rx="2" fill="#fed7aa" />
      <line x1="12" y1="64" x2="148" y2="64" stroke="#f1f5f9" strokeWidth="1" />

      {/* Fila 3 */}
      <circle cx="16" cy="74" r="4" fill="#cbd5e1" />
      <rect x="25" y="72" width="24" height="4" rx="2" fill="#94a3b8" />
      <rect x="68" y="73" width="20" height="3" rx="1.5" fill="#cbd5e1" />
      <rect x="104" y="73" width="22" height="3" rx="1.5" fill="#cbd5e1" />
      <rect x="134" y="72" width="14" height="4" rx="2" fill="#bbf7d0" />
    </svg>
  );
}

function TableCompactMockup() {
  return (
    <svg viewBox="0 0 160 100" fill="none" xmlns="http://www.w3.org/2000/svg" className={styles.mockupSvg}>
      <rect width="160" height="100" rx="8" fill="#f8fafc" />
      <text x="12" y="15" fontSize="5.5" fontWeight="600" fill="#475569" fontFamily="system-ui">Customer</text>
      <rect x="114" y="9" width="34" height="7" rx="3.5" fill="#e2e8f0" />
      <line x1="0" y1="22" x2="160" y2="22" stroke="#e2e8f0" strokeWidth="1" />

      {/* Filas compactas con espaciado denso */}
      {[29, 40, 51, 62, 73, 84].map((y, idx) => (
        <g key={idx}>
          <circle cx="16" cy={y} r="2.7" fill="#cbd5e1" />
          <rect x="24" y={y - 2} width="26" height="3.5" rx="1.75" fill="#94a3b8" />
          <rect x="66" y={y - 1.5} width="22" height="2.5" rx="1.25" fill="#cbd5e1" />
          <rect x="102" y={y - 1.5} width="18" height="2.5" rx="1.25" fill="#cbd5e1" />
          <rect x="134" y={y - 2} width="14" height="3.5" rx="1.75" fill={idx % 2 === 0 ? "#bbf7d0" : "#fed7aa"} />
          {idx < 5 && <line x1="12" y1={y + 5.5} x2="148" y2={y + 5.5} stroke="#f1f5f9" strokeWidth="0.75" />}
        </g>
      ))}
    </svg>
  );
}

export default SettingsView;

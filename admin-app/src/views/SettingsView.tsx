import React, { useState, useEffect, useRef, useMemo } from "react";
import { 
  Check, 
  ArrowRightLeft, 
  RefreshCw, 
  ExternalLink, 
  ShieldCheck, 
  Lock, 
  Sparkles, 
  LogIn, 
  Eye,
  Sliders,
  Layers,
  Trash2,
  Copy,
  CheckCircle2
} from "lucide-react";
import { Button } from "../components/arc/button/button";
import { Input } from "../components/arc/input/input";
import { Switch } from "../components/arc/switch/switch";
import { Alert } from "../components/arc/alert/alert";
import { Modal } from "../components/arc/modal/modal";
import { ColorPicker } from "../components/arc/color-picker/color-picker";
import { SegmentedControl, SegmentOption } from "../components/arc/segmented-control/segmented-control";
import { Select } from "../components/arc/select/select";
import { FileDropzone } from "../components/arc/file-dropzone/file-dropzone";
import { api, LMSSettingsResponse, LMSMigrationResponse, AuthSettings } from "../services/api";
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

type SettingsTabId = "general" | "login" | "access" | "lms";

const SETTINGS_TABS: SegmentOption<SettingsTabId>[] = [
  { value: "general", label: "General & Marca", icon: <Sliders size={14} /> },
  { value: "login", label: "Pantalla de Login", icon: <LogIn size={14} /> },
  { value: "access", label: "Flujos & Roles", icon: <ShieldCheck size={14} /> },
  { value: "lms", label: "Motor LMS", icon: <Layers size={14} /> },
];

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
  const [activeTab, setActiveTab] = useState<SettingsTabId>("general");
  const [localName, setLocalName] = useState(academyName);
  const [localLogo, setLocalLogo] = useState(academyLogo);
  const [isSaving, setIsSaving] = useState(false);
  const [isUploadingLogo, setIsUploadingLogo] = useState(false);
  const [showAdvancedPicker, setShowAdvancedPicker] = useState(false);
  const [toast, setToast] = useState<ToastState | null>(null);

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

  // Configuración de Login y Acceso Personalizable
  const [authSettings, setAuthSettings] = useState<AuthSettings>({
    logo_url: "",
    logo_width: 160,
    bg_type: "gradient",
    bg_color: "#090d16",
    bg_gradient_start: "#1d68ed",
    bg_gradient_end: "#0b3294",
    bg_image_url: "",
    accent_color: "#1d68ed",
    hero_badge_enabled: true,
    hero_badge_text: "Join Us to Build 🤩",
    hero_title: "Start your Journey",
    hero_subtitle: "Follow these simple steps to set up your account and start learning today.",
    steps_enabled: true,
    step_1_num: "1",
    step_1_text: "Register your\naccount",
    step_2_num: "2",
    step_2_text: "Set up your profile\ninformation",
    step_3_num: "3",
    step_3_text: "Access your courses\ninstantly",
    login_title: "Welcome Back",
    login_subtitle: "Inicia sesión en tu cuenta para continuar",
    allow_registration: false,
    terms_url: "/terminos-y-condiciones",
    privacy_url: "/politica-de-privacidad",
    custom_css: "",
    sync_academy_colors: true,
    student_default_view: "courses",
    admin_default_redirect: "academy",
    form_theme: "light",
  });

  useEffect(() => {
    let active = true;
    api.getAuthSettings().then((data) => {
      if (active && data) {
        setAuthSettings(data);
      }
    });
    return () => { active = false; };
  }, []);

  useEffect(() => {
    api.getLMSSettings().then((data) => {
      setLmsSettings(data);
      setSelectedLmsMode(data.mode);
    });
  }, []);

  // Cálculo del degradado complementario en vivo derivado del color de la academia
  const complementaryGradient = useMemo(() => {
    const preset = PRESET_COLORS.find(
      (c) => c.hex.toLowerCase() === currentAccent.toLowerCase() || c.id === currentAccent.toLowerCase()
    );
    if (preset) {
      const darkEndMap: Record<string, string> = {
        violet: "#1a1038",
        blue: "#041635",
        ocean: "#041d2f",
        sky: "#042435",
        teal: "#042421",
        emerald: "#04281b",
        amber: "#35260c",
        coral: "#351513",
        dark: "#090d16",
      };
      const end = darkEndMap[preset.id] || "#090d16";
      return `linear-gradient(135deg, ${preset.hex} 0%, ${end} 100%)`;
    }
    if (currentAccent.startsWith("#") && currentAccent.length === 7) {
      const r = parseInt(currentAccent.substring(1, 3), 16);
      const g = parseInt(currentAccent.substring(3, 5), 16);
      const b = parseInt(currentAccent.substring(5, 7), 16);
      const endR = Math.max(4, Math.round(r * 0.22)).toString(16).padStart(2, "0");
      const endG = Math.max(6, Math.round(g * 0.22)).toString(16).padStart(2, "0");
      const endB = Math.max(14, Math.round(b * 0.22)).toString(16).padStart(2, "0");
      return `linear-gradient(135deg, ${currentAccent} 0%, #${endR}${endG}${endB} 100%)`;
    }
    return "linear-gradient(135deg, #7747ff 0%, #1a1038 100%)";
  }, [currentAccent]);

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
        // Sincronizar automáticamente matrículas de alumnos para los nuevos cursos
        await api.syncLMSEnrollments();
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

  const [isSyncingEnrollments, setIsSyncingEnrollments] = useState(false);

  const handleSyncEnrollments = async () => {
    setIsSyncingEnrollments(true);
    try {
      const res = await api.syncLMSEnrollments();
      if (res.success) {
        showToast("success", "Sincronización completada", res.message);
      } else {
        showToast("warning", "Aviso de sincronización", res.message);
      }
    } catch {
      showToast("danger", "Error", "No se pudo sincronizar las matrículas.");
    } finally {
      setIsSyncingEnrollments(false);
    }
  };

  // Manejadores de Logotipo Principal
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
    api.getAuthSettings().then((data) => setAuthSettings(data));
    showToast("info", "Cambios descartados", "Se restablecieron los datos guardados.");
  };

  // Guardar Ajustes en el Servidor
  const handleSave = async () => {
    setIsSaving(true);
    try {
      const [platformOk, authOk] = await Promise.all([
        api.savePlatformSettings({
          academy_name: localName,
          academy_logo: localLogo,
          theme: currentTheme,
          accent: currentAccent,
        }),
        api.saveAuthSettings(authSettings),
      ]);
      const ok = platformOk && authOk;

      if (ok) {
        localStorage.setItem("crezca_academy_name", localName);
        localStorage.setItem("crezca_academy_logo", localLogo);
        localStorage.setItem("crezca_theme", currentTheme);
        localStorage.setItem("crezca_accent", currentAccent);

        showToast(
          "success",
          "Ajustes guardados correctamente",
          "Tus preferencias de identidad, login y accesos se han sincronizado."
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
      {/* Encabezado con Tabs SegmentedControl */}
      <div className={styles.header}>
        <div style={{ display: "flex", flexDirection: "column", gap: 4 }}>
          <h1 className={styles.title}>Ajustes y Personalización</h1>
          <p className={styles.subtitle}>
            Personaliza el logotipo, colores armónicos, pantalla de autenticación y flujos de acceso.
          </p>
        </div>

        <div className={styles.tabBarContainer}>
          <SegmentedControl<SettingsTabId>
            options={SETTINGS_TABS}
            value={activeTab}
            onChange={(tab) => setActiveTab(tab)}
            size="md"
          />
        </div>
      </div>

      {/* Contenedor tipo panel con filas e inspectores */}
      <div className={styles.settingsCard}>
        {/* ============================================================== */}
        {/* TAB 1: GENERAL & MARCA                                          */}
        {/* ============================================================== */}
        {activeTab === "general" && (
          <>
            {/* FILA 1: LOGOTIPO DE LA COMPAÑÍA / ACADEMIA */}
            <div className={styles.settingsRow}>
              <div className={styles.rowInfo}>
                <h3 className={styles.rowTitle}>Logotipo de la Academia</h3>
                <p className={styles.rowDesc}>
                  Actualiza el logotipo oficial mostrado en el menú superior y lateral.
                </p>
              </div>
              <div className={styles.rowContent}>
                <div className={styles.dropzoneContainer}>
                  {localLogo && (
                    <div className={styles.imagePreviewBox}>
                      <img src={localLogo} alt="Logo de la Academia" className={styles.imagePreviewThumbnail} />
                      <div className={styles.imagePreviewDetails}>
                        <span className={styles.imagePreviewName}>Logotipo Activo</span>
                        <span className={styles.imagePreviewSub}>Visible en el campus y correos</span>
                      </div>
                      <Button
                        variant="ghost"
                        size="sm"
                        onClick={handleRemoveLogo}
                        title="Quitar logotipo y usar icono por defecto"
                      >
                        <Trash2 size={13} /> Quitar
                      </Button>
                    </div>
                  )}

                  <FileDropzone
                    accept="image/png,image/jpeg,image/svg+xml,image/webp,image/gif"
                    multiple={false}
                    maxFiles={1}
                    showList={false}
                    label={localLogo ? "Arrastra otra imagen para reemplazar el logo" : "Arrastra el logotipo de la academia aquí"}
                    description="o haz clic para examinar tus archivos (PNG, SVG, JPG, WEBP)"
                    note="Recomendado: Formato SVG o PNG con transparencia (máx. 10MB)"
                    onFilesChange={async (files) => {
                      if (files && files.length > 0) {
                        setIsUploadingLogo(true);
                        try {
                          const uploadedUrl = await api.uploadLogo(files[0]);
                          if (uploadedUrl) {
                            handleLogoChange(uploadedUrl);
                            showToast("success", "Logotipo subido con éxito", "El nuevo logotipo ya está activo.");
                          } else {
                            const localUrl = URL.createObjectURL(files[0]);
                            handleLogoChange(localUrl);
                            showToast("info", "Vista previa local", "El logotipo se guardará al confirmar cambios.");
                          }
                        } catch {
                          showToast("danger", "Error", "No se pudo subir la imagen seleccionada.");
                        } finally {
                          setIsUploadingLogo(false);
                        }
                      }
                    }}
                  />
                </div>
              </div>
            </div>

            {/* FILA 2: NOMBRE DE LA ACADEMIA */}
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

            {/* FILA 3: COLOR DE MARCA (BRAND COLOR) */}
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

            {/* FILA 4: TEMA DE LA INTERFAZ */}
            <div className={styles.settingsRow}>
              <div className={styles.rowInfo}>
                <h3 className={styles.rowTitle}>Tema de la interfaz</h3>
                <p className={styles.rowDesc}>Selecciona el estilo visual que deseas para el panel.</p>
              </div>
              <div className={styles.rowContent}>
                <div className={styles.themeCardsGrid}>
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

            {/* FILA 5: BARRA LATERAL TRANSLÚCIDA */}
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

            {/* FILA 6: ELEMENTOS DEL MENÚ CON ARC UI SELECT */}
            <div className={styles.settingsRow}>
              <div className={styles.rowInfo}>
                <h3 className={styles.rowTitle}>Elementos del menú</h3>
                <p className={styles.rowDesc}>Configura qué accesos o estados se priorizan en la barra lateral.</p>
              </div>
              <div className={styles.rowContent}>
                <Select
                  options={[
                    { value: "recent", label: "Cambios recientes", sublabel: "Prioriza módulos editados recientemente" },
                    { value: "all", label: "Todos los módulos", sublabel: "Muestra la estructura completa" },
                    { value: "compact", label: "Modo esencial", sublabel: "Vista minimalista de navegación" },
                  ]}
                  value={sidebarFeature}
                  onChange={(val) => {
                    setSidebarFeature(val);
                    localStorage.setItem("crezca_sidebar_feature", val);
                  }}
                  style={{ width: "280px" }}
                />
              </div>
            </div>

            {/* FILA 7: VISTA DE TABLAS Y LISTAS */}
            <div className={styles.settingsRow}>
              <div className={styles.rowInfo}>
                <h3 className={styles.rowTitle}>Vista de tablas y listas</h3>
                <p className={styles.rowDesc}>Ajusta la densidad visual de las tablas de estudiantes y cursos.</p>
              </div>
              <div className={styles.rowContent}>
                <div className={styles.themeCardsGrid}>
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
          </>
        )}

        {/* ============================================================== */}
        {/* TAB 2: PANTALLA DE LOGIN                                        */}
        {/* ============================================================== */}
        {activeTab === "login" && (
          <>
            {/* FILA 1: SINCRONIZACIÓN Y COLOR DE MARCA ARMÓNICO */}
            <div className={styles.settingsRow}>
              <div className={styles.rowInfo}>
                <h3 className={styles.rowTitle}>Armonía de Colores con la Academia</h3>
                <p className={styles.rowDesc}>
                  Garantiza que el acceso público use una paleta complementaria al panel de estudiantes.
                </p>
              </div>
              <div className={styles.rowContent}>
                <div className={styles.colorSyncCard}>
                  <div className={styles.colorSyncHeader}>
                    <div className={styles.colorSyncTitleWrap}>
                      <div className={styles.colorSyncTitle}>
                        <Sparkles size={16} style={{ color: "var(--control-on, #7747ff)" }} />
                        <span>Sincronizar colores con la Academia automáticamente</span>
                      </div>
                      <p className={styles.colorSyncDesc}>
                        El degradado del Hero, botones e indicadores adoptan el color de marca activo ({currentAccent}) sin configuraciones manuales.
                      </p>
                    </div>
                    <Switch
                      checked={authSettings.sync_academy_colors}
                      onCheckedChange={(checked) => setAuthSettings((prev) => ({ ...prev, sync_academy_colors: checked }))}
                    />
                  </div>

                  {authSettings.sync_academy_colors && (
                    <div className={styles.colorSyncPreviewRow}>
                      <div className={styles.colorSyncGradientBox} style={{ background: complementaryGradient }}>
                        Degradado Hero
                      </div>
                      <div style={{ display: "flex", flexDirection: "column", gap: 4 }}>
                        <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
                          <span style={{ fontSize: 12, color: "var(--text-muted)" }}>Color de acento en botones:</span>
                          <span className={styles.colorSyncPill} style={{ backgroundColor: currentAccent.startsWith("#") ? currentAccent : "#7747ff" }}>
                            <Check size={11} strokeWidth={3} /> {currentAccent}
                          </span>
                        </div>
                        <span style={{ fontSize: 11.5, color: "var(--text-muted)" }}>
                          Se genera automáticamente un contraste armónico profundo y moderno para el acceso web y móvil.
                        </span>
                      </div>
                    </div>
                  )}
                </div>

                {/* Si no está sincronizado, controles manuales de color y fondo */}
                {!authSettings.sync_academy_colors && (
                  <div className={styles.inputGroup} style={{ marginTop: 8 }}>
                    <label className={styles.fieldLabel}>Tipo de Fondo Manual:</label>
                    <Select
                      options={[
                        { value: "gradient", label: "Degradado Moderno", sublabel: "Transición fluida de dos colores" },
                        { value: "solid", label: "Color Plano / Sólido", sublabel: "Tonalidad uniforme limpia" },
                        { value: "image", label: "Imagen de Fondo", sublabel: "Fotografía con capa de oscurecimiento" },
                      ]}
                      value={authSettings.bg_type}
                      onChange={(val) => setAuthSettings((prev) => ({ ...prev, bg_type: val as any }))}
                      style={{ width: "280px" }}
                    />

                    {authSettings.bg_type === "gradient" && (
                      <div className={styles.grid2Col} style={{ marginTop: 8 }}>
                        <div>
                          <label className={styles.fieldLabel}>Color Inicial:</label>
                          <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
                            <input
                              type="color"
                              value={authSettings.bg_gradient_start.startsWith("#") ? authSettings.bg_gradient_start : "#1d68ed"}
                              onChange={(e) => setAuthSettings((prev) => ({ ...prev, bg_gradient_start: e.target.value }))}
                              style={{ width: 36, height: 36, border: "none", borderRadius: 8, cursor: "pointer", background: "none" }}
                            />
                            <Input
                              value={authSettings.bg_gradient_start}
                              onChange={(e) => setAuthSettings((prev) => ({ ...prev, bg_gradient_start: e.target.value }))}
                            />
                          </div>
                        </div>
                        <div>
                          <label className={styles.fieldLabel}>Color Final:</label>
                          <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
                            <input
                              type="color"
                              value={authSettings.bg_gradient_end.startsWith("#") ? authSettings.bg_gradient_end : "#0b3294"}
                              onChange={(e) => setAuthSettings((prev) => ({ ...prev, bg_gradient_end: e.target.value }))}
                              style={{ width: 36, height: 36, border: "none", borderRadius: 8, cursor: "pointer", background: "none" }}
                            />
                            <Input
                              value={authSettings.bg_gradient_end}
                              onChange={(e) => setAuthSettings((prev) => ({ ...prev, bg_gradient_end: e.target.value }))}
                            />
                          </div>
                        </div>
                      </div>
                    )}

                    {authSettings.bg_type === "solid" && (
                      <div style={{ maxWidth: 320, marginTop: 8 }}>
                        <label className={styles.fieldLabel}>Color de Fondo Sólido:</label>
                        <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
                          <input
                            type="color"
                            value={authSettings.bg_color.startsWith("#") ? authSettings.bg_color : "#090d16"}
                            onChange={(e) => setAuthSettings((prev) => ({ ...prev, bg_color: e.target.value }))}
                            style={{ width: 36, height: 36, border: "none", borderRadius: 8, cursor: "pointer", background: "none" }}
                          />
                          <Input
                            value={authSettings.bg_color}
                            onChange={(e) => setAuthSettings((prev) => ({ ...prev, bg_color: e.target.value }))}
                          />
                        </div>
                      </div>
                    )}

                    {authSettings.bg_type === "image" && (
                      <div className={styles.dropzoneContainer} style={{ marginTop: 8 }}>
                        <label className={styles.fieldLabel}>Imagen de Fondo para el Login:</label>
                        {authSettings.bg_image_url && (
                          <div className={styles.imagePreviewBox}>
                            <img src={authSettings.bg_image_url} alt="Fondo Login" className={styles.bannerPreviewThumbnail} />
                            <div className={styles.imagePreviewDetails}>
                              <span className={styles.imagePreviewName}>Fondo Fotográfico Activo</span>
                              <span className={styles.imagePreviewSub}>Se aplica capa oscura para garantizar lectura</span>
                            </div>
                            <Button
                              variant="ghost"
                              size="sm"
                              onClick={() => setAuthSettings((prev) => ({ ...prev, bg_image_url: "" }))}
                            >
                              <Trash2 size={13} /> Quitar
                            </Button>
                          </div>
                        )}
                        <FileDropzone
                          accept="image/png,image/jpeg,image/webp"
                          multiple={false}
                          maxFiles={1}
                          showList={false}
                          label={authSettings.bg_image_url ? "Arrastra otra imagen para reemplazar el fondo" : "Arrastra la imagen de fondo del login aquí"}
                          description="o haz clic para examinar (JPG, PNG, WEBP de alta resolución)"
                          note="Recomendado: 1920x1080 px (16:9)"
                          onFilesChange={async (files) => {
                            if (files && files.length > 0) {
                              try {
                                const uploadedUrl = await api.uploadLogo(files[0]);
                                if (uploadedUrl) {
                                  setAuthSettings((prev) => ({ ...prev, bg_image_url: uploadedUrl }));
                                  showToast("success", "Fondo actualizado", "La imagen de fondo del login fue cargada.");
                                }
                              } catch {
                                showToast("danger", "Error", "No se pudo subir la imagen de fondo.");
                              }
                            }
                          }}
                        />
                      </div>
                    )}

                    <div style={{ marginTop: 12 }}>
                      <label className={styles.fieldLabel}>Color de Acento en Botones y Enlaces:</label>
                      <div style={{ display: "flex", alignItems: "center", gap: 8, maxWidth: 320 }}>
                        <input
                          type="color"
                          value={authSettings.accent_color.startsWith("#") ? authSettings.accent_color : "#1d68ed"}
                          onChange={(e) => setAuthSettings((prev) => ({ ...prev, accent_color: e.target.value }))}
                          style={{ width: 36, height: 36, border: "none", borderRadius: 8, cursor: "pointer", background: "none" }}
                        />
                        <Input
                          value={authSettings.accent_color}
                          onChange={(e) => setAuthSettings((prev) => ({ ...prev, accent_color: e.target.value }))}
                        />
                      </div>
                    </div>
                  </div>
                )}
              </div>
            </div>

            {/* FILA 2: LOGOTIPO DE LA PANTALLA DE LOGIN CON FILEDROPZONE */}
            <div className={styles.settingsRow}>
              <div className={styles.rowInfo}>
                <h3 className={styles.rowTitle}>Logotipo de la Pantalla de Login</h3>
                <p className={styles.rowDesc}>
                  Logo que se muestra en el panel de bienvenida y en smartphones sobre el formulario.
                </p>
              </div>
              <div className={styles.rowContent}>
                <div className={styles.dropzoneContainer}>
                  {authSettings.logo_url ? (
                    <div className={styles.imagePreviewBox}>
                      <img src={authSettings.logo_url} alt="Logo Login" className={styles.imagePreviewThumbnail} />
                      <div className={styles.imagePreviewDetails}>
                        <span className={styles.imagePreviewName}>Logotipo Personalizado de Login</span>
                        <span className={styles.imagePreviewSub}>Ancho configurado: {authSettings.logo_width || 160}px</span>
                      </div>
                      <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => setAuthSettings((prev) => ({ ...prev, logo_url: "" }))}
                      >
                        <Trash2 size={13} /> Usar logo de academia
                      </Button>
                    </div>
                  ) : (
                    <div style={{ fontSize: 12, color: "var(--text-muted)", padding: "2px 0" }}>
                      Actualmente utiliza el logotipo oficial de la Academia. Sube uno aquí si deseas una versión diferente para el login.
                    </div>
                  )}

                  <FileDropzone
                    accept="image/png,image/jpeg,image/svg+xml,image/webp,image/gif"
                    multiple={false}
                    maxFiles={1}
                    showList={false}
                    label={authSettings.logo_url ? "Arrastra otra imagen para reemplazar el logo de login" : "Arrastra el logotipo para el login aquí"}
                    description="o haz clic para examinar tus archivos (PNG, SVG, JPG, WEBP)"
                    note="Recomendado: Formato horizontal con fondo transparente"
                    onFilesChange={async (files) => {
                      if (files && files.length > 0) {
                        try {
                          const uploadedUrl = await api.uploadLogo(files[0]);
                          if (uploadedUrl) {
                            setAuthSettings((prev) => ({ ...prev, logo_url: uploadedUrl }));
                            showToast("success", "Logo de login subido", "El logotipo para la pantalla de acceso fue actualizado.");
                          }
                        } catch {
                          showToast("danger", "Error", "No se pudo subir la imagen.");
                        }
                      }
                    }}
                  />

                  <div style={{ maxWidth: 220, marginTop: 4 }}>
                    <label className={styles.fieldLabel}>Ancho Máximo del Logo (px):</label>
                    <Input
                      type="number"
                      min={60}
                      max={400}
                      value={String(authSettings.logo_width || 160)}
                      onChange={(e) => setAuthSettings((prev) => ({ ...prev, logo_width: Number(e.target.value) || 160 }))}
                    />
                    <span className={styles.fieldHint}>Recomendado: 140px a 200px para nitidez.</span>
                  </div>
                </div>
              </div>
            </div>

            {/* FILA 3: TEXTOS DEL HERO & TARJETAS DE PASOS */}
            <div className={styles.settingsRow}>
              <div className={styles.rowInfo}>
                <h3 className={styles.rowTitle}>Textos del Hero & Tarjetas</h3>
                <p className={styles.rowDesc}>
                  Personaliza el mensaje motivacional, la píldora superior y las 3 tarjetas de beneficios.
                </p>
              </div>
              <div className={styles.rowContent}>
                <div className={styles.inputGroup}>
                  <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: 8 }}>
                    <span className={styles.fieldLabel}>Píldora / Badge superior destacada:</span>
                    <Switch
                      checked={authSettings.hero_badge_enabled}
                      onCheckedChange={(checked) => setAuthSettings((prev) => ({ ...prev, hero_badge_enabled: checked }))}
                    />
                  </div>

                  {authSettings.hero_badge_enabled && (
                    <div style={{ marginBottom: 12 }}>
                      <Input
                        value={authSettings.hero_badge_text}
                        onChange={(e) => setAuthSettings((prev) => ({ ...prev, hero_badge_text: e.target.value }))}
                        placeholder="Ej. Join Us to Build 🤩"
                      />
                    </div>
                  )}

                  <div style={{ marginBottom: 12 }}>
                    <label className={styles.fieldLabel}>Título Principal:</label>
                    <Input
                      value={authSettings.hero_title}
                      onChange={(e) => setAuthSettings((prev) => ({ ...prev, hero_title: e.target.value }))}
                      placeholder="Ej. Start your Journey"
                    />
                  </div>

                  <div style={{ marginBottom: 14 }}>
                    <label className={styles.fieldLabel}>Subtítulo Descriptivo:</label>
                    <Input
                      value={authSettings.hero_subtitle}
                      onChange={(e) => setAuthSettings((prev) => ({ ...prev, hero_subtitle: e.target.value }))}
                      placeholder="Ej. Follow these simple steps to set up your account and start learning today."
                    />
                  </div>

                  <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", margin: "14px 0 10px 0" }}>
                    <span className={styles.fieldLabel}>Tarjetas de Pasos / Beneficios:</span>
                    <Switch
                      checked={authSettings.steps_enabled}
                      onCheckedChange={(checked) => setAuthSettings((prev) => ({ ...prev, steps_enabled: checked }))}
                    />
                  </div>

                  {authSettings.steps_enabled && (
                    <div className={styles.grid3Col}>
                      <div className={styles.authStepCardBox}>
                        <label className={styles.fieldLabel}>Paso 1:</label>
                        <Input
                          style={{ height: 32 }}
                          value={authSettings.step_1_num}
                          onChange={(e) => setAuthSettings((prev) => ({ ...prev, step_1_num: e.target.value }))}
                          placeholder="1"
                        />
                        <Input
                          value={authSettings.step_1_text.replace(/\n/g, " ")}
                          onChange={(e) => setAuthSettings((prev) => ({ ...prev, step_1_text: e.target.value }))}
                          placeholder="Crea tu cuenta"
                        />
                      </div>

                      <div className={styles.authStepCardBox}>
                        <label className={styles.fieldLabel}>Paso 2:</label>
                        <Input
                          style={{ height: 32 }}
                          value={authSettings.step_2_num}
                          onChange={(e) => setAuthSettings((prev) => ({ ...prev, step_2_num: e.target.value }))}
                          placeholder="2"
                        />
                        <Input
                          value={authSettings.step_2_text.replace(/\n/g, " ")}
                          onChange={(e) => setAuthSettings((prev) => ({ ...prev, step_2_text: e.target.value }))}
                          placeholder="Completa tu perfil"
                        />
                      </div>

                      <div className={styles.authStepCardBox}>
                        <label className={styles.fieldLabel}>Paso 3:</label>
                        <Input
                          style={{ height: 32 }}
                          value={authSettings.step_3_num}
                          onChange={(e) => setAuthSettings((prev) => ({ ...prev, step_3_num: e.target.value }))}
                          placeholder="3"
                        />
                        <Input
                          value={authSettings.step_3_text.replace(/\n/g, " ")}
                          onChange={(e) => setAuthSettings((prev) => ({ ...prev, step_3_text: e.target.value }))}
                          placeholder="Accede a tus cursos"
                        />
                      </div>
                    </div>
                  )}
                </div>
              </div>
            </div>

            {/* FILA 4: FORMULARIO Y ENLACES LEGALES */}
            <div className={styles.settingsRow}>
              <div className={styles.rowInfo}>
                <h3 className={styles.rowTitle}>Formulario & Políticas Legales</h3>
                <p className={styles.rowDesc}>
                  Personaliza el saludo del formulario, registro público y enlaces legales.
                </p>
              </div>
              <div className={styles.rowContent}>
                <div className={styles.inputGroup}>
                  <div className={styles.grid2Col} style={{ marginBottom: 12 }}>
                    <div>
                      <label className={styles.fieldLabel}>Título del Formulario:</label>
                      <Input
                        value={authSettings.login_title}
                        onChange={(e) => setAuthSettings((prev) => ({ ...prev, login_title: e.target.value }))}
                        placeholder="Welcome Back"
                      />
                    </div>
                    <div>
                      <label className={styles.fieldLabel}>Subtítulo del Formulario:</label>
                      <Input
                        value={authSettings.login_subtitle}
                        onChange={(e) => setAuthSettings((prev) => ({ ...prev, login_subtitle: e.target.value }))}
                        placeholder="Inicia sesión en tu cuenta para continuar"
                      />
                    </div>
                  </div>

                  <div style={{ marginBottom: 14 }}>
                    <label className={styles.fieldLabel}>Aspecto / Fondo del Formulario (White vs Dark):</label>
                    <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 10, marginTop: 6 }}>
                      <div
                        style={{
                          display: "flex",
                          alignItems: "center",
                          gap: 10,
                          padding: "10px 14px",
                          borderRadius: "var(--radius-panel, 12px)",
                          border: `1.5px solid ${authSettings.form_theme !== "dark" ? "var(--control-on, #7747ff)" : "var(--border)"}`,
                          background: authSettings.form_theme !== "dark" ? "var(--control-on-subtle, rgba(119, 71, 255, 0.08))" : "var(--surface)",
                          cursor: "pointer",
                          transition: "all 0.2s ease",
                        }}
                        onClick={() => setAuthSettings((prev) => ({ ...prev, form_theme: "light" }))}
                      >
                        <div style={{ width: 22, height: 22, borderRadius: "50%", background: "#ffffff", border: "1px solid #cbd5e1", display: "flex", alignItems: "center", justifyContent: "center" }}>
                          {authSettings.form_theme !== "dark" && <Check size={12} color="#0f172a" strokeWidth={3} />}
                        </div>
                        <div>
                          <div style={{ fontSize: 13, fontWeight: 600, color: "var(--foreground)" }}>Claro (White)</div>
                          <div style={{ fontSize: 11.5, color: "var(--text-muted)" }}>Superficie blanca limpia tradicional</div>
                        </div>
                      </div>

                      <div
                        style={{
                          display: "flex",
                          alignItems: "center",
                          gap: 10,
                          padding: "10px 14px",
                          borderRadius: "var(--radius-panel, 12px)",
                          border: `1.5px solid ${authSettings.form_theme === "dark" ? "var(--control-on, #7747ff)" : "var(--border)"}`,
                          background: authSettings.form_theme === "dark" ? "var(--control-on-subtle, rgba(119, 71, 255, 0.08))" : "var(--surface)",
                          cursor: "pointer",
                          transition: "all 0.2s ease",
                        }}
                        onClick={() => setAuthSettings((prev) => ({ ...prev, form_theme: "dark" }))}
                      >
                        <div style={{ width: 22, height: 22, borderRadius: "50%", background: "#0f121a", border: "1px solid rgba(255,255,255,0.2)", display: "flex", alignItems: "center", justifyContent: "center" }}>
                          {authSettings.form_theme === "dark" && <Check size={12} color="#ffffff" strokeWidth={3} />}
                        </div>
                        <div>
                          <div style={{ fontSize: 13, fontWeight: 600, color: "var(--foreground)" }}>Oscuro (Dark)</div>
                          <div style={{ fontSize: 11.5, color: "var(--text-muted)" }}>Superficie grafito oscuro complementario</div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", padding: "10px 0", borderTop: "1px solid var(--border-subtle)", borderBottom: "1px solid var(--border-subtle)", marginBottom: 12 }}>
                    <div>
                      <div style={{ fontWeight: 600, fontSize: 13, color: "var(--foreground)" }}>Permitir Registro Público Directo</div>
                      <div className={styles.fieldHint}>Si lo desactivas, solo los usuarios matriculados podrán acceder.</div>
                    </div>
                    <Switch
                      checked={authSettings.allow_registration}
                      onCheckedChange={(checked) => setAuthSettings((prev) => ({ ...prev, allow_registration: checked }))}
                    />
                  </div>

                  <div className={styles.grid2Col}>
                    <div>
                      <label className={styles.fieldLabel}>URL de Términos y Condiciones:</label>
                      <Input
                        value={authSettings.terms_url}
                        onChange={(e) => setAuthSettings((prev) => ({ ...prev, terms_url: e.target.value }))}
                        placeholder="/terminos-y-condiciones"
                      />
                    </div>
                    <div>
                      <label className={styles.fieldLabel}>URL de Política de Privacidad:</label>
                      <Input
                        value={authSettings.privacy_url}
                        onChange={(e) => setAuthSettings((prev) => ({ ...prev, privacy_url: e.target.value }))}
                        placeholder="/politica-de-privacidad"
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {/* FILA 5: CSS ADICIONAL Y PREVISUALIZACIÓN */}
            <div className={styles.settingsRow}>
              <div className={styles.rowInfo}>
                <h3 className={styles.rowTitle}>CSS Adicional & Vista Previa</h3>
                <p className={styles.rowDesc}>
                  Añade reglas CSS personalizadas y prueba tu pantalla de login en vivo.
                </p>
              </div>
              <div className={styles.rowContent}>
                <div className={styles.inputGroup}>
                  <label className={styles.fieldLabel}>Reglas CSS Personalizadas (opcional):</label>
                  <textarea
                    className={styles.textareaCustom}
                    placeholder=".alezux-auth-card { /* Tus estilos adicionales aquí */ }"
                    value={authSettings.custom_css}
                    onChange={(e) => setAuthSettings((prev) => ({ ...prev, custom_css: e.target.value }))}
                  />

                  <div style={{ marginTop: 12, display: "flex", gap: 10, alignItems: "center", flexWrap: "wrap" }}>
                    <Button
                      variant="secondary"
                      size="sm"
                      onClick={() => window.open("/wp-login.php?preview_auth=1", "_blank")}
                    >
                      <Eye size={14} /> Previsualizar Pantalla de Login
                    </Button>
                    <span className={styles.fieldHint}>Abre la pantalla de acceso en una nueva pestaña sin cerrar tu sesión actual.</span>
                  </div>
                </div>
              </div>
            </div>
          </>
        )}

        {/* ============================================================== */}
        {/* TAB 3: FLUJOS DE ACCESO & ROLES                                */}
        {/* ============================================================== */}
        {activeTab === "access" && (
          <>
            <div className={styles.settingsRow}>
              <div className={styles.rowInfo}>
                <h3 className={styles.rowTitle}>Destinos al Iniciar Sesión</h3>
                <p className={styles.rowDesc}>
                  Define a dónde aterriza cada rol tras autenticarse con éxito.
                </p>
              </div>
              <div className={styles.rowContent}>
                <div className={styles.inputGroup}>
                  <label className={styles.fieldLabel}>Destino inicial de Estudiantes al ingresar:</label>
                  <div className={styles.lmsModeGrid}>
                    <div
                      className={[styles.lmsModeTile, authSettings.student_default_view === "courses" ? styles.lmsModeTileActive : ""].join(" ")}
                      onClick={() => setAuthSettings((prev) => ({ ...prev, student_default_view: "courses" }))}
                    >
                      <div className={styles.lmsRadioDot}>
                        {authSettings.student_default_view === "courses" && <div className={styles.lmsRadioInner} />}
                      </div>
                      <div>
                        <div className={styles.lmsModeHeading}>
                          <span>Mis Cursos / Catálogo</span>
                          <span className={styles.recommendedBadge}>Recomendado</span>
                        </div>
                        <p className={styles.lmsModeText}>
                          Los alumnos van directo al listado de cursos para reanudar lecciones inmediatamente.
                        </p>
                      </div>
                    </div>

                    <div
                      className={[styles.lmsModeTile, authSettings.student_default_view === "dashboard" ? styles.lmsModeTileActive : ""].join(" ")}
                      onClick={() => setAuthSettings((prev) => ({ ...prev, student_default_view: "dashboard" }))}
                    >
                      <div className={styles.lmsRadioDot}>
                        {authSettings.student_default_view === "dashboard" && <div className={styles.lmsRadioInner} />}
                      </div>
                      <div>
                        <div className={styles.lmsModeHeading}>Dashboard del Estudiante</div>
                        <p className={styles.lmsModeText}>
                          Muestra resumen de avance, estadísticas de estudio y métricas personales de progreso.
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div className={styles.settingsRow}>
              <div className={styles.rowInfo}>
                <h3 className={styles.rowTitle}>Seguridad & Blindaje de WordPress</h3>
                <p className={styles.rowDesc}>
                  Aislamiento estricto de los estudiantes respecto al panel de administración.
                </p>
              </div>
              <div className={styles.rowContent}>
                <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
                  <span className={styles.securityBadgeNotice}>
                    <ShieldCheck size={18} />
                    <span><strong>Blindaje de WordPress Activo:</strong> Los estudiantes nunca verán la barra de herramientas ni tendrán acceso a <code>/wp-admin/</code>.</span>
                  </span>

                  <div style={{ display: "flex", flexDirection: "column", gap: 8, fontSize: 13, color: "var(--text-muted)", lineHeight: 1.5 }}>
                    <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
                      <CheckCircle2 size={15} style={{ color: "#0db879" }} />
                      <span>El botón <strong>"Volver a WordPress"</strong> y <strong>"Modo Administrador"</strong> están completamente ocultos para estudiantes.</span>
                    </div>
                    <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
                      <CheckCircle2 size={15} style={{ color: "#0db879" }} />
                      <span>Los administradores que inician sesión son redirigidos de inmediato a la academia a pantalla completa.</span>
                    </div>
                    <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
                      <CheckCircle2 size={15} style={{ color: "#0db879" }} />
                      <span>Cualquier intento directo de acceder a <code>/wp-admin/</code> por parte de un estudiante es redirigido al campus virtual.</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </>
        )}

        {/* ============================================================== */}
        {/* TAB 4: MOTOR LMS & MIGRACIÓN                                    */}
        {/* ============================================================== */}
        {activeTab === "lms" && (
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

              {/* Banner de migración & sincronización con 1 clic */}
              {((lmsSettings?.learndash_courses_count ?? 0) > 0 || (lmsSettings?.native_courses_count ?? 0) > 0) && (
                <div className={styles.migrationNotice}>
                  <div className={styles.migrationNoticeText}>
                    <strong>Sincronización & Migración LMS</strong>
                    <p>
                      Crezca LMS y LearnDash operan con sincronización automática de cursos y alumnos. Si acabas de migrar o importar formaciones, puedes sincronizar los accesos de todos tus alumnos inmediatamente.
                    </p>
                  </div>
                  <div style={{ display: "flex", gap: 8, flexWrap: "wrap", alignItems: "center" }}>
                    <Button
                      variant="secondary"
                      size="sm"
                      onClick={handleSyncEnrollments}
                      disabled={isSyncingEnrollments || isMigrating}
                    >
                      <RefreshCw size={13} className={isSyncingEnrollments ? styles.spinIcon : ""} />
                      {isSyncingEnrollments ? "Sincronizando..." : "Sincronizar Alumnos"}
                    </Button>
                    {(lmsSettings?.learndash_courses_count ?? 0) > 0 && (
                      <Button
                        variant="primary"
                        size="sm"
                        onClick={handleOpenMigrateModal}
                        disabled={isMigrating || isSyncingEnrollments}
                      >
                        <ArrowRightLeft size={13} /> Migrar Cursos
                      </Button>
                    )}
                  </div>
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
        )}
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
      <rect width="160" height="100" rx="8" fill="#f8fafc" />
      <g>
        <circle cx="10" cy="10" r="2.2" fill="#ef4444" />
        <circle cx="17" cy="10" r="2.2" fill="#f59e0b" />
        <circle cx="24" cy="10" r="2.2" fill="#10b981" />
        <line x1="0" y1="18" x2="80" y2="18" stroke="#e2e8f0" strokeWidth="1" />
        <circle cx="12" cy="27" r="3" fill="#cbd5e1" />
        <rect x="18" y="25.5" width="16" height="3" rx="1.5" fill="#94a3b8" />
        <rect x="8" y="35" width="26" height="2.5" rx="1.25" fill="#cbd5e1" />
        <rect x="8" y="42" width="22" height="2.5" rx="1.25" fill="#e2e8f0" />
        <rect x="8" y="49" width="24" height="2.5" rx="1.25" fill="#e2e8f0" />
        <rect x="8" y="56" width="20" height="2.5" rx="1.25" fill="#e2e8f0" />
        <line x1="38" y1="18" x2="38" y2="100" stroke="#e2e8f0" strokeWidth="1" />
        <text x="44" y="28" fontSize="5.5" fontWeight="600" fill="#64748b" fontFamily="system-ui">Your dashboard</text>
        <rect x="44" y="34" width="32" height="58" rx="4" fill="#ffffff" stroke="#e2e8f0" strokeWidth="1" />
        <rect x="48" y="40" width="18" height="3" rx="1.5" fill="#cbd5e1" />
        <rect x="48" y="47" width="24" height="2" rx="1" fill="#f1f5f9" />
      </g>
      <g>
        <rect x="80" y="0" width="80" height="100" fill="#1e293b" />
        <line x1="80" y1="18" x2="160" y2="18" stroke="#334155" strokeWidth="1" />
        <text x="86" y="28" fontSize="5.5" fontWeight="600" fill="#94a3b8" fontFamily="system-ui">Your dashboard</text>
        <rect x="86" y="34" width="34" height="58" rx="4" fill="#0f172a" stroke="#334155" strokeWidth="1" />
        <rect x="91" y="40" width="18" height="3" rx="1.5" fill="#475569" />
        <rect x="91" y="47" width="24" height="2" rx="1" fill="#1e293b" />
        <line x1="126" y1="18" x2="126" y2="100" stroke="#334155" strokeWidth="1" />
        <rect x="132" y="26" width="20" height="2.5" rx="1.25" fill="#475569" />
        <rect x="132" y="33" width="16" height="2.5" rx="1.25" fill="#334155" />
        <rect x="132" y="40" width="18" height="2.5" rx="1.25" fill="#334155" />
      </g>
      <line x1="80" y1="0" x2="80" y2="100" stroke="#94a3b8" strokeWidth="0.75" />
    </svg>
  );
}

function LightMockup() {
  return (
    <svg viewBox="0 0 160 100" fill="none" xmlns="http://www.w3.org/2000/svg" className={styles.mockupSvg}>
      <rect width="160" height="100" rx="8" fill="#f8fafc" />
      <circle cx="10" cy="10" r="2.2" fill="#ef4444" />
      <circle cx="17" cy="10" r="2.2" fill="#f59e0b" />
      <circle cx="24" cy="10" r="2.2" fill="#10b981" />
      <line x1="0" y1="18" x2="160" y2="18" stroke="#e2e8f0" strokeWidth="1" />
      <circle cx="12" cy="27" r="3" fill="#cbd5e1" />
      <rect x="18" y="25.5" width="18" height="3" rx="1.5" fill="#94a3b8" />
      <rect x="8" y="35" width="28" height="2.5" rx="1.25" fill="#cbd5e1" />
      <rect x="8" y="42" width="22" height="2.5" rx="1.25" fill="#e2e8f0" />
      <rect x="8" y="49" width="26" height="2.5" rx="1.25" fill="#e2e8f0" />
      <rect x="8" y="56" width="20" height="2.5" rx="1.25" fill="#e2e8f0" />
      <rect x="8" y="63" width="24" height="2.5" rx="1.25" fill="#e2e8f0" />
      <line x1="42" y1="18" x2="42" y2="100" stroke="#e2e8f0" strokeWidth="1" />
      <text x="50" y="28" fontSize="5.5" fontWeight="600" fill="#475569" fontFamily="system-ui">Your dashboard</text>
      <rect x="124" y="23" width="26" height="7" rx="3.5" fill="#e2e8f0" />
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
      <circle cx="10" cy="10" r="2.2" fill="#ef4444" />
      <circle cx="17" cy="10" r="2.2" fill="#f59e0b" />
      <circle cx="24" cy="10" r="2.2" fill="#10b981" />
      <line x1="0" y1="18" x2="160" y2="18" stroke="#27272a" strokeWidth="1" />
      <circle cx="12" cy="27" r="3" fill="#71717a" />
      <rect x="18" y="25.5" width="18" height="3" rx="1.5" fill="#a1a1aa" />
      <rect x="8" y="35" width="28" height="2.5" rx="1.25" fill="#52525b" />
      <rect x="8" y="42" width="22" height="2.5" rx="1.25" fill="#3f3f46" />
      <rect x="8" y="49" width="26" height="2.5" rx="1.25" fill="#3f3f46" />
      <rect x="8" y="56" width="20" height="2.5" rx="1.25" fill="#3f3f46" />
      <rect x="8" y="63" width="24" height="2.5" rx="1.25" fill="#3f3f46" />
      <line x1="42" y1="18" x2="42" y2="100" stroke="#27272a" strokeWidth="1" />
      <text x="50" y="28" fontSize="5.5" fontWeight="600" fill="#a1a1aa" fontFamily="system-ui">Your dashboard</text>
      <rect x="124" y="23" width="26" height="7" rx="3.5" fill="#27272a" />
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
      <circle cx="16" cy="34" r="4" fill="#cbd5e1" />
      <rect x="25" y="32" width="26" height="4" rx="2" fill="#94a3b8" />
      <rect x="68" y="33" width="22" height="3" rx="1.5" fill="#cbd5e1" />
      <rect x="104" y="33" width="18" height="3" rx="1.5" fill="#cbd5e1" />
      <rect x="134" y="32" width="14" height="4" rx="2" fill="#bbf7d0" />
      <line x1="12" y1="44" x2="148" y2="44" stroke="#f1f5f9" strokeWidth="1" />
      <circle cx="16" cy="54" r="4" fill="#cbd5e1" />
      <rect x="25" y="52" width="30" height="4" rx="2" fill="#94a3b8" />
      <rect x="68" y="53" width="24" height="3" rx="1.5" fill="#cbd5e1" />
      <rect x="104" y="53" width="16" height="3" rx="1.5" fill="#cbd5e1" />
      <rect x="134" y="52" width="14" height="4" rx="2" fill="#fed7aa" />
      <line x1="12" y1="64" x2="148" y2="64" stroke="#f1f5f9" strokeWidth="1" />
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

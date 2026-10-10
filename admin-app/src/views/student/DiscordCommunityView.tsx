import React, { useState, useEffect, useRef, useMemo } from "react";
import {
  Hash,
  Volume2,
  Lock,
  Plus,
  Trash2,
  Edit3,
  Send,
  MessageSquare,
  RefreshCw,
  ExternalLink,
  Crown,
  Sparkles,
  ShieldCheck,
  ChevronDown,
  Info,
  CheckCircle2,
  Video,
  AlertCircle,
  Globe,
  Folder,
  FolderPlus,
  X
} from "lucide-react";
import {
  api,
  Course,
  CommunityChannel,
  CommunityMessage,
  CreateCommunityChannelPayload,
} from "../../services/api";
import { Modal } from "../../components/arc/modal";
import { Select, SelectOption } from "../../components/arc/select";
import styles from "./DiscordCommunityView.module.css";

interface DiscordCommunityViewProps {
  courses?: Course[];
  academyName?: string;
  isAdmin?: boolean;
  currentUser?: {
    id: number;
    name: string;
    avatar?: string;
    role?: string;
  };
  onNavigateToCourse?: (courseId: number) => void;
}

const QUICK_EMOJIS = ["🚀", "🔥", "💡", "❤️", "👍", "👏", "🎉", "🙌"];

export const DiscordCommunityView: React.FC<DiscordCommunityViewProps> = ({
  courses = [],
  academyName = "Academia",
  isAdmin = false,
  currentUser,
  onNavigateToCourse,
}) => {
  // Canales
  const [channels, setChannels] = useState<CommunityChannel[]>([]);
  const [activeChannelId, setActiveChannelId] = useState<number | null>(null);
  const [channelsLoading, setChannelsLoading] = useState(true);

  // Mensajes
  const [messages, setMessages] = useState<CommunityMessage[]>([]);
  const [messagesLoading, setMessagesLoading] = useState(false);
  const [newMessage, setNewMessage] = useState("");
  const [isSending, setIsSending] = useState(false);

  // Categorías colapsadas en la barra lateral
  const [collapsedCategories, setCollapsedCategories] = useState<Record<string, boolean>>({});

  // Modales
  const [isChannelModalOpen, setIsChannelModalOpen] = useState(false);
  const [editingChannel, setEditingChannel] = useState<CommunityChannel | null>(null);
  const [channelForm, setChannelForm] = useState<CreateCommunityChannelPayload>({
    name: "",
    description: "",
    category: "General",
    course_id: 0,
    is_announcement: false,
  });

  // Estado para selección de categoría y restricción con Arc Select
  const NEW_CATEGORY_OPTION = "__NEW_CATEGORY__";
  const [selectedCategoryKey, setSelectedCategoryKey] = useState<string>("General");
  const [isCustomCategory, setIsCustomCategory] = useState<boolean>(false);
  const [customCategoryText, setCustomCategoryText] = useState<string>("");

  const [channelToDelete, setChannelToDelete] = useState<CommunityChannel | null>(null);
  const [isDeletingChannel, setIsDeletingChannel] = useState(false);

  // Lista de cursos para el dropdown de restricciones
  const [availableCourses, setAvailableCourses] = useState<Course[]>(courses);

  const messagesEndRef = useRef<HTMLDivElement | null>(null);
  const textareaRef = useRef<HTMLTextAreaElement | null>(null);

  // Cargar cursos si no vienen en props
  useEffect(() => {
    if (courses.length > 0) {
      setAvailableCourses(courses);
    } else {
      api.getCourses().then((res) => {
        if (Array.isArray(res) && res.length > 0) {
          setAvailableCourses(res);
        }
      }).catch((e) => console.warn("Error cargando cursos en comunidad:", e));
    }
  }, [courses]);

  // 1. Cargar canales al montar
  const loadChannels = async (keepActive = true) => {
    try {
      const data = await api.getCommunityChannels();
      setChannels(data);

      if (data.length > 0) {
        if (!keepActive || !activeChannelId || !data.some((c) => c.id === activeChannelId)) {
          setActiveChannelId(data[0].id);
        }
      }
    } catch (err) {
      console.error("Error al cargar canales:", err);
    } finally {
      setChannelsLoading(false);
    }
  };

  useEffect(() => {
    loadChannels(false);
  }, []);

  // Canal activo actual
  const activeChannel = useMemo(() => {
    return channels.find((c) => c.id === activeChannelId) || null;
  }, [channels, activeChannelId]);

  // 2. Cargar mensajes del canal activo
  const loadMessages = async (channelId: number) => {
    setMessagesLoading(true);
    try {
      const res = await api.getChannelMessages(channelId);
      setMessages(res.messages || []);
    } catch (err) {
      console.error("Error cargando mensajes:", err);
    } finally {
      setMessagesLoading(false);
    }
  };

  useEffect(() => {
    if (activeChannelId) {
      loadMessages(activeChannelId);
    }
  }, [activeChannelId]);

  // Polling periódico ligero cada 8 segundos para mensajes nuevos
  useEffect(() => {
    if (!activeChannelId) return;
    const interval = setInterval(async () => {
      try {
        const res = await api.getChannelMessages(activeChannelId);
        if (res && Array.isArray(res.messages)) {
          setMessages((prev) => {
            if (prev.length !== res.messages.length) {
              return res.messages;
            }
            return prev;
          });
        }
      } catch (_) {}
    }, 8000);

    return () => clearInterval(interval);
  }, [activeChannelId]);

  // Scroll automático hacia el final del chat
  const scrollToBottom = (behavior: ScrollBehavior = "smooth") => {
    if (messagesEndRef.current) {
      messagesEndRef.current.scrollIntoView({ behavior });
    }
  };

  useEffect(() => {
    scrollToBottom("auto");
  }, [messages.length, activeChannelId]);

  // Agrupar canales por categoría
  const groupedChannels = useMemo(() => {
    const groups: Record<string, CommunityChannel[]> = {};
    channels.forEach((ch) => {
      const cat = ch.category || "General";
      if (!groups[cat]) {
        groups[cat] = [];
      }
      groups[cat].push(ch);
    });
    return groups;
  }, [channels]);

  // Lista de categorías existentes únicas
  const existingCategories = useMemo(() => {
    const set = new Set<string>();
    channels.forEach((ch) => {
      if (ch.category && ch.category.trim()) {
        set.add(ch.category.trim());
      }
    });
    if (set.size === 0) {
      set.add("General");
    }
    return Array.from(set);
  }, [channels]);

  // Opciones para el Select de Categorías (con Lucide Folder y FolderPlus)
  const categorySelectOptions: SelectOption<string>[] = useMemo(() => {
    const list: SelectOption<string>[] = existingCategories.map((cat) => {
      const count = channels.filter((c) => c.category === cat).length;
      return {
        value: cat,
        label: cat,
        sublabel: `${count} ${count === 1 ? "canal" : "canales"}`,
        icon: <Folder size={15} color="var(--accent)" />,
      };
    });

    list.push({
      value: NEW_CATEGORY_OPTION,
      label: "+ Crear nueva categoría...",
      sublabel: "Escribir un nuevo grupo",
      icon: <FolderPlus size={15} color="#10b981" />,
    });

    return list;
  }, [existingCategories, channels]);

  // Opciones para el Select de Restricciones (con Lucide Globe y Lock)
  const restrictionOptions: SelectOption<number>[] = useMemo(() => {
    const list: SelectOption<number>[] = [
      {
        value: 0,
        label: "Público para todos los estudiantes",
        sublabel: "Sin restricción de curso (Canal abierto)",
        icon: <Globe size={15} color="var(--accent)" />,
      },
    ];

    availableCourses.forEach((crs) => {
      list.push({
        value: crs.id,
        label: crs.title,
        sublabel: "Exclusivo para alumnos con este curso activo",
        icon: <Lock size={14} color="#f59e0b" />,
      });
    });

    return list;
  }, [availableCourses]);

  // Alternar categoría colapsada
  const toggleCategory = (cat: string) => {
    setCollapsedCategories((prev) => ({
      ...prev,
      [cat]: !prev[cat],
    }));
  };

  // Abrir modal para crear canal
  const handleOpenCreateModal = (defaultCategory = "General") => {
    setEditingChannel(null);
    const cat = defaultCategory || existingCategories[0] || "General";
    setSelectedCategoryKey(cat);
    setIsCustomCategory(false);
    setCustomCategoryText("");
    setChannelForm({
      name: "",
      description: "",
      category: cat,
      course_id: 0,
      is_announcement: false,
    });
    setIsChannelModalOpen(true);
  };

  // Abrir modal para editar canal
  const handleOpenEditModal = (ch: CommunityChannel, e: React.MouseEvent) => {
    e.stopPropagation();
    setEditingChannel(ch);
    const cat = ch.category || "General";
    if (existingCategories.includes(cat)) {
      setSelectedCategoryKey(cat);
      setIsCustomCategory(false);
      setCustomCategoryText("");
    } else {
      setSelectedCategoryKey(NEW_CATEGORY_OPTION);
      setIsCustomCategory(true);
      setCustomCategoryText(cat);
    }
    setChannelForm({
      name: ch.name,
      description: ch.description || "",
      category: cat,
      course_id: ch.course_id || 0,
      is_announcement: !!ch.is_announcement,
    });
    setIsChannelModalOpen(true);
  };

  // Guardar canal (crear o editar)
  const handleSaveChannel = async () => {
    if (!channelForm.name.trim()) return;
    const finalCategory = isCustomCategory
      ? (customCategoryText.trim() || "General")
      : (selectedCategoryKey || "General");

    try {
      const cleanSlug = channelForm.name
        .toLowerCase()
        .replace(/#/g, "")
        .replace(/\s+/g, "-")
        .replace(/[^a-z0-9_-]/g, "");

      if (editingChannel) {
        const updated = await api.updateCommunityChannel(editingChannel.id, {
          ...channelForm,
          category: finalCategory,
          name: cleanSlug,
        });
        setChannels(updated);
      } else {
        const updated = await api.createCommunityChannel({
          ...channelForm,
          category: finalCategory,
          name: cleanSlug,
        });
        setChannels(updated);
        const created = updated.find((c) => c.name === cleanSlug);
        if (created) {
          setActiveChannelId(created.id);
        }
      }
      setIsChannelModalOpen(false);
    } catch (err) {
      console.error("Error al guardar canal:", err);
    }
  };

  // Confirmar y eliminar canal
  const handleConfirmDeleteChannel = async () => {
    if (!channelToDelete) return;
    setIsDeletingChannel(true);
    try {
      const updated = await api.deleteCommunityChannel(channelToDelete.id);
      setChannels(updated);
      if (activeChannelId === channelToDelete.id) {
        setActiveChannelId(updated[0]?.id || null);
      }
      setChannelToDelete(null);
    } catch (err) {
      console.error("Error al borrar canal:", err);
    } finally {
      setIsDeletingChannel(false);
    }
  };

  // Enviar mensaje
  const handleSendMessage = async () => {
    if (!activeChannelId || !newMessage.trim() || isSending) return;
    const textToSend = newMessage.trim();
    setIsSending(true);
    try {
      const sentMsg = await api.sendChannelMessage(activeChannelId, textToSend);
      setMessages((prev) => [...prev, sentMsg]);
      setNewMessage("");
      if (textareaRef.current) {
        textareaRef.current.style.height = "auto";
      }
      setTimeout(() => scrollToBottom("smooth"), 100);
    } catch (err: any) {
      alert(err?.message || "No se pudo enviar el mensaje.");
    } finally {
      setIsSending(false);
    }
  };

  // Borrar mensaje
  const handleDeleteMessage = async (msgId: number) => {
    if (!window.confirm("¿Deseas eliminar este mensaje?")) return;
    try {
      await api.deleteChannelMessage(msgId, activeChannelId || undefined);
      setMessages((prev) => prev.filter((m) => m.id !== msgId));
    } catch (err) {
      console.error("Error al borrar mensaje:", err);
    }
  };

  // Insertar emoji
  const handleInsertEmoji = (emoji: string) => {
    setNewMessage((prev) => prev + emoji);
    if (textareaRef.current) {
      textareaRef.current.focus();
    }
  };

  // Tecla Enter para enviar
  const handleKeyDown = (e: React.KeyboardEvent<HTMLTextAreaElement>) => {
    if (e.key === "Enter" && !e.shiftKey) {
      e.preventDefault();
      handleSendMessage();
    }
  };

  // Auto expandir altura del textarea
  const handleTextareaChange = (e: React.ChangeEvent<HTMLTextAreaElement>) => {
    setNewMessage(e.target.value);
    e.target.style.height = "auto";
    e.target.style.height = `${Math.min(e.target.scrollHeight, 120)}px`;
  };

  // URLs externas configuradas en los cursos
  const courseWithWhatsApp = availableCourses.find((c) => c.whatsapp_url);
  const courseWithSlack = availableCourses.find((c) => c.slack_url);
  const courseWithZoom = availableCourses.find((c) => c.zoom_url);

  // Roles y visualización
  const effectiveIsAdmin = isAdmin || currentUser?.role === "admin";
  const channelIsLocked = Boolean(activeChannel && !activeChannel.has_access && !effectiveIsAdmin);
  const channelIsAnnouncement = Boolean(activeChannel?.is_announcement);
  const canSendMessages = !channelIsLocked && (!channelIsAnnouncement || effectiveIsAdmin);

  return (
    <div className={styles.discordContainer}>
      {/* =========================================================
          1. BARRA LATERAL ESTILO DISCORD (CANALES & CATEGORÍAS)
          ========================================================= */}
      <aside className={styles.channelsSidebar}>
        {/* Cabecera del servidor */}
        <div className={styles.serverHeader}>
          <div className={styles.serverInfo}>
            <span className={styles.serverName}>{academyName}</span>
            <div className={styles.serverBadgeRow}>
              <span className={styles.serverStatusDot} />
              <span className={styles.serverStatusText}>
                {channels.length} {channels.length === 1 ? "canal" : "canales"} activos
              </span>
            </div>
          </div>

          {effectiveIsAdmin && (
            <button
              type="button"
              className={styles.addChannelBtn}
              onClick={() => handleOpenCreateModal()}
              title="Crear nuevo canal"
              aria-label="Crear nuevo canal"
            >
              <Plus size={16} />
            </button>
          )}
        </div>

        {/* Lista con scroll de canales agrupados */}
        <div className={styles.channelsScrollList}>
          {/* Enlaces Rápidos a Sesiones Externas si existen */}
          {(courseWithWhatsApp || courseWithSlack || courseWithZoom) && (
            <div className={styles.externalSection}>
              <div className={styles.externalSectionHeader}>
                <span>Enlaces Directos</span>
                <Sparkles size={12} />
              </div>

              {courseWithWhatsApp && (
                <a
                  href={courseWithWhatsApp.whatsapp_url}
                  target="_blank"
                  rel="noopener noreferrer"
                  className={styles.externalLinkItem}
                  title="Unirse al grupo de WhatsApp"
                >
                  <div className={styles.externalLinkLeft}>
                    <div className={styles.externalLinkIconBox} style={{ background: "rgba(37, 211, 102, 0.15)", color: "#25D366" }}>
                      <MessageSquare size={13} />
                    </div>
                    <span>WhatsApp VIP</span>
                  </div>
                  <ExternalLink size={12} color="var(--text-dim)" />
                </a>
              )}

              {courseWithSlack && (
                <a
                  href={courseWithSlack.slack_url}
                  target="_blank"
                  rel="noopener noreferrer"
                  className={styles.externalLinkItem}
                  title="Abrir workspace de Slack"
                >
                  <div className={styles.externalLinkLeft}>
                    <div className={styles.externalLinkIconBox} style={{ background: "rgba(192, 132, 252, 0.15)", color: "#c084fc" }}>
                      <Hash size={13} />
                    </div>
                    <span>Slack Oficial</span>
                  </div>
                  <ExternalLink size={12} color="var(--text-dim)" />
                </a>
              )}

              {courseWithZoom && (
                <a
                  href={courseWithZoom.zoom_url}
                  target="_blank"
                  rel="noopener noreferrer"
                  className={styles.externalLinkItem}
                  title="Acceder a la sala de Zoom"
                >
                  <div className={styles.externalLinkLeft}>
                    <div className={styles.externalLinkIconBox} style={{ background: "rgba(45, 140, 255, 0.15)", color: "#2D8CFF" }}>
                      <Video size={13} />
                    </div>
                    <span>Sala de Zoom</span>
                  </div>
                  <ExternalLink size={12} color="var(--text-dim)" />
                </a>
              )}
            </div>
          )}

          {/* Categorías y Canales */}
          {channelsLoading ? (
            <div style={{ padding: "16px 12px", color: "var(--text-muted)", fontSize: "12px" }}>
              Cargando canales...
            </div>
          ) : (
            Object.entries(groupedChannels).map(([category, channelList]) => {
              const isCollapsed = collapsedCategories[category];
              return (
                <div key={category} className={styles.categoryGroup}>
                  <div className={styles.categoryHeader} onClick={() => toggleCategory(category)}>
                    <div className={styles.categoryTitleWrap}>
                      <ChevronDown
                        size={12}
                        className={`${styles.categoryChevron} ${isCollapsed ? styles.collapsed : ""}`}
                      />
                      <span>{category}</span>
                    </div>

                    {effectiveIsAdmin && (
                      <button
                        type="button"
                        className={styles.channelActionIconBtn}
                        onClick={(e) => {
                          e.stopPropagation();
                          handleOpenCreateModal(category);
                        }}
                        title={`Crear canal en ${category}`}
                      >
                        <Plus size={13} />
                      </button>
                    )}
                  </div>

                  {!isCollapsed && (
                    <div className={styles.categoryChannelList}>
                      {channelList.map((ch) => {
                        const isActive = ch.id === activeChannelId;
                        const isLockedForUser = !ch.has_access && !effectiveIsAdmin;

                        return (
                          <div
                            key={ch.id}
                            className={`${styles.channelItem} ${isActive ? styles.active : ""}`}
                            onClick={() => setActiveChannelId(ch.id)}
                            title={ch.description || `#${ch.name}`}
                          >
                            {isActive && <div className={styles.activeIndicatorPill} />}

                            <div className={styles.channelItemLeft}>
                              {ch.is_announcement ? (
                                <Volume2 size={16} className={styles.channelIcon} />
                              ) : isLockedForUser ? (
                                <Lock size={15} className={styles.channelIcon} />
                              ) : (
                                <Hash size={16} className={styles.channelIcon} />
                              )}
                              <span className={styles.channelName}>{ch.name}</span>
                            </div>

                            <div className={styles.channelItemRight}>
                              {ch.course_id > 0 && (
                                <span
                                  className={`${styles.channelLockBadge} ${isLockedForUser ? styles.locked : ""}`}
                                  title={`Canal restringido al curso: ${ch.course_name || ch.course_id}`}
                                >
                                  <Lock size={9} />
                                  <span>{ch.course_name ? ch.course_name.slice(0, 12) + "..." : "Curso"}</span>
                                </span>
                              )}

                              {effectiveIsAdmin && (
                                <div className={styles.channelAdminActions}>
                                  <button
                                    type="button"
                                    className={styles.channelActionIconBtn}
                                    onClick={(e) => handleOpenEditModal(ch, e)}
                                    title="Configurar canal"
                                  >
                                    <Edit3 size={12} />
                                  </button>
                                  <button
                                    type="button"
                                    className={`${styles.channelActionIconBtn} ${styles.delete}`}
                                    onClick={(e) => {
                                      e.stopPropagation();
                                      setChannelToDelete(ch);
                                    }}
                                    title="Eliminar canal"
                                  >
                                    <Trash2 size={12} />
                                  </button>
                                </div>
                              )}
                            </div>
                          </div>
                        );
                      })}
                    </div>
                  )}
                </div>
              );
            })
          )}
        </div>

        {/* Pie de la barra lateral con el perfil del usuario actual */}
        <div className={styles.channelUserFooter}>
          <div className={styles.channelUserLeft}>
            <div className={styles.channelUserAvatarWrap}>
              {currentUser?.avatar ? (
                <img src={currentUser.avatar} alt="Avatar" className={styles.channelUserAvatarImg} />
              ) : (
                <div className={styles.channelUserAvatarFallback}>
                  {currentUser?.name?.charAt(0)?.toUpperCase() || (effectiveIsAdmin ? "A" : "E")}
                </div>
              )}
              <div className={styles.channelUserOnlineDot} />
            </div>

            <div className={styles.channelUserInfo}>
              <span className={styles.channelUserName}>
                {currentUser?.name || (effectiveIsAdmin ? "Administrador" : "Estudiante")}
              </span>
              <div className={styles.channelUserRoleBadge}>
                {effectiveIsAdmin ? (
                  <>
                    <Crown size={10} />
                    <span>Admin</span>
                  </>
                ) : (
                  <span>Estudiante</span>
                )}
              </div>
            </div>
          </div>
        </div>
      </aside>

      {/* =========================================================
          2. VENTANA PRINCIPAL DE CHAT ESTILO DISCORD
          ========================================================= */}
      <section className={styles.chatMainArea}>
        {activeChannel ? (
          <>
            {/* Cabecera del canal activo */}
            <div className={styles.chatHeader}>
              <div className={styles.chatHeaderLeft}>
                {activeChannel.is_announcement ? (
                  <Volume2 size={20} className={styles.chatHeaderIcon} />
                ) : (
                  <Hash size={20} className={styles.chatHeaderIcon} />
                )}
                <div className={styles.chatHeaderTitle}>
                  <span>{activeChannel.name}</span>
                </div>

                {activeChannel.description && (
                  <>
                    <div className={styles.chatHeaderDivider} />
                    <span className={styles.chatHeaderDesc} title={activeChannel.description}>
                      {activeChannel.description}
                    </span>
                  </>
                )}
              </div>

              <div className={styles.chatHeaderRight}>
                {activeChannel.course_id > 0 ? (
                  <span className={`${styles.chatHeaderBadge} ${styles.exclusive}`}>
                    <Lock size={12} />
                    <span>Exclusivo: {activeChannel.course_name || `Curso #${activeChannel.course_id}`}</span>
                  </span>
                ) : (
                  <span className={styles.chatHeaderBadge}>
                    <Globe size={12} />
                    <span>Comunidad Global</span>
                  </span>
                )}

                {activeChannel.is_announcement && (
                  <span className={`${styles.chatHeaderBadge} ${styles.announcement}`}>
                    <Volume2 size={12} />
                    <span>Canal de Anuncios</span>
                  </span>
                )}

                <button
                  type="button"
                  className={`${styles.chatHeaderIconBtn} ${messagesLoading ? styles.spinning : ""}`}
                  onClick={() => activeChannelId && loadMessages(activeChannelId)}
                  title="Refrescar mensajes"
                >
                  <RefreshCw size={14} />
                </button>
              </div>
            </div>

            {/* Muro de acceso restringido si el estudiante no tiene el curso */}
            {channelIsLocked ? (
              <div className={styles.lockedAccessWall}>
                <div className={styles.lockedWallCard}>
                  <div className={styles.lockedWallIconBox}>
                    <Lock size={32} />
                  </div>
                  <h3 className={styles.lockedWallTitle}>Canal Exclusivo para Alumnos</h3>
                  <p className={styles.lockedWallDesc}>
                    Este canal de debate y mentoría está restringido exclusivamente a estudiantes matriculados en:
                  </p>
                  <div className={styles.lockedWallCourseBadge}>
                    <ShieldCheck size={14} />
                    <span>{activeChannel.course_name || "Curso Restringido"}</span>
                  </div>
                  {onNavigateToCourse && (
                    <button
                      type="button"
                      className={styles.btnPrimary}
                      style={{ marginTop: "12px" }}
                      onClick={() => onNavigateToCourse(activeChannel.course_id)}
                    >
                      Ver Curso en el Catálogo
                    </button>
                  )}
                </div>
              </div>
            ) : (
              <>
                {/* Feed de Mensajes */}
                <div className={styles.messagesStream}>
                  {/* Tarjeta de bienvenida al canal */}
                  <div className={styles.channelHeroCard}>
                    <div className={styles.channelHeroIconBox}>
                      {activeChannel.is_announcement ? <Volume2 size={30} /> : <Hash size={30} />}
                    </div>
                    <h2 className={styles.channelHeroTitle}>
                      ¡Te damos la bienvenida a #{activeChannel.name}!
                    </h2>
                    <p className={styles.channelHeroSubtitle}>
                      {activeChannel.description ||
                        `Este es el comienzo oficial del canal #${activeChannel.name}. Participa cordialmente y comparte con tus compañeros.`}
                    </p>
                  </div>

                  {/* Separador de fecha */}
                  <div className={styles.dateSeparator}>
                    <span className={styles.dateSeparatorBadge}>Historial de Mensajes</span>
                  </div>

                  {/* Mensajes del chat */}
                  {messages.map((msg) => {
                    const isAuthorOrAdmin =
                      effectiveIsAdmin ||
                      (currentUser?.id && msg.user_id === currentUser.id);

                    // Formatear hora
                    let displayTime = msg.created_at;
                    try {
                      const d = new Date(msg.created_at);
                      if (!isNaN(d.getTime())) {
                        displayTime = d.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
                      }
                    } catch (_) {}

                    return (
                      <div key={msg.id} className={styles.messageRow}>
                        <div className={styles.messageAvatarWrap}>
                          {msg.user_avatar ? (
                            <img src={msg.user_avatar} alt={msg.user_name} className={styles.messageAvatarImg} />
                          ) : (
                            <div className={styles.messageAvatarFallback}>
                              {msg.user_name?.charAt(0)?.toUpperCase() || "U"}
                            </div>
                          )}
                        </div>

                        <div className={styles.messageContentWrap}>
                          <div className={styles.messageHeader}>
                            <span className={styles.messageAuthorName}>{msg.user_name}</span>

                            {msg.user_role === "admin" && (
                              <span className={`${styles.roleBadge} ${styles.admin}`}>
                                <Crown size={10} />
                                <span>Admin</span>
                              </span>
                            )}

                            {msg.user_role === "instructor" && (
                              <span className={`${styles.roleBadge} ${styles.instructor}`}>
                                <span>Instructor</span>
                              </span>
                            )}

                            {msg.user_role === "student" && (
                              <span className={`${styles.roleBadge} ${styles.student}`}>
                                <span>Estudiante</span>
                              </span>
                            )}

                            <span className={styles.messageTimestamp}>{displayTime}</span>
                          </div>

                          <div className={styles.messageBody}>
                            {msg.message}
                          </div>
                        </div>

                        {/* Botón flotante para borrar mensaje */}
                        {isAuthorOrAdmin && (
                          <div className={styles.messageActionsBar}>
                            <button
                              type="button"
                              className={styles.deleteMsgBtn}
                              onClick={() => handleDeleteMessage(msg.id)}
                              title="Eliminar mensaje"
                            >
                              <Trash2 size={13} />
                            </button>
                          </div>
                        )}
                      </div>
                    );
                  })}

                  <div ref={messagesEndRef} />
                </div>

                {/* Barra Inferior de Entrada de Mensaje */}
                <div className={styles.chatInputContainer}>
                  {canSendMessages ? (
                    <>
                      {/* Emojis rápidos */}
                      <div className={styles.emojiQuickBar}>
                        <span className={styles.emojiQuickLabel}>Reacciones:</span>
                        {QUICK_EMOJIS.map((em) => (
                          <button
                            key={em}
                            type="button"
                            className={styles.emojiQuickBtn}
                            onClick={() => handleInsertEmoji(em)}
                          >
                            {em}
                          </button>
                        ))}
                      </div>

                      {/* Caja de texto */}
                      <div className={styles.inputBoxWrapper}>
                        <textarea
                          ref={textareaRef}
                          rows={1}
                          className={styles.messageTextarea}
                          placeholder={`Enviar un mensaje a #${activeChannel.name}... (Enter para enviar)`}
                          value={newMessage}
                          onChange={handleTextareaChange}
                          onKeyDown={handleKeyDown}
                        />

                        <button
                          type="button"
                          className={styles.sendMessageBtn}
                          onClick={handleSendMessage}
                          disabled={!newMessage.trim() || isSending}
                          title="Enviar mensaje"
                        >
                          <Send size={15} />
                        </button>
                      </div>
                    </>
                  ) : (
                    <div className={styles.announcementLockedBar}>
                      <Lock size={15} />
                      <span>
                        Canal oficial de anuncios. Solo los administradores e instructores pueden publicar aquí.
                      </span>
                    </div>
                  )}
                </div>
              </>
            )}
          </>
        ) : (
          <div style={{ flex: 1, display: "flex", alignItems: "center", justifyContent: "center", color: "var(--text-muted)" }}>
            <p>Selecciona un canal para comenzar a chatear.</p>
          </div>
        )}
      </section>

      {/* =========================================================
          3. MODAL DE CREACIÓN / EDICIÓN DE CANAL (ADMIN)
          ========================================================= */}
      <Modal
        isOpen={isChannelModalOpen}
        onClose={() => setIsChannelModalOpen(false)}
        title={editingChannel ? `Editar Canal #${editingChannel.name}` : "Crear Nuevo Canal"}
        description="Configura el nombre del canal, la categoría y restringe el acceso según los cursos de la academia."
        maxWidth="540px"
      >
        <div style={{ display: "flex", flexDirection: "column", gap: "14px", marginTop: "10px" }}>
          {/* Nombre del canal con icono Hash de Lucide */}
          <div className={styles.modalFormGroup}>
            <label className={styles.modalLabel}>
              <span>Nombre del canal</span>
              <span className={styles.modalLabelHint}>Formato minúscula sin espacios</span>
            </label>
            <div className={styles.modalInputWrapper}>
              <span className={styles.modalInputPrefix}>
                <Hash size={14} />
              </span>
              <input
                type="text"
                className={styles.modalInput}
                placeholder="ej: preguntas-respuestas"
                value={channelForm.name}
                onChange={(e) =>
                  setChannelForm({
                    ...channelForm,
                    name: e.target.value.toLowerCase().replace(/#/g, "").replace(/\s+/g, "-"),
                  })
                }
              />
            </div>
          </div>

          {/* Categoría o Grupo con Arc Select */}
          <div className={styles.modalFormGroup}>
            <label className={styles.modalLabel}>
              <span>Categoría o Grupo</span>
              <span className={styles.modalLabelHint}>Elige una categoría existente o crea una nueva</span>
            </label>
            <Select<string>
              options={categorySelectOptions}
              value={selectedCategoryKey}
              onChange={(val) => {
                if (val === NEW_CATEGORY_OPTION) {
                  setIsCustomCategory(true);
                  setSelectedCategoryKey(NEW_CATEGORY_OPTION);
                } else {
                  setIsCustomCategory(false);
                  setSelectedCategoryKey(val);
                  setChannelForm((prev) => ({ ...prev, category: val }));
                }
              }}
              placeholder="Seleccionar categoría..."
              size="md"
            />

            {/* Input para nueva categoría si el usuario selecciona "+ Crear nueva categoría..." */}
            {isCustomCategory && (
              <div className={styles.newCategoryRow}>
                <div className={styles.modalInputWrapper} style={{ flex: 1 }}>
                  <span className={styles.modalInputPrefix}>
                    <FolderPlus size={14} color="#10b981" />
                  </span>
                  <input
                    type="text"
                    className={styles.modalInput}
                    placeholder="Escribe el nombre del nuevo grupo (ej: Proyectos, Mentorías)..."
                    value={customCategoryText}
                    autoFocus
                    onChange={(e) => setCustomCategoryText(e.target.value)}
                  />
                </div>
                <button
                  type="button"
                  className={styles.cancelNewCategoryBtn}
                  onClick={() => {
                    setIsCustomCategory(false);
                    const fallback = existingCategories[0] || "General";
                    setSelectedCategoryKey(fallback);
                    setChannelForm((prev) => ({ ...prev, category: fallback }));
                  }}
                  title="Volver a seleccionar una categoría existente"
                >
                  <X size={13} />
                  <span>Cancelar</span>
                </button>
              </div>
            )}
          </div>

          {/* Descripción */}
          <div className={styles.modalFormGroup}>
            <label className={styles.modalLabel}>Propósito o descripción del canal</label>
            <textarea
              className={styles.modalTextarea}
              placeholder="Explica a los miembros de la academia de qué trata este canal..."
              value={channelForm.description}
              onChange={(e) => setChannelForm({ ...channelForm, description: e.target.value })}
            />
          </div>

          {/* Restricción de Curso con Arc Select y Lucide Globe / Lock */}
          <div className={styles.modalFormGroup}>
            <label className={styles.modalLabel}>
              <span>Restricción de Acceso por Curso</span>
              <span className={styles.modalLabelHint}>Control exclusivo para matriculados</span>
            </label>
            <Select<number>
              options={restrictionOptions}
              value={channelForm.course_id || 0}
              onChange={(val) => setChannelForm({ ...channelForm, course_id: val })}
              placeholder="Seleccionar restricción de curso..."
              size="md"
            />
          </div>

          {/* Canal de Anuncios */}
          <label className={styles.modalCheckboxCard}>
            <input
              type="checkbox"
              className={styles.modalCheckboxInput}
              checked={channelForm.is_announcement}
              onChange={(e) =>
                setChannelForm({ ...channelForm, is_announcement: e.target.checked })
              }
            />
            <div className={styles.modalCheckboxTexts}>
              <span className={styles.modalCheckboxTitle}>Canal de solo difusión / Anuncios</span>
              <span className={styles.modalCheckboxDesc}>
                Solo los administradores e instructores podrán enviar mensajes. Los estudiantes solo podrán leer.
              </span>
            </div>
          </label>

          {/* Botones del Modal */}
          <div className={styles.modalFooterBtns}>
            <button
              type="button"
              className={styles.btnSecondary}
              onClick={() => setIsChannelModalOpen(false)}
            >
              Cancelar
            </button>
            <button
              type="button"
              className={styles.btnPrimary}
              onClick={handleSaveChannel}
              disabled={!channelForm.name.trim()}
            >
              {editingChannel ? "Guardar Cambios" : "Crear Canal"}
            </button>
          </div>
        </div>
      </Modal>

      {/* =========================================================
          4. MODAL DE CONFIRMACIÓN DE ELIMINACIÓN DE CANAL
          ========================================================= */}
      <Modal
        isOpen={Boolean(channelToDelete)}
        onClose={() => setChannelToDelete(null)}
        title="¿Eliminar este canal?"
        description="Esta acción eliminará el canal de forma definitiva junto con todos sus mensajes compartidos."
        maxWidth="450px"
      >
        <div style={{ marginTop: "14px" }}>
          <p style={{ fontSize: "14px", color: "var(--foreground)" }}>
            Estás a punto de borrar permanentemente el canal{" "}
            <strong>#{channelToDelete?.name}</strong>.
          </p>
          <div className={styles.modalFooterBtns} style={{ marginTop: "24px" }}>
            <button
              type="button"
              className={styles.btnSecondary}
              onClick={() => setChannelToDelete(null)}
              disabled={isDeletingChannel}
            >
              Cancelar
            </button>
            <button
              type="button"
              className={styles.btnDanger}
              onClick={handleConfirmDeleteChannel}
              disabled={isDeletingChannel}
            >
              {isDeletingChannel ? "Eliminando..." : "Eliminar Canal"}
            </button>
          </div>
        </div>
      </Modal>
    </div>
  );
};

export default DiscordCommunityView;

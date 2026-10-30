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
  Crown,
  ShieldCheck,
  ChevronDown,
  ChevronRight,
  ArrowLeft,
  Globe,
  Folder,
  FolderPlus,
  X,
  Smile,
  Search,
  Paperclip,
  GitBranch,
  Video,
  Image as ImageIcon,
  FileText,
} from "lucide-react";
import {
  api,
  Course,
  Student,
  CommunityChannel,
  CommunityMessage,
  CreateCommunityChannelPayload,
} from "../../services/api";

export interface AttachmentItem {
  name: string;
  url: string;
  type?: "image" | "video" | "file";
}

function isImageAttachment(att: AttachmentItem): boolean {
  if (att.type === "image") return true;
  if (att.url?.startsWith("data:image/")) return true;
  return /\.(png|jpe?g|gif|webp|svg)(\?.*)?$/i.test(att.name || "") || /\.(png|jpe?g|gif|webp|svg)(\?.*)?$/i.test(att.url || "");
}

function isVideoAttachment(att: AttachmentItem): boolean {
  if (att.type === "video") return true;
  if (att.url?.startsWith("data:video/")) return true;
  return /\.(mp4|webm|mov|m4v)(\?.*)?$/i.test(att.name || "") || /\.(mp4|webm|mov|m4v)(\?.*)?$/i.test(att.url || "");
}
import { Modal } from "../../components/arc/modal";
import { Select, SelectOption } from "../../components/arc/select";
import moneyStyles from "../../components/arc/money-input/money-input.module.css";
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

// Categorías de emojis para el popover estilo Discord
const EMOJI_CATEGORIES = [
  {
    id: "popular",
    label: "Populares",
    emojis: ["🚀", "🔥", "💡", "❤️", "👍", "👏", "🎉", "🙌", "✨", "⭐", "💯", "🎯", "💪", "🎓", "📚", "💻"],
  },
  {
    id: "faces",
    label: "Caritas",
    emojis: [
      "😀", "😃", "😄", "😁", "😆", "😅", "😂", "🤣", "😊", "😇",
      "🙂", "🙃", "😉", "😌", "😍", "🥰", "😘", "😋", "😛", "😜",
      "🤪", "🤓", "😎", "🥳", "😏", "🧐", "🤔", "🤫", "🤭", "🤗",
      "😴", "🥺", "😳", "🤯", "🥳", "🤩", "🤐", "🤨", "😐", "😑"
    ],
  },
  {
    id: "gestures",
    label: "Gestos",
    emojis: [
      "👍", "👎", "👏", "🙌", "👐", "🤲", "🤝", "✌️", "🤞", "🤟",
      "🤘", "🤙", "👈", "👉", "👆", "👇", "☝️", "✋", "🤚", "🖐️",
      "🖖", "👋", "👊", "✊", "🤛", "🤜", "💪", "🙏", "✍️", "💅"
    ],
  },
  {
    id: "study",
    label: "Estudio & Objetos",
    emojis: [
      "💻", "🖥️", "📱", "⌨️", "🖱️", "📚", "📖", "🎓", "📝", "✏️",
      "💡", "🔍", "🔎", "🔔", "📢", "🎯", "🏆", "🥇", "🎨", "🚀",
      "⚙️", "🔧", "⚡", "💎", "📌", "📍", "📂", "📁", "📊", "📈"
    ],
  },
];

// Helper para leer canales iniciales en caché sincrónico (0ms)
function getInitialCachedChannels(): CommunityChannel[] {
  try {
    const saved = localStorage.getItem("crezca_community_channels");
    if (saved) {
      const parsed = JSON.parse(saved);
      if (Array.isArray(parsed) && parsed.length > 0) return parsed;
    }
  } catch (_) {}
  return [
    { id: 1, name: "bienvenida", slug: "bienvenida", description: "Canal de bienvenida para todos los nuevos estudiantes.", category: "General", course_id: 0, is_announcement: false, has_access: true, messages_count: 1 },
    { id: 2, name: "anuncios-oficiales", slug: "anuncios-oficiales", description: "Comunicados oficiales de la academia.", category: "General", course_id: 0, is_announcement: true, has_access: true, messages_count: 0 },
    { id: 3, name: "charla-general", slug: "charla-general", description: "Espacio para compartir ideas y proyectos.", category: "Comunidad", course_id: 0, is_announcement: false, has_access: true, messages_count: 0 },
    { id: 4, name: "networking-proyectos", slug: "networking-proyectos", description: "Colabora en proyectos con tus compañeros.", category: "Comunidad", course_id: 0, is_announcement: false, has_access: true, messages_count: 0 },
  ];
}

// Helper para leer mensajes en caché sincrónico (0ms)
function getInitialCachedMessages(channelId: number | null): CommunityMessage[] {
  if (!channelId) return [];
  try {
    const saved = localStorage.getItem(`crezca_channel_messages_${channelId}`);
    if (saved) {
      const parsed = JSON.parse(saved);
      if (Array.isArray(parsed)) return parsed;
    }
  } catch (_) {}
  return [];
}

export const DiscordCommunityView: React.FC<DiscordCommunityViewProps> = ({
  courses = [],
  academyName = "Academia",
  isAdmin = false,
  currentUser,
  onNavigateToCourse,
}) => {
  // Canales - Inicialización instantánea con caché
  const [channels, setChannels] = useState<CommunityChannel[]>(getInitialCachedChannels);
  const [activeChannelId, setActiveChannelId] = useState<number | null>(() => {
    const initial = getInitialCachedChannels();
    const savedActive = localStorage.getItem("crezca_active_channel_id");
    if (savedActive && initial.some((c) => c.id === Number(savedActive))) {
      return Number(savedActive);
    }
    return initial[0]?.id || 1;
  });
  const [channelsLoading, setChannelsLoading] = useState(false);

  // Mensajes - Inicialización instantánea con caché
  const [messages, setMessages] = useState<CommunityMessage[]>(() =>
    getInitialCachedMessages(activeChannelId)
  );
  const [messagesLoading, setMessagesLoading] = useState(false);
  const [newMessage, setNewMessage] = useState("");
  const [isSending, setIsSending] = useState(false);

  // Adjuntos temporales con tipo detectado (image, video, file)
  const [pendingAttachment, setPendingAttachment] = useState<AttachmentItem | null>(null);
  const [threadAttachment, setThreadAttachment] = useState<AttachmentItem | null>(null);

  // Menú flotante del botón + para elegir si subir imagen, video o archivo
  const [isAttachMenuOpen, setIsAttachMenuOpen] = useState(false);
  const [fileInputAccept, setFileInputAccept] = useState("*/*");
  const attachMenuRef = useRef<HTMLDivElement | null>(null);

  // Categorías colapsadas en la barra lateral
  const [collapsedCategories, setCollapsedCategories] = useState<Record<string, boolean>>({});

  // Selector flotante de Emojis
  const [isEmojiPickerOpen, setIsEmojiPickerOpen] = useState(false);
  const [activeEmojiCategory, setActiveEmojiCategory] = useState("popular");
  const [emojiSearchQuery, setEmojiSearchQuery] = useState("");

  // Autocompletado de menciones (@usuario y #canal)
  const [mentionMode, setMentionMode] = useState<"user" | "channel" | null>(null);
  const [mentionQuery, setMentionQuery] = useState("");
  const [mentionMatchIndex, setMentionMatchIndex] = useState(0);

  // Hilos de conversación (Threads)
  const [activeThreadMessage, setActiveThreadMessage] = useState<CommunityMessage | null>(null);
  const [threadNewMessage, setThreadNewMessage] = useState("");
  const [isThreadSending, setIsThreadSending] = useState(false);

  // Navegación móvil entre lista de canales y sala de chat activa
  const [mobileTab, setMobileTab] = useState<"channels" | "chat">("chat");

  // Modales de administración de canales
  const [isChannelModalOpen, setIsChannelModalOpen] = useState(false);
  const [editingChannel, setEditingChannel] = useState<CommunityChannel | null>(null);
  const [channelForm, setChannelForm] = useState<CreateCommunityChannelPayload>({
    name: "",
    description: "",
    category: "General",
    course_id: 0,
    is_announcement: false,
  });

  const NEW_CATEGORY_OPTION = "__NEW_CATEGORY__";
  const [selectedCategoryKey, setSelectedCategoryKey] = useState<string>("General");
  const [isCustomCategory, setIsCustomCategory] = useState<boolean>(false);
  const [customCategoryText, setCustomCategoryText] = useState<string>("");
  const [channelToDelete, setChannelToDelete] = useState<CommunityChannel | null>(null);
  const [isDeletingChannel, setIsDeletingChannel] = useState(false);

  // Cursos disponibles para restricción (carga perezosa solo cuando se necesita)
  const [availableCourses, setAvailableCourses] = useState<Course[]>(courses);
  const [availableStudents, setAvailableStudents] = useState<Student[]>([]);
  const [loadingStudents, setLoadingStudents] = useState(false);
  const [studentSearch, setStudentSearch] = useState("");

  const messagesEndRef = useRef<HTMLDivElement | null>(null);
  const textareaRef = useRef<HTMLTextAreaElement | null>(null);
  const threadTextareaRef = useRef<HTMLTextAreaElement | null>(null);
  const fileInputRef = useRef<HTMLInputElement | null>(null);
  const threadFileInputRef = useRef<HTMLInputElement | null>(null);
  const emojiPickerRef = useRef<HTMLDivElement | null>(null);

  // Cargar cursos bajo demanda cuando se abre el modal si no estaban cargados
  const ensureCoursesLoaded = () => {
    if (availableCourses.length === 0) {
      api.getCourses().then((res) => {
        if (Array.isArray(res)) setAvailableCourses(res);
      }).catch((e) => console.warn("Error cargando cursos:", e));
    }
  };

  const ensureStudentsLoaded = () => {
    if (availableStudents.length === 0) {
      setLoadingStudents(true);
      api.getStudents()
        .then((res) => {
          if (Array.isArray(res)) setAvailableStudents(res);
        })
        .catch((e) => console.warn("Error cargando estudiantes:", e))
        .finally(() => setLoadingStudents(false));
    }
  };

  const courseFilteredStudents = useMemo(() => {
    if (!channelForm.course_id) return availableStudents;
    const enrolled = availableStudents.filter((st) => {
      const ids = (st.enabledCourseIds || []).map(Number);
      return ids.includes(Number(channelForm.course_id));
    });
    return enrolled.length > 0 ? enrolled : availableStudents;
  }, [availableStudents, channelForm.course_id]);

  const searchedStudents = useMemo(() => {
    if (!studentSearch.trim()) return courseFilteredStudents;
    const term = studentSearch.toLowerCase();
    return courseFilteredStudents.filter(
      (st) =>
        st.name.toLowerCase().includes(term) ||
        st.email.toLowerCase().includes(term)
    );
  }, [courseFilteredStudents, studentSearch]);

  // 1. Cargar canales en background sin bloquear
  const loadChannels = async (keepActive = true) => {
    try {
      const data = await api.getCommunityChannels();
      if (Array.isArray(data) && data.length > 0) {
        setChannels(data);
        localStorage.setItem("crezca_community_channels", JSON.stringify(data));

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
    loadChannels(true);
  }, []);

  // Guardar canal activo en persistencia local
  useEffect(() => {
    if (activeChannelId) {
      localStorage.setItem("crezca_active_channel_id", String(activeChannelId));
    }
  }, [activeChannelId]);

  // Canal activo actual
  const activeChannel = useMemo(() => {
    return channels.find((c) => c.id === activeChannelId) || channels[0] || null;
  }, [channels, activeChannelId]);

  // 2. Cargar mensajes del canal activo (SWR: primero lee caché, luego revalida)
  const loadMessages = async (channelId: number) => {
    // Si no tenemos mensajes para este canal, leemos caché inmediatamente
    const cached = getInitialCachedMessages(channelId);
    if (cached.length > 0 && messages.length === 0) {
      setMessages(cached);
    }

    try {
      const res = await api.getChannelMessages(channelId);
      if (res && Array.isArray(res.messages)) {
        setMessages(res.messages);
        localStorage.setItem(`crezca_channel_messages_${channelId}`, JSON.stringify(res.messages));
      }
    } catch (err) {
      console.error("Error cargando mensajes:", err);
    } finally {
      setMessagesLoading(false);
    }
  };

  useEffect(() => {
    if (activeChannelId) {
      // Cargar del caché inmediatamente al cambiar de canal
      const cached = getInitialCachedMessages(activeChannelId);
      if (cached.length > 0) {
        setMessages(cached);
      }
      loadMessages(activeChannelId);
      // Cerrar hilo si se cambia de canal
      setActiveThreadMessage(null);
    }
  }, [activeChannelId]);

  // Polling periódico ligero cada 10 segundos para nuevos mensajes
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
    }, 10000);

    return () => clearInterval(interval);
  }, [activeChannelId]);

  // Scroll automático
  const scrollToBottom = (behavior: ScrollBehavior = "smooth") => {
    if (messagesEndRef.current) {
      messagesEndRef.current.scrollIntoView({ behavior });
    }
  };

  useEffect(() => {
    scrollToBottom("auto");
  }, [messages.length, activeChannelId]);

  // Cerrar popover de emojis y menú de adjuntos al hacer click afuera
  useEffect(() => {
    const handleClickOutside = (e: MouseEvent) => {
      if (emojiPickerRef.current && !emojiPickerRef.current.contains(e.target as Node)) {
        setIsEmojiPickerOpen(false);
      }
      if (attachMenuRef.current && !attachMenuRef.current.contains(e.target as Node)) {
        setIsAttachMenuOpen(false);
      }
    };
    if (isEmojiPickerOpen || isAttachMenuOpen) {
      document.addEventListener("mousedown", handleClickOutside);
    }
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, [isEmojiPickerOpen, isAttachMenuOpen]);

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

  // Lista de categorías únicas
  const existingCategories = useMemo(() => {
    const set = new Set<string>();
    channels.forEach((ch) => {
      if (ch.category && ch.category.trim()) {
        set.add(ch.category.trim());
      }
    });
    if (set.size === 0) set.add("General");
    return Array.from(set);
  }, [channels]);

  // Opciones para el Select de Categorías
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

  // Opciones de restricción
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

  // Lista de usuarios detectados para autocompletado de menciones @
  const mentionableUsers = useMemo(() => {
    const map = new Map<string, { id: number; name: string; avatar?: string; role?: string }>();

    // Usuario actual
    if (currentUser) {
      map.set(currentUser.name.toLowerCase(), currentUser);
    }

    // Administrador por defecto
    map.set("administrador", { id: 1, name: "Administrador", role: "admin" });
    map.set("soporte", { id: 2, name: "Soporte Academia", role: "instructor" });

    // Autores de los mensajes actuales
    messages.forEach((m) => {
      if (m.user_name && !map.has(m.user_name.toLowerCase())) {
        map.set(m.user_name.toLowerCase(), {
          id: m.user_id,
          name: m.user_name,
          avatar: m.user_avatar,
          role: m.user_role,
        });
      }
    });

    return Array.from(map.values());
  }, [messages, currentUser]);

  // Filtrado para menú flotante de menciones
  const filteredMentionUsers = useMemo(() => {
    if (mentionMode !== "user") return [];
    const q = mentionQuery.toLowerCase();
    return mentionableUsers.filter((u) => u.name.toLowerCase().includes(q)).slice(0, 5);
  }, [mentionMode, mentionQuery, mentionableUsers]);

  const filteredMentionChannels = useMemo(() => {
    if (mentionMode !== "channel") return [];
    const q = mentionQuery.toLowerCase();
    return channels.filter((c) => c.name.toLowerCase().includes(q)).slice(0, 5);
  }, [mentionMode, mentionQuery, channels]);

  // Alternar categoría colapsada
  const toggleCategory = (cat: string) => {
    setCollapsedCategories((prev) => ({ ...prev, [cat]: !prev[cat] }));
  };

  // Abrir modal canal
  const handleOpenCreateModal = (defaultCategory = "General") => {
    ensureCoursesLoaded();
    setEditingChannel(null);
    const cat = defaultCategory || existingCategories[0] || "General";
    setSelectedCategoryKey(cat);
    setIsCustomCategory(false);
    setCustomCategoryText("");
    setStudentSearch("");
    setChannelForm({
      name: "",
      description: "",
      category: cat,
      course_id: 0,
      allowed_students: [],
      is_announcement: false,
    });
    setIsChannelModalOpen(true);
  };

  const handleOpenEditModal = (ch: CommunityChannel, e: React.MouseEvent) => {
    e.stopPropagation();
    ensureCoursesLoaded();
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
    setStudentSearch("");
    setChannelForm({
      name: ch.name,
      description: ch.description || "",
      category: cat,
      course_id: ch.course_id || 0,
      allowed_students: (ch as any).allowed_students || [],
      is_announcement: !!ch.is_announcement,
    });
    if (ch.course_id > 0) {
      ensureStudentsLoaded();
    }
    setIsChannelModalOpen(true);
  };

  // Guardar canal
  const handleSaveChannel = async () => {
    if (!channelForm.name.trim()) return;
    const finalCategory = isCustomCategory
      ? customCategoryText.trim() || "General"
      : selectedCategoryKey || "General";

    try {
      const cleanSlug = channelForm.name
        .toLowerCase()
        .replace(/#/g, "")
        .replace(/\s+/g, "-")
        .replace(/[^a-z0-9_-]/g, "");

      const payloadToSave: CreateCommunityChannelPayload = {
        ...channelForm,
        category: finalCategory,
        name: cleanSlug,
        allowed_students: channelForm.allowed_students && channelForm.allowed_students.length > 0
          ? channelForm.allowed_students
          : undefined,
      };

      if (editingChannel) {
        const updated = await api.updateCommunityChannel(editingChannel.id, payloadToSave);
        setChannels(updated);
        localStorage.setItem("crezca_community_channels", JSON.stringify(updated));
      } else {
        const updated = await api.createCommunityChannel(payloadToSave);
        setChannels(updated);
        localStorage.setItem("crezca_community_channels", JSON.stringify(updated));
        const created = updated.find((c) => c.name === cleanSlug);
        if (created) setActiveChannelId(created.id);
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
      localStorage.setItem("crezca_community_channels", JSON.stringify(updated));
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

  // Enviar mensaje en canal principal
  const handleSendMessage = async () => {
    if (!activeChannelId || (!newMessage.trim() && !pendingAttachment) || isSending) return;
    const textToSend = newMessage.trim();
    const attachmentsToSend = pendingAttachment ? [pendingAttachment] : [];
    setIsSending(true);
    try {
      const sentMsg = await api.sendChannelMessage(activeChannelId, textToSend, attachmentsToSend, 0);
      setMessages((prev) => {
        const next = [...prev, sentMsg];
        localStorage.setItem(`crezca_channel_messages_${activeChannelId}`, JSON.stringify(next));
        return next;
      });
      setNewMessage("");
      setPendingAttachment(null);
      setMentionMode(null);
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

  // Enviar mensaje dentro de un hilo (Thread)
  const handleSendThreadReply = async () => {
    if (!activeChannelId || !activeThreadMessage || (!threadNewMessage.trim() && !threadAttachment) || isThreadSending) return;
    const textToSend = threadNewMessage.trim();
    const attachmentsToSend = threadAttachment ? [threadAttachment] : [];
    setIsThreadSending(true);
    try {
      const sentReply = await api.sendChannelMessage(
        activeChannelId,
        textToSend,
        attachmentsToSend,
        activeThreadMessage.id
      );

      // Actualizar mensajes locales: agregar la respuesta y aumentar el reply_count del padre
      setMessages((prev) => {
        const next = prev.map((m) => {
          if (m.id === activeThreadMessage.id) {
            return {
              ...m,
              reply_count: (m.reply_count || 0) + 1,
              last_reply_at: sentReply.created_at,
            };
          }
          return m;
        });
        next.push(sentReply);
        localStorage.setItem(`crezca_channel_messages_${activeChannelId}`, JSON.stringify(next));
        return next;
      });

      // Actualizar objeto de mensaje activo en hilo
      setActiveThreadMessage((prev) => {
        if (!prev) return null;
        return {
          ...prev,
          reply_count: (prev.reply_count || 0) + 1,
        };
      });

      setThreadNewMessage("");
      setThreadAttachment(null);
      if (threadTextareaRef.current) {
        threadTextareaRef.current.style.height = "auto";
      }
    } catch (err: any) {
      alert(err?.message || "Error al enviar la respuesta al hilo.");
    } finally {
      setIsThreadSending(false);
    }
  };

  // Borrar mensaje
  const handleDeleteMessage = async (msgId: number) => {
    if (!window.confirm("¿Deseas eliminar este mensaje?")) return;
    try {
      await api.deleteChannelMessage(msgId, activeChannelId || undefined);
      setMessages((prev) => {
        const next = prev.filter((m) => m.id !== msgId && m.parent_id !== msgId);
        if (activeChannelId) {
          localStorage.setItem(`crezca_channel_messages_${activeChannelId}`, JSON.stringify(next));
        }
        return next;
      });
      if (activeThreadMessage?.id === msgId) {
        setActiveThreadMessage(null);
      }
    } catch (err) {
      console.error("Error al borrar mensaje:", err);
    }
  };

  // Detección de menciones (@ y #) mientras se escribe
  const handleInputChangeWithMentions = (val: string, cursorPosition: number) => {
    setNewMessage(val);
    const textBeforeCursor = val.slice(0, cursorPosition);
    const lastWordMatch = textBeforeCursor.match(/(^|\s)([@#])([a-zA-Z0-9_-]*)$/);

    if (lastWordMatch) {
      const symbol = lastWordMatch[2];
      const query = lastWordMatch[3] || "";
      if (symbol === "@") {
        setMentionMode("user");
        setMentionQuery(query);
        setMentionMatchIndex(0);
      } else if (symbol === "#") {
        setMentionMode("channel");
        setMentionQuery(query);
        setMentionMatchIndex(0);
      }
    } else {
      setMentionMode(null);
    }
  };

  // Insertar mención de usuario seleccionada
  const handleSelectUserMention = (user: { name: string }) => {
    const cursor = textareaRef.current?.selectionStart || newMessage.length;
    const textBeforeCursor = newMessage.slice(0, cursor);
    const textAfterCursor = newMessage.slice(cursor);
    const replacedBefore = textBeforeCursor.replace(/(^|\s)@[a-zA-Z0-9_-]*$/, `$1@${user.name} `);

    setNewMessage(replacedBefore + textAfterCursor);
    setMentionMode(null);
    if (textareaRef.current) {
      textareaRef.current.focus();
    }
  };

  // Insertar mención de canal seleccionada
  const handleSelectChannelMention = (ch: CommunityChannel) => {
    const cursor = textareaRef.current?.selectionStart || newMessage.length;
    const textBeforeCursor = newMessage.slice(0, cursor);
    const textAfterCursor = newMessage.slice(cursor);
    const replacedBefore = textBeforeCursor.replace(/(^|\s)#[a-zA-Z0-9_-]*$/, `$1#${ch.name} `);

    setNewMessage(replacedBefore + textAfterCursor);
    setMentionMode(null);
    if (textareaRef.current) {
      textareaRef.current.focus();
    }
  };

  // Insertar emoji desde el popover estilo Discord
  const handleInsertEmoji = (emoji: string) => {
    setNewMessage((prev) => prev + emoji);
    setIsEmojiPickerOpen(false);
    if (textareaRef.current) {
      textareaRef.current.focus();
    }
  };

  // Manejo de teclado (Enter para enviar, flechas para menciones)
  const handleKeyDown = (e: React.KeyboardEvent<HTMLTextAreaElement>) => {
    if (mentionMode) {
      const listLength = mentionMode === "user" ? filteredMentionUsers.length : filteredMentionChannels.length;
      if (e.key === "ArrowDown") {
        e.preventDefault();
        setMentionMatchIndex((prev) => (prev + 1) % Math.max(1, listLength));
        return;
      }
      if (e.key === "ArrowUp") {
        e.preventDefault();
        setMentionMatchIndex((prev) => (prev - 1 + listLength) % Math.max(1, listLength));
        return;
      }
      if (e.key === "Enter" || e.key === "Tab") {
        e.preventDefault();
        if (mentionMode === "user" && filteredMentionUsers[mentionMatchIndex]) {
          handleSelectUserMention(filteredMentionUsers[mentionMatchIndex]);
        } else if (mentionMode === "channel" && filteredMentionChannels[mentionMatchIndex]) {
          handleSelectChannelMention(filteredMentionChannels[mentionMatchIndex]);
        }
        return;
      }
      if (e.key === "Escape") {
        setMentionMode(null);
        return;
      }
    }

    if (e.key === "Enter" && !e.shiftKey) {
      e.preventDefault();
      handleSendMessage();
    }
  };

  // Auto expandir altura del textarea
  const handleTextareaChange = (e: React.ChangeEvent<HTMLTextAreaElement>) => {
    const cursor = e.target.selectionStart;
    handleInputChangeWithMentions(e.target.value, cursor);
    e.target.style.height = "auto";
    e.target.style.height = `${Math.min(e.target.scrollHeight, 140)}px`;
  };

  // Abrir selector de archivo con el tipo filtrado (imagen, video, archivo)
  const handleTriggerAttach = (type: "image" | "video" | "file") => {
    setIsAttachMenuOpen(false);
    if (type === "image") {
      setFileInputAccept("image/*");
    } else if (type === "video") {
      setFileInputAccept("video/*");
    } else {
      setFileInputAccept("*/*");
    }
    setTimeout(() => {
      fileInputRef.current?.click();
    }, 50);
  };

  // Adjuntar archivo o imagen simulado/local con detección de tipo
  const handleFileUpload = (e: React.ChangeEvent<HTMLInputElement>, isThread = false) => {
    const file = e.target.files?.[0];
    if (!file) return;

    let fileType: "image" | "video" | "file" = "file";
    if (file.type.startsWith("image/") || /\.(png|jpe?g|gif|webp|svg)$/i.test(file.name)) {
      fileType = "image";
    } else if (file.type.startsWith("video/") || /\.(mp4|webm|mov|m4v)$/i.test(file.name)) {
      fileType = "video";
    }

    const reader = new FileReader();
    reader.onload = () => {
      const resultUrl = reader.result as string;
      const att: AttachmentItem = { name: file.name, url: resultUrl, type: fileType };
      if (isThread) {
        setThreadAttachment(att);
      } else {
        setPendingAttachment(att);
      }
    };
    reader.readAsDataURL(file);
    e.target.value = "";
  };

  // Renderizar texto formateado con menciones interactivas (@ y #)
  const renderMessageText = (text: string) => {
    // Regex para detectar menciones @Nombre o #canal
    const parts = text.split(/(@[a-zA-Z0-9_\u00C0-\u017F\s]+?|\#[a-zA-Z0-9_-]+)(?=[.,!?\s]|$)/g);

    return parts.map((part, index) => {
      if (part.startsWith("@")) {
        const username = part.slice(1).trim();
        const isSelf = currentUser && username.toLowerCase() === currentUser.name.toLowerCase();
        return (
          <span
            key={index}
            className={`${styles.userMentionPill} ${isSelf ? styles.selfMention : ""}`}
            title={`Mención a @${username}`}
          >
            @{username}
          </span>
        );
      }
      if (part.startsWith("#")) {
        const chanSlug = part.slice(1).trim().toLowerCase();
        const targetChan = channels.find((c) => c.name.toLowerCase() === chanSlug);
        return (
          <button
            key={index}
            type="button"
            className={styles.channelMentionPill}
            onClick={() => {
              if (targetChan) {
                setActiveChannelId(targetChan.id);
                setMobileTab("chat");
              }
            }}
            title={targetChan ? `Ir al canal #${targetChan.name}` : undefined}
          >
            <Hash size={12} />
            <span>{chanSlug}</span>
          </button>
        );
      }
      return <React.Fragment key={index}>{part}</React.Fragment>;
    });
  };

  // Roles y visualización
  const effectiveIsAdmin = isAdmin || currentUser?.role === "admin";
  const channelIsLocked = Boolean(activeChannel && !activeChannel.has_access && !effectiveIsAdmin);
  const channelIsAnnouncement = Boolean(activeChannel?.is_announcement);
  const canSendMessages = !channelIsLocked && (!channelIsAnnouncement || effectiveIsAdmin);

  // Mensajes principales del feed (excluyendo respuestas que pertenecen a un hilo)
  const topLevelMessages = useMemo(() => {
    return messages.filter((m) => !m.parent_id || m.parent_id === 0);
  }, [messages]);

  // Respuestas del hilo activo actual
  const threadReplies = useMemo(() => {
    if (!activeThreadMessage) return [];
    return messages.filter((m) => m.parent_id === activeThreadMessage.id);
  }, [messages, activeThreadMessage]);

  // Emojis filtrados para el selector popover
  const currentCategoryEmojis = useMemo(() => {
    if (emojiSearchQuery.trim()) {
      const q = emojiSearchQuery.toLowerCase();
      const all: string[] = [];
      EMOJI_CATEGORIES.forEach((cat) => all.push(...cat.emojis));
      return Array.from(new Set(all)).filter((em) => em.includes(q));
    }
    const found = EMOJI_CATEGORIES.find((cat) => cat.id === activeEmojiCategory);
    return found ? found.emojis : EMOJI_CATEGORIES[0].emojis;
  }, [activeEmojiCategory, emojiSearchQuery]);

  return (
    <div
      className={[
        styles.discordContainer,
        mobileTab === "channels" ? styles.mobileViewChannels : styles.mobileViewChat,
      ].join(" ")}
    >
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

          <div style={{ display: "flex", alignItems: "center", gap: "6px" }}>
            {activeChannel && (
              <button
                type="button"
                className={styles.mobileGoToChatBtn}
                onClick={() => setMobileTab("chat")}
                title="Ir al chat actual"
                aria-label="Ir al chat"
              >
                <span>Chat</span>
                <ChevronRight size={14} />
              </button>
            )}

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
        </div>

        {/* Lista con scroll de canales agrupados */}
        <div className={styles.channelsScrollList}>
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
                            onClick={() => {
                              setActiveChannelId(ch.id);
                              setMobileTab("chat");
                            }}
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
                {/* Botón Móvil para volver a la lista de canales */}
                <button
                  type="button"
                  className={styles.mobileChannelsBtn}
                  onClick={() => setMobileTab("channels")}
                  title="Ver todos los canales"
                  aria-label="Ver canales"
                >
                  <ArrowLeft size={16} />
                  <span>Canales</span>
                </button>

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
                {/* Feed de Mensajes Principales */}
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
                  {topLevelMessages.map((msg) => {
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

                    // Comprobar si el mensaje contiene una mención directa al usuario actual
                    const mentionsCurrentUser =
                      currentUser &&
                      msg.message.toLowerCase().includes(`@${currentUser.name.toLowerCase()}`);

                    return (
                      <div
                        key={msg.id}
                        className={`${styles.messageRow} ${mentionsCurrentUser ? styles.hasMention : ""}`}
                      >
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
                            {renderMessageText(msg.message)}
                          </div>

                          {/* Adjuntos en el mensaje (Previsualización de imágenes, videos o archivos) */}
                          {msg.attachments && msg.attachments.length > 0 && (
                            <div className={styles.messageAttachmentsGrid}>
                              {msg.attachments.map((att, i) => {
                                if (isImageAttachment(att)) {
                                  return (
                                    <div key={i} className={styles.messageAttachmentImageWrap}>
                                      <img
                                        src={att.url}
                                        alt={att.name || "Imagen adjunta"}
                                        className={styles.messageAttachmentImage}
                                        onClick={() => window.open(att.url, "_blank")}
                                        title="Click para ver en tamaño completo"
                                      />
                                    </div>
                                  );
                                }
                                if (isVideoAttachment(att)) {
                                  return (
                                    <div key={i} className={styles.messageAttachmentVideoWrap}>
                                      <video src={att.url} controls className={styles.messageAttachmentVideo} />
                                    </div>
                                  );
                                }
                                return (
                                  <a
                                    key={i}
                                    href={att.url}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className={styles.messageAttachmentFileCard}
                                  >
                                    <FileText size={16} color="var(--accent)" />
                                    <span className={styles.messageAttachmentFileName}>{att.name}</span>
                                  </a>
                                );
                              })}
                            </div>
                          )}

                          {/* Indicador de Hilo / Respuestas (Discord Style) */}
                          {msg.reply_count && msg.reply_count > 0 ? (
                            <div
                              className={styles.threadSummaryBar}
                              onClick={() => setActiveThreadMessage(msg)}
                              title="Abrir hilo de respuestas"
                            >
                              <GitBranch size={13} className={styles.threadSummaryIcon} />
                              <span className={styles.threadSummaryCount}>
                                {msg.reply_count} {msg.reply_count === 1 ? "respuesta" : "respuestas"}
                              </span>
                              <span>• Ver hilo</span>
                            </div>
                          ) : null}
                        </div>

                        {/* Botonera flotante al hacer hover sobre el mensaje */}
                        <div className={styles.messageActionsBar}>
                          {/* Botón para iniciar o ver hilo */}
                          <button
                            type="button"
                            className={styles.threadActionBtn}
                            onClick={() => setActiveThreadMessage(msg)}
                            title="Responder en hilo"
                          >
                            <MessageSquare size={13} />
                          </button>

                          {/* Botón para borrar si es autor o admin */}
                          {isAuthorOrAdmin && (
                            <button
                              type="button"
                              className={styles.deleteMsgBtn}
                              onClick={() => handleDeleteMessage(msg.id)}
                              title="Eliminar mensaje"
                            >
                              <Trash2 size={13} />
                            </button>
                          )}
                        </div>
                      </div>
                    );
                  })}

                  <div ref={messagesEndRef} />
                </div>

                {/* =========================================================
                    3. BARRA INFERIOR DE REDACCIÓN DISCORD (COMO EN LA IMAGEN)
                    ========================================================= */}
                <div className={styles.chatInputContainer}>
                  {canSendMessages ? (
                    <>
                      {/* Popover flotante de Autocompletado de menciones (@usuario y #canal) */}
                      {mentionMode && (
                        <div className={styles.mentionAutocompletePopover}>
                          <div className={styles.mentionHeader}>
                            <span>{mentionMode === "user" ? "Mencionar miembro" : "Mencionar canal"}</span>
                            <span style={{ fontSize: "10px", color: "var(--text-dim)" }}>
                              Tab o Enter para elegir
                            </span>
                          </div>

                          <div className={styles.mentionListScroll}>
                            {mentionMode === "user" &&
                              filteredMentionUsers.map((u, i) => (
                                <button
                                  key={u.id}
                                  type="button"
                                  className={`${styles.mentionOptionItem} ${i === mentionMatchIndex ? styles.selected : ""}`}
                                  onClick={() => handleSelectUserMention(u)}
                                >
                                  <div className={styles.mentionOptionLeft}>
                                    {u.avatar ? (
                                      <img src={u.avatar} alt={u.name} className={styles.mentionOptionAvatar} />
                                    ) : (
                                      <div className={styles.mentionOptionAvatarFallback}>
                                        {u.name.charAt(0).toUpperCase()}
                                      </div>
                                    )}
                                    <span className={styles.mentionOptionName}>{u.name}</span>
                                  </div>
                                  <span className={styles.mentionOptionBadge}>{u.role || "Miembro"}</span>
                                </button>
                              ))}

                            {mentionMode === "channel" &&
                              filteredMentionChannels.map((c, i) => (
                                <button
                                  key={c.id}
                                  type="button"
                                  className={`${styles.mentionOptionItem} ${i === mentionMatchIndex ? styles.selected : ""}`}
                                  onClick={() => handleSelectChannelMention(c)}
                                >
                                  <div className={styles.mentionOptionLeft}>
                                    <Hash size={14} color="var(--accent)" />
                                    <span className={styles.mentionOptionName}>{c.name}</span>
                                  </div>
                                  <span className={styles.mentionOptionBadge}>{c.category}</span>
                                </button>
                              ))}

                            {((mentionMode === "user" && filteredMentionUsers.length === 0) ||
                              (mentionMode === "channel" && filteredMentionChannels.length === 0)) && (
                              <div style={{ padding: "8px 10px", fontSize: "12px", color: "var(--text-muted)" }}>
                                No se encontraron coincidencias
                              </div>
                            )}
                          </div>
                        </div>
                      )}

                      {/* Popover flotante de Emojis Discord */}
                      {isEmojiPickerOpen && (
                        <div ref={emojiPickerRef} className={styles.emojiPickerPopover}>
                          <div className={styles.emojiPickerHeader}>
                            <input
                              type="text"
                              className={styles.emojiSearchInput}
                              placeholder="Buscar emoji..."
                              value={emojiSearchQuery}
                              onChange={(e) => setEmojiSearchQuery(e.target.value)}
                              autoFocus
                            />

                            <div className={styles.emojiCategoryTabs}>
                              {EMOJI_CATEGORIES.map((cat) => (
                                <button
                                  key={cat.id}
                                  type="button"
                                  className={`${styles.emojiCategoryTabBtn} ${activeEmojiCategory === cat.id ? styles.active : ""}`}
                                  onClick={() => {
                                    setActiveEmojiCategory(cat.id);
                                    setEmojiSearchQuery("");
                                  }}
                                >
                                  {cat.label}
                                </button>
                              ))}
                            </div>
                          </div>

                          <div className={styles.emojiGridContainer}>
                            {currentCategoryEmojis.map((em, i) => (
                              <button
                                key={i}
                                type="button"
                                className={styles.emojiItemBtn}
                                onClick={() => handleInsertEmoji(em)}
                              >
                                {em}
                              </button>
                            ))}
                          </div>
                        </div>
                      )}

                      {/* Previsualización del archivo adjunto antes de enviar */}
                      {pendingAttachment && (
                        <div className={styles.attachmentDraftWrapper}>
                          {pendingAttachment.type === "image" ? (
                            <div className={styles.attachmentImageThumbBox}>
                              <img
                                src={pendingAttachment.url}
                                alt={pendingAttachment.name}
                                className={styles.attachmentImageThumb}
                              />
                              <button
                                type="button"
                                className={styles.removeDraftAttachmentBtn}
                                onClick={() => setPendingAttachment(null)}
                                title="Quitar imagen"
                              >
                                <X size={12} />
                              </button>
                            </div>
                          ) : pendingAttachment.type === "video" ? (
                            <div className={styles.attachmentFileThumbBox}>
                              <div className={styles.attachmentFileThumbIcon} style={{ background: "rgba(14, 165, 233, 0.15)", color: "#0ea5e9" }}>
                                <Video size={18} />
                              </div>
                              <div className={styles.attachmentFileInfo}>
                                <span className={styles.attachmentThumbName}>{pendingAttachment.name}</span>
                                <span className={styles.attachmentSubText}>Video listo para enviar</span>
                              </div>
                              <button
                                type="button"
                                className={styles.removeDraftAttachmentBtnInline}
                                onClick={() => setPendingAttachment(null)}
                                title="Quitar video"
                              >
                                <X size={14} />
                              </button>
                            </div>
                          ) : (
                            <div className={styles.attachmentFileThumbBox}>
                              <div className={styles.attachmentFileThumbIcon}>
                                <FileText size={18} />
                              </div>
                              <div className={styles.attachmentFileInfo}>
                                <span className={styles.attachmentThumbName}>{pendingAttachment.name}</span>
                                <span className={styles.attachmentSubText}>Archivo adjunto</span>
                              </div>
                              <button
                                type="button"
                                className={styles.removeDraftAttachmentBtnInline}
                                onClick={() => setPendingAttachment(null)}
                                title="Quitar archivo"
                              >
                                <X size={14} />
                              </button>
                            </div>
                          )}
                        </div>
                      )}

                      {/* Input container exacto estilo Discord */}
                      <div className={styles.discordInputBoxWrapper}>
                        {/* Menú flotante de opciones para el botón + */}
                        {isAttachMenuOpen && (
                          <div ref={attachMenuRef} className={styles.attachMenuPopover}>
                            <div className={styles.attachMenuHeader}>Compartir contenido</div>
                            <button
                              type="button"
                              className={styles.attachMenuItem}
                              onClick={() => handleTriggerAttach("image")}
                            >
                              <div className={styles.attachMenuItemIconBox} style={{ background: "rgba(139, 92, 246, 0.15)", color: "#8b5cf6" }}>
                                <ImageIcon size={18} />
                              </div>
                              <div className={styles.attachMenuItemTexts}>
                                <span className={styles.attachMenuItemTitle}>Subir imagen</span>
                                <span className={styles.attachMenuItemDesc}>PNG, JPG, GIF o WebP</span>
                              </div>
                            </button>
                            <button
                              type="button"
                              className={styles.attachMenuItem}
                              onClick={() => handleTriggerAttach("video")}
                            >
                              <div className={styles.attachMenuItemIconBox} style={{ background: "rgba(14, 165, 233, 0.15)", color: "#0ea5e9" }}>
                                <Video size={18} />
                              </div>
                              <div className={styles.attachMenuItemTexts}>
                                <span className={styles.attachMenuItemTitle}>Subir video</span>
                                <span className={styles.attachMenuItemDesc}>MP4, WebM o clips</span>
                              </div>
                            </button>
                            <button
                              type="button"
                              className={styles.attachMenuItem}
                              onClick={() => handleTriggerAttach("file")}
                            >
                              <div className={styles.attachMenuItemIconBox} style={{ background: "rgba(16, 185, 129, 0.15)", color: "#10b981" }}>
                                <FileText size={18} />
                              </div>
                              <div className={styles.attachMenuItemTexts}>
                                <span className={styles.attachMenuItemTitle}>Subir archivo</span>
                                <span className={styles.attachMenuItemDesc}>PDF, documentos, comprimidos</span>
                              </div>
                            </button>
                          </div>
                        )}

                        {/* Input de archivo oculto con filtro dinámico */}
                        <input
                          type="file"
                          ref={fileInputRef}
                          style={{ display: "none" }}
                          accept={fileInputAccept}
                          onChange={(e) => handleFileUpload(e, false)}
                        />

                        {/* Botón + circular (Abre menú flotante) */}
                        <button
                          type="button"
                          className={`${styles.discordAttachBtn} ${isAttachMenuOpen ? styles.active : ""}`}
                          onClick={() => setIsAttachMenuOpen((prev) => !prev)}
                          title="Adjuntar imagen, video o archivo"
                        >
                          <Plus size={16} />
                        </button>

                        {/* Textarea de redacción totalmente sin bordes */}
                        <textarea
                          ref={textareaRef}
                          rows={1}
                          className={styles.discordTextarea}
                          placeholder={`Enviar mensaje a #${activeChannel.name}`}
                          value={newMessage}
                          onChange={handleTextareaChange}
                          onKeyDown={handleKeyDown}
                        />

                        {/* Botones de acción derecha estilo Discord */}
                        <div className={styles.discordInputActionsGroup}>
                          {/* Selector de Emojis Popover */}
                          <button
                            type="button"
                            className={`${styles.discordActionIconBtn} ${isEmojiPickerOpen ? styles.active : ""}`}
                            onClick={() => setIsEmojiPickerOpen((prev) => !prev)}
                            title="Selector de emojis"
                          >
                            <Smile size={19} />
                          </button>

                          {/* Botón Enviar */}
                          <button
                            type="button"
                            className={styles.discordSendBtn}
                            onClick={handleSendMessage}
                            disabled={(!newMessage.trim() && !pendingAttachment) || isSending}
                            title="Enviar mensaje (Enter)"
                          >
                            <Send size={14} />
                          </button>
                        </div>
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
          4. PANEL LATERAL DE HILOS (THREAD DRAWER DISCORD)
          ========================================================= */}
      {activeThreadMessage && (
        <aside className={styles.threadPanel}>
          <div className={styles.threadPanelHeader}>
            <div className={styles.threadPanelTitleBox}>
              <div className={styles.threadPanelTitle}>
                <MessageSquare size={16} color="var(--accent)" />
                <span>Hilo de conversación</span>
              </div>
              <span className={styles.threadPanelSubtitle}>
                En #{activeChannel?.name} • Iniciado por {activeThreadMessage.user_name}
              </span>
            </div>

            <button
              type="button"
              className={styles.closeThreadBtn}
              onClick={() => setActiveThreadMessage(null)}
              title="Cerrar hilo"
            >
              <X size={15} />
            </button>
          </div>

          <div className={styles.threadStreamArea}>
            {/* Mensaje original padre fijado arriba */}
            <div className={styles.threadParentCard}>
              <div className={styles.threadParentHeader}>
                <div className={styles.messageAvatarWrap} style={{ width: "30px", height: "30px" }}>
                  {activeThreadMessage.user_avatar ? (
                    <img
                      src={activeThreadMessage.user_avatar}
                      alt={activeThreadMessage.user_name}
                      className={styles.messageAvatarImg}
                    />
                  ) : (
                    <div className={styles.messageAvatarFallback} style={{ fontSize: "12px" }}>
                      {activeThreadMessage.user_name.charAt(0).toUpperCase()}
                    </div>
                  )}
                </div>

                <div style={{ display: "flex", flexDirection: "column" }}>
                  <span style={{ fontSize: "13px", fontWeight: 700, color: "var(--foreground)" }}>
                    {activeThreadMessage.user_name}
                  </span>
                  <span style={{ fontSize: "10.5px", color: "var(--text-dim)" }}>
                    {activeThreadMessage.created_at}
                  </span>
                </div>
              </div>

              <div className={styles.threadParentBody}>
                {renderMessageText(activeThreadMessage.message)}
              </div>

              {/* Adjuntos del mensaje padre si tiene */}
              {activeThreadMessage.attachments && activeThreadMessage.attachments.length > 0 && (
                <div className={styles.messageAttachmentsGrid}>
                  {activeThreadMessage.attachments.map((att, i) => {
                    if (isImageAttachment(att)) {
                      return (
                        <div key={i} className={styles.messageAttachmentImageWrap}>
                          <img
                            src={att.url}
                            alt={att.name || "Imagen adjunta"}
                            className={styles.messageAttachmentImage}
                            onClick={() => window.open(att.url, "_blank")}
                            title="Click para ver en tamaño completo"
                          />
                        </div>
                      );
                    }
                    if (isVideoAttachment(att)) {
                      return (
                        <div key={i} className={styles.messageAttachmentVideoWrap}>
                          <video src={att.url} controls className={styles.messageAttachmentVideo} />
                        </div>
                      );
                    }
                    return (
                      <a
                        key={i}
                        href={att.url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className={styles.messageAttachmentFileCard}
                      >
                        <FileText size={16} color="var(--accent)" />
                        <span className={styles.messageAttachmentFileName}>{att.name}</span>
                      </a>
                    );
                  })}
                </div>
              )}
            </div>

            {/* Divisor de respuestas */}
            <div className={styles.threadRepliesDivider}>
              <span>{threadReplies.length} {threadReplies.length === 1 ? "Respuesta" : "Respuestas"}</span>
            </div>

            {/* Lista de respuestas del hilo */}
            {threadReplies.map((reply) => {
              const isAuthorOrAdmin =
                effectiveIsAdmin || (currentUser?.id && reply.user_id === currentUser.id);

              return (
                <div key={reply.id} className={styles.messageRow}>
                  <div className={styles.messageAvatarWrap} style={{ width: "32px", height: "32px" }}>
                    {reply.user_avatar ? (
                      <img src={reply.user_avatar} alt={reply.user_name} className={styles.messageAvatarImg} />
                    ) : (
                      <div className={styles.messageAvatarFallback} style={{ fontSize: "12px" }}>
                        {reply.user_name.charAt(0).toUpperCase()}
                      </div>
                    )}
                  </div>

                  <div className={styles.messageContentWrap}>
                    <div className={styles.messageHeader}>
                      <span className={styles.messageAuthorName}>{reply.user_name}</span>
                      <span className={styles.messageTimestamp}>{reply.created_at}</span>
                    </div>
                    <div className={styles.messageBody}>
                      {renderMessageText(reply.message)}
                    </div>

                    {/* Adjuntos en respuesta del hilo */}
                    {reply.attachments && reply.attachments.length > 0 && (
                      <div className={styles.messageAttachmentsGrid}>
                        {reply.attachments.map((att, i) => {
                          if (isImageAttachment(att)) {
                            return (
                              <div key={i} className={styles.messageAttachmentImageWrap}>
                                <img
                                  src={att.url}
                                  alt={att.name || "Imagen adjunta"}
                                  className={styles.messageAttachmentImage}
                                  onClick={() => window.open(att.url, "_blank")}
                                  title="Click para ver en tamaño completo"
                                />
                              </div>
                            );
                          }
                          if (isVideoAttachment(att)) {
                            return (
                              <div key={i} className={styles.messageAttachmentVideoWrap}>
                                <video src={att.url} controls className={styles.messageAttachmentVideo} />
                              </div>
                            );
                          }
                          return (
                            <a
                              key={i}
                              href={att.url}
                              target="_blank"
                              rel="noopener noreferrer"
                              className={styles.messageAttachmentFileCard}
                            >
                              <FileText size={16} color="var(--accent)" />
                              <span className={styles.messageAttachmentFileName}>{att.name}</span>
                            </a>
                          );
                        })}
                      </div>
                    )}
                  </div>

                  {isAuthorOrAdmin && (
                    <div className={styles.messageActionsBar}>
                      <button
                        type="button"
                        className={styles.deleteMsgBtn}
                        onClick={() => handleDeleteMessage(reply.id)}
                        title="Eliminar respuesta"
                      >
                        <Trash2 size={12} />
                      </button>
                    </div>
                  )}
                </div>
              );
            })}
          </div>

          {/* Input inferior del Hilo */}
          <div className={styles.threadInputWrapper}>
            <input
              type="file"
              ref={threadFileInputRef}
              style={{ display: "none" }}
              onChange={(e) => handleFileUpload(e, true)}
            />

            {/* Previsualización del archivo adjunto en el hilo */}
            {threadAttachment && (
              <div className={styles.attachmentDraftWrapper}>
                {threadAttachment.type === "image" ? (
                  <div className={styles.attachmentImageThumbBox} style={{ width: "70px", height: "70px" }}>
                    <img
                      src={threadAttachment.url}
                      alt={threadAttachment.name}
                      className={styles.attachmentImageThumb}
                    />
                    <button
                      type="button"
                      className={styles.removeDraftAttachmentBtn}
                      onClick={() => setThreadAttachment(null)}
                      title="Quitar imagen"
                    >
                      <X size={11} />
                    </button>
                  </div>
                ) : (
                  <div className={styles.attachmentFileThumbBox} style={{ padding: "4px 8px" }}>
                    <div className={styles.attachmentFileThumbIcon} style={{ width: "24px", height: "24px" }}>
                      <FileText size={14} />
                    </div>
                    <span className={styles.attachmentThumbName} style={{ fontSize: "11px", maxWidth: "160px" }}>
                      {threadAttachment.name}
                    </span>
                    <button
                      type="button"
                      className={styles.removeDraftAttachmentBtnInline}
                      onClick={() => setThreadAttachment(null)}
                      title="Quitar archivo"
                    >
                      <X size={12} />
                    </button>
                  </div>
                )}
              </div>
            )}

            <div className={styles.discordInputBoxWrapper}>
              <button
                type="button"
                className={styles.discordAttachBtn}
                style={{ width: "28px", height: "28px" }}
                onClick={() => threadFileInputRef.current?.click()}
                title="Adjuntar en el hilo"
              >
                <Plus size={14} />
              </button>

              <textarea
                ref={threadTextareaRef}
                rows={1}
                className={styles.discordTextarea}
                placeholder={`Responder en el hilo...`}
                value={threadNewMessage}
                onChange={(e) => {
                  setThreadNewMessage(e.target.value);
                  e.target.style.height = "auto";
                  e.target.style.height = `${Math.min(e.target.scrollHeight, 100)}px`;
                }}
                onKeyDown={(e) => {
                  if (e.key === "Enter" && !e.shiftKey) {
                    e.preventDefault();
                    handleSendThreadReply();
                  }
                }}
              />

              <button
                type="button"
                className={styles.discordSendBtn}
                style={{ width: "28px", height: "28px" }}
                onClick={handleSendThreadReply}
                disabled={(!threadNewMessage.trim() && !threadAttachment) || isThreadSending}
                title="Enviar respuesta (Enter)"
              >
                <Send size={13} />
              </button>
            </div>
          </div>
        </aside>
      )}

      {/* =========================================================
          5. MODAL DE CREACIÓN / EDICIÓN DE CANAL (ADMIN)
          ========================================================= */}
      <Modal
        isOpen={isChannelModalOpen}
        onClose={() => setIsChannelModalOpen(false)}
        title={editingChannel ? `Editar Canal #${editingChannel.name}` : "Crear Nuevo Canal"}
        description="Configura el nombre del canal, la categoría y restringe el acceso según los cursos de la academia."
        maxWidth="540px"
      >
        <div style={{ display: "flex", flexDirection: "column", gap: "14px", marginTop: "10px" }}>
          {/* Nombre del canal */}
          <div className={styles.modalFormGroup}>
            <label className={styles.modalLabel}>
              <span>Nombre del canal</span>
              <span className={styles.modalLabelHint}>Formato minúscula sin espacios</span>
            </label>
            <div className={moneyStyles.container}>
              <span className={moneyStyles.symbol} aria-hidden="true">
                <Hash size={16} />
              </span>
              <input
                type="text"
                className={moneyStyles.input}
                placeholder="preguntas-respuestas"
                value={channelForm.name}
                onChange={(e) =>
                  setChannelForm({
                    ...channelForm,
                    name: e.target.value.toLowerCase().replace(/#/g, "").replace(/\s+/g, "-"),
                  })
                }
                autoComplete="off"
                spellCheck={false}
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

            {/* Input para nueva categoría si se elige crear nueva */}
            {isCustomCategory && (
              <div className={styles.newCategoryRow}>
                <div className={moneyStyles.container} style={{ flex: 1 }}>
                  <span className={moneyStyles.symbol} aria-hidden="true" style={{ color: "#10b981" }}>
                    <FolderPlus size={16} />
                  </span>
                  <input
                    type="text"
                    className={moneyStyles.input}
                    placeholder="Nombre del nuevo grupo..."
                    value={customCategoryText}
                    autoFocus
                    onChange={(e) => setCustomCategoryText(e.target.value)}
                    autoComplete="off"
                    spellCheck={false}
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

          {/* Restricción de Curso */}
          <div className={styles.modalFormGroup}>
            <label className={styles.modalLabel}>
              <span>Restricción de Acceso por Curso</span>
              <span className={styles.modalLabelHint}>Control exclusivo para matriculados</span>
            </label>
            <Select<number>
              options={restrictionOptions}
              value={channelForm.course_id || 0}
              onChange={(val) => {
                setChannelForm({ ...channelForm, course_id: val });
                if (val > 0) {
                  ensureStudentsLoaded();
                }
              }}
              placeholder="Seleccionar restricción de curso..."
              size="md"
            />
          </div>

          {/* Restricción de Estudiantes Específicos del Curso */}
          {channelForm.course_id > 0 && (
            <div className={styles.modalFormGroup} style={{ marginTop: "4px" }}>
              <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: "6px" }}>
                <label className={styles.modalLabel} style={{ marginBottom: 0 }}>
                  <span>Estudiantes con Acceso</span>
                  <span className={styles.modalLabelHint}>
                    {(channelForm.allowed_students?.length ?? 0) === 0
                      ? "Todos los matriculados (Por defecto)"
                      : `${channelForm.allowed_students?.length} alumno(s) seleccionado(s)`}
                  </span>
                </label>
                <div style={{ display: "flex", gap: "6px" }}>
                  <button
                    type="button"
                    className={styles.miniActionBtn}
                    onClick={() => {
                      setChannelForm((prev) => ({
                        ...prev,
                        allowed_students: [],
                      }));
                    }}
                    title="Permitir acceso a todos los estudiantes que tengan el curso"
                  >
                    Todos del curso
                  </button>
                  {courseFilteredStudents.length > 0 && (
                    <button
                      type="button"
                      className={styles.miniActionBtn}
                      onClick={() => {
                        setChannelForm((prev) => ({
                          ...prev,
                          allowed_students: courseFilteredStudents.map((s) => s.id),
                        }));
                      }}
                      title="Seleccionar a todos los estudiantes de la lista"
                    >
                      Marcar todos
                    </button>
                  )}
                </div>
              </div>

              {/* Buscador de estudiantes */}
              <div style={{ position: "relative", marginBottom: "8px" }}>
                <input
                  type="text"
                  placeholder="Buscar estudiante por nombre o correo..."
                  value={studentSearch}
                  onChange={(e) => setStudentSearch(e.target.value)}
                  className={styles.modalInput}
                  style={{ fontSize: "12px", padding: "7px 10px", width: "100%", boxSizing: "border-box" }}
                />
              </div>

              {/* Lista scrolleable con checkboxes */}
              <div
                style={{
                  maxHeight: "155px",
                  overflowY: "auto",
                  background: "var(--surface-muted, #1a1e27)",
                  border: "1px solid var(--border-subtle, rgba(255,255,255,0.08))",
                  borderRadius: "8px",
                  padding: "6px",
                  display: "flex",
                  flexDirection: "column",
                  gap: "3px",
                }}
              >
                {loadingStudents ? (
                  <div style={{ padding: "12px", textAlign: "center", fontSize: "12px", color: "var(--text-muted)" }}>
                    Cargando estudiantes...
                  </div>
                ) : searchedStudents.length === 0 ? (
                  <div style={{ padding: "12px", textAlign: "center", fontSize: "12px", color: "var(--text-muted)" }}>
                    {availableStudents.length === 0
                      ? "No hay estudiantes registrados en la academia."
                      : "No se encontraron estudiantes para esa búsqueda."}
                  </div>
                ) : (
                  searchedStudents.map((st) => {
                    const isSelected = (channelForm.allowed_students || []).includes(st.id);
                    const isEnrolledInCourse = (st.enabledCourseIds || []).map(Number).includes(Number(channelForm.course_id));
                    return (
                      <label
                        key={st.id}
                        style={{
                          display: "flex",
                          alignItems: "center",
                          justifyContent: "space-between",
                          padding: "6px 8px",
                          borderRadius: "6px",
                          background: isSelected ? "var(--surface-raised, rgba(255,255,255,0.06))" : "transparent",
                          cursor: "pointer",
                          fontSize: "12px",
                          transition: "background 0.15s ease",
                        }}
                      >
                        <div style={{ display: "flex", alignItems: "center", gap: "8px", minWidth: 0, flex: 1 }}>
                          <input
                            type="checkbox"
                            checked={isSelected}
                            onChange={(e) => {
                              const current = channelForm.allowed_students || [];
                              if (e.target.checked) {
                                setChannelForm((prev) => ({
                                  ...prev,
                                  allowed_students: [...current, st.id],
                                }));
                              } else {
                                setChannelForm((prev) => ({
                                  ...prev,
                                  allowed_students: current.filter((id) => id !== st.id),
                                }));
                              }
                            }}
                          />
                          <span style={{ fontWeight: 500, color: "var(--foreground)", whiteSpace: "nowrap", overflow: "hidden", textOverflow: "ellipsis" }}>
                            {st.name}
                          </span>
                          <span style={{ color: "var(--text-muted)", fontSize: "11px", whiteSpace: "nowrap", overflow: "hidden", textOverflow: "ellipsis" }}>
                            ({st.email})
                          </span>
                        </div>
                        {isEnrolledInCourse && (
                          <span style={{ fontSize: "10px", color: "var(--accent, #7747ff)", background: "var(--accent-subtle, rgba(119,71,255,0.12))", padding: "1px 6px", borderRadius: "4px", flexShrink: 0 }}>
                            Matriculado
                          </span>
                        )}
                      </label>
                    );
                  })
                )}
              </div>
              <p style={{ margin: "4px 0 0", fontSize: "11px", color: "var(--text-muted)" }}>
                *(Si no marcas ningún estudiante específico, el canal estará visible para <strong>todos</strong> los alumnos que tengan acceso al curso).*
              </p>
            </div>
          )}

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
          6. MODAL DE CONFIRMACIÓN DE ELIMINACIÓN DE CANAL
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

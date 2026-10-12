import React, { useState, useEffect, useRef } from "react";
import { 
  Bell, 
  Calendar, 
  BookOpen, 
  MessageSquare, 
  CheckCheck, 
  Info, 
  Clock, 
  ExternalLink 
} from "lucide-react";
import styles from "./NotificationsPopover.module.css";

export interface NotificationItem {
  id: string;
  title: string;
  message: string;
  type: "event" | "course" | "community" | "system";
  time: string;
  read: boolean;
  targetTab?: string;
  targetId?: string;
}

interface NotificationsPopoverProps {
  isOpen: boolean;
  onClose: () => void;
  onNavigate?: (tab: string, targetId?: string) => void;
  appMode: "admin" | "student";
}

const DEFAULT_NOTIFICATIONS: NotificationItem[] = [
  {
    id: "notif-1",
    title: "Masterclass en Vivo por Zoom",
    message: "Q&A en Vivo: Estrategias de Crecimiento inicia pronto. ¡Únete a la sala!",
    type: "event",
    time: "Hoy 7:00 PM",
    read: false,
    targetTab: "events",
  },
  {
    id: "notif-2",
    title: "Nuevo Taller Práctico",
    message: "Workshop Intensivo: De Cero a Escala ha sido programado en el calendario.",
    type: "event",
    time: "Ayer",
    read: false,
    targetTab: "events",
  },
  {
    id: "notif-3",
    title: "Comunidad Crezca",
    message: "Hay 5 nuevos aportes en el canal de debate y networking.",
    type: "community",
    time: "Hace 2 horas",
    read: false,
    targetTab: "community",
  },
  {
    id: "notif-4",
    title: "Sistema Actualizado",
    message: "El campus virtual ahora cuenta con sincronización en tiempo real y modo pomodoro.",
    type: "system",
    time: "Hace 1 día",
    read: true,
  },
];

export const NotificationsPopover: React.FC<NotificationsPopoverProps> = ({
  isOpen,
  onClose,
  onNavigate,
  appMode,
}) => {
  const [filter, setFilter] = useState<"all" | "unread">("all");
  const [notifications, setNotifications] = useState<NotificationItem[]>(() => {
    try {
      const stored = localStorage.getItem("crezca_notifications");
      if (stored) return JSON.parse(stored);
    } catch (e) {
      // fallback
    }
    return DEFAULT_NOTIFICATIONS;
  });

  const popoverRef = useRef<HTMLDivElement | null>(null);

  // Guardar cambios en localStorage
  useEffect(() => {
    localStorage.setItem("crezca_notifications", JSON.stringify(notifications));
  }, [notifications]);

  // Cerrar al presionar Escape o hacer click afuera
  useEffect(() => {
    if (!isOpen) return;

    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === "Escape") onClose();
    };

    const handleClickOutside = (e: MouseEvent) => {
      if (popoverRef.current && !popoverRef.current.contains(e.target as Node)) {
        onClose();
      }
    };

    document.addEventListener("keydown", handleKeyDown);
    // Timeout para evitar que el click que abre el popover lo cierre inmediatamente
    const timer = setTimeout(() => {
      document.addEventListener("click", handleClickOutside);
    }, 10);

    return () => {
      document.removeEventListener("keydown", handleKeyDown);
      document.removeEventListener("click", handleClickOutside);
      clearTimeout(timer);
    };
  }, [isOpen, onClose]);

  if (!isOpen) return null;

  const unreadCount = notifications.filter((n) => !n.read).length;
  const filteredList = filter === "unread" 
    ? notifications.filter((n) => !n.read) 
    : notifications;

  const handleMarkAllAsRead = (e: React.MouseEvent) => {
    e.stopPropagation();
    setNotifications((prev) => prev.map((n) => ({ ...n, read: true })));
  };

  const handleClickItem = (item: NotificationItem) => {
    // Marcar como leída
    setNotifications((prev) =>
      prev.map((n) => (n.id === item.id ? { ...n, read: true } : n))
    );
    if (item.targetTab && onNavigate) {
      onNavigate(item.targetTab, item.targetId);
    }
    onClose();
  };

  const getIcon = (type: NotificationItem["type"]) => {
    switch (type) {
      case "event":
        return <Calendar size={17} />;
      case "course":
        return <BookOpen size={17} />;
      case "community":
        return <MessageSquare size={17} />;
      case "system":
      default:
        return <Info size={17} />;
    }
  };

  return (
    <div ref={popoverRef} className={styles.popoverContainer} onClick={(e) => e.stopPropagation()}>
      <div className={styles.header}>
        <div className={styles.headerTitleRow}>
          <h4 className={styles.headerTitle}>Notificaciones</h4>
          {unreadCount > 0 && (
            <span className={styles.unreadChip}>{unreadCount} nuevas</span>
          )}
        </div>
        {unreadCount > 0 && (
          <button
            type="button"
            className={styles.markAllBtn}
            onClick={handleMarkAllAsRead}
            title="Marcar todas como leídas"
          >
            <CheckCheck size={14} />
            <span>Leer todas</span>
          </button>
        )}
      </div>

      <div className={styles.tabsRow}>
        <button
          type="button"
          className={`${styles.tabBtn} ${filter === "all" ? styles.tabActive : ""}`}
          onClick={() => setFilter("all")}
        >
          Todas ({notifications.length})
        </button>
        <button
          type="button"
          className={`${styles.tabBtn} ${filter === "unread" ? styles.tabActive : ""}`}
          onClick={() => setFilter("unread")}
        >
          No leídas ({unreadCount})
        </button>
      </div>

      <div className={styles.list}>
        {filteredList.length === 0 ? (
          <div className={styles.emptyState}>
            <Bell size={24} style={{ opacity: 0.3 }} />
            <p>No tienes notificaciones {filter === "unread" ? "sin leer" : ""}</p>
          </div>
        ) : (
          filteredList.map((item) => (
            <div
              key={item.id}
              className={`${styles.item} ${!item.read ? styles.unread : ""}`}
              onClick={() => handleClickItem(item)}
            >
              <div className={`${styles.iconWrap} ${styles[item.type]}`}>
                {getIcon(item.type)}
              </div>
              <div className={styles.itemContent}>
                <div className={styles.itemTitle}>{item.title}</div>
                <div className={styles.itemDesc}>{item.message}</div>
                <div className={styles.itemTime}>{item.time}</div>
              </div>
              {!item.read && <span className={styles.unreadDot} />}
            </div>
          ))
        )}
      </div>

      <div className={styles.footer}>
        <span className={styles.footerText}>
          {appMode === "admin" ? "Notificaciones de la Academia" : "Campus Virtual del Estudiante"}
        </span>
      </div>
    </div>
  );
};

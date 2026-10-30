import React, { useState, useEffect, useRef } from "react";
import ReactDOM from "react-dom";
import { 
  Bell, 
  Calendar, 
  BookOpen, 
  MessageSquare, 
  Check,
  CheckCheck, 
  Trash2,
  Info, 
  Clock, 
  ExternalLink,
  X
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
  appMode: "admin" | "student" | "instructor";
  onUpdateCount?: (count: number) => void;
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
  onUpdateCount,
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

  // Guardar cambios en localStorage y notificar al padre
  useEffect(() => {
    localStorage.setItem("crezca_notifications", JSON.stringify(notifications));
    const unread = notifications.filter((n) => !n.read).length;
    if (onUpdateCount) {
      onUpdateCount(unread);
    }
  }, [notifications, onUpdateCount]);

  // Cerrar al presionar Escape
  useEffect(() => {
    if (!isOpen) return;

    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === "Escape") onClose();
    };

    document.addEventListener("keydown", handleKeyDown);
    return () => {
      document.removeEventListener("keydown", handleKeyDown);
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

  const handleToggleRead = (e: React.MouseEvent, id: string) => {
    e.stopPropagation();
    setNotifications((prev) =>
      prev.map((n) => (n.id === id ? { ...n, read: !n.read } : n))
    );
  };

  const handleDeleteItem = (e: React.MouseEvent, id: string) => {
    e.stopPropagation();
    setNotifications((prev) => prev.filter((n) => n.id !== id));
  };

  const handleClearAll = (e: React.MouseEvent) => {
    e.stopPropagation();
    setNotifications([]);
  };

  const handleClearRead = (e: React.MouseEvent) => {
    e.stopPropagation();
    setNotifications((prev) => prev.filter((n) => !n.read));
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

  const popoverContent = (
    <>
      <div 
        className={styles.popoverOverlay} 
        onClick={(e) => {
          e.stopPropagation();
          onClose();
        }} 
      />
      <div 
        ref={popoverRef} 
        className={styles.popoverContainer} 
        onClick={(e) => e.stopPropagation()}
      >
        <div className={styles.header}>
          <div className={styles.headerTitleRow}>
            <h4 className={styles.headerTitle}>Notificaciones</h4>
            {unreadCount > 0 && (
              <span className={styles.unreadChip}>{unreadCount} nuevas</span>
            )}
          </div>
          <div className={styles.headerActions}>
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
            {notifications.length > 0 && (
              <button
                type="button"
                className={styles.clearAllBtn}
                onClick={handleClearAll}
                title="Eliminar todas las notificaciones"
              >
                <Trash2 size={13} />
                <span>Limpiar</span>
              </button>
            )}
            <button
              type="button"
              className={styles.closePopoverBtn}
              onClick={onClose}
              title="Cerrar notificaciones"
              aria-label="Cerrar"
            >
              <X size={16} />
            </button>
          </div>
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
                <div className={styles.iconWrap}>
                  {getIcon(item.type)}
                </div>
                <div className={styles.itemContent}>
                  <div className={styles.itemTitle}>{item.title}</div>
                  <div className={styles.itemDesc}>{item.message}</div>
                  <div className={styles.itemTime}>{item.time}</div>
                </div>

                <div className={styles.itemRightSide}>
                  {!item.read && <span className={styles.unreadDot} />}
                  <div className={styles.itemActions}>
                    <button
                      type="button"
                      className={`${styles.itemActionBtn} ${item.read ? styles.actionRead : styles.actionUnread}`}
                      onClick={(e) => handleToggleRead(e, item.id)}
                      title={item.read ? "Marcar como no leída" : "Marcar como leída"}
                      aria-label={item.read ? "Marcar como no leída" : "Marcar como leída"}
                    >
                      {item.read ? <CheckCheck size={13} /> : <Check size={13} />}
                    </button>
                    <button
                      type="button"
                      className={`${styles.itemActionBtn} ${styles.actionDelete}`}
                      onClick={(e) => handleDeleteItem(e, item.id)}
                      title="Eliminar notificación"
                      aria-label="Eliminar"
                    >
                      <Trash2 size={13} />
                    </button>
                  </div>
                </div>
              </div>
            ))
          )}
        </div>

        <div className={styles.footer}>
          <span className={styles.footerText}>
            {appMode === "admin" ? "Notificaciones de la Academia" : (appMode === "instructor" ? "Panel del Docente" : "Campus Virtual del Estudiante")}
          </span>
          {notifications.some((n) => n.read) && (
            <button
              type="button"
              className={styles.clearReadFooterBtn}
              onClick={handleClearRead}
              title="Eliminar notificaciones leídas"
            >
              <Trash2 size={11} />
              <span>Borrar leídas</span>
            </button>
          )}
        </div>
      </div>
    </>
  );

  // En móvil montamos directo en body para evitar problemas de stacking context / backdrop-filter
  const isMobileView = typeof window !== "undefined" && window.innerWidth <= 768;
  if (isMobileView && typeof document !== "undefined") {
    return ReactDOM.createPortal(popoverContent, document.body);
  }

  return popoverContent;
};

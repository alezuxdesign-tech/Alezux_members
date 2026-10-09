import { ReactNode } from "react";
import styles from "./badge.module.css";

export interface BadgeProps {
  children: ReactNode;
  variant?: "neutral" | "accent" | "success" | "warning" | "danger";
  size?: "sm" | "md";
  className?: string;
}

export function Badge({ children, variant = "neutral", size = "sm", className }: BadgeProps) {
  const classes = [styles.badge, styles[variant], styles[size], className].filter(Boolean).join(" ");
  return <span className={classes}>{children}</span>;
}

export default Badge;

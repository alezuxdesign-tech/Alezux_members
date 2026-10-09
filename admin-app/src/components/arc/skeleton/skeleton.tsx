import React from "react";
import styles from "./skeleton.module.css";

export interface SkeletonProps extends React.HTMLAttributes<HTMLDivElement> {
  width?: string | number;
  height?: string | number;
  variant?: "text" | "circular" | "rectangular" | "rounded" | "surface";
  className?: string;
  style?: React.CSSProperties;
}

export function Skeleton({
  width,
  height,
  variant = "rounded",
  className = "",
  style,
  ...props
}: SkeletonProps) {
  const customStyle: React.CSSProperties = {
    ...style,
    ...(width !== undefined ? { width: typeof width === "number" ? `${width}px` : width } : {}),
    ...(height !== undefined ? { height: typeof height === "number" ? `${height}px` : height } : {}),
  };

  return (
    <div
      className={[styles.skeleton, styles[variant], className].filter(Boolean).join(" ")}
      style={customStyle}
      aria-hidden="true"
      {...props}
    />
  );
}

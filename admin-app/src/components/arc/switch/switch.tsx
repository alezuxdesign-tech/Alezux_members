import { forwardRef } from "react";
import { motion } from "motion/react";
import { motionTokens } from "../motion-tokens";
import styles from "./switch.module.css";

export interface SwitchProps {
  checked: boolean;
  onCheckedChange: (checked: boolean) => void;
  disabled?: boolean;
  size?: "sm" | "md";
  id?: string;
  name?: string;
  className?: string;
  "aria-label"?: string;
}

export const Switch = forwardRef<HTMLButtonElement, SwitchProps>(function Switch(
  { checked, onCheckedChange, disabled = false, size = "md", className, id, name, ...props },
  ref
) {
  const travelDistance = size === "sm" ? 16 : 20;

  return (
    <button
      type="button"
      role="switch"
      id={id}
      name={name}
      ref={ref}
      aria-checked={checked}
      disabled={disabled}
      data-state={checked ? "checked" : "unchecked"}
      className={[styles.root, styles[size], className].filter(Boolean).join(" ")}
      onClick={() => !disabled && onCheckedChange(!checked)}
      {...props}
    >
      <motion.span
        className={styles.thumb}
        animate={{
          x: checked ? travelDistance : 0,
        }}
        transition={motionTokens.spring.snappy}
      />
    </button>
  );
});

Switch.displayName = "Switch";
export default Switch;

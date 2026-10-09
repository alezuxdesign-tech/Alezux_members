import { forwardRef, InputHTMLAttributes, ReactNode } from "react";
import styles from "./input.module.css";

export interface InputProps extends InputHTMLAttributes<HTMLInputElement> {
  label?: string;
  hint?: string;
  error?: string;
  leftIcon?: ReactNode;
  sizeVariant?: "sm" | "md";
}

export const Input = forwardRef<HTMLInputElement, InputProps>(function Input(
  { label, hint, error, leftIcon, sizeVariant = "md", className, id, ...props },
  ref
) {
  return (
    <div className={[styles.wrapper, styles[sizeVariant], className].filter(Boolean).join(" ")}>
      {label && (
        <label htmlFor={id} className={styles.label}>
          {label}
        </label>
      )}
      <div className={[styles.inputRoot, leftIcon ? styles.hasLeftIcon : ""].filter(Boolean).join(" ")}>
        {leftIcon && <span className={styles.leftIcon}>{leftIcon}</span>}
        <input
          ref={ref}
          id={id}
          className={styles.input}
          {...props}
        />
      </div>
      {hint && !error && <span className={styles.hint}>{hint}</span>}
      {error && <span className={styles.error}>{error}</span>}
    </div>
  );
});

Input.displayName = "Input";
export default Input;

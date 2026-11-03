const fs = require('fs');
const path = require('path');

const packages = [
  'arc-foundation',
  'arc-motion-tokens',
  'site-header',
  'hero-section',
  'logo-marquee',
  'stats-band',
  'card',
  'avatar',
  'badge',
  'button',
  'input',
  'slider',
  'segmented-control',
  'switch',
  'tabs',
  'metric-card',
  'animated-counter',
  'line-chart',
  'sparkline',
  'search-field',
  'comparison-table',
  'faq-section',
  'cta-section',
  'site-footer',
  'modal'
];

const visited = new Set();

async function installItem(name) {
  const cleanName = name.replace('https://uiarc.dev/r/', '').replace('.json', '');
  if (visited.has(cleanName)) return;
  visited.add(cleanName);

  const url = name.startsWith('http') ? name : `https://uiarc.dev/r/${name}.json`;
  console.log('Downloading:', cleanName);

  try {
    const res = await fetch(url);
    if (!res.ok) {
      console.error('Failed to download', url, res.status);
      return;
    }
    const data = await res.json();
    if (data.files && Array.isArray(data.files)) {
      for (const file of data.files) {
        let target = file.target || file.path;
        let dest;
        if (target.startsWith('@components/arc/')) {
          dest = path.join('web/src/components/arc', target.replace('@components/arc/', ''));
        } else if (target.startsWith('@/components/arc/')) {
          dest = path.join('web/src/components/arc', target.replace('@/components/arc/', ''));
        } else if (target.startsWith('@components/')) {
          dest = path.join('web/src/components', target.replace('@components/', ''));
        } else {
          dest = path.join('web/src/components/arc', target);
        }

        let content = file.content;
        
        // Next.js Image shim for pure React/Vite
        if (content.includes('import Image from "next/image";') || content.includes("import Image from 'next/image';")) {
          content = content.replace(
            /import Image from ["']next\/image["'];/,
            'const Image = ({ src, alt, width, height, className, style, ...props }: any) => <img src={src} alt={alt || ""} width={width} height={height} className={className} style={style} {...props} />;'
          );
        }

        // HTMLMotionProps inert fix
        if (content.includes('inert={!present}')) {
          content = content.replace('inert={!present}', 'data-inert={!present ? "" : undefined}');
        }

        // Card default export
        if (cleanName === 'card' && target.endsWith('card.tsx') && !content.includes('export default Card;')) {
          content = content.replace(/(\n}\s*)$/, '\n}\n\nexport default Card;\n');
          content = content.replace('{...(props as HTMLMotionProps<"article">)}', '{...(props as any)}');
        }

        // Tabs RefObject fix
        if (cleanName === 'tabs' && target.endsWith('tabs.tsx')) {
          content = content.replace('RefObject<number | null>', 'React.MutableRefObject<number | null>');
          content = content.replace('RefObject<DOMRect | null>', 'React.MutableRefObject<DOMRect | null>');
        }

        // Switch props fix
        if (cleanName === 'switch' && target.endsWith('switch.tsx')) {
          content = content.replace(
            /export interface SwitchProps extends ComponentPropsWithoutRef<typeof SwitchPrimitive\.Root>[\s\S]*?}/,
            `export interface SwitchProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  label?: string;
  checked?: boolean;
  defaultChecked?: boolean;
  onCheckedChange?: (checked: boolean) => void;
  disabled?: boolean;
}`
          );
          content = content.replace('forwardRef<ElementRef<typeof SwitchPrimitive.Root>, SwitchProps>', 'forwardRef<HTMLButtonElement, SwitchProps>');
          content = content.replace('<SwitchPrimitive.Root\n      {...props}', '<SwitchPrimitive.Root\n      {...(props as any)}');
        }

        fs.mkdirSync(path.dirname(dest), { recursive: true });
        fs.writeFileSync(dest, content, 'utf8');
        console.log('  -> Wrote', dest);
      }
    }

    if (data.registryDependencies && Array.isArray(data.registryDependencies)) {
      for (const dep of data.registryDependencies) {
        await installItem(dep);
      }
    }
  } catch (err) {
    console.error('Error installing', name, err.message);
  }
}

async function run() {
  for (const pkg of packages) {
    await installItem(pkg);
  }
  console.log('All Arc UI components and blocks installed successfully!');
}

run();

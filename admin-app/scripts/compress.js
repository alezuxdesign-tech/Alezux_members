import fs from 'fs';
import path from 'path';
import zlib from 'zlib';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const distDir = path.resolve(__dirname, '../../assets/dist');

const filesToCompress = ['alezux-dashboard.js', 'alezux-dashboard.css'];

for (const file of filesToCompress) {
  const filePath = path.join(distDir, file);
  if (fs.existsSync(filePath)) {
    const content = fs.readFileSync(filePath);
    const compressed = zlib.gzipSync(content, { level: 9 });
    fs.writeFileSync(`${filePath}.gz`, compressed);
    const originalKb = (content.length / 1024).toFixed(1);
    const gzKb = (compressed.length / 1024).toFixed(1);
    const saving = (100 - (compressed.length / content.length) * 100).toFixed(1);
    console.log(`✓ Pre-compressed ${file}: ${originalKb} kB -> ${gzKb} kB (${saving}% savings)`);
  }
}

const fs = require('fs');
const path = require('path');

const srcDir = path.resolve('web/src');
const ourFiles = [
  'App.tsx',
  'components/Navbar.tsx',
  'components/Hero.tsx',
  'components/SocialProofMarquee.tsx',
  'components/Metrics.tsx',
  'components/BentoGrid.tsx',
  'components/DiscordCommunity.tsx',
  'components/InteractiveTabs.tsx',
  'components/SavingsCalculator.tsx',
  'components/StepsSection.tsx',
  'components/Testimonials.tsx',
  'components/IntegrationsSection.tsx',
  'components/PricingSection.tsx',
  'components/FAQSection.tsx',
  'components/FinalCTA.tsx',
  'components/Footer.tsx',
  'components/CheckoutModal.tsx'
];

let issues = 0;

for (const file of ourFiles) {
  const fullPath = path.join(srcDir, file);
  if (!fs.existsSync(fullPath)) continue;
  const content = fs.readFileSync(fullPath, 'utf8');

  // Check 1: em dashes (—)
  if (content.includes('\u2014') || content.includes('—')) {
    console.log('[FAIL] Em dash in', file);
    issues++;
  }

  // Check 2: font-weight > 500
  const weightMatches = content.match(/fontWeight:\s*['"]?(600|700|800|900|bold)/g) || content.match(/font-weight:\s*(600|700|800|900|bold)/g);
  if (weightMatches) {
    console.log('[FAIL] Font weight > 500 in', file, weightMatches);
    issues++;
  }

  // Check 3: text-transform: uppercase / uppercase keywords in style
  if (content.match(/textTransform:\s*['"]uppercase/i) || content.match(/text-transform:\s*uppercase/i)) {
    console.log('[FAIL] Uppercase transform in', file);
    issues++;
  }

  // Check 4: raw hex colors in style (except in brand logos / SVG)
  const lines = content.split('\n');
  lines.forEach((line, idx) => {
    if (line.includes('colorIcon:') || (line.includes('color:') && file.includes('SocialProofMarquee'))) return;
    if (line.match(/#[0-9a-fA-F]{3,8}\b/) && !line.includes('data:image/svg')) {
      console.log('[FAIL] Raw hex on line', idx + 1, 'in', file, ':', line.trim());
      issues++;
    }
  });
}

console.log('Total issues found in our landing components:', issues);

import sharp from 'sharp';
import { readFileSync } from 'fs';

const svgBuffer = readFileSync('docs/ERD.svg');

// Render SVG to PNG then rotate 90° clockwise to make it horizontal
const png = await sharp(svgBuffer)
  .png({ quality: 100 })
  .toBuffer();

await sharp(png)
  .rotate(90)
  .toFile('docs/ERD.png');

console.log('✅ ERD rendered to docs/ERD.png (horizontal)');

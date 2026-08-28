import sharp from 'sharp';
import { readdirSync } from 'fs';
import { join } from 'path';

const dir = join(import.meta.dirname, '..', 'docs', 'use-cases');
const files = readdirSync(dir).filter(f => f.endsWith('.svg'));

console.log(`Found ${files.length} SVG files`);

for (const file of files) {
    const svgPath = join(dir, file);
    const pngPath = join(dir, file.replace('.svg', '.png'));

    await sharp(svgPath)
        .png({ quality: 100 })
        .toFile(pngPath);

    console.log(`Converted: ${file} → ${file.replace('.svg', '.png')}`);
}

console.log('\n✅ All use case diagrams converted to PNG');

import { mkdirSync, writeFileSync } from 'fs';
import { join } from 'path';

const outputDir = join(import.meta.dirname, '..', 'docs', 'flowcharts');
mkdirSync(outputDir, { recursive: true });

function escapeXml(str) {
    return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function createFlowchartSVG(title, nodes, arrows) {
    const width = 1000;
    const height = Math.max(400, nodes.length * 70 + 200);

    let svg = `<svg xmlns="http://www.w3.org/2000/svg" width="${width}" height="${height}" viewBox="0 0 ${width} ${height}">
  <style>
    text { font-family: Arial, sans-serif; font-size: 12px; }
    .title { font-size: 20px; font-weight: bold; fill: #1d71b8; }
    .node { fill: #e7e2dd; stroke: #1d71b8; stroke-width: 2; }
    .decision { fill: #fff3cd; stroke: #ffc107; stroke-width: 2; }
    .start { fill: #d4edda; stroke: #28a745; stroke-width: 2; }
    .end { fill: #f8d7da; stroke: #dc3545; stroke-width: 2; }
    .process { fill: #cce5ff; stroke: #17a2b8; stroke-width: 2; }
  </style>
  
  <rect width="100%" height="100%" fill="white"/>
  <text x="${width/2}" y="30" text-anchor="middle" class="title">${escapeXml(title)}</text>
`;

    // Draw nodes
    const startX = width / 2;
    let y = 80;

    nodes.forEach((node, i) => {
        const nodeWidth = 180;
        const nodeHeight = 40;
        const x = startX - nodeWidth / 2;

        let className = 'node';
        if (node.type === 'start') className = 'start';
        else if (node.type === 'end') className = 'end';
        else if (node.type === 'decision') className = 'decision';
        else if (node.type === 'process') className = 'process';

        if (node.type === 'decision') {
            // Diamond shape for decisions
            const cx = startX;
            const cy = y + nodeHeight / 2;
            svg += `  <polygon points="${cx},${y} ${cx + nodeWidth/2},${cy} ${cx},${y + nodeHeight} ${cx - nodeWidth/2},${cy}" class="${className}"/>
`;
        } else if (node.type === 'start' || node.type === 'end') {
            // Rounded rectangle for start/end
            svg += `  <rect x="${x}" y="${y}" width="${nodeWidth}" height="${nodeHeight}" rx="20" class="${className}"/>
`;
        } else {
            // Regular rectangle
            svg += `  <rect x="${x}" y="${y}" width="${nodeWidth}" height="${nodeHeight}" class="${className}"/>
`;
        }

        // Node text
        svg += `  <text x="${startX}" y="${y + nodeHeight/2 + 4}" text-anchor="middle" class="node-text">${escapeXml(node.label)}</text>
`;

        // Draw arrow to next node
        if (i < nodes.length - 1) {
            const arrowStartY = y + nodeHeight;
            const arrowEndY = y + nodeHeight + 25;
            svg += `  <line x1="${startX}" y1="${arrowStartY}" x2="${startX}" y2="${arrowEndY}" stroke="#333" stroke-width="2" marker-end="url(#arrow)"/>
`;
            y += 65;
        } else {
            y += 50;
        }
    });

    // Add arrow marker
    svg = svg.replace('<rect', `
  <defs>
    <marker id="arrow" markerWidth="10" markerHeight="10" refX="9" refY="3" orient="auto">
      <path d="M0,0 L0,6 L9,3 z" fill="#333"/>
    </marker>
  </defs>
  <rect`);

    svg += `</svg>`;
    return svg;
}

// Flowchart 1: Peminjaman Alat
const borrowingFlow = createFlowchartSVG('1. Flowchart Peminjaman Alat', [
    { label: 'User Login', type: 'start' },
    { label: 'Lihat Katalog Alat', type: 'process' },
    { label: 'Pilih Alat', type: 'process' },
    { label: 'Tambah ke Keranjang', type: 'process' },
    { label: 'Checkout', type: 'process' },
    { label: 'Upload Dokumen', type: 'process' },
    { label: 'Submit Pengajuan', type: 'process' },
    { label: 'Admin Review', type: 'decision' },
    { label: 'Diterima', type: 'process' },
    { label: 'Proses Pengembalian', type: 'process' },
    { label: 'Selesai', type: 'end' }
]);
writeFileSync(join(outputDir, 'flowchart-1-peminjaman.svg'), borrowingFlow);
console.log('Created: flowchart-1-peminjaman.svg');

// Flowchart 2: Pengujian Sampel
const testFlow = createFlowchartSVG('2. Flowchart Pengujian Sampel', [
    { label: 'User Login', type: 'start' },
    { label: 'Lihat Katalog Parameter', type: 'process' },
    { label: 'Pilih Parameter', type: 'process' },
    { label: 'Tambah ke Keranjang', type: 'process' },
    { label: 'Checkout', type: 'process' },
    { label: 'Submit Sampel', type: 'process' },
    { label: 'Admin Assign Laboran', type: 'process' },
    { label: 'Laboran Proses', type: 'process' },
    { label: 'Upload Hasil', type: 'process' },
    { label: 'User Unduh Hasil', type: 'end' }
]);
writeFileSync(join(outputDir, 'flowchart-2-pengujian.svg'), testFlow);
console.log('Created: flowchart-2-pengujian.svg');

// Flowchart 3: Proposal Riset
const researchFlow = createFlowchartSVG('3. Flowchart Proposal Riset', [
    { label: 'User Login', type: 'start' },
    { label: 'Buat Proposal Baru', type: 'process' },
    { label: 'Tambah Anggota Tim', type: 'process' },
    { label: 'Pilih Alat Riset', type: 'process' },
    { label: 'Submit Proposal', type: 'process' },
    { label: 'Admin Review', type: 'decision' },
    { label: 'Setujui', type: 'process' },
    { label: 'Assign Laboran', type: 'process' },
    { label: 'Isi Logbook Harian', type: 'process' },
    { label: 'Selesai Riset', type: 'end' }
]);
writeFileSync(join(outputDir, 'flowchart-3-riset.svg'), researchFlow);
console.log('Created: flowchart-3-riset.svg');

// Flowchart 4: Pemeriksaan Kesehatan
const healthFlow = createFlowchartSVG('4. Flowchart Pemeriksaan Kesehatan', [
    { label: 'User Login', type: 'start' },
    { label: 'Pilih Jenis Pemeriksaan', type: 'process' },
    { label: 'Pilih Jadwal', type: 'process' },
    { label: 'Booking', type: 'process' },
    { label: 'Bayar', type: 'process' },
    { label: 'Hadir ke Lab', type: 'process' },
    { label: 'Laboran Periksa', type: 'process' },
    { label: 'Upload Hasil', type: 'process' },
    { label: 'User Unduh Surat', type: 'end' }
]);
writeFileSync(join(outputDir, 'flowchart-4-kesehatan.svg'), healthFlow);
console.log('Created: flowchart-4-kesehatan.svg');

// Flowchart 5: Event Registration
const eventFlow = createFlowchartSVG('5. Flowchart Pendaftaran Event', [
    { label: 'User Login', type: 'start' },
    { label: 'Lihat Event', type: 'process' },
    { label: 'Detail Event', type: 'process' },
    { label: 'Daftar Event', type: 'process' },
    { label: 'Admin Approve', type: 'decision' },
    { label: 'Hadiri Event', type: 'process' },
    { label: 'Absensi', type: 'process' },
    { label: 'Unduh Sertifikat', type: 'end' }
]);
writeFileSync(join(outputDir, 'flowchart-5-event.svg'), eventFlow);
console.log('Created: flowchart-5-event.svg');

// Flowchart 6: Autentikasi
const authFlow = createFlowchartSVG('6. Flowchart Autentikasi', [
    { label: 'Buka Halaman Login', type: 'start' },
    { label: 'Input Email & Password', type: 'process' },
    { label: 'Validasi', type: 'decision' },
    { label: 'Cek Profil Lengkap', type: 'decision' },
    { label: 'Lengkapi Profil', type: 'process' },
    { label: 'Dashboard', type: 'end' }
]);
writeFileSync(join(outputDir, 'flowchart-6-autentikasi.svg'), authFlow);
console.log('Created: flowchart-6-autentikasi.svg');

console.log('\n✅ All flowcharts created in docs/flowcharts/');

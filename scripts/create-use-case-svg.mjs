import { mkdirSync, writeFileSync } from 'fs';
import { join } from 'path';

const outputDir = join(import.meta.dirname, '..', 'docs', 'use-cases');
mkdirSync(outputDir, { recursive: true });

function escapeXml(str) {
    return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function createUseCaseSVG(title, actors, useCases) {
    const width = 1200;
    const padding = 40;
    const actorWidth = 120;
    const actorSpacing = 180;
    const startX = padding + 100;

    // Calculate heights
    const maxUseCases = Math.max(...actors.map(a => a.useCases.length));
    const height = Math.max(500, maxUseCases * 50 + 200);

    let svg = `<svg xmlns="http://www.w3.org/2000/svg" width="${width}" height="${height}" viewBox="0 0 ${width} ${height}">
  <style>
    text { font-family: Arial, sans-serif; }
    .title { font-size: 24px; font-weight: bold; fill: #1d71b8; }
    .actor { font-size: 16px; fill: #333; }
    .usecase { font-size: 14px; fill: #333; }
    .actor-label { font-size: 14px; fill: #1d71b8; }
  </style>
  
  <rect width="100%" height="100%" fill="white"/>
  
  <text x="${width/2}" y="35" text-anchor="middle" class="title">${escapeXml(title)}</text>
`;

    // Draw actors (stick figures)
    actors.forEach((actor, i) => {
        const x = startX + i * actorSpacing;
        const y = 80;

        // Head
        svg += `  <circle cx="${x}" cy="${y}" r="15" fill="none" stroke="#1d71b8" stroke-width="2"/>
`;
        // Body
        svg += `  <line x1="${x}" y1="${y+15}" x2="${x}" y2="${y+55}" stroke="#1d71b8" stroke-width="2"/>
`;
        // Arms
        svg += `  <line x1="${x-25}" y1="${y+30}" x2="${x+25}" y2="${y+30}" stroke="#1d71b8" stroke-width="2"/>
`;
        // Legs
        svg += `  <line x1="${x}" y1="${y+55}" x2="${x-20}" y2="${y+85}" stroke="#1d71b8" stroke-width="2"/>
`;
        svg += `  <line x1="${x}" y1="${y+55}" x2="${x+20}" y2="${y+85}" stroke="#1d71b8" stroke-width="2"/>
`;
        // Label
        svg += `  <text x="${x}" y="${y+105}" text-anchor="middle" class="actor-label">${escapeXml(actor.name)}</text>
`;
    });

    // Draw use case ovals
    const useCaseX = width - 200;
    actors.forEach((actor, actorIndex) => {
        const actorX = startX + actorIndex * actorSpacing;
        actor.useCases.forEach((uc, ucIndex) => {
            const y = 150 + ucIndex * 50;

            // Draw connection line
            svg += `  <line x1="${actorX}" y1="185" x2="${useCaseX}" y2="${y}" stroke="#ccc" stroke-width="1"/>
`;

            // Draw oval
            svg += `  <ellipse cx="${useCaseX}" cy="${y}" rx="120" ry="20" fill="#e7e2dd" stroke="#1d71b8" stroke-width="2"/>
`;
            // Use case text
            svg += `  <text x="${useCaseX}" y="${y+5}" text-anchor="middle" class="usecase">${escapeXml(uc)}</text>
`;
        });
    });

    svg += `</svg>`;
    return svg;
}

// Diagram 1: Autentikasi & Profil
const diagram1 = createUseCaseSVG('1. Autentikasi & Profil', [
    { name: 'User', useCases: ['Login', 'Register', 'Lupa Password', 'Lengkapi Profil', 'Edit Profil'] },
    { name: 'Admin', useCases: ['Login', 'Logout', 'Edit Profil'] },
    { name: 'Laboran', useCases: ['Login', 'Logout', 'Edit Profil'] },
    { name: 'Superadmin', useCases: ['Login', 'Logout', 'Edit Profil'] }
]);
writeFileSync(join(outputDir, 'use-case-1.svg'), diagram1);
console.log('Created: use-case-1.svg');

// Diagram 2: Manajemen Alat & Peminjaman
const diagram2 = createUseCaseSVG('2. Manajemen Alat & Peminjaman', [
    { name: 'User', useCases: ['Lihat Katalog', 'Detail Alat', 'Tambah Keranjang', 'Buat Pengajuan', 'Lihat Status'] },
    { name: 'Admin', useCases: ['Tambah/Edit/Hapus Alat', 'Import/Export', 'Kelola Kategori', 'Setujui/Tolak', 'Proses Pengembalian'] }
]);
writeFileSync(join(outputDir, 'use-case-2.svg'), diagram2);
console.log('Created: use-case-2.svg');

// Diagram 3: Pengujian Sampel
const diagram3 = createUseCaseSVG('3. Pengujian Sampel', [
    { name: 'User', useCases: ['Katalog Parameter', 'Tambah Keranjang', 'Checkout', 'Submit Sampel', 'Unduh Hasil'] },
    { name: 'Admin', useCases: ['Kelola Parameter', 'Buat/Edit Pengujian', 'Assign Laboran', 'Upload Hasil'] },
    { name: 'Laboran', useCases: ['Update Status', 'Upload Hasil', 'Cetak Hasil'] }
]);
writeFileSync(join(outputDir, 'use-case-3.svg'), diagram3);
console.log('Created: use-case-3.svg');

// Diagram 4: Proposal Riset
const diagram4 = createUseCaseSVG('4. Proposal Riset', [
    { name: 'User', useCases: ['Buat Proposal', 'Tambah Anggota', 'Tambah Alat', 'Isi Logbook', 'Cetak Logbook'] },
    { name: 'Admin', useCases: ['Review Proposal', 'Setujui/Tolak', 'Assign Laboran', 'Update Pembayaran'] }
]);
writeFileSync(join(outputDir, 'use-case-4.svg'), diagram4);
console.log('Created: use-case-4.svg');

// Diagram 5: Pemeriksaan Kesehatan
const diagram5 = createUseCaseSVG('5. Pemeriksaan Kesehatan', [
    { name: 'User', useCases: ['Katalog Pemeriksaan', 'Booking', 'Lihat Antrian', 'Batalkan', 'Unduh Hasil'] },
    { name: 'Admin', useCases: ['Kelola Jenis', 'Kelola Jadwal', 'Proses Booking'] },
    { name: 'Laboran', useCases: ['Update Status', 'Upload Hasil'] }
]);
writeFileSync(join(outputDir, 'use-case-5.svg'), diagram5);
console.log('Created: use-case-5.svg');

// Diagram 6: Event & Workshop
const diagram6 = createUseCaseSVG('6. Event & Workshop', [
    { name: 'User', useCases: ['Lihat Event', 'Daftar Event', 'Cari Teman', 'Absensi', 'Unduh Sertifikat'] },
    { name: 'Admin', useCases: ['Buat/Edit/Hapus Event', 'Export Data', 'Kelola Sertifikat', 'Generate Massal'] }
]);
writeFileSync(join(outputDir, 'use-case-6.svg'), diagram6);
console.log('Created: use-case-6.svg');

// Diagram 7: CMS & Pengaturan
const diagram7 = createUseCaseSVG('7. CMS & Pengaturan (Superadmin)', [
    { name: 'Superadmin', useCases: ['CRUD User', 'Import/Export', 'Kelola Menu', 'Kelola CMS', 'Lihat Log', 'Kelola Laboratorium'] }
]);
writeFileSync(join(outputDir, 'use-case-7.svg'), diagram7);
console.log('Created: use-case-7.svg');

// Diagram 8: Dashboard
const diagram8 = createUseCaseSVG('8. Dashboard & Laporan', [
    { name: 'Admin', useCases: ['Lihat Statistik', 'Lihat Ringkasan'] },
    { name: 'Laboran', useCases: ['Daftar Pengujian', 'Daftar Pemeriksaan', 'Update Status', 'Cetak Hasil'] }
]);
writeFileSync(join(outputDir, 'use-case-8.svg'), diagram8);
console.log('Created: use-case-8.svg');

console.log('\n✅ All use case diagrams created as SVG files in docs/use-cases/');

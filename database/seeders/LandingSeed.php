<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Setting, Menu, MenuItem, Service, PortfolioItem, TechStack, Page};
use Illuminate\Support\Str;


class LandingSeed extends Seeder
{

    public function run(): void
    {
        // Site settings (hero, contact)
        Setting::updateOrCreate(['key' => 'site'], ['value' => [
            'name'     => 'OZNET Systems',
            'brand'    => 'OZNET Systems',
            'whatsapp' => '+6289637498586',
            'email'    => 'stylus.smg@gmail.com',
            'tagline'  => 'Solusi End-to-End: Aplikasi • Infrastruktur • Keamanan',
            'contact'  => [
                'title'    => 'Kontak',
                'subtitle' => 'Kami responsif via WhatsApp & email. Sertakan gambaran singkat kebutuhan Anda.',
            ],
            'process' => [
                ['no' => '01', 'label' => 'Konsultasi',       'desc' => 'Gali kebutuhan & scope. Estimasi biaya & timeline.'],
                ['no' => '02', 'label' => 'Desain',           'desc' => 'Wireframe, arsitektur, desain UI, dan rencana infra.'],
                ['no' => '03', 'label' => 'Implementasi',     'desc' => 'Pengembangan iteratif, CI/CD, review berkala.'],
                ['no' => '04', 'label' => 'Go-Live & Support', 'desc' => 'Deployment, training, dokumentasi, perawatan.'],
            ],

            'company' => 'OZNET Systems',
            'address' => 'Doplang Krajan 01/03, Kec. Bawen, Kab. Semarang',
            'social'  => [
                'facebook' => 'https://facebook.com/oznetsystems',
                'instagram' => 'https://instagram.com/oznetsystems',
                'linkedin' => 'https://www.linkedin.com/company/oznetsystems',
                'youtube'  => 'https://youtube.com/@oznetsystems',
            ],

        ]]);


        // Main menu
        $menu = Menu::firstOrCreate(['name' => 'main']);
        $items = [
            ['label' => 'Layanan', 'url' => '#services'],
            ['label' => 'Teknologi', 'url' => '#tech'],
            ['label' => 'Portfolio', 'url' => '#portfolio'],
            ['label' => 'Proses', 'url' => '#process'],
            ['label' => 'About', 'url' => '#about'],
            ['label' => 'Kontak', 'url' => '#contact'],
        ];
        foreach ($items as $i => $it) {
            MenuItem::updateOrCreate(
                ['menu_id' => $menu->id, 'label' => $it['label']],
                ['url' => $it['url'], 'order' => $i]
            );
        }


        // Services (6 kartu)
        $services = [
            ['Aplikasi Web Kustom', 'monitor', 'CMS sekolah, hotel, perusahaan, inventory, POS, dashboard, dan integrasi API — berbasis Laravel & JS.'],
            ['Aplikasi Mobile', 'smartphone', 'Hybrid/JS stack dengan API aman — pantas untuk operasional dan monitoring lapangan.'],
            ['Networking, Mikrotik & RADIUS', 'arrow-big-up', 'Design VLAN, hotspot, captive portal, RADIUS server, QoS, monitoring & troubleshooting.'],
            ['CCTV & NVR', 'monitor-play', 'Perencanaan titik kamera, NVR, storage, akses jarak jauh, dan notifikasi.'],
            ['Server, NAS & Backup', 'server', 'Deploy server Linux/Proxmox, NAS, backup terjadwal, dan CI/CD GitHub Actions.'],
            ['Support & Maintenance', 'plus', 'SLA fleksibel, monitoring, hardening, audit keamanan, dan dokumentasi lengkap.'],
        ];
        foreach ($services as $i => [$name, $icon, $excerpt]) {
            Service::updateOrCreate(['name' => $name], compact('icon', 'excerpt') + ['order' => $i]);
        }


        // Tech chips
        foreach (
            [
                'Laravel',
                'JavaScript',
                'jQuery',
                'Bootstrap',
                'Tailwind CSS',
                'GitHub Actions',
                'MySQL',
                'Redis',
                'Mikrotik',
                'RADIUS',
                'CCTV/NVR',
                'Proxmox & NAS'
            ] as $i => $t
        ) {
            TechStack::updateOrCreate(['name' => $t], ['order' => $i]);
        }


        // Portfolio dummy
        foreach (
            [
                ['CMS Sekolah', 'Akademik, PPDB, nilai, integrasi WhatsApp.'],
                ['Hotel & Reservasi', 'Booking engine, payment, channel manager (API).'],
                ['Inventory & POS', 'Multi‑gudang, barcode, laporan akuntansi dasar.'],
                ['Infrastruktur & Mikrotik', 'Hotspot, RADIUS, captive portal, VLAN & QoS.'],
            ] as $i => [$title, $summary]
        ) {
            PortfolioItem::updateOrCreate(
                ['slug' => Str::slug($title)],
                ['title' => $title, 'summary' => $summary, 'order' => $i]
            );
        }


        // about page (json)
        Page::updateOrCreate(
            ['slug' => 'about'],
            ['title' => 'Tentang Kami', 'published' => true, 'content' => [
                'visi'  => 'Menjadi partner IT terpercaya yang memberikan inovasi, efisiensi, dan keamanan bagi setiap klien.',
                'misi'  => [
                    'Membangun aplikasi web & mobile yang stabil dan scalable.',
                    'Menyediakan infrastruktur jaringan yang aman & handal.',
                    'Memberikan dukungan purna jual & dokumentasi lengkap.',
                ],
                'nilai' => 'Integritas, profesionalisme, kolaborasi, dan komitmen penuh pada kepuasan klien adalah budaya kerja kami.',
            ]]
        );
    }
}

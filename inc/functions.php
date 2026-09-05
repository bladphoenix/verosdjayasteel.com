<?php
// inc/functions.php — helper bersama untuk frontend & admin.

/**
 * Escape output untuk HTML (cegah XSS).
 */
function e($str): string {
    return htmlspecialchars((string)($str ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Token CSRF per-sesi (butuh sesi sudah aktif).
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/**
 * Hidden input berisi token CSRF untuk disisipkan ke dalam form.
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

/**
 * Validasi token CSRF dari POST. Kembalikan true bila cocok.
 */
function csrf_check(): bool {
    return !empty($_SESSION['csrf'])
        && !empty($_POST['csrf'])
        && hash_equals($_SESSION['csrf'], (string)$_POST['csrf']);
}

/**
 * Format angka ke Rupiah. 0 / null dianggap "Hubungi Kami".
 */
function rupiah($n): string {
    $n = (float)$n;
    if ($n <= 0) {
        return 'Hubungi Kami';
    }
    return 'Rp ' . number_format($n, 0, ',', '.');
}

/**
 * Buat slug URL dari teks bebas.
 */
function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

/**
 * Pengaturan situs default (fallback jika tabel settings kosong / tidak ada).
 */
function default_settings(): array {
    return [
        'company_name'  => 'Veros Djaya Steel',
        'tagline'       => 'Spesialis Kandang Ayam Baja Galvanis Berkualitas',
        'whatsapp'      => '62859191749378',
        'telegram'      => '+6287821381136',
        'email'         => 'info@verosdjayasteel.com',
        'phone'         => '0859-1917-49378',
        'address'       => 'Indonesia',
        'hero_title'    => 'Kandang Ayam Baja Galvanis Kuat, Awet & Anti Karat',
        'hero_subtitle' => 'Produsen kandang ayam petelur, broiler, kampung, dan box DOC. Bahan galvanis berkualitas, harga pabrik, siap kirim seluruh Indonesia.',
        'about_text'    => 'Veros Djaya Steel adalah produsen kandang ayam berbahan baja galvanis anti karat.',
        'working_hours' => 'Senin - Sabtu, 08.00 - 17.00 WIB',
    ];
}

/**
 * Ambil semua pengaturan situs (DB di-merge di atas default).
 * Aman meski tabel settings belum ada.
 */
function get_settings(PDO $pdo): array {
    $settings = default_settings();
    try {
        $rows = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll();
        foreach ($rows as $row) {
            if ($row['setting_value'] !== null && $row['setting_value'] !== '') {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        }
    } catch (PDOException $e) {
        // biarkan pakai default
    }
    return $settings;
}

/**
 * Bangun link WhatsApp (wa.me) dengan pesan opsional.
 */
function wa_link(string $whatsapp, string $text = ''): string {
    $num = preg_replace('/[^0-9]/', '', $whatsapp);
    $url = 'https://wa.me/' . $num;
    if ($text !== '') {
        $url .= '?text=' . rawurlencode($text);
    }
    return $url;
}

/**
 * Bangun link Telegram. Nilai bisa "@user", "+62..." atau "username".
 */
function tg_link(string $telegram): string {
    $t = trim($telegram);
    if (stripos($t, 'http') === 0) {
        return $t;
    }
    $t = ltrim($t, '@');
    return 'https://t.me/' . $t;
}

/**
 * Placeholder SVG (data URI) untuk produk tanpa gambar.
 */
function product_placeholder(): string {
    $svg = "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 300'>"
         . "<rect width='400' height='300' fill='%23e8edf2'/>"
         . "<g fill='none' stroke='%2394a3b8' stroke-width='6'>"
         . "<rect x='120' y='90' width='160' height='130' rx='6'/>"
         . "<path d='M120 130h160M120 170h160M160 90v130M200 90v130M240 90v130'/>"
         . "</g>"
         . "<text x='200' y='260' font-family='Arial' font-size='18' fill='%2394a3b8' text-anchor='middle'>Veros Djaya Steel</text>"
         . "</svg>";
    return 'data:image/svg+xml,' . $svg;
}

/**
 * URL gambar produk yang aman (pakai placeholder bila kosong).
 * $prefix = '' untuk root, '../' untuk folder admin.
 */
function product_image(?string $path, string $prefix = ''): string {
    if ($path) {
        return $prefix . e($path);
    }
    return product_placeholder();
}

/**
 * Inline SVG icon (feather-style). Kembalikan string SVG siap cetak.
 */
function icon(string $name, string $extra = ''): string {
    $paths = [
        'whatsapp' => '<path d="M12 2a10 10 0 0 0-8.6 15l-1.3 4.7 4.8-1.3A10 10 0 1 0 12 2Z" fill="none"/><path d="M8.5 7.5c-.3 0-.7.1-1 .5-.4.4-1 1-1 2.4s1 2.8 1.2 3c.1.2 2 3.2 5 4.4 2.4 1 2.9.8 3.4.8.5-.1 1.6-.7 1.9-1.4.2-.6.2-1.2.1-1.3-.1-.1-.3-.2-.6-.4-.3-.2-1.6-.8-1.9-.9-.2-.1-.4-.1-.6.1-.2.3-.7.9-.8 1-.2.2-.3.2-.6.1-.3-.2-1.2-.5-2.3-1.4-.8-.8-1.4-1.7-1.6-2-.1-.3 0-.4.1-.6l.5-.5c.1-.2.2-.3.3-.5 0-.2 0-.4-.1-.5 0-.2-.6-1.6-.9-2.1-.2-.5-.4-.4-.6-.4Z"/>',
        'telegram' => '<path d="M21.9 4.3 2.9 11.6c-1 .4-1 1.8.1 2.1l4.6 1.4 1.8 5.5c.3.7 1.1.9 1.6.4l2.6-2.5 4.7 3.4c.6.4 1.5.1 1.7-.6l3.4-15.9c.2-1-.7-1.8-1.6-1.5Z" fill="none"/><path d="m8 14 9-6-7 7v3l2-2"/>',
        'phone'    => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3-8.6A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2Z"/>',
        'mail'     => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/>',
        'map'      => '<path d="M21 10c0 6-9 12-9 12s-9-6-9-12a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/>',
        'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'check'    => '<path d="M20 6 9 17l-5-5"/>',
        'shield'   => '<path d="M12 2 4 5v6c0 5 3.4 8.6 8 10 4.6-1.4 8-5 8-10V5l-8-3Z"/><path d="m9 12 2 2 4-4"/>',
        'truck'    => '<path d="M1 3h15v13H1z"/><path d="M16 8h4l3 3v5h-7"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>',
        'star'     => '<path d="M12 2l3 6.3 6.9 1-5 4.9 1.2 6.8L12 17.8 5.9 21l1.2-6.8-5-4.9 6.9-1z"/>',
        'box'      => '<path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="M3 8l9 5 9-5M12 13v8"/>',
        'grid'     => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'award'    => '<circle cx="12" cy="8" r="6"/><path d="M8.2 13.9 7 22l5-3 5 3-1.2-8.1"/>',
        'tools'    => '<path d="M14.7 6.3a4 4 0 0 0 5 5l-8.4 8.4a2 2 0 0 1-3-3l8.4-8.4Z"/><path d="m14.7 6.3 3-3"/>',
        'arrow-right' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'chevron'  => '<path d="m9 18 6-6-6-6"/>',
        'menu'     => '<path d="M3 12h18M3 6h18M3 18h18"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.6 1.6 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.6 1.6 0 0 0-2.7 1.1V21a2 2 0 0 1-4 0v-.1A1.6 1.6 0 0 0 7 19.4l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1A1.6 1.6 0 0 0 3 15H3a2 2 0 0 1 0-4h.1A1.6 1.6 0 0 0 4.6 8.4l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1A1.6 1.6 0 0 0 9 4.6V4a2 2 0 0 1 4 0v.1a1.6 1.6 0 0 0 2.7 1.1l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.6 1.6 0 0 0 1.1 2.7H21a2 2 0 0 1 0 4h-.1a1.6 1.6 0 0 0-1.5 1Z"/>',
    ];
    $p = $paths[$name] ?? '';
    return '<svg class="i" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" ' . $extra . '>' . $p . '</svg>';
}

/**
 * Label & warna status stok.
 */
function stock_badge(string $status): array {
    return [
        'ready'    => ['label' => 'Ready Stock', 'class' => 'badge-ready'],
        'preorder' => ['label' => 'Pre-Order',   'class' => 'badge-preorder'],
        'habis'    => ['label' => 'Stok Habis',  'class' => 'badge-habis'],
    ][$status] ?? ['label' => ucfirst($status), 'class' => 'badge-ready'];
}

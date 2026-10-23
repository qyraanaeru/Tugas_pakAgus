<?php
// Menghasilkan gambar captcha dan menyimpan kodenya di session.
require_once __DIR__ . '/auth_lib.php';

$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';   // tanpa I, O, 0, 1 agar tidak membingungkan
$code  = '';
for ($i = 0; $i < 5; $i++) {
    $code .= $chars[random_int(0, strlen($chars) - 1)];
}
$_SESSION['captcha_code'] = $code;
$_SESSION['captcha_time'] = time();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if (function_exists('imagecreatetruecolor') && function_exists('imagepng')) {
    // Gambar kecil digambar dengan font bawaan GD, lalu diperbesar 3x agar bentuknya tidak mudah dibaca mesin.
    $sw = 74;
    $sh = 24;
    $small = imagecreatetruecolor($sw, $sh);
    imagefilledrectangle($small, 0, 0, $sw - 1, $sh - 1, imagecolorallocate($small, 255, 255, 255));
    for ($i = 0; $i < strlen($code); $i++) {
        $col = imagecolorallocate($small, random_int(0, 70), random_int(20, 90), random_int(90, 170));
        $x = 8 + $i * 12;
        $y = random_int(2, 7);
        imagechar($small, 5, $x, $y, $code[$i], $col);
        imagechar($small, 5, $x + 1, $y, $code[$i], $col);   // tebalkan huruf
    }

    $scale = 3;
    $w = $sw * $scale;
    $h = $sh * $scale;
    $img = imagecreatetruecolor($w, $h);
    imagecopyresampled($img, $small, 0, 0, 0, 0, $w, $h, $sw, $sh);

    for ($i = 0; $i < 6; $i++) {
        $c = imagecolorallocate($img, random_int(80, 190), random_int(80, 190), random_int(120, 220));
        imageline($img, random_int(0, $w), random_int(0, $h), random_int(0, $w), random_int(0, $h), $c);
    }
    for ($i = 0; $i < 150; $i++) {
        $c = imagecolorallocate($img, random_int(100, 200), random_int(100, 200), random_int(100, 200));
        imagesetpixel($img, random_int(0, $w - 1), random_int(0, $h - 1), $c);
    }

    header('Content-Type: image/png');
    imagepng($img);
    exit;
}

// Cadangan jika ekstensi GD tidak aktif: gambar SVG
header('Content-Type: image/svg+xml');
$svg = '<svg xmlns="http://www.w3.org/2000/svg" width="222" height="72" viewBox="0 0 222 72">'
     . '<rect width="222" height="72" fill="#fff"/>';
for ($i = 0; $i < 6; $i++) {
    $svg .= '<line x1="' . random_int(0, 222) . '" y1="' . random_int(0, 72) . '" x2="' . random_int(0, 222)
          . '" y2="' . random_int(0, 72) . '" stroke="rgb(' . random_int(80, 190) . ',' . random_int(80, 190)
          . ',' . random_int(120, 220) . ')" stroke-width="1.5"/>';
}
for ($i = 0; $i < strlen($code); $i++) {
    $svg .= '<text x="' . (20 + $i * 36) . '" y="' . random_int(44, 56) . '" font-size="38" font-weight="bold" '
          . 'font-family="monospace" fill="rgb(' . random_int(0, 70) . ',' . random_int(20, 90) . ',' . random_int(90, 170)
          . ')" transform="rotate(' . random_int(-14, 14) . ' ' . (30 + $i * 36) . ' 40)">' . $code[$i] . '</text>';
}
echo $svg . '</svg>';

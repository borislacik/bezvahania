<?php

/**
 * Builds the logo and favicons from a source image with a black background.
 *
 * Usage (in Docker): php scripts/make-logo.php design/logo/<source>.jpg
 *
 * - black → transparent, anti-aliased edges derived from the red channel (removes the dark JPEG fringe),
 * - cropped to the emblem on a square canvas,
 * - outputs: design/logo/logo.png (master), resources/images/logo.webp (website),
 *   public/favicon.png, public/favicon.ico (16 + 32 px), public/apple-touch-icon.png (white background).
 */
$source = $argv[1] ?? 'design/logo/docasne-logo-zdroj.jpg';
$src = imagecreatefromstring((string) file_get_contents($source));
if ($src === false) {
    fwrite(STDERR, "Cannot read {$source}\n");
    exit(1);
}

$w = imagesx($src);
$h = imagesy($src);
[$red, $green, $blue] = [246, 2, 3];

$out = imagecreatetruecolor($w, $h);
imagealphablending($out, false);
imagesavealpha($out, true);
imagefill($out, 0, 0, imagecolorallocatealpha($out, 0, 0, 0, 127));

$minX = $w;
$minY = $h;
$maxX = 0;
$maxY = 0;

for ($y = 0; $y < $h; $y++) {
    for ($x = 0; $x < $w; $x++) {
        $r = (imagecolorat($src, $x, $y) >> 16) & 255;
        $coverage = max(0.0, min(1.0, ($r - 30) / 200));

        if ($coverage <= 0) {
            continue;
        }

        imagesetpixel($out, $x, $y, ($red << 16) | ($green << 8) | $blue | ((int) round(127 * (1 - $coverage)) << 24));

        if ($coverage > 0.3) {
            $minX = min($minX, $x);
            $minY = min($minY, $y);
            $maxX = max($maxX, $x);
            $maxY = max($maxY, $y);
        }
    }
}

$width = $maxX - $minX + 1;
$height = $maxY - $minY + 1;
$size = max($width, $height);
$pad = (int) round($size * 0.04);
$side = $size + 2 * $pad;

$square = transparentCanvas($side);
imagecopy($square, $out, $pad + intdiv($size - $width, 2), $pad + intdiv($size - $height, 2), $minX, $minY, $width, $height);
imagepng($square, 'design/logo/logo.png', 9);

imagewebp(scaled($square, 512), 'resources/images/logo.webp', 90);
imagepng(scaled($square, 64), 'public/favicon.png', 9);

$apple = imagecreatetruecolor(180, 180);
imagefill($apple, 0, 0, imagecolorallocate($apple, 255, 255, 255));
imagealphablending($apple, true);
imagecopyresampled($apple, $square, 14, 14, 0, 0, 152, 152, $side, $side);
imagepng($apple, 'public/apple-touch-icon.png', 9);

$pngs = [];
foreach ([16, 32] as $px) {
    ob_start();
    imagepng(scaled($square, $px), null, 9);
    $pngs[$px] = (string) ob_get_clean();
}
$ico = pack('vvv', 0, 1, count($pngs));
$offset = 6 + 16 * count($pngs);
$data = '';
foreach ($pngs as $px => $png) {
    $ico .= pack('CCCCvvVV', $px, $px, 0, 0, 1, 32, strlen($png), $offset);
    $offset += strlen($png);
    $data .= $png;
}
file_put_contents('public/favicon.ico', $ico.$data);

printf("Emblem %dx%d px, canvas %d px. Done.\n", $width, $height, $side);

function transparentCanvas(int $px): GdImage
{
    $canvas = imagecreatetruecolor($px, $px);
    imagealphablending($canvas, false);
    imagesavealpha($canvas, true);
    imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));

    return $canvas;
}

function scaled(GdImage $image, int $px): GdImage
{
    $target = transparentCanvas($px);
    imagecopyresampled($target, $image, 0, 0, 0, 0, $px, $px, imagesx($image), imagesy($image));

    return $target;
}

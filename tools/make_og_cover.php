<?php
// One-off: generate a raster 1200x630 branded Open Graph cover with GD (no TTF
// needed) so social share previews always have a real image to fall back to.
// Run: docker exec volamani_app php /var/www/tools/make_og_cover.php

$W = 1200; $H = 630;
$img = imagecreatetruecolor($W, $H);
imageantialias($img, true);

// Diagonal gradient 0b1220 -> 15275f -> 1a56db
function lerp($a, $b, $t) { return (int) round($a + ($b - $a) * $t); }
$stops = [[0.0, 0x0b,0x12,0x20], [0.55, 0x15,0x27,0x5f], [1.0, 0x1a,0x56,0xdb]];
for ($y = 0; $y < $H; $y++) {
    for ($x = 0; $x < $W; $x += 2) {
        $t = (($x / $W) * 0.6 + ($y / $H) * 0.4); // diagonal
        // find segment
        $r=$g=$b=0;
        for ($s = 0; $s < count($stops) - 1; $s++) {
            if ($t >= $stops[$s][0] && $t <= $stops[$s+1][0]) {
                $lt = ($t - $stops[$s][0]) / ($stops[$s+1][0] - $stops[$s][0]);
                $r = lerp($stops[$s][1], $stops[$s+1][1], $lt);
                $g = lerp($stops[$s][2], $stops[$s+1][2], $lt);
                $b = lerp($stops[$s][3], $stops[$s+1][3], $lt);
                break;
            }
        }
        $col = imagecolorallocate($img, $r, $g, $b);
        imagefilledrectangle($img, $x, $y, $x + 1, $y, $col);
    }
}

// Soft glow circles
function glow($img, $cx, $cy, $rad, $r, $g, $b, $alpha) {
    for ($i = $rad; $i > 0; $i -= 3) {
        $a = (int) (127 - (127 - $alpha) * ($i / $rad));
        $c = imagecolorallocatealpha($img, $r, $g, $b, min(127, $a));
        imagefilledellipse($img, $cx, $cy, $i, $i, $c);
    }
}
glow($img, 1030, 120, 460, 0x4f, 0x46, 0xe5, 118);
glow($img, 170, 560, 380, 0x3b, 0x82, 0xf6, 120);

// Rounded blue logo tile with white "V" monogram
$tileX = 110; $tileY = 250; $tileS = 120; $rad = 26;
$blue = imagecolorallocate($img, 0x1a, 0x56, 0xdb);
function roundedRect($img, $x, $y, $s, $r, $col) {
    imagefilledrectangle($img, $x + $r, $y, $x + $s - $r, $y + $s, $col);
    imagefilledrectangle($img, $x, $y + $r, $x + $s, $y + $s - $r, $col);
    imagefilledellipse($img, $x + $r, $y + $r, $r*2, $r*2, $col);
    imagefilledellipse($img, $x + $s - $r, $y + $r, $r*2, $r*2, $col);
    imagefilledellipse($img, $x + $r, $y + $s - $r, $r*2, $r*2, $col);
    imagefilledellipse($img, $x + $s - $r, $y + $s - $r, $r*2, $r*2, $col);
}
roundedRect($img, $tileX, $tileY, $tileS, $rad, $blue);

// White "V" drawn as a clean filled polygon inside the tile
$white = imagecolorallocate($img, 255, 255, 255);
imagesetthickness($img, 1);
$pad = 32; $lx = $tileX + $pad; $rx = $tileX + $tileS - $pad; $mx = $tileX + $tileS/2;
$ty = $tileY + $pad; $by = $tileY + $tileS - $pad; $tw = 22; // arm width
$V = [
    $lx, $ty,            // outer top-left
    $mx, $by,            // outer bottom point
    $rx, $ty,            // outer top-right
    $rx - $tw, $ty,      // inner top-right
    $mx, $by - $tw * 1.1,// inner bottom (slightly above → crisp point)
    $lx + $tw, $ty,      // inner top-left
];
imagefilledpolygon($img, $V, 6, $white);

imagepng($img, '/var/www/public/images/og-cover.png', 6);
imagedestroy($img);
echo "wrote public/images/og-cover.png\n";

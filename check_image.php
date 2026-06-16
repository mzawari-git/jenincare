<?php
$f = 'C:\xampp\htdocs\jenincare\public\images\panoramas\001_02.jpg';
$img = imagecreatefromjpeg($f);
if ($img) {
    echo 'Width: ' . imagesx($img) . ' Height: ' . imagesy($img) . ' Ratio: ' . (imagesx($img)/imagesy($img)) . PHP_EOL;
    imagedestroy($img);
} else {
    echo 'Failed' . PHP_EOL;
}

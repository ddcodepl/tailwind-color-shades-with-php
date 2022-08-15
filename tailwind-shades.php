<?php
require_once "tailwind-config.php";

function hexToRgb($hex): array
{
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
        $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
        $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
    } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    }

    return array(
        'r' => $r,
        'g' => $g,
        'b' => $b
    );
}

function rgbToHex($r, $g, $b): string
{
    $hex = "#";
    $hex .= str_pad(dechex($r), 2, "0", STR_PAD_LEFT);
    $hex .= str_pad(dechex($g), 2, "0", STR_PAD_LEFT);
    $hex .= str_pad(dechex($b), 2, "0", STR_PAD_LEFT);
    return $hex;
}

function getTextColor($color): string
{
    $rgb = hexToRgb($color);
    $r = $rgb['r'];
    $g = $rgb['g'];
    $b = $rgb['b'];
    $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

    return ($yiq >= 128) ? '#333333' : '#ffffff';
}

function colorChange($hex, $intensity, $type)
{
    $color = hexToRgb('#' . $hex);

    if (!$color) {
        return "";
    }

    $r = round($color['r']);
    $g = round($color['g']);
    $b = round($color['b']);

    if ($type === 'lighten') {
        $r = round($color['r'] + (255 - $color['r']) * $intensity);
        $g = round($color['g'] + (255 - $color['g']) * $intensity);
        $b = round($color['b'] + (255 - $color['b']) * $intensity);
    } else if ($type === 'darken') {
        $r = round($color['r'] * $intensity);
        $g = round($color['g'] * $intensity);
        $b = round($color['b'] * $intensity);
    }

    return rgbToHex($r, $g, $b);
}

function getColorName($color)
{
    $sanitizedName = str_replace('/[/]/gi', '', $color);
    $sanitizedName = str_replace('/[/]/gi', '', $sanitizedName);

    return strtolower($sanitizedName);
}

function generateColors($baseColor)
{
    $name = getColorName($baseColor);

    $intensityMap = [
        50 => 0.95,
        100 => 0.9,
        200 => 0.75,
        300 => 0.6,
        400 => 0.3,
        600 => 0.9,
        700 => 0.75,
        800 => 0.6,
        900 => 0.49
    ];

    $palette = [
        500 => $baseColor,
    ];

    foreach ($intensityMap as $intensity => $value) {
        $direction = $value > 500 ? 'lighten' : 'darken';
        $palette[$intensity] = colorChange($baseColor, $value, $direction);
    }

    return $palette;
}

function generatePalette()
{
    $palette = [];

    $palette['primary'] = generateColors($primary);
    $palette['secondary'] = generateColors($secondary);

    // loop over palette to create --$name-$intensity: $color;
    foreach ($palette as $name => $colors) {
        foreach ($colors as $intensity => $color) {
            $palette[$name][$intensity] = '--' . $name . '-' . $intensity . ': ' . $color . ';';
        }
    }

    foreach ($defaultColors as $name => $color) {
        $defaultColors[$name] = '--' . $name . ': ' . $color . ';';
    }

    return "
        <style lang='text/css'>
            :root {
                " . implode('', $palette['primary']) . "
                " . implode('', $palette['secondary']) . "
                " . implode('', $defaultColors) . "
            }
		</style>
    ";
}

echo generatePalette();

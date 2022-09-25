<?php

/*
PHP Render Image
* Simple way to render an image the way you want without needing to touch original file
*/

if (!isset($_GET['image']) || !isset($_GET['profile'])) {
    exit();
}

$allowed = false;
if (!preg_match("/" . addslashes($_SERVER['HTTP_HOST']) . "/", $_GET['image']) && file_exists($_GET['image'])) {
    $allowed = true;
}
if (preg_match("/\/" . addSlashes($_SERVER['HTTP_HOST']) . "\//", $_GET['image'])) {
    $allowed = true;
}
if (!$allowed) {
    exit;
}

$setup = array(
    /*"profile_name" => array( // no repeat
        "with" => 300, // width in pixels
        "height" => 300, // height in pixels
        "mode" => 'cut', // render mode (cut, fit, scale, original)
        "background_color" => "#000000" // transparent color only for PNG images
    ),*/
    "bandeira" => array(
        "width" => 18,
        "height" => 12,
        "mode" => 'fit',
    ),
    "icone" => array(
        "width" => 14,
        "height" => 12,
        "mode" => 'fit',
    ),
    "seta" => array(
        "width" => 11,
        "height" => 12,
        "mode" => 'fit',
    ),
    "partner" => array(
        "width" => 400,
        "height" => 47,
        "mode" => 'fit',
    ),
    "logo" => array(
        "width" => 91,
        "height" => 39,
        "mode" => 'fit',
    ),
);

$profile = array();

if (isset($setup[$_GET['profile']])) {
    $profile = $setup[$_GET['profile']];
} else {
    exit();
}

$image = $_GET['image'];
$mime_type = @mime_content_type($image);
if ($mime_type) {
    $array_mime_type = explode('/', $mime_type);
    if ($array_mime_type[0] == "image") {
        $extension = strtolower($array_mime_type[1]);
    } else {
        exit();
    }
} else {
    $mime_type = @file_get_contents($image);
    if ($mime_type) {
        $pattern = "/^content-type\s*:\s*(.*)$/i";
        if (($header = array_values(preg_grep($pattern, $http_response_header))) && (preg_match($pattern, $header[0], $match) !== false)) {
            $content_type = $match[1];
            $test = explode("/", $content_type);
            if ($test[0] == "image") {
                $extension = strtolower($test[1]);
            } else {
                exit();
            }
        } else {
            exit();
        }
    } else {
        exit();
    }
}

function getCanvas($width, $height, $color = false)
{
    if (!$color) {
        $color = '#FFFFFF';
    }
    if ($color !== "transparent") {
        $color = str_replace('#', '', $color);
        $rgb = array();
        for ($x = 0; $x < 3; $x++) {
            $rgb[$x] = hexdec(substr($color, (2 * $x), 2));
        }
    }
    $canvas = imagecreatetruecolor($width, $height);
    if ($color == "transparent") {
        $background = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
    } else {
        $background = imagecolorallocate($canvas, $rgb[0], $rgb[1], $rgb[2]);
    }
    imagefill($canvas, 0, 0, $background);
    return $canvas;
}

function getImage($image, $extension)
{
    $resource = "";

    //New way
    $resource = imagecreatefromstring(file_get_contents($image));

    /*
    //Old fashioned way
    switch ($extension) {
        case "jpeg":
        case "jpg":
            $resource = imagecreatefromjpeg($image);
            break;
        case "png":
            $resource = imagecreatefrompng($image);
            break;
        case "gif":
            $resource = imagecreatefromgif($image);
            break;
        case "bmp":
            $resource = imagecreatefrombmp($image);
            break;
    }*/
    return $resource;
}

function setImage($image, $extension)
{
    $quality = 100;

    //New way
    //imagewebp($image, null, $quality);
    /* Old fashioned way */

    switch ($extension) {
        case ("jpeg" || "jpg"):
            imagejpeg($image, NULL, $quality);
            break;
        case "png":
            imagepng($image, NULL, $quality);
            break;
        case "gif":
            imagegif($image, NULL, $quality);
            break;
        case "bmp":
            imagebmp($image, NULL, $quality);
            break;
    }
}

function setGlobals()
{
    global $profile, $image, $extension;
    $profile['image'] = $image;
    $profile['extension'] = $extension;
    list($profile['original_width'], $profile['original_height']) = getimagesize($image);
    if (!isset($profile['background_color'])) {
        $profile['background_color'] = false;
    }
    $profile['canvas_width'] = $profile['width'];
    if (isset($profile['height'])) {
        $profile['canvas_height'] = $profile['height'];
        $profile['canvas'] = getCanvas($profile['canvas_width'], $profile['canvas_height'], $profile['background_color']);
    }
    $profile['resource'] = getImage($image, $extension);
    return $profile;
}

function _render_scale($profile)
{
    imagecopyresampled($profile['canvas'], $profile['resource'], 0, 0, 0, 0, $profile['width'], $profile['height'], $profile['original_width'], $profile['original_height']);
}

function _render_original($profile)
{
    $profile['width'] = $profile['canvas_width'] = $profile['original_width'];
    $profile['height'] = $profile['canvas_height'] = $profile['original_height'];
    $profile['canvas'] = getCanvas($profile['canvas_width'], $profile['canvas_height'], $profile['background_color']);
    imagecopyresampled($profile['canvas'], $profile['resource'], 0, 0, 0, 0, $profile['width'], $profile['height'], $profile['original_width'], $profile['original_height']);
}

function _render_cut($profile)
{
    if ($profile['original_width'] > $profile['canvas_width']) {
        $difference = 100 - (($profile['original_width'] - $profile['canvas_width']) * 100) / $profile['original_width'];
        $profile['height'] = ($profile['original_height'] / 100) * $difference;
        $profile['width'] = ($profile['original_width'] / 100) * $difference;
    } else {
        $difference = 100 - (($profile['canvas_width'] - $profile['original_width']) * 100) / $profile['canvas_width'];
        $profile['height'] = ($profile['original_height'] * 100) / $difference;
        $profile['width'] = ($profile['original_width'] * 100) / $difference;
    }
    if ($profile['height'] < $profile['canvas_height']) {
        $difference = (($profile['canvas_height'] - $profile['height']) * 100) / $profile['height'];
        $profile['height'] = $profile['height'] + (($profile['height'] / 100) * $difference);
        $profile['width'] = $profile['width'] + (($profile['width'] / 100) * $difference);
    }
    if ($profile['width'] < $profile['canvas_width']) {
        $difference = (($profile['canvas_width'] - $profile['width']) * 100) / $profile['width'];
        $profile['height'] = $profile['height'] + (($profile['height'] / 100) * $difference);
        $profile['width'] = $profile['width'] + (($profile['width'] / 100) * $difference);
    }
    $profile['padding_y'] = (($profile['height'] - $profile['canvas_height']) / 2) * -1;
    $profile['padding_x'] = (($profile['width'] - $profile['canvas_width']) / 2) * -1;
    imagecopyresampled($profile['canvas'], $profile['resource'], $profile['padding_x'], $profile['padding_y'], 0, 0, $profile['width'], $profile['height'], $profile['original_width'], $profile['original_height']);
}

function _render_fit($profile, $definir = false)
{
    if ($profile['original_width'] > $profile['canvas_width']) {
        $difference = 100 - (($profile['original_width'] - $profile['canvas_width']) * 100) / $profile['original_width'];
        $profile['height'] = ($profile['original_height'] / 100) * $difference;
        $profile['width'] = ($profile['original_width'] / 100) * $difference;
    } else {
        $difference = 100 - (($profile['canvas_width'] - $profile['original_width']) * 100) / $profile['canvas_width'];
        $profile['height'] = ($profile['original_height'] * 100) / $difference;
        $profile['width'] = ($profile['original_width'] * 100) / $difference;
    }
    if (isset($profile['height']) && isset($profile['canvas_height']) && $profile['height'] > $profile['canvas_height']) {
        $difference = (($profile['height'] - $profile['canvas_height']) * 100) / $profile['height'];
        $profile['height'] = $profile['height'] - (($profile['height'] / 100) * $difference);
        $profile['width'] = $profile['width'] - (($profile['width'] / 100) * $difference);
    }
    if ($profile['width'] > $profile['canvas_width']) {
        $difference = (($profile['width'] - $profile['canvas_width']) * 100) / $profile['width'];
        $profile['height'] = $profile['height'] + (($profile['height'] / 100) * $difference);
        $profile['width'] = $profile['width'] + (($profile['width'] / 100) * $difference);
    }
    if (!$definir) {
        $profile['padding_y'] = 0;
        if (isset($profile['canvas_height'])) {
            $profile['padding_y'] = (($profile['height'] - $profile['canvas_height']) / 2) * -1;
        }
        if (!isset($profile['canvas'])) {
            $profile['canvas'] = getCanvas($profile['width'], $profile['height'], $profile['background_color']);
        }
        $profile['padding_x'] = (($profile['width'] - $profile['canvas_width']) / 2) * -1;
        imagecopyresampled($profile['canvas'], $profile['resource'], $profile['padding_x'], $profile['padding_y'], 0, 0, $profile['width'], $profile['height'], $profile['original_width'], $profile['original_height']);
    }
    return $profile;
}

function render()
{
    $profile = setGlobals();
    //header("Content-type:image/" . $profile['extension']);
    header("Content-type:image/png");
    // new gen format image
    //header("Content-type:image/webp");
    if ($profile['mode'] == "fit") {
        if (!isset($profile['canvas'])) {
            $profile = _render_fit($profile, 'definir');
            $profile['canvas'] = getCanvas($profile['width'], $profile['height'], $profile['background_color']);
        }
        _render_fit($profile);
    }
    if ($profile['mode'] == "cut") {
        _render_cut($profile);
    }
    if ($profile['mode'] == "scale") {
        _render_scale($profile);
    }
    if ($profile['mode'] == "original") {
        _render_original($profile);
    }
    setImage($profile['canvas'], $profile['extension']);
    imagedestroy($profile['canvas']);
}

render();

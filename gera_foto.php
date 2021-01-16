<?php

if (!isset($_GET['imagem']) || !isset($_GET['perfil']) || !file_exists($_GET['imagem'])) {
    exit();
}

$configuracao = array(
    "nome_perfil" => array(
        "largura_gerar" => 600,
        //"altura_gerar" => 885,
        "modo" => 'enquadrar',
    ),
);

$perfil = array();

if (isset($configuracao[$_GET['perfil']])) {
    $perfil = $configuracao[$_GET['perfil']];
} else {
    exit();
}

$imagem = $_GET['imagem'];
$mime_type = @mime_content_type($imagem);
if ($mime_type) {
    $array_mime_type = explode('/', $mime_type);
    if ($array_mime_type[0] == "image") {
        $extensao = strtolower($array_mime_type[1]);
    } else {
        exit();
    }
} else {
    exit();
}

function getCanvas($largura, $altura, $cor = false) {
    if (!$cor) {
        $cor = '#FFFFFF';
    }
    $cor = str_replace('#', '', $cor);
    $rgb = array();
    for ($x = 0; $x < 3; $x++) {
        $rgb[$x] = hexdec(substr($cor, (2 * $x), 2));
    }
    $canvas = imagecreatetruecolor($largura, $altura);
    $background = imagecolorallocate($canvas, $rgb[0], $rgb[1], $rgb[2]);
    imagefill($canvas, 0, 0, $background);
    return $canvas;
}

function getImage($imagem, $extensao) {
    $resource = "";

    /*
    //New way
    // */ 
    $resource = imagecreatefromstring(file_get_contents($imagem));
    // */

    /*
    //Old fashioned way
    switch ($extensao) {
        case "jpeg":
        case "jpg":
            $resource = imagecreatefromjpeg($imagem);
            break;
        case "png":
            $resource = imagecreatefrompng($imagem);
            break;
        case "gif":
            $resource = imagecreatefromgif($imagem);
            break;
        case "bmp":
            $resource = imagecreatefrombmp($imagem);
            break;
    }*/
    return $resource;
}

function setImage($imagem, $extensao) {
    $qualidade = 100;
    switch ($extensao) {
        case ("jpeg" || "jpg"):
            imagejpeg($imagem, NULL, $qualidade);
            break;
        case "png":
            imagepng($imagem, NULL, $qualidade);
            break;
        case "gif":
            imagegif($imagem, NULL, $qualidade);
            break;
        case "bmp":
            imagebmp($imagem, NULL, $qualidade);
            break;
    }
}

function defineGlobais() {
    global $perfil, $imagem, $extensao;
    $perfil['imagem'] = $imagem;
    $perfil['extensao'] = $extensao;
    list($perfil['largura_original'], $perfil['altura_original']) = getimagesize($imagem);
    if (!isset($perfil['cor_fundo'])) {
        $perfil['cor_fundo'] = false;
    }
    $perfil['largura_canvas'] = $perfil['largura_gerar'];
    if (isset($perfil['altura_gerar'])) {
        $perfil['altura_canvas'] = $perfil['altura_gerar'];
        $perfil['canvas'] = getCanvas($perfil['largura_canvas'], $perfil['altura_canvas'], $perfil['cor_fundo']);
    }
    $perfil['resource'] = getImage($imagem, $extensao);
    return $perfil;
}

function _gerar_aumentado($perfil) {
    imagecopyresampled($perfil['canvas'], $perfil['resource'], 0, 0, 0, 0, $perfil['largura_gerar'], $perfil['altura_gerar'], $perfil['largura_original'], $perfil['altura_original']);
}

function _gerar_original($perfil) {
    $perfil['largura_gerar'] = $perfil['largura_canvas'] = $perfil['largura_original'];
    $perfil['altura_gerar'] = $perfil['altura_canvas'] = $perfil['altura_original'];
    $perfil['canvas'] = getCanvas($perfil['largura_canvas'], $perfil['altura_canvas'], $perfil['cor_fundo']);
    imagecopyresampled($perfil['canvas'], $perfil['resource'], 0, 0, 0, 0, $perfil['largura_gerar'], $perfil['altura_gerar'], $perfil['largura_original'], $perfil['altura_original']);
}

function _gerar_cortado($perfil) {
    if ($perfil['largura_original'] > $perfil['largura_canvas']) {
        $diferenca = 100 - (($perfil['largura_original'] - $perfil['largura_canvas']) * 100) / $perfil['largura_original'];
        $perfil['altura_gerar'] = ($perfil['altura_original'] / 100) * $diferenca;
        $perfil['largura_gerar'] = ($perfil['largura_original'] / 100) * $diferenca;
    } else {
        $diferenca = 100 - (($perfil['largura_canvas'] - $perfil['largura_original']) * 100) / $perfil['largura_canvas'];
        $perfil['altura_gerar'] = ($perfil['altura_original'] * 100) / $diferenca;
        $perfil['largura_gerar'] = ($perfil['largura_original'] * 100) / $diferenca;
    }
    if ($perfil['altura_gerar'] < $perfil['altura_canvas']) {
        $diferenca = (($perfil['altura_canvas'] - $perfil['altura_gerar']) * 100) / $perfil['altura_gerar'];
        $perfil['altura_gerar'] = $perfil['altura_gerar'] + (($perfil['altura_gerar'] / 100) * $diferenca);
        $perfil['largura_gerar'] = $perfil['largura_gerar'] + (($perfil['largura_gerar'] / 100) * $diferenca);
    }
    if ($perfil['largura_gerar'] < $perfil['largura_canvas']) {
        $diferenca = (($perfil['largura_canvas'] - $perfil['largura_gerar']) * 100) / $perfil['largura_gerar'];
        $perfil['altura_gerar'] = $perfil['altura_gerar'] + (($perfil['altura_gerar'] / 100) * $diferenca);
        $perfil['largura_gerar'] = $perfil['largura_gerar'] + (($perfil['largura_gerar'] / 100) * $diferenca);
    }
    $perfil['padding_altura'] = (($perfil['altura_gerar'] - $perfil['altura_canvas']) / 2) * -1;
    $perfil['padding_largura'] = (($perfil['largura_gerar'] - $perfil['largura_canvas']) / 2) * -1;
    imagecopyresampled($perfil['canvas'], $perfil['resource'], $perfil['padding_largura'], $perfil['padding_altura'], 0, 0, $perfil['largura_gerar'], $perfil['altura_gerar'], $perfil['largura_original'], $perfil['altura_original']);
}

function _gerar_enquadrado($perfil, $definir = false) {
    if ($perfil['largura_original'] > $perfil['largura_canvas']) {
        $diferenca = 100 - (($perfil['largura_original'] - $perfil['largura_canvas']) * 100) / $perfil['largura_original'];
        $perfil['altura_gerar'] = ($perfil['altura_original'] / 100) * $diferenca;
        $perfil['largura_gerar'] = ($perfil['largura_original'] / 100) * $diferenca;
    } else {
        $diferenca = 100 - (($perfil['largura_canvas'] - $perfil['largura_original']) * 100) / $perfil['largura_canvas'];
        $perfil['altura_gerar'] = ($perfil['altura_original'] * 100) / $diferenca;
        $perfil['largura_gerar'] = ($perfil['largura_original'] * 100) / $diferenca;
    }
    if (isset($perfil['altura_gerar']) && isset($perfil['altura_canvas']) && $perfil['altura_gerar'] > $perfil['altura_canvas']) {
        $diferenca = (($perfil['altura_gerar'] - $perfil['altura_canvas']) * 100) / $perfil['altura_gerar'];
        $perfil['altura_gerar'] = $perfil['altura_gerar'] - (($perfil['altura_gerar'] / 100) * $diferenca);
        $perfil['largura_gerar'] = $perfil['largura_gerar'] - (($perfil['largura_gerar'] / 100) * $diferenca);
    }
    if ($perfil['largura_gerar'] > $perfil['largura_canvas']) {
        $diferenca = (($perfil['largura_gerar'] - $perfil['largura_canvas']) * 100) / $perfil['largura_gerar'];
        $perfil['altura_gerar'] = $perfil['altura_gerar'] + (($perfil['altura_gerar'] / 100) * $diferenca);
        $perfil['largura_gerar'] = $perfil['largura_gerar'] + (($perfil['largura_gerar'] / 100) * $diferenca);
    }
    if (!$definir) {
        $perfil['padding_altura'] = 0;
        if (isset($perfil['altura_canvas'])) {
            $perfil['padding_altura'] = (($perfil['altura_gerar'] - $perfil['altura_canvas']) / 2) * -1;
        }
        if (!isset($perfil['canvas'])) {
            $perfil['canvas'] = getCanvas($perfil['largura_gerar'], $perfil['altura_gerar'], $perfil['cor_fundo']);
        }
        $perfil['padding_largura'] = (($perfil['largura_gerar'] - $perfil['largura_canvas']) / 2) * -1;
        imagecopyresampled($perfil['canvas'], $perfil['resource'], $perfil['padding_largura'], $perfil['padding_altura'], 0, 0, $perfil['largura_gerar'], $perfil['altura_gerar'], $perfil['largura_original'], $perfil['altura_original']);
    }
    return $perfil;
}

function gerar() {
    $perfil = defineGlobais();
    header("Content-type:image/" . $perfil['extensao']);
    if ($perfil['modo'] == "enquadrar") {
        if (!isset($perfil['canvas'])) {
            $perfil = _gerar_enquadrado($perfil, 'definir');
            $perfil['canvas'] = getCanvas($perfil['largura_gerar'], $perfil['altura_gerar'], $perfil['cor_fundo']);
        }
        _gerar_enquadrado($perfil);
    }
    if ($perfil['modo'] == "cortar") {
        _gerar_cortado($perfil);
    }
    if ($perfil['modo'] == "aumentar") {
        _gerar_aumentado($perfil);
    }
    if ($perfil['modo'] == "original") {
        _gerar_original($perfil);
    }
    setImage($perfil['canvas'], $perfil['extensao']);
    imagedestroy($perfil['canvas']);
}

gerar();
?>
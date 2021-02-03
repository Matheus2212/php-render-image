<?php

/*
PHP_GeraFoto v1.0

Esse script foi desenvolvido por Matheus Felipe Marques, com inspiração na experiência que adquiriu em trabalhos passados, como um dos passatempos mais divertidos durante a quarentena de 2020.
Esse script pode proporcionar uma economia de tempo gigantesca relacionado a imagens com dimensões incorretas.
Esse script não utiliza nenhuma dependência ou algo do tipo (é independente). Sinta-se livre para poder editá-lo e usá-lo da forma que melhor lhe conver. 
Ele não suporta imagens via endereço web (URL), pois senão qualquer um pode enviar uma URL para este script e isso pode sobrecarregar seu servidor. 
Sinta-se livre para comentar ou apoiar.

Sintaxe de uso: <img src="caminho/ate/script/gera_foto.php?imagem=../local/da/imagem.extensao&modo=perfil_configuracao" />

Link projeto: https://github.com/Matheus2212/PHP_GeraFoto

Perfil: https://github.com/Matheus2212
*/

if (!isset($_GET['imagem']) || !isset($_GET['perfil']) || (!file_exists($_GET['imagem']))) {
    exit();
}

$configuracao = array(
    /*"nome_perfil" => array( // é o nome da configuração - evite repetir
        "largura_gerar" => 300, // largura a ser gerado - utilizar px (pixels)
        "altura_gerar" => 300, // altura a ser gerado - utilizar px (pixels)
        "modo" => 'cortar', // modo de renderização (opcões: cortar, enquadrar, aumentar, original)
        "cor_fundo" => "#000000" // cor de fundo (utilizar hexadecimal - melhor resultado em imagens .png com fundo transparente)
    ),*/
    "procedimentos" => array(
        "largura_gerar" => 300,
        "altura_gerar" => 300,
        "modo" => 'cortar',
    ),
    "blog" => array(
        "largura_gerar" => 400,
        "altura_gerar" => 400,
        "modo" => 'cortar',
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

function getCanvas($largura, $altura, $cor = false)
{
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

function getImage($imagem, $extensao)
{
    $resource = "";

    //New way
    $resource = imagecreatefromstring(file_get_contents($imagem));

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

function setImage($imagem, $extensao)
{
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

function defineGlobais()
{
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

function _gerar_aumentado($perfil)
{
    imagecopyresampled($perfil['canvas'], $perfil['resource'], 0, 0, 0, 0, $perfil['largura_gerar'], $perfil['altura_gerar'], $perfil['largura_original'], $perfil['altura_original']);
}

function _gerar_original($perfil)
{
    $perfil['largura_gerar'] = $perfil['largura_canvas'] = $perfil['largura_original'];
    $perfil['altura_gerar'] = $perfil['altura_canvas'] = $perfil['altura_original'];
    $perfil['canvas'] = getCanvas($perfil['largura_canvas'], $perfil['altura_canvas'], $perfil['cor_fundo']);
    imagecopyresampled($perfil['canvas'], $perfil['resource'], 0, 0, 0, 0, $perfil['largura_gerar'], $perfil['altura_gerar'], $perfil['largura_original'], $perfil['altura_original']);
}

function _gerar_cortado($perfil)
{
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

function _gerar_enquadrado($perfil, $definir = false)
{
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

function gerar()
{
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

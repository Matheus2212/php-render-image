# PHP Render Image
This script will render the image on the server side.

Just use the HTML and PHP $_GET sintax to render the image you want.

The script sets the header depending on the type of file (jpeg -> header(image/jpeg));

This scrips allows you to:
* Fit image on specified dimmensions
* Cut image
* Enlarge image
* Keep original dimmensions

If the image doesn't match any of the specified dimmensions, the script will automatically render it with the most suitable dimmensions.

All renders keeps image aspect ratio "as is".

---

## For example:
```HTML
<img src="render_image.php?imagem=../path/to/image.jpeg&modo=perfil_configuracao" />
```

It can also be set using URL (the script have a domain validation).
```HTML
<img src="render_image.php?imagem=../path/to/image.jpeg&perfil=perfil_configuracao&modo=cortar&largura=1080&altura=720" />
<img src="geraFoto.php?imagem=../path/to/image.jpeg&perfil=perfil_configuracao&modo=enquadrar&largura=1080&altura=720" />
<img src="geraFoto.php?imagem=../path/to/image.jpeg&perfil=perfil_configuracao&modo=aumentar&largura=1080&altura=720" />
<img src="geraFoto.php?imagem=../path/to/image.jpeg&perfil=perfil_configuracao&modo=original" />
```
---

## Example of render profile: 

```PHP
$configuracao = array(
    /*"nome_perfil" => array( // é o nome da configuração - evite repetir
        "largura_gerar" => 300, // largura a ser gerado - utilizar px (pixels)
        "altura_gerar" => 300, // altura a ser gerado - utilizar px (pixels)
        "modo" => 'cortar', // modo de renderização (opcões: cortar, enquadrar, aumentar, original)
        "cor_fundo" => "#000000" // também pode colocar "transparente". cor de fundo (utilizar hexadecimal - melhor resultado em imagens .png com fundo transparente)
    ),*/
    "screenshot" => array(
        "largura_gerar" => 1200,
        "altura_gerar" => 900,
        "modo" => 'cortar',
    ),
);
```
Enjoy!

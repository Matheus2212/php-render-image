# PHP_GeraFoto
Script que vai gerar a foto que você precisar em dimensões específicas

Você não precisa incluir o script em arquivo algum. Basta utilizar a sintaxe HTML para exibir uma imagem.

O script já retorna um recurso de imagem e seu cabelalho será o mesmo da do tipo de imagem informada (jpeg -> jpeg, png -> png, etc).

Ele te permite:
* Enquadrar a Imagem
* Cortar a Imagem
* Aumentar a Imagem (obviamente, diminui qualidade)
* Manter tamanho original

Se a imagem não corresponder as dimensões informadas, ela será aumentada e definido no formato escolhido (enquadrar ou cortar). 
É uma operação bem simples e pode ser chamada de forma bem simples também. 

Todos os modos de renderização mantém a imagem na proporção dela (não vai ter altura mais estreita que largura ou vice versa, por exemplo).

---

## Exemplo:
```HTML
<img src="geraFoto.php?imagem=../local/da/imagem.extensao&modo=perfil_configuracao" />
```

Também pode ser passada a configuração via URL - obviamente sanitizar nas configurações:
```HTML
<img src="geraFoto.php?imagem=../local/da/imagem.extensao&perfil=perfil_configuracao&modo=cortar&largura=1080&altura=720" />
<img src="geraFoto.php?imagem=../local/da/imagem.extensao&perfil=perfil_configuracao&modo=enquadrar&largura=1080&altura=720" />
<img src="geraFoto.php?imagem=../local/da/imagem.extensao&perfil=perfil_configuracao&modo=aumentar&largura=1080&altura=720" />
<img src="geraFoto.php?imagem=../local/da/imagem.extensao&perfil=perfil_configuracao&modo=original" />
```
---

## Exemplo de perfil dentro do script: 

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
Aproveite!

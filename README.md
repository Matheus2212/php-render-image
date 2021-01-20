# PHP-Gera-foto
Script que vai gerar a foto que você precisar em dimensões específicas

Ele te permite:
* Enquadrar a Imagem
* Cortar a Imagem

Se a imagem não corresponder as dimensões informadas, ela será aumentada e definido no formato escolhido (enquadrar ou cortar). 
É uma operação bem simples e poder chamado de forma bem simples também. 

Exemplo:
```HTML
<img src="gera_foto.php?imagem=../local/da/imagem.extensao&modo=perfil_configuracao" />
```

Também pode ser passada a configuração via URL - obviamente sanitizar nas configurações:
```HTML
<img src="gera_foto.php?imagem=../local/da/imagem.extensao&perfil=perfil_configuracao&modo=cortar&largura=1080&altura=720" />
<img src="gera_foto.php?imagem=../local/da/imagem.extensao&perfil=perfil_configuracao&modo=enquadrar&largura=1080&altura=720" />
<img src="gera_foto.php?imagem=../local/da/imagem.extensao&perfil=perfil_configuracao&modo=expandir&largura=1080&altura=720" />
```

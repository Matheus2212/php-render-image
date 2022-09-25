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
<img src="render_image.php?image=../path/to/image.jpeg&mode=profile_name" />
```

It can also be set using URL (the script have a domain validation).
```HTML
<img src="render_image.php?image=../path/to/image.jpeg&profile=profile_name&mode=cut&width=1080&height=720" />
<img src="render_image.php?image=../path/to/image.jpeg&profile=profile_name&mode=fit&width=1080&height=720" />
<img src="render_image.php?image=../path/to/image.jpeg&profile=profile_name&mode=scale&width=1080&height=720" />
<img src="render_image.php?image=../path/to/image.jpeg&profile=profile_name&mode=original" />
```
---

## Example of render profile: 

```PHP
$setup = array(
    /*"profile_name" => array( // é o nome da configuração - evite repetir
        "width" => 300, // width in pixels
        "height" => 300, // height in pixels
        "mode" => 'cut', // render mode (cut, fit, scale, original)
        "background_color" => "#000000" // transparent for .png images
    ),*/
    "screenshot" => array(
        "width" => 1200,
        "height" => 900,
        "mode" => 'cut',
    ),
);
```
Enjoy!

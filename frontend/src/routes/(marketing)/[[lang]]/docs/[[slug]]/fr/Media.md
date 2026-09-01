<script>
	import { Callout, CodeBlock } from '@hyvor/design/components';
	import IconLightbulb from '@hyvor/icons/IconLightbulb';
</script>

# Médias

Les « médias » désignent les fichiers que vous téléchargez sur votre blog. Il peut s'agir d'images, de vidéos, de fichiers audio ou de tout autre type de fichier. Rendez-vous dans **Outils → Bibliothèque de médias** pour gérer les médias de votre blog.

<h2 id="images">Images</h2>

« Une image vaut mille mots ». Ajouter des images à vos articles de blog est un excellent moyen de les rendre plus intéressants et informatifs. Mais attention – utiliser trop d'images ou des images trop volumineuses peut ralentir votre site web. Hyvor Blogs optimise automatiquement les images pour vous lorsque cela est possible.

Vous pouvez télécharger des images dans l'éditeur d'articles, dans la bibliothèque de médias, ou dans les paramètres du blog tels que le logo, le favicon, etc. Les formats d'image suivants sont pris en charge :

- PNG - `.png`
- JPEG - `.jpg`, `.jpeg`, `.jfif`, `.pjpeg`, `.pjp`
- GIF - `.gif`
- APNG - `.apng`
- AVIF - `.avif`
- SVG - `.svg`
- WebP - `.webp`

<h3 id="webp">Conversion automatique en WebP</h3>

Les images <a href="https://en.wikipedia.org/wiki/WebP" rel="nofollow" target="_blank">WebP</a> sont 25 à 34 % plus légères que les images JPEG/PNG de même qualité. Il est aujourd'hui recommandé d'utiliser le WebP sur les sites web dans la mesure du possible, car tous les navigateurs modernes prennent en charge les images WebP. Vous pouvez télécharger des images JPEG et PNG comme d'habitude dans vos articles. Hyvor Blogs les convertira automatiquement en images WebP à la volée. Vous n'avez rien à faire.

<Callout type="info">
	{#snippet icon()}
		<IconLightbulb />
	{/snippet}
	Notez que l'extension dans l'URL ne changera pas (ex : <code>/media/image.jpg</code> ou
	<code>/media/image.png</code>), mais l'image sera servie en WebP avec les en-têtes HTTP appropriés.
	Vous pouvez le vérifier en analysant votre page avec
	<a href="https://pagespeed.web.dev/" target="_blank" rel="nofollow">PageSpeed Insights</a>.
</Callout>

<h3 id="responsive-images">Images adaptatives</h3>

Hyvor Blogs gère les images adaptatives pour vous. Voici comment cela fonctionne. Les images (PNG, JPEG, WebP uniquement) téléchargées dans la bibliothèque de médias peuvent être automatiquement redimensionnées en ajoutant `/{width}w` à l'URL.

- `/media/image.png` - Image originale
- `/media/image/100w.png` - Redimensionnée à une largeur maximale de 100px
- `/media/image/750w.png` - Redimensionnée à une largeur maximale de 750px

Nous utilisons cette fonctionnalité de redimensionnement ainsi que <a href="https://developer.mozilla.org/en-US/docs/Learn/HTML/Multimedia_and_embedding/Responsive_images" target="_blank" rel="nofollow">srcset</a> (prise en charge par tous les navigateurs modernes) pour rendre les images de vos articles adaptatives. Voici les tailles que nous utilisons :

- `500w`
- `750w`
- `1000w`
- `1500w`

Par exemple, si vous téléchargez une image de 1250px dans un article, le code HTML ressemblerait à ceci :

```html
<img
	src="/media/image.jpg"
	srcset="
		/media/image.jpg/500w   500w,
		/media/image.jpg/750w   750w,
		/media/image.jpg/1000w 1000w,
		/media/image.jpg       1250w
	"
	alt="Image"
/>
```

Le navigateur déterminera ensuite la meilleure version de l'image à charger en fonction de la taille de l'écran et du ratio de pixels de l'appareil (DPR).

Notez que cette fonctionnalité n'est disponible que pour les images téléchargées dans la bibliothèque de médias. Les images hébergées à l'extérieur ne seront pas redimensionnées.

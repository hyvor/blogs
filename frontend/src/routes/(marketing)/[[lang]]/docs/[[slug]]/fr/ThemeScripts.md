# Scripts

Contrairement à la prise en charge de SCSS, Hyvor Blogs ne prend pas en charge le pré-traitement du JS (pour la prise en charge de Typescript ou des modules). Vous devez donc écrire du Javascript que le navigateur comprend directement. Vous pouvez créer des fichiers Javascript dans le dossier `/assets` et les lier dans le template.

```html
<script src="{{ 'script.js' | asset_url }}"></script>
```

Utilisez `async` pour les scripts non essentiels.

```html
<script src="{{ 'non-essential.js' | asset_url }}"></script>
```

Si possible, essayez d'écrire du Javascript en ligne pour éviter complètement les requêtes HTTP.

```html
<script>
	// my js here
</script>
```

<h2 id="flashload-safe">Compatibilité avec Flashload</h2>

[Flashload](https://github.com/hyvor/flashload) fait de la magie, mais assurez-vous de comprendre comment cela fonctionne pour éviter les pièges courants. Disons que vous naviguez de `/` vers `/page`. Flashload empêche le rechargement du navigateur, charge la page lui-même et remplace **uniquement le** `<body>`. Cela signifie que,

- Le CSS dans `<head>` n'est chargé qu'une seule fois. C'est pourquoi nous avons une seule feuille de style `styles.css` qui contient tous les styles du blog.
- Les `<script>` dans `<head>` ne s'exécuteront qu'au premier chargement de la page, et non lors de la navigation.
- Les `<script>` dans `<body>` se chargeront/s'exécuteront à chaque navigation, sauf s'ils possèdent un attribut **data-flashload-skip-script**.

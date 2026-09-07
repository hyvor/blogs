<script>
	import { Callout, CodeBlock } from '@hyvor/design/components';
	import { DocsImage } from '@hyvor/design/marketing';
</script>

# Polices

Tous les thèmes de Hyvor Blogs sont fournis avec une police par défaut. Vous pouvez facilement la changer pour n'importe quelle police de votre choix. Il existe deux façons de changer la police.

- [Bunny Fonts (intégré)](#bunny-fonts)
- [Polices personnalisées](#custom-fonts)

<h2 id="bunny-fonts">1. Bunny Fonts (intégré)</h2>

Hyvor Blogs prend en charge nativement <a href="https://fonts.bunny.net/" target="_blank" rel="nofollow">Bunny Fonts</a>. Toutes les polices sont chargées directement via le domaine de votre blog, ce qui évite tout problème de confidentialité, tout suivi par des tiers ou toute résolution DNS supplémentaire.

- `https://[your-domain]/fonts/css/{family}` - Proxy CSS
- `https://[your-domain]/fonts/file/{file_name}` - Proxy de fichier de police

<h3 id="load-fonts">Étape 1 : Charger les polices</h3>

Tout d'abord, rendez-vous sur <a href="https://fonts.bunny.net/" rel="nofollow" target="_blank">Bunny Fonts</a> et sélectionnez les polices et variantes qui vous plaisent. Vous verrez un code CSS pour charger les polices comme ci-dessous.

```css
@import url(https://fonts.bunny.net/css?family=mulish:400,700);
```

À partir de cela, copiez uniquement la partie relative à la famille. Dans ce cas, il s'agit de `mulish:400,700`.

<DocsImage src="/images/docs/fonts/fonts-select.png" alt="Sélectionner une police sur bunny fonts" />

Ensuite, collez-la dans **Theme → config.yaml → THEME_FONTS** dans la console Hyvor Blogs.

<DocsImage src="/images/docs/fonts/fonts-config.png" alt="Configurer les polices" />

Lorsque vous ajoutez ceci au fichier config.yaml, les polices seront chargées automatiquement dans la balise `<head>` de votre blog. Vous pourrez alors utiliser cette police dans votre blog.

<Callout type="info">
	Remarque : si vous ne voyez pas l'option Theme Fonts dans l'interface, passez en mode YAML (coin supérieur droit)
	et ajoutez l'option <code>THEME_FONTS</code> après <code>THEME_VERSION</code>.

    <CodeBlock
    	code={`
        THEME_NAME: hello
        THEME_VERSION: 1.0.0
        THEME_FONTS: "mulish:400,700"
    `}
    	language="yaml"
    />

</Callout>

<h3 id="use-fonts">Étape 2 : Utiliser les polices</h3>

Tous les thèmes officiels prennent en charge la personnalisation des polices via `config.yaml`. Certains thèmes proposent plusieurs options de police pour différentes parties du blog (par ex. : texte vs titres).

Copiez la valeur de la famille de polices depuis la section **Embed CSS** de Bunny Fonts.

<DocsImage src="/images/docs/fonts/fonts-embed-css.png" alt="Intégrer le CSS" width={300} />

Collez-la dans l'option correspondante dans `config.yaml` de votre thème.

<DocsImage src="/images/docs/fonts/fonts-use.png" alt="Utiliser les polices" />

Si votre thème ne prend pas en charge la personnalisation des polices, vous pouvez utiliser du CSS pour changer la police comme expliqué dans la section suivante.

<h2 id="custom-fonts">2. Polices personnalisées</h2>

Si vous devez ajouter une police personnalisée qui n'est pas disponible dans Bunny Fonts, suivez ces étapes :

- Téléversez les fichiers de police dans **Theme → assets**
- Ajoutez du CSS personnalisé à un fichier SCSS dans **Theme → styles**

Votre CSS personnalisé devrait ressembler à ceci :

```css
@font-face {
	font-family: 'My Font';
	src: url('/assets/my-font.woff2') format('woff2');
	font-weight: normal;
	font-style: normal;
}
body {
	font-family: 'My Font', sans-serif;
}
```

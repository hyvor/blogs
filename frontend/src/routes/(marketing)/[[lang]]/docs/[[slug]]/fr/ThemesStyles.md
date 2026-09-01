<script lang="ts">
	import { Callout } from '@hyvor/design/components';
</script>

# Thèmes de style

Ce dossier contient des fichiers SCSS. `index.scss` est requis.

En développant et en travaillant avec d'autres plateformes de blog/CMS, nous avons compris que la personnalisation d'un thème devient vraiment difficile lorsque le développeur du thème met tout le CSS dans un seul fichier. Nous avons donc décidé de prendre en charge les fichiers « chunk-css » pour faciliter l'édition pour le blogueur. Et, nous utilisons <a href="https://sass-lang.com/" rel="nofollow">SCSS</a> au lieu de CSS pour faciliter la vie du développeur du thème. Tout CSS est du SCSS valide. Donc, si vous n'avez jamais utilisé SCSS auparavant, utilisez simplement du CSS. SCSS a juste des fonctionnalités sympas comme l'imbrication de règles.

Revenons au « chunk-css ». Disons que vous créez un fichier partiel pour l'en-tête du blog (`templates/_header.twig`). Créez ensuite un fichier SCSS pour contenir son CSS (`header.scss`). Ce modèle facilite la compréhension et l'édition pour le blogueur. Enfin, importez tous les fichiers « chunk » dans `index.scss` en utilisant des instructions `@import`.

```css
@import 'css-variables.scss';
@import 'header.scss';
@import 'body.scss';
```

De notre côté, nous traitons le fichier `index.scss` et générons un `styles.css`, qui sera accessible via <ocde>/styles.css</ocde>. **C'est le seul fichier CSS de tout le blog !**

<Callout type="info">
	<p>
		Nous vous encourageons vivement à écrire du CSS à partir de zéro sans utiliser de bibliothèques
		comme Bootstrap. Un thème de blog est très simple et il est tout à fait possible de tout gérer
		vous-même sans dépendre de bibliothèques tierces. Si vous voulez vraiment utiliser une
		bibliothèque, ajoutez-la aux assets plutôt qu'aux styles.
	</p>
</Callout>

<h2 id="fonts">Polices</h2>

Le moyen le plus simple de charger des polices est d'ajouter `THEME_FONTS` au fichier de [configuration](/docs/themes-config).

```yaml
THEME_FONTS: 'mulish:400'
```

Ensuite, vous pouvez utiliser la police dans vos fichiers SCSS. Consultez notre page sur les [polices](/docs/fonts) pour un guide approfondi.

<h2 id="advanced-nodes">Nœuds avancés</h2>

La page [Écriture](/docs/writing) décrit tous les nœuds pris en charge. Nous essayons d'utiliser les éléments HTML les plus basiques pour représenter chaque nœud. Cependant, il existe des composants avancés qui nécessitent une attention particulière lors de l'écriture des styles.

<h3 id="image">Image</h3>

```html
<figure>
	<img src="https://exmaple.com/image.png" />
	<figcaption>Here goes the caption</figcaption>
</figure>
```

Notez que figcaption peut être vide. Vérifiez donc si les marges sont correctes lorsque figcaption n'est pas présent.

<h3 id="embed-rich">Intégration</h3>

```html
<figure>
	<div class="rich-embed">{# embed HTML code goes here... #}</div>
	<figcaption>Here goes the caption</figcaption>
</figure>
```

<h3 id="embed-link">Signet de lien</h3>

```html
<figure>
	<a class="rich-link">
		<div class="rich-link-details">
			<div class="rich-link-title">{{ data.title }}</div>
			<div class="rich-link-description">{{ data.description }}</div>
			<div class="rich-link-domain">{{ data.domain }}</div>
		</div>
		<div class="rich-link-thumbnail">
			<img src="{{ data.thumbnail }}" />
		</div>
	</a>
	<figcaption>{{ data.caption }}</figcaption>
</figure>
```

<h3 id="callout">Encadré</h3>

```html
<aside style="background-color:#0000000;color:#ffffff">
	<mark></mark>
</aside>
```

<h2 id="node-templates">Modèles de nœuds</h2>

Certains nœuds ont des modèles. Et, en tant que développeur du thème, vous pouvez les personnaliser si le modèle par défaut ne correspond pas à votre design. Pour ce faire, ajoutez le fichier donné au dossier `/templates`.

<h3 id="template-link-bookmark">Signet de lien</h3>

Nom de fichier personnalisé : `node-bookmark.twig`

Modèle par défaut :

```html
<a class="bookmark" target="_blank" href="{{ data.url }}" data-url="{{ data.original_url }}">
	<div class="bookmark-details">
		<div class="bookmark-title">{{ data.title }}</div>
		<div class="bookmark-description">{{ data.description }}</div>
		<div class="bookmark-domain">{{ data.domain }}</div>
	</div>
	<div class="bookmark-thumbnail">
		<img src="{{ data.thumbnail_url }}" alt="{{ data.title }}" />
	</div>
</a>
```

Définition de l'objet `data` :

```json
{
	"url": "https://blogs.hyvor.com",
	"original_url": "https://blogs.hyvor.com",
	"title": "Hyvor Blogs",
	"description": "A simple blogging platform",
	"domain": "blogs.hyvor.com",
	"thumbnail_url": "https://blogs.hyvor.com/thumbnail.png"
}
```

<h3 id="template-toc">Table des matières (TOC)</h3>

Nom de fichier personnalisé : `node-toc.twig`

Modèle par défaut :

```html
{{ toc | raw }}
```

La variable `toc` est une chaîne qui contient le HTML de la table des matières sous forme d'éléments `ul` et `li` imbriqués.

Exemple : si vous souhaitez ajouter un titre à la table des matières, vous pouvez le faire comme suit. Le filtre `raw` est requis pour restituer le HTML de la table des matières.

```html
<div class="toc-wrap">
	<h2>Table of Contents</h2>
	{{ toc | raw }}
</div>
```

<h2 id="light-dark">Modes clair/sombre</h2>

Le blogueur dispose des options suivantes à choisir dans la Console.

- Quels modes sont autorisés ?
  - Clair
  - Sombre
  - Les deux
- Quel est le mode par défaut ?
  - Préférence au niveau du système d'exploitation de l'utilisateur (par défaut)
  - Clair
  - Sombre

Il serait fastidieux pour vous d'écrire une logique pour prendre en compte toutes ces options et déterminer quel thème afficher à l'utilisateur. C'est pourquoi nous facilitons le développement du mode clair/sombre en ajoutant un nom de classe à l'élément `<html></html>`. Vous pouvez décider des couleurs en fonction de cette classe. Nous vous recommandons d'utiliser les <a href="https://developer.mozilla.org/en-US/docs/Web/CSS/Using_CSS_custom_properties" rel="nofollow">variables CSS</a> pour définir les couleurs dans le fichier `colors.scss`.

Voici un exemple de définition des couleurs pour les modes clair et sombre.

```css
.mode-light:root {
	--color-background: #ffffff;
	--color-text: #000000;
}
.mode-dark:root {
	--color-background: #000000;
	--color-text: #ffffff;
}
```

Ensuite, dans les éléments, utilisez ces variables. Évitez de coder en dur les valeurs de couleur !

```css
body {
	background-color: var(--color-background);
	color: var(--color-text);
}
```

C'est tout ce que vous avez à faire pour prendre en charge les modes clair et sombre. Nous nous chargeons d'afficher le thème correct à l'utilisateur.

<Callout type="info">
	<p>
		En coulisses, la détermination de la couleur est gérée par un petit code Javascript injecté
		dans le blog dans le <code>_head</code>
		<a href="/docs/themes-templates#placeholders">placeholder</a>.
	</p>
</Callout>

<h2 id="mode-toggler">Bascule de mode clair/sombre</h2>

Si vous prenez en charge à la fois les thèmes clair et sombre, vous aurez très probablement besoin d'un bouton permettant aux utilisateurs de basculer entre les modes de couleur. Sur la plupart des autres plateformes, vous devez écrire manuellement la logique pour cela et enregistrer les préférences dans le stockage local - mais pas avec Hyvor Blogs !

Nous avons mentionné ci-dessus que nous ajoutons un petit code Javascript pour vous aider à déterminer les modes clair/sombre. Il expose également une API simple pour vous aider à basculer entre les modes.

```html
_hb.changeColorMode(mode); // mode = os|light|dark _hb.getColorMode() // returns light|dark
_hb.getColorModePreference() // returns light|dark|os
```

Utilisez ces fonctions globales dans les boutons de bascule - nous nous chargerons du LocalStorage.

```html
<div class="mode-toggler">
	<button class="toggle-dark" onclick="_hb.changeColorMode('dark')"><!-- DARK MODE ICON --></button>
	<button class="toggle-light" onclick="_hb.changeColorMode('light')">
		<!-- LIGHT MODE ICON -->
	</button>
</div>
```

Vous pouvez utiliser un SCSS comme celui-ci pour afficher les boutons en fonction du thème. Cela affichera l'icône du mode clair en mode sombre, et le bouton du mode sombre en mode clair.

```css
.mode-dark {
	.toggle-dark {
		display: none;
	}
}
.mode-light {
	.toggle-light {
		display: none;
	}
}
```

<h2 id="mode-toggler-os">Bascule clair + sombre + préférence du système d'exploitation</h2>

Certains voudront peut-être aussi ajouter une option de préférence du système d'exploitation au bouton de bascule. Dans ce cas, le HTML sera similaire mais nous devons utiliser les classes `mode-preference-*` pour détecter la préférence de l'utilisateur.

- Les classes `mode-light` et `mode-dark` sont ajoutées à `<html>`, et elles représentent le **mode de couleur** actuel.
- Les classes `mode-preference-light`, `mode-preference-dark`, `mode-preference-os` sont ajoutées à `<html>` et elles représentent la **préférence de mode de couleur** actuelle.

```html
<div class="mode-toggler">
	<button class="toggle-light" onclick="_hb.changeColorMode('dark')">
		<!-- LIGHT MODE ICON -->
	</button>
	<button class="toggle-dark" onclick="_hb.changeColorMode('os')"><!-- DARK MODE ICON --></button>
	<button class="toggle-os" onclick="_hb.changeColorMode('light')"><!-- OS MODE ICON --></button>
</div>
```

Le code SCSS suivant affichera le bouton de préférence de mode actuellement actif.

```css
.toggle-dark,
.toggle-light,
.toggle-os {
	display: none;
}
.mode-preference-light .toggle-light {
	display: inline-block;
}
.mode-preference-dark .toggle-dark {
	display: inline-block;
}
.mode-preference-os .toggle-os {
	display: inline-block;
}
```

<Callout type="info">
	<p>
		Veuillez noter que ces exemples sont là uniquement pour vous expliquer comment cela fonctionne.
		N'hésitez pas à concevoir des bascules de mode de couleur plus créatives ;)
	</p>
</Callout>

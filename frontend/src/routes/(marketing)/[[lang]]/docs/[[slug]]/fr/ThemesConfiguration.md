<script lang="ts">
	import { Callout, Table, TableRow } from '@hyvor/design/components';
</script>

# Configuration

L'objectif des configurations est de rendre les thèmes personnalisables dans une certaine mesure sans avoir à
modifier le code du thème. Les configurations peuvent être utilisées pour permettre au blogueur d'activer ou de désactiver des fonctionnalités,
changer les couleurs et les polices, ou même définir des clés API pour des services externes.

<Callout type="info">
	<p>
		Si vous développez un thème pour vous-même ou pour un seul client, vous <b>n'avez peut-être pas besoin</b> d'utiliser
		des configurations. Cependant, si vous prévoyez de
		<a href="/docs/themes-publishing">publier</a> votre thème, l'ajout de configurations est requis.
	</p>
</Callout>

Toutes les configurations sont ajoutées à `config.yaml` avec leurs valeurs par défaut. Il existe deux types
de configurations.

- **Configurations connues de HB** - HB est conscient de ces configurations et prendra des décisions en fonction de leurs valeurs. Vous pouvez aussi utiliser leurs valeurs dans les modèles.
- **Configurations de thème** - HB n'est pas conscient de ces configurations. Vous pouvez les utiliser dans les modèles pour du contenu ou des styles dynamiques.

Toutes les configurations sont accessibles dans les modèles à partir de la
[variable de route](/docs/themes-templates#variables) `_config`.

<h2 id="hb-config">Configurations connues de HB</h2>

Les configurations connues de HB doivent être écrites en `ENGLISH_UPPER_SNAKE_CASE` dans
`config.yaml`.

- `THEME_NAME` - Nom du thème
  - Requis : Uniquement si [publié](/docs/themes-publishing)
- `THEME_VERSION` - Version sémantique du thème
  - Requis : Uniquement si publié
- `THEME_FONTS` - Polices à charger dans le blog. Voir [polices](/docs/fonts).
  - Requis : Uniquement si publié
- `DEMO_URL` - Peut être utilisé pour définir une URL de démonstration personnalisée lors de la publication
  - Requis : Non
  - Valeur par défaut : Générée automatiquement
- `POSTS_PER_PAGINATION` - Nombre d'articles chargés initialement dans la [variable de route](/docs/themes-templates#variables) `_posts`
  - Requis : Non
  - Valeur par défaut : 10

<h2 id="theme-config">Configurations de thème</h2>

Les configurations de thème (couleurs, polices, etc.) doivent être écrites en `english_lower_snake_case`.

<h3 id="config-yaml">Exemple de config.yaml</h3>

Bien que vous puissiez utiliser des configurations YAML imbriquées à plusieurs niveaux, nous vous recommandons de n'utiliser
qu'un ou deux niveaux d'imbrication.

```yaml
THEME_NAME: hello
THEME_VERSION: 1.0.0
THEME_FONTS: 'mulish:400,700'
POSTS_PER_PAGINATION: 15

dark_theme: Yes
accent_color: 0000000
image_service:
  api_key:
  api_version: 2
```

Dans cet exemple, les 3 premières lignes sont des configurations connues de HB. Les autres sont des configurations de thème.
Vous pouvez ajouter autant de configurations de thème que nécessaire.

<h3 id="config-def">Définitions de configuration</h3>

`config.def.yaml` « décrit » vos **configurations de thème**. Cela aide le blogueur
à comprendre ce que fait chaque configuration. Cela permet également de rendre le
fichier `config.yaml`
dans **Console → Thème** sous forme d'interface utilisateur plutôt que sous forme de fichier.

<Callout type="info">
	<p>Testez vos définitions de configuration sur <a href="/config">blogs.hyvor.com/config</a>.</p>
</Callout>

Voici un exemple de fichier `config.def.yaml` qui explique les configurations de l'exemple
précédent.

```yaml
dark_theme:
  $name: Dark theme
  $description: Turn on dark theme for this blog
  $type: checkbox

accent_color:
  $name: Accent Color
  $description: Main color of the blog
  $type: color

image_service:
  $name: Image Service API Details

  api_key:
    $name: API Key
    $description: ...
    $type: text
    $maxlength: 255

  api_version:
    $name: API Version
    $description: ...
    $type: number
    $min: 1
    $max: 2
```

<Callout type="info">
	<p>
		Nous utilisons le fichier <code>config.def.yaml</code> pour afficher le fichier <code>config.yaml</code> dans
		<code>Console → Thème</code> sous forme d'interface utilisateur plutôt que sous forme de fichier. De plus, l'ajout de conditions dans le fichier def (par exemple :
		min, max) garantit que le blogueur ne peut pas définir de configurations incorrectes.
	</p>
</Callout>

<h4 id="configuration-definitions">Définitions de configuration</h4>

Voici les définitions prises en charge pour les configurations de thème :

- `$type` - Type de la configuration. Voir [Types de `$type` pris en charge](/docs/themes-config#supported-types).
- `$name` - Nom de la configuration.
- `$description` - Description de la configuration.
- `$minlength` - Nombre minimum de caractères dans une entrée.
- `$maxlength` - Nombre maximum de caractères dans une entrée.
- `$min` - Valeur minimale pour un nombre.
- `$max` - Valeur maximale pour un nombre.

<h4 id="supported-types">Types de <code>$type</code> pris en charge</h4>

Voici les types pris en charge pour les configurations de thème :

<Table columns="2fr 3fr">
	<TableRow head>
		<div><code>$type</code></div>
		<div>Description</div>
	</TableRow>

    <TableRow>
    	<div><code>none</code></div>
    	<div>Aucune entrée. Ceci est utile pour les configurations qui ne sont pas modifiables par le blogueur</div>
    </TableRow>

    <TableRow>
    	<div><code>text</code></div>
    	<div>Entrée de texte sur une seule ligne. C'est la valeur par défaut, si <code>$type</code> n'est pas défini</div>
    </TableRow>

    <TableRow>
    	<div><code>textarea</code></div>
    	<div>Entrée de texte sur plusieurs lignes</div>
    </TableRow>

    <TableRow>
    	<div><code>number</code></div>
    	<div>Sélectionner un nombre</div>
    </TableRow>

    <TableRow>
    	<div><code>checkbox</code></div>
    	<div>Case à cocher (valeur booléenne)</div>
    </TableRow>

    <TableRow>
    	<div><code>radio</code></div>
    	<div>
    		Sélectionner l'une des différentes options. Voir les exemples <a href="/docs/themes-config#radio">ci-dessous</a>
    	</div>
    </TableRow>

    <TableRow>
    	<div><code>color</code></div>
    	<div>Sélectionner une couleur</div>
    </TableRow>

</Table>

<h4 id="radio">Exemple de bouton radio</h4>

Vous pouvez définir des options radio dans `$options`, qui est une liste de paires `clé : étiquette`.
`clé`
est la valeur réelle qui sera enregistrée dans le fichier `config.yaml`
. `étiquette` est ce que l'utilisateur verra.

```yaml
some_key:
  $title: When to use caching
  $type: radio
  $options:
    all: For All Posts and Pages
    posts: Only Posts
    pages: Only Pages
```

<h2 id="config-usage">Utilisation des configurations dans les modèles</h2>

Après avoir défini les configurations, vous pouvez les utiliser dans vos modèles. Vous pouvez accéder aux configurations
via la variable de route _config.

Exemple : Variables CSS configurables.

`config.yaml` :

```yaml
colors:
  accent: '#896c6b'

font:
  size: 16
  family: 'Nunito, sans-serif'

line_height: 24

box:
  radius: 20
  shadow: '0 0 30px rgba(0,0,0,0.05)'
```

Ensuite, utilisez les configurations dans vos modèles.

```html
<style>
	:root {
	    --color-accent: {{ _config.colors.accent }};
	    --font-size: {{ _config.font.size }}px;
	    --font-family: {{ _config.font.family }};
	    --line-height: {{ _config.line_height }}px;
	    --box-radius: {{ _config.box.radius }}px;
	    --box-shadow: {{ _config.box.shadow }};
	}
</style>
```

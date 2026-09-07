<script lang="ts">
	import { Callout, Table, TableRow, Checkbox, InputGroup } from '@hyvor/design/components';
</script>

<h1 id="publishing-themes">Publier des thèmes</h1>

Êtes-vous prêt à publier votre thème nouvellement créé dans notre [liste de thèmes](/themes) ? Pour ce faire, votre thème doit être développé dans un fork de notre dépôt <a href="https://github.com/hyvor/hyvor-blogs-themes">hyvor-blogs-themes</a>. Une fois tout terminé, envoyez-nous une pull request vers la branche `main`. Si elle est fusionnée, votre thème sera automatiquement ajouté à notre liste de thèmes, et les autres blogueurs pourront l'installer facilement.

<Callout type="info">
	<p>
		Tous les thèmes de notre liste de thèmes sont gratuits. Si vous souhaitez créer un thème payant,
		vous devez le vendre en dehors de notre plateforme, et les utilisateurs peuvent télécharger le
		fichier ZIP depuis la Console pour l'installer.
	</p>
</Callout>

<h2 id="checklist">Liste de vérification</h2>

Toutes les exigences suivantes doivent être respectées afin de publier un thème dans notre liste officielle de thèmes.

<div class="checklist-main"><Checkbox>Ces fichiers doivent être ajoutés :</Checkbox></div>
<div class="child-checkbox">
	<InputGroup>
		<Checkbox>index.twig</Checkbox>
		<Checkbox>post.twig</Checkbox>
		<Checkbox>tag.twig</Checkbox>
		<Checkbox>author.twig</Checkbox>
		<Checkbox>404.twig</Checkbox>
	</InputGroup>
</div>

<div class="checklist-main">
	<Checkbox>Prend en charge plusieurs langues (dispose d'un sélecteur de langue)</Checkbox>
</div>

<div class="checklist-main"><Checkbox>Prend en charge les modes couleur clair et sombre</Checkbox></div>

<div class="checklist-main"><Checkbox>Respecte les paramètres de mode couleur du blog</Checkbox></div>

<div class="checklist-main"><Checkbox>Pagination</Checkbox></div>

<div class="checklist-main" id="mult-line">
	<div class="checklist-main-checkbox"><Checkbox></Checkbox></div>

    <div class="checklist-main-text">
    	La recherche du blog doit être implémentée au moins avec la recherche d'articles. En option,
    	vous pouvez ajouter une recherche pour les tags et les auteurs.
    </div>

</div>

<div class="checklist-main" id="mult-line">
	<div class="checklist-main-checkbox"><Checkbox></Checkbox></div>

    <div class="checklist-main-text">
    	Tous les articles doivent contenir des liens vers leurs versions traduites (si disponibles).
    	Ex : « Cet article est aussi disponible en... » ou « Traductions : ... »
    </div>

</div>

<div class="checklist-main">
	<Checkbox>Tous les <a href="/docs/themes-templates#placeholders">placeholders</a> sont ajoutés</Checkbox>
</div>
<div class="child-checkbox">
	<InputGroup>
		<Checkbox><code>_head</code></Checkbox>
		<Checkbox><code>_foot</code></Checkbox>
		<Checkbox><code>_comments</code></Checkbox>
		<Checkbox><code>_newsletter</code></Checkbox>
	</InputGroup>
</div>

<div class="checklist-main">
	<Checkbox><code>_comments</code> ne devrait être ajouté qu'aux articles, pas aux pages.</Checkbox>
</div>

<div class="checklist-main">
	<Checkbox
		>Les blocs <code>_comments</code> et <code>_newsletter</code> ne devraient pas être affichés si la
		valeur de chacun est vide.</Checkbox
	>
</div>

<div class="checklist-main" id="mult-line">
	<div class="checklist-main-checkbox"><Checkbox></Checkbox></div>

    <div class="checklist-main-text">
    	Si le blog a un logo (<code>_blog.logo_url</code>), le logo doit être affiché dans l'en-tête
    	en reliant le logo à la page d'accueil du blog.
    </div>

</div>

<div class="checklist-main">
	<Checkbox><a href="/docs/themes-internationalization">Internationalisé</a></Checkbox>
</div>

<div class="checklist-main">
	<Checkbox
		><code>{`<html lang="{{ _lang.code }}" dir="{{ _lang.direction }}"></html>`}</code> est ajouté</Checkbox
	>
</div>

<div class="checklist-main">
	<Checkbox
		>Prend en charge les langues RTL. Voir <a href="/docs/themes-publishing#rtl">Prise en charge RTL</a> ci-dessous.</Checkbox
	>
</div>

<div class="checklist-main">
	<Checkbox
		>Les configurations sont ajoutées pour les couleurs, polices, etc. Voir la section <a href="/docs/themes-publishing#config"
			>Config</a
		>.</Checkbox
	>
</div>

<div class="checklist-main" id="mult-line">
	<div class="checklist-main-checkbox"><Checkbox></Checkbox></div>
	<div class="checklist-main-text">
		Les définitions de configuration (<code>config.def.yaml</code>) sont ajoutées. Voir la page
		<a href="/docs/themes-config#config-def">Configuration -> Définitions de configuration</a>.
		Utilisez l'<a href="/config">outil de configuration</a> pour valider <code>config.def.yaml</code>.
	</div>
</div>

<div class="checklist-main">
	<Checkbox>Les fichiers YAML doivent utiliser 2 espaces par indentation (pas de tabulations, pas 4 espaces).</Checkbox>
</div>

<div class="checklist-main"><Checkbox>Compatible mobile (responsive)</Checkbox></div>

<div class="checklist-main" id="mult-line">
	<div class="checklist-main-checkbox"><Checkbox></Checkbox></div>
	<div class="checklist-main-text">
		Les articles en vedette peuvent avoir une interface unique dans la page d'index (ex : une icône
		épinglée/étoile)
	</div>
</div>

<div class="checklist-main">
	<Checkbox>Les styles de contenu sont ajoutés. Voir la section Styles de contenu ci-dessous.</Checkbox>
</div>

<div class="checklist-main" id="mult-line">
	<div class="checklist-main-checkbox"><Checkbox></Checkbox></div>
	<div class="checklist-main-text">
		Toutes les ressources (JS, polices, etc.) doivent être ajoutées dans le dossier <code>assets</code>.
		Ne chargez pas de ressources depuis des sources externes comme Google Fonts.
	</div>
</div>

<div class="checklist-main" id="mult-line">
	<div class="checklist-main-checkbox"><Checkbox></Checkbox></div>
	<div class="checklist-main-text">
		Doit prendre en charge les liens de réseaux sociaux du blog (affiche une icône ou un lien vers le
		profil du réseau social si le lien est disponible)
	</div>
</div>
<div class="child-checkbox">
	<InputGroup>
		<Checkbox>Facebook</Checkbox>
		<Checkbox>Twitter</Checkbox>
		<Checkbox>Linkedin</Checkbox>
		<Checkbox>Youtube</Checkbox>
		<Checkbox>TikTok</Checkbox>
		<Checkbox>Instagram</Checkbox>
		<Checkbox>Github</Checkbox>
	</InputGroup>
</div>

<div class="checklist-main">
	<Checkbox>
		La mention Hyvor Blogs (« Fait avec Hyvor Blogs ») est affichée dans le pied de page si
		<code>_branding</code> est vrai.
	</Checkbox>
</div>

<h2 id="config">Config</h2>

Comme expliqué dans la page des [configurations](/docs/themes-config), les configurations suivantes sont requises lors de la publication de votre thème.

```yaml
THEME_NAME: my-theme
THEME_VERSION: 1.0.0
```

Les configurations suivantes sont recommandées pour tous les thèmes publiés.

```yaml
colors:
  light:
    # ... colors for the light theme
  dark:
    # ... colors for the dark theme

# if only one font
font:
  size: 16px
  line_height: 1
  family: 'Inter, sans-serif'

# if multiple fonts
fonts:
  body:
    size: 16px
    family: 'Inter, sans-serif'
  heading:
    size: 24px
    family: 'Nunito, sans-serif'

settings:
  loop: # features in the index page (list of posts)
    authors: true
    tags: true
    featured_image: true
  post: # features in the post page
    authors: true
    tags: true
    featured_image: true
    toc: true # table of contents
  feed: true # a link to RSS feed (if available)
```

<h2 id="rtl">Prise en charge RTL</h2>

Tous les thèmes publiés doivent prendre en charge les langues RTL (de droite à gauche). Suivez ces conseils pour vous assurer que votre thème prend en charge le RTL.

- Ajoutez `dir="{{ _lang.direction }}"` à la balise `<html>`
- Utilisez des propriétés CSS sensibles à la direction lors de l'ajout de padding horizontal, de marges et de bordures gauche/droite

<Table columns="3fr 3fr" hover>
	<TableRow head>
		<div>Ne pas utiliser</div>
		<div>Utiliser ceci</div>
	</TableRow>

    <TableRow>
    	<div><code>padding-left</code></div>
    	<div><code>padding-inline-start</code></div>
    </TableRow>

    <TableRow>
    	<div><code>padding-right</code></div>
    	<div><code>padding-inline-end</code></div>
    </TableRow>

    <TableRow>
    	<div><code>margin-left</code></div>
    	<div><code>margin-inline-start</code></div>
    </TableRow>

    <TableRow>
    	<div><code>margin-right</code></div>
    	<div><code>margin-inline-end</code></div>
    </TableRow>

    <TableRow>
    	<div><code>border-left</code></div>
    	<div><code>border-inline-start</code></div>
    </TableRow>

    <TableRow>
    	<div><code>border-right</code></div>
    	<div><code>border-inline-end</code></div>
    </TableRow>

</Table>

- Assurez-vous que les éléments positionnés en absolu/fixe sont correctement positionnés en mode RTL
- Les blocs `<pre><code>` doivent avoir la propriété CSS `direction: ltr`
- Assurez-vous d'ajouter une langue RTL à votre blog DEV et de tester la prise en charge du RTL

<h2 id="content-styles">Styles de contenu</h2>

Tous les thèmes publiés doivent styliser correctement tous les blocs du billet « Guide de style de contenu » de votre blog DEV. En plus de les styliser, suivez ces directives pour éviter les problèmes d'UX courants mais subtils.

<h3 id="heading-anchors">1. Ancres de titre</h3>

Nous ajoutons automatiquement des ancres aux titres qui ont un attribut `id`.

HTML sans ID :

```html
<h1>My Heading</h1>
```

HTML avec ID :

```html
<h1 id="heading-anchor">
	<a href="#heading-anchor" class="heading-anchor"> My Heading </a>
</h1>
```

Ces ancres de titre doivent être **stylisées différemment** des autres liens. Par exemple, vous pouvez ajouter un `#` ou une image SVG (via `background-image`) avant le texte de l'ancre.

```css
h1,
h2,
h3,
h4,
h5,
h6 {
	a[href^='#'] {
		/* Remove usual link styles */
		color: inherit;
		text-decoration: none;
		position: relative;

		/* Add different styles */
		&:hover:before {
			content: '#';
			position: absolute;
			right: 100%;
			margin-right: 5px;
			color: var(--color-text-content-secondary);
		}
	}
}
```

<h3 id="code-blocks">2. Blocs de code</h3>

- Les blocs de code doivent avoir `tab-size: 4`
- Les blocs de code doivent avoir `direction: ltr`
- Les numéros de ligne doivent être positionnés en absolu
- Ajoutez un padding gauche lorsque les numéros de ligne sont activés (vérifiez `.has-line-numbers`).

Et, les numéros de ligne doivent être positionnés en absolu. Ajoutez également un padding gauche lorsque les numéros de ligne sont activés (vérifiez `.has-line-numbers`).

```css
pre {
	position: relative;
	tab-size: 4;
	direction: ltr;
	.line-number {
		margin-right: 1rem;
		position: absolute;
		left: 1rem;
	}
	&.has-line-numbers .line {
		padding-left: 2rem;
	}
}
```

<h3 id="tables">3. Tableaux</h3>

- `.table-container` doit avoir `overflow-x: auto` pour s'assurer que le tableau soit défilable sur les appareils mobiles

```css
.table-container {
	overflow-x: auto;
}
```

<h3 id="paragraphs">4. Paragraphes</h3>

Les marges doivent être gérées avec soin pour les paragraphes à l'intérieur de listes, tableaux et citations. Ce qui suit fonctionne bien dans la plupart des cas.

```css
li {
	p {
		margin-top: 0;
		&:last-child {
			margin-bottom: 0;
		}
	}
}
```

<h2 id="versioning">Versionnage</h2>

La première version du thème doit être `1.0.0`. Ensuite, vous pouvez incrémenter le numéro de version selon les changements que vous effectuez. Pour un correctif (ex : correction de bug), vous pouvez utiliser `1.0.1`, `1.0.2`, etc. Pour un changement mineur, vous pouvez utiliser `1.1.0`, `1.2.0`, etc. Contrairement aux autres logiciels, les thèmes n'ont pas de changements majeurs cassant la compatibilité. Par conséquent, nous ne pensons pas que vous aurez un jour besoin d'un changement de version majeure.

<h2 id="changelog">Journal des modifications</h2>

Ajoutez un fichier `CHANGELOG.md` à votre dossier de thème et ajoutez les modifications pour chaque version. Consultez <a href="https://keepachangelog.com/en/1.1.0/" rel="nofollow">keepachangelog.com</a> pour apprendre à rédiger un journal des modifications.

Une fois tout terminé, envoyez-nous une pull request vers le dépôt <a href="https://github.com/hyvor/hyvor-blogs-themes">hyvor-blogs-themes</a>. Lorsque la PR est fusionnée, la liste des thèmes se mettra automatiquement à jour avec votre nouveau thème.

<style lang="scss">
	.checklist-main {
		margin-bottom: 15px;
		margin-top: 15px;
	}

	.child-checkbox {
		margin-top: 5px;
		margin-left: 30px;
	}

	.checklist-main :global(.placeholder) {
		flex-shrink: 0;
	}

	#mult-line {
		display: flex;
		align-items: flex-start;
	}

	#mult-line .checklist-main-checkbox {
		margin-right: 8px;
	}
</style>

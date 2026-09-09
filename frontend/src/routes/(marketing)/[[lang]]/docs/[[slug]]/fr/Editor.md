<script lang="ts">
	import { DocsImage } from '@hyvor/design/marketing';
	import { Callout, Table, TableRow } from '@hyvor/design/components';
</script>

# Éditeur

Hyvor Blogs est livré avec un éditeur de texte enrichi qui prend en charge les styles en ligne et les blocs.

<h3 id="inline-styles">Styles en ligne</h3>

Pour ajouter des styles en ligne à un texte, sélectionnez le texte. Une fenêtre contextuelle s'affichera avec les options disponibles. Cliquez sur le style en ligne que vous souhaitez ajouter. Les styles en ligne suivants sont pris en charge.

- Gras
- Italique
- Barré
- Code en ligne

<DocsImage src="/images/docs/writing/inline-styles.png" alt="Styles en ligne" width={400} />

<h4 id="links">Liens</h4>

##### Ajout et suppression de liens

L'ajout de liens est similaire à l'ajout de [styles en ligne](#inline-styles). Sélectionnez le texte que vous souhaitez lier, puis cliquez sur l'icône de lien. Ensuite, collez l'URL dans le champ et appuyez sur Entrée.

<DocsImage src="/images/docs/writing/link-add.gif" alt="Ajout de liens" />

Pour supprimer un lien, sélectionnez le texte et cliquez sur l'icône de suppression.

<DocsImage src="/images/docs/writing/link-removal.gif" alt="Suppression de liens" />

##### Liens avec ancres

<!--text for anchor links-->

Pour ajouter facilement des liens d'ancrage, suivez les étapes ci-dessous.

1. Sélectionnez le texte que vous souhaitez lier et cliquez sur l'icône de lien
2. Cliquez sur « Ancres »
3. Choisissez l'ancre et cliquez dessus

<DocsImage src="/images/docs/writing/anchors.png" alt="Ajout de liens d'ancrage" width={400} />

<Callout type="info">
	{#snippet icon()}
		<div>💡</div>
	{/snippet}
	<p>
		La liste des ancres contient tous les titres de votre contenu, qu'ils aient un ID ou non.
	</p>
</Callout>

##### Lier des articles

Pour créer un lien vers un article du même blog, suivez ces étapes.

1. Sélectionnez le texte que vous souhaitez lier et cliquez sur l'icône de lien
2. Cliquez sur « Articles »
3. Recherchez l'article en tapant le titre, puis cliquez dessus

<DocsImage src="/images/docs/writing/post-link.png" alt="Lier des articles" width={400} />

<h4 id="markdown-inline-styles">Markdown pour les styles en ligne</h4>

Vous pouvez également utiliser des raccourcis Markdown pour créer des styles en ligne.

<Table columns="2fr 3fr" hover>
	<TableRow head>
		<div>Style en ligne</div>
		<div>Raccourci Markdown</div>
	</TableRow>

    <TableRow>
    	<div><a href="#links">Lien</a></div>
    	<div><code>[Ancre](https://example.com)</code></div>
    </TableRow>

    <TableRow>
    	<div><b>Gras</b></div>
    	<div><code>**texte**</code></div>
    </TableRow>

    <TableRow>
    	<div><i>Italique</i></div>
    	<div><code>*texte*</code></div>
    </TableRow>

    <TableRow>
    	<div><s>Barré</s></div>
    	<div><code>~~texte~~</code></div>
    </TableRow>

    <TableRow>
    	<div>Exposant<sup>script</sup></div>
    	<div><code>^texte^</code></div>
    </TableRow>

    <TableRow>
    	<div>Indice<sub>script</sub></div>
    	<div><code>~texte~</code></div>
    </TableRow>

</Table>

<h3 id="slash-command">Blocs</h3>

Le terme « blocs » désigne les éléments de type bloc que vous pouvez ajouter aux articles, tels que les paragraphes et les citations. Les paragraphes sont les blocs de base. Vous pouvez créer un paragraphe en appuyant sur `Enter` n'importe où dans l'éditeur.

Pour ajouter d'autres blocs, utilisez la commande slash : tapez la barre oblique (`/`) sur une nouvelle ligne pour ouvrir la liste des blocs. Utilisez la souris ou les flèches haut/bas pour naviguer dans la liste.

<DocsImage src="/images/docs/writing/blocks.gif" alt="Blocs" width={400} />

Hyvor Blogs prend en charge les blocs suivants.

- Paragraphe
- Séparateur
- [Titre](#headings)
- [Listes](#lists)
- [Citation](#quote)
- [Encadré](#callout)
- [Image](#image)
- [Intégration](#embed)
- [Signet de lien](#link-bookmark)
- [Bloc de code](#code-block)
- [HTML/Twig personnalisé](#custom-html)

<h4 id="headings">Titres</h4>

HB prend en charge les titres de `<h1>` à `<h6>`. La commande slash propose seulement deux options : Grand (h2) et Moyen (h3). Les autres titres peuvent être ajoutés en utilisant la syntaxe Markdown sur une nouvelle ligne.

- `#` + `espace` pour `h1`
- `##` + `espace` pour `h2`
- `###` + `espace` pour `h3`
- ...

<DocsImage src="/images/docs/writing/headings.gif" alt="Titres" width={400} />
<Callout type="info">
	<p>
		Veuillez noter que la raison pour laquelle le <b>Grand titre</b> utilise <code>{`<h2>`}</code>est que
		<code>{`<h1>`}</code> est réservé au titre de l'article dans votre thème. Cependant, vous pouvez utiliser h1 dans
		vos articles si nécessaire.
	</p>
</Callout>

<h5 id="heading-ids">IDs de titres</h5>

Il existe deux façons d'ajouter des IDs de titres.

<ol>
	<li>Placez le focus sur le champ ID en haut du titre et saisissez-y l'ID.</li>
	<DocsImage src="/images/docs/writing/heading-id.png" alt="ID de titre" width={400} />
	<li>
		Tapez un ID de titre au format Markdown (<code>{`{#heading-id}`}</code>) à la fin du titre.
	</li>
	<DocsImage src="/images/docs/writing/heading-id-markdown.gif" alt="ID de titre en Markdown" />
</ol>

<h4 id="lists">Listes</h4>

<Table columns="2fr 3fr" hover>
	<TableRow head>
		<div>Action</div>
		<div>Commande</div>
	</TableRow>

    <TableRow>
    	<div>Créer une liste non ordonnée</div>
    	<div><code>* + espace</code> ou <code>- + espace</code></div>
    </TableRow>

    <TableRow>
    	<div>Créer une liste ordonnée</div>
    	<div><code>1. + espace</code>, <code>2. + espace</code>, etc.</div>
    </TableRow>

    <TableRow>
    	<div>Nouvel élément de liste</div>
    	<div><code>Enter</code></div>
    </TableRow>

    <TableRow>
    	<div>Diminuer le retrait</div>
    	<div><code>Shift + Tab</code></div>
    </TableRow>

    <TableRow>
    	<div>Augmenter le retrait</div>
    	<div><code>Tab</code></div>
    </TableRow>

</Table>

<DocsImage src="/images/docs/writing/lists.gif" alt="Listes" width={400} />

<h4 id="quote">Citation</h4>

Le bloc Citation est généralement utilisé pour citer quelque chose, mais il est également utilisé de manière générale pour faire ressortir du texte. Vous pouvez créer un bloc Citation via la [commande slash](#slash-command) ou en saisissant `> + espace` sur une nouvelle ligne (syntaxe Markdown).

<DocsImage src="/images/docs/writing/quote.gif" alt="Citation" width={400} />

Vous pouvez sortir du bloc Citation en appuyant sur `Enter` sur une nouvelle ligne à l'intérieur du bloc Citation.

<h4 id="callout">Encadré</h4>

Le bloc Encadré est utilisé pour écrire un texte qui se distingue du reste du contenu de l'article. Vous pouvez définir un emoji ainsi que des couleurs de fond/police pour chaque bloc encadré.

<DocsImage src="/images/docs/writing/callout.gif" alt="Citation" width={400} />

<h4 id="image">Image</h4>

Pour ajouter des images, utilisez la [commande slash](#slash-command) (`/` sur une nouvelle ligne), et choisissez **Image**. Vous pouvez ajouter une image de l'une des façons suivantes :

<ul>
	<li>Téléverser depuis votre appareil</li>
	<DocsImage src="/images/docs/writing/image-upload.gif" alt="Téléversement d'image" />
	<li>Téléverser depuis une URL</li>
	<DocsImage src="/images/docs/writing/image-url.gif" alt="URL de l'image" />
	<li>Choisir depuis <a href="https://unsplash.com/" rel="nofollow">Unsplash</a></li>
	<DocsImage src="/images/docs/writing/unsplash.gif" alt="Unsplash" />
	<li>Choisir depuis la médiathèque</li>
	<DocsImage src="/images/docs/writing/media-lib.gif" alt="Médiathèque" />
</ul>

<h5 id="excalidraw">Excalidraw</h5>

Excalidraw est un outil permettant de créer des diagrammes et des dessins. Vous pouvez ajouter des dessins/modifications Excalidraw dans vos articles.

<DocsImage src="/images/docs/writing/excalidraw.gif" alt="Excalidraw" />

Pour les téléversements, la taille maximale de fichier est de **50 Mo**. Les formats suivants sont pris en charge.

- PNG - `.png`
- JPEG - `.jpg`, `.jpeg`, `.jfif`, `.pjpeg`, `.pjp`
- GIF - `.gif`
- APNG - `.apng`
- AVIF - `.avif`
- SVG - `.svg`
- WebP - `.webp`

<Callout type="info">
	<p>
		Les images téléversées depuis un appareil ou une URL sont automatiquement ajoutées à votre <a href="/docs/media"
			>médiathèque</a
		>, assurant ainsi leur disponibilité sans dépendre d'un service externe. Cependant, les images ajoutées via
		Unsplash sont hébergées sur unsplash.com et ne sont pas téléversées dans les médias du blog.
	</p>
</Callout>

Le nœud image vous permet également de :

- Ajouter une légende
- Ajouter un texte ALT
- Redimensionner l'image

<h4 id="embed">Intégration</h4>

Hyvor Blogs prend en charge l'intégration de contenu depuis Youtube, Twitter, Instagram, Facebook et d'autres plateformes. <a href="https://unfold.hyvor.com/" target="_blank">Hyvor Unfold</a> est utilisé en coulisses pour prendre en charge diverses plateformes. Vous pouvez ajouter une intégration à l'aide de la [commande slash](#slash-command) (`/` **sur une nouvelle ligne → Intégration**). Ensuite, collez l'URL que vous souhaitez intégrer dans le champ de saisie.

<DocsImage src="/images/docs/writing/embed.gif" alt="Intégration" />

Consultez les <a href="https://unfold.hyvor.com/#embed-platforms" target="_blank">plateformes prises en charge</a>.

<h4 id="link-bookmark">Signet de lien</h4>

Vous pouvez utiliser le bloc Signet de lien pour ajouter des aperçus de liens enrichis à vos articles. Pour insérer un signet de lien, tapez `/` **sur une nouvelle ligne → Signet de lien**. Ensuite, collez l'URL que vous souhaitez intégrer dans le champ de saisie et appuyez sur Entrée. Hyvor Blogs générera un aperçu enrichi du lien à l'aide des balises OG et d'autres métadonnées.

<DocsImage src="/images/docs/writing/link-bookmark.gif" alt="Signet de lien" />
<Callout type="info">
	<p>
		L'URL que vous saisissez doit être accessible publiquement pour générer un aperçu. Par exemple, vous ne pouvez pas
		générer de signets de lien pour des publications privées sur les réseaux sociaux.
	</p>
</Callout>

<h4 id="code-block">Bloc de code</h4>

Vous pouvez ajouter des blocs de code de deux façons :

- `/` **sur une nouvelle ligne → Bloc de code**
- Tapez ` ```lang ` ou ` ```js ` (avec le code de langage) sur une nouvelle ligne et appuyez sur `Enter`

<DocsImage src="/images/docs/writing/code-block.png" alt="Bloc de code" />

Consultez [Coloration syntaxique](/docs/syntax-highlighting) pour en savoir plus sur les langages pris en charge, les thèmes disponibles et l'utilisation des annotations.

<h4 id="custom-html">HTML/Twig personnalisé</h4>

Le bloc HTML/Twig personnalisé vous permet d'ajouter du code HTML ou Twig personnalisé à vos articles. Vous pouvez l'utiliser pour ajouter des éléments personnalisés à vos articles. Par exemple, vous pouvez ajouter un formulaire personnalisé, un widget personnalisé, etc. Vous pouvez utiliser les [variables](/docs/themes-templates#variables) Twig dans le code.

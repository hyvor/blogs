<script lang="ts">
	import { DocsImage } from '@hyvor/design/marketing';
	import { Callout, Table, TableRow } from '@hyvor/design/components';
</script>

# Rédaction

Apprenons à utiliser l'éditeur Hyvor Blogs et à publier votre **premier article !**

<!-- <ul>
    <li><a href="#posts-pages">Posts & Pages</a></li>
    <li>
        <a href="#editor">Editor</a>
        <ul>
            <li>
                <a href="#inline">Inline Styles</a> (bold, italic, etc.)
            </li>
            <li>
                <a href="#links">Links</a>
            </li>
            <li>
                <a href="#images">Blocks</a>
            </li>
        </ul>
    </li>
    <li>
        <a href="#post-metadata">Post Metadata</a> (authors, tag, custom code)
    </li>
    <li>
        <a href="#post-status">Post Status</a>
        <ul>
            <li><a href="#publishing">Publishing</a></li>
            <li><a href="#scheduling">Scheduling</a></li>
            <li><a href="#unpublishing">Unpublishing</a></li>
            <li><a href="#deleting">Deleting</a></li>
        </ul>
    </li>
    <li>
        <a href="#other-guides">Other Guides</a>
        <ul>
            <li><a href="#auto-saving">Auto-saving & Post History</a></li>
            <li><a href="#editing-published">Editing a published post</a></li>
            <li><a href="#multi-language">Multi-language posts</a></li>
        </ul>
    </li>
    <li><a href="#seo-analysis">SEO Analysis</a></li>
    <li><a href="#link-analysis">Link Analysis</a></li>
    <li><a href="#gpt-writing">GPT Writing</a></li>
</ul> -->

<h2 id="posts-pages">Articles & Pages</h2>

Hyvor Blogs prend en charge deux types de contenu : les **articles** et les **pages**. Dans la plupart des cas, vous utiliserez des articles. Les pages sont utilisées pour du contenu statique comme À propos, Contact, etc.

**Articles** :

- constituent la partie principale de votre blog, où vous partagez vos idées, réflexions et histoires.
- apparaissent sur la page d'index ou d'autres pages de collection
- peuvent être créés, modifiés et supprimés depuis Console → Articles
- ont des auteurs et des étiquettes

**Pages** :

- contiennent des informations statiques
- n'apparaissent pas sur la page d'index.
- sont généralement liées dans les navigations d'en-tête ou de pied de page.
- peuvent être créées, modifiées et supprimées depuis Console → Pages
- n'ont ni auteurs ni étiquettes

**Les deux** :

- sont identifiées de manière unique par le **slug**
- ont par défaut un permalien `/{slug}`. Voir [ceci](/docs/routes#permalinks) si vous souhaitez modifier les permaliens des articles/pages, par exemple pour inclure l'année et le mois dans l'URL.

<DocsImage src="/images/docs/writing/posts-pages-console.png" alt="Articles & Pages" width={400} />
<h2 id="editor">Éditeur</h2>

Hyvor Blogs est fourni avec un éditeur de texte enrichi qui prend en charge les styles en ligne et les blocs.

<h3 id="inline-styles">Styles en ligne</h3>

Pour ajouter des styles en ligne à du texte, sélectionnez le texte. Une fenêtre contextuelle s'affichera avec les options disponibles. Cliquez sur le style en ligne que vous souhaitez ajouter. Les styles en ligne suivants sont pris en charge.

- Gras
- Italique
- Barré
- Code en ligne

<DocsImage src="/images/docs/writing/inline-styles.png" alt="Styles en ligne" width={400} />

<h4 id="links">Liens</h4>

##### Ajout et suppression de liens

Ajouter des liens fonctionne de la même manière que l'ajout de [styles en ligne](/docs/writing#inline-styles). Sélectionnez le texte que vous voulez lier, puis cliquez sur l'icône Lien. Ensuite, collez l'URL dans le champ et appuyez sur Entrée.

<DocsImage src="/images/docs/writing/link-add.gif" alt="Ajout de liens" />

Pour supprimer un lien, sélectionnez le texte et cliquez sur l'icône de suppression.

<DocsImage src="/images/docs/writing/link-removal.gif" alt="Suppression de liens" />

##### Liens avec ancres

<!--text for anchor links-->

Pour ajouter facilement des liens d'ancrage, suivez les étapes ci-dessous.

1. Sélectionnez le texte que vous voulez lier et cliquez sur l'icône de lien
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

Pour lier un article du même blog, suivez ces étapes.

1. Sélectionnez le texte que vous voulez lier et cliquez sur l'icône de lien
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
    	<div><a href="/docs/writing#links">Lien</a></div>
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
    	<div>Exposant<sup>ent</sup></div>
    	<div><code>^texte^</code></div>
    </TableRow>

    <TableRow>
    	<div>Indice<sub>ent</sub></div>
    	<div><code>~texte~</code></div>
    </TableRow>

</Table>

<h3 id="slash-command">Blocs</h3>

Le terme « blocs » désigne les éléments de type bloc que vous pouvez ajouter aux articles, comme les paragraphes et les citations. Les paragraphes sont les blocs de base. Vous pouvez créer un paragraphe en appuyant sur `Entrée` n'importe où dans l'éditeur.

Pour ajouter d'autres blocs, utilisez la commande slash : tapez une barre oblique (`/`) sur une nouvelle ligne pour ouvrir la liste des blocs. Utilisez la souris ou les flèches haut/bas pour naviguer dans la liste.

<DocsImage src="/images/docs/writing/blocks.gif" alt="Blocs" width={400} />

Hyvor Blogs prend en charge les blocs suivants.

- Paragraphe
- Séparateur
- [Titre](/docs/writing#headings)
- [Listes](/docs/writing#lists)
- [Citation](/docs/writing#quote)
- [Encadré](/docs/writing#callout)
- [Image](/docs/writing#image)
- [Intégration](/docs/writing#embed)
- [Signet de lien](/docs/writing#link-bookmark)
- [Bloc de code](/docs/writing#code-block)
- [HTML/Twig personnalisé](/docs/writing#custom-html)

<h4 id="headings">Titres</h4>

HB prend en charge les titres de `<h1>` à `<h6>`. La commande slash ne propose que deux options : Grand (h2) et Moyen (h3). D'autres titres peuvent être ajoutés à l'aide de la syntaxe Markdown sur une nouvelle ligne.

- `#` + `espace` pour `h1`
- `##` + `espace` pour `h2`
- `###` + `espace` pour `h3`
- ...

<DocsImage src="/images/docs/writing/headings.gif" alt="Titres" width={400} />
<Callout type="info">
	<p>
		Veuillez noter que la raison pour laquelle le <b>Grand titre</b> utilise <code>{`<h2>`}</code> est que
		<code>{`<h1>`}</code> est réservé au titre de l'article dans votre thème. Cependant, vous pouvez utiliser h1 dans
		vos articles si nécessaire.
	</p>
</Callout>

<h5 id="heading-ids">ID des titres</h5>

Il existe deux façons d'ajouter des ID de titre.

<ol>
	<li>Cliquez sur le champ ID en haut du titre et saisissez-y l'ID.</li>
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
    	<div><code>Entrée</code></div>
    </TableRow>

    <TableRow>
    	<div>Diminuer l'indentation</div>
    	<div><code>Maj + Tab</code></div>
    </TableRow>

    <TableRow>
    	<div>Augmenter l'indentation</div>
    	<div><code>Tab</code></div>
    </TableRow>

</Table>

<DocsImage src="/images/docs/writing/lists.gif" alt="Listes" width={400} />

<h4 id="quote">Citation</h4>

Le bloc Citation est généralement utilisé pour citer quelque chose, mais il sert aussi souvent à mettre du texte en valeur. Vous pouvez créer un bloc Citation via la [commande slash](/docs/writing#slash-command) ou en tapant `> + espace` sur une nouvelle ligne (syntaxe Markdown).

<DocsImage src="/images/docs/writing/quote.gif" alt="Citation" width={400} />

Vous pouvez sortir du bloc Citation en appuyant sur `Entrée` sur une nouvelle ligne à l'intérieur du bloc Citation.

<h4 id="callout">Encadré</h4>

Le bloc Encadré sert à écrire du texte qui se démarque du reste du contenu de l'article. Vous pouvez définir un emoji ainsi que des couleurs d'arrière-plan/police pour chaque bloc encadré.

<DocsImage src="/images/docs/writing/callout.gif" alt="Citation" width={400} />

<h4 id="image">Image</h4>

Pour ajouter des images, utilisez la [commande slash](/docs/writing#slash-command) (`/` sur une nouvelle ligne) et choisissez **Image**. Vous pouvez ajouter une image de l'une des façons suivantes :

<ul>
	<li>Téléverser depuis votre appareil</li>
	<DocsImage src="/images/docs/writing/image-upload.gif" alt="Téléversement d'image" />
	<li>Téléverser depuis une URL</li>
	<DocsImage src="/images/docs/writing/image-url.gif" alt="URL de l'image" />
	<li>Choisir depuis <a href="https://unsplash.com/" rel="nofollow">Unsplash</a></li>
	<DocsImage src="/images/docs/writing/unsplash.gif" alt="Unsplash" />
	<li>Choisir depuis la Médiathèque</li>
	<DocsImage src="/images/docs/writing/media-lib.gif" alt="Médiathèque" />
</ul>

<h5 id="excalidraw">Excalidraw</h5>

Excalidraw est un outil permettant de créer des diagrammes et des dessins. Vous pouvez ajouter des dessins/modifications Excalidraw dans vos articles.

<DocsImage src="/images/docs/writing/excalidraw.gif" alt="Excalidraw" />

Pour les téléversements, la taille de fichier maximale est de **50 Mo**. Les formats suivants sont pris en charge.

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
		> assurant leur disponibilité sans dépendre d'un service externe. Cependant, les images ajoutées via
		Unsplash sont hébergées sur unsplash.com et ne sont pas téléversées dans les médias du blog.
	</p>
</Callout>

Le nœud d'image vous permet également de :

- Ajouter une légende
- Ajouter un texte ALT
- Redimensionner l'image

<h4 id="embed">Intégration</h4>

Hyvor Blogs prend en charge l'intégration de contenu provenant de Youtube, Twitter, Instagram, Facebook et d'autres plateformes. <a href="https://unfold.hyvor.com/" target="_blank">Hyvor Unfold</a> est utilisé en coulisses pour prendre en charge diverses plateformes. Vous pouvez ajouter une intégration à l'aide de la [commande slash](/docs/writing#slash-command) (`/` **sur une nouvelle ligne → Intégration**). Ensuite, collez l'URL que vous souhaitez intégrer dans le champ de saisie.

<DocsImage src="/images/docs/writing/embed.gif" alt="Intégration" />

Voir les <a href="https://unfold.hyvor.com/#embed-platforms" target="_blank">plateformes prises en charge</a>.

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
- Tapez ` ```lang ` ou ` ```js ` (avec le code de langage) sur une nouvelle ligne et appuyez sur `Entrée`

<DocsImage src="/images/docs/writing/code-block.png" alt="Bloc de code" />

Consultez [Coloration syntaxique](/docs/syntax-highlighting) pour en savoir plus sur les langages pris en charge, les thèmes disponibles et l'utilisation des annotations.

<h4 id="custom-html">HTML/Twig personnalisé</h4>

Le bloc HTML/Twig personnalisé vous permet d'ajouter du code HTML ou Twig personnalisé à vos articles. Vous pouvez l'utiliser pour ajouter des éléments personnalisés à vos articles. Par exemple, vous pouvez ajouter un formulaire personnalisé, un widget personnalisé, etc. Vous pouvez utiliser des [variables](/docs/themes-templates#variables) Twig dans le code.

<h2 id="metadata">Métadonnées de l'article</h2>

Cliquez sur le bouton Paramètres dans l'éditeur pour ouvrir les paramètres de l'article.

<DocsImage src="/images/docs/writing/post-meta-data.png" alt="Métadonnées de l'article" width={400} />

Vous pouvez configurer les paramètres suivants pour un article :

- **Slug** - Un identifiant unique et convivial pour l'URL de l'article. Si vous ne définissez pas de slug, Hyvor Blogs en générera automatiquement un basé sur le titre de l'article lors de sa publication.
- **Heure de publication** - L'heure de publication sera ajoutée automatiquement lors de la publication de l'article. Cependant, vous pouvez définir une heure de publication personnalisée pour un article.
- **Auteurs** - Vous pouvez ajouter des [utilisateurs](/docs/users) de votre blog en tant qu'auteurs d'un article. Par défaut, le créateur de l'article est ajouté comme auteur de l'article. Les utilisateurs disposant de [permissions de niveau éditeur](/docs/users#roles) peuvent ajouter ou supprimer des auteurs d'un article. Un article peut avoir plusieurs auteurs.
- **Étiquettes** - Vous pouvez attribuer une ou plusieurs [étiquettes](/docs/tags) à un article.
- **Description** - Une brève description de l'article. Elle sera utilisée comme méta-description de l'article pour les moteurs de recherche et les réseaux sociaux.
- **Image à la une** - L'image à la une de l'article. Elle sera utilisée comme méta-image de l'article pour les moteurs de recherche et les réseaux sociaux.

Dans les paramètres avancés, vous pouvez ajouter les éléments suivants :

<DocsImage
	src="/images/docs/writing/post-metadata-advanced.png"
	alt="Métadonnées avancées de l'article"
	width={400}
/>

- **URL canonique** - Si vous avez publié le même article à un autre emplacement, vous pouvez ajouter ici l'URL canonique de l'article.
- **Code d'en-tête** - Code personnalisé à ajouter juste avant la balise `</head>` de l'article.
- **Code de pied de page** - Code personnalisé à ajouter juste avant la balise `</body>` de l'article.

<Callout type="info">
	<p>
		Consultez la documentation sur le <a href="/docs/custom-code">code personnalisé</a> pour plus d'informations sur les différentes façons
		d'ajouter du code personnalisé à votre blog.
	</p>
</Callout>

<h2 id="status">Statut de l'article</h2>

Un article peut avoir l'un des statuts suivants :

- **Brouillon** - Non visible par le public. Seuls les [utilisateurs](/docs/users) de votre blog peuvent voir l'article dans la Console.
- **Planifié** - Non visible par le public. Il sera publié automatiquement à l'heure spécifiée.
- **Publié** - Visible par le public.

<h3 id="publishing">Publication</h3>

Une fois que vous avez terminé de rédiger votre article, vous pouvez le publier. Une fois publié, l'article apparaîtra publiquement sur votre blog.

**Article → Publier**

<DocsImage src="/images/docs/writing/publishing.gif" alt="Publication" />

<h3 id="scheduling">Planification</h3>

Vous pouvez également planifier l'article à une date et une heure spécifiques. Hyvor Blogs publiera automatiquement votre article à l'heure indiquée.

**Article → Publier → Publier plus tard → Planifier**

<DocsImage src="/images/docs/writing/publish-schedule.gif" alt="Planification" />

<h3 id="unpublishing">Dépublication</h3>

Vous pouvez dépublier un article publié. Le statut de l'article passera à Brouillon. Il n'apparaîtra donc plus sur le blog. Vous pourrez le republier ultérieurement.

**Article → Dépublier**

<DocsImage src="/images/docs/writing/unpublish.gif" alt="Dépublication" />

<h3 id="deleting">Suppression</h3>

Vous pouvez également supprimer définitivement un article. Notez qu'il n'est pas possible de restaurer un article après suppression. Envisagez de le dépublier si vous souhaitez simplement le masquer de votre blog.

**Article → Paramètres → Supprimer l'article**

<DocsImage src="/images/docs/writing/delete.gif" alt="Suppression" />

<h2 id="other-guides">Autres guides</h2>
<h3 id="auto-saving">Enregistrement automatique et historique des articles</h3>

Si vous modifiez un brouillon, Hyvor Blogs enregistrera automatiquement votre article toutes les 15 secondes si des données de l'article (contenu ou métadonnées) ont été modifiées. Vous pouvez également enregistrer manuellement votre article en appuyant sur **Ctrl + S**. Vérifiez le coin inférieur droit de l'éditeur pour voir le statut de l'enregistrement automatique.

<DocsImage src="/images/docs/writing/saving.gif" alt="Enregistrement automatique" />

Si le contenu est modifié, un historique de l'article sera créé. Vous pouvez consulter cet historique si vous souhaitez revenir à une version précédente de votre article. Un article peut avoir jusqu'à 25 historiques.

<h3 id="editing-published">Modifier un article publié</h3>

Vous pouvez apporter des modifications au contenu d'un article publié à tout moment. Cependant, les modifications ne seront pas visibles par le public tant que vous n'aurez pas publié les changements.

<DocsImage src="/images/docs/writing/editor.png" alt="Éditeur" />

1. **Mettre à jour** - Publie les modifications de l'article. L'article sera mis à jour immédiatement.
2. **Annuler** - Annule les modifications et revient à la version publiée de l'article.
3. **Édition publiée** - Ce message s'affichera si vous modifiez un article publié.
4. Ce message peut être l'un des suivants :
   - **Non enregistré** - Les modifications apportées à l'article ne sont pas encore enregistrées. Appuyez sur **Ctrl + S** pour enregistrer les modifications. Sinon, elles seront enregistrées automatiquement dans 15 secondes.
   - **Enregistré** - Toutes les modifications sont enregistrées

<Callout type="info">
	<p>Notez que les modifications de métadonnées seront enregistrées immédiatement et seront visibles par le public.</p>
</Callout>

<h3 id="multi-language">Articles multilingues</h3>

Si vous avez configuré plusieurs langues pour votre blog, vous verrez les codes de langue en haut de l'éditeur d'articles. Cliquez sur un code de langue pour passer à cette variante linguistique de l'article. Chaque variante doit être publiée séparément. Consultez notre guide sur les [langues](/docs/languages), qui explique tout ce que vous devez savoir sur la publication d'articles multilingues.

<h2 id="seo-analysis">Analyse SEO</h2>

L'outil d'analyse SEO dans l'éditeur d'articles vous donnera des suggestions pour améliorer le référencement de votre article. Il fonctionne selon des règles prédéfinies, inspirées du plugin WordPress <a href="https://rankmath.com/kb/score-100-in-tests" rel="nofollow">Rank Math</a>.

<DocsImage src="/images/docs/writing/seo.png" alt="Analyse SEO" width={400} />
<Callout type="info">
	<p><b>Important !</b></p>

    <p>
    	L'analyse SEO n'est <b>qu'une suggestion</b>. Obtenir un score plus élevé ne suffira pas à faire
    	remonter vos articles dans les résultats. D'autres facteurs influencent également votre SEO, comme les backlinks, l'autorité
    	de domaine, etc. Cependant, ces suggestions vous aideront à mieux optimiser votre contenu pour les
    	moteurs de recherche.
    </p>

</Callout>

Pour commencer à analyser votre article, ajoutez un mot-clé principal pour votre article. Vous pouvez également ajouter des mots-clés secondaires. Hyvor Blogs analysera alors le contenu et les métadonnées de votre article et vous donnera des suggestions en temps réel pour améliorer le SEO de votre article.

Voici les tests que Hyvor Blogs effectuera sur votre article :

<ul>
	<li><b>Mot-clé principal dans le titre</b></li>
	<ul>
		<li>100 % si le mot-clé principal est au début du titre</li>
		<li>75 % si le mot-clé principal se trouve dans les 50 premiers caractères du titre</li>
		<li>49 % si le mot-clé principal se trouve après les 50 premiers caractères du titre</li>
		<li>0 % si le mot-clé principal n'est pas dans le titre</li>
	</ul>

    <li><b>Mot-clé principal dans la description</b></li>
    <li><b>Mot-clé principal dans le slug</b></li>
    <p>
    	Si le mot-clé principal est <code>blogging platforms</code>, nous vérifions la présence de
    	<code>blogging-platforms</code>
    	dans le slug. Il est recommandé de définir un <b>slug court avec des tirets</b>. Dans ce cas, le score
    	sera :
    </p>
    <ul>
    	<li>100 % si le slug correspond exactement à <code>blogging-platforms</code></li>
    	<li>75 % si le slug contient <code>blogging-platforms</code> avec d'autres mots</li>
    </ul>

    <li><b>Mot-clé principal au début du contenu</b></li>
    <p>
    	Si votre contenu compte plus de 300 mots, le mot-clé principal doit se trouver dans les 10 premiers pour cent du
    	contenu. S'il compte moins de 300 mots, il doit se trouver quelque part dans le contenu.
    </p>

    <li><b>Longueur du contenu</b></li>
    <p>(La longueur idéale du contenu dépend du sujet, ce qui n'est pas pris en compte ici)</p>
    <ul>
    	<li>0 % pour moins de 400 mots</li>
    	<li>1 % pour chaque tranche de 25 mots après 400 mots (2500+ mots = 100 %)</li>
    </ul>

    <li><b>Tous les mots-clés dans le contenu</b></li>
    <p>Tous les mots-clés doivent être présents dans le contenu de l'article.</p>

    <li><b>Tous les mots-clés dans les sous-titres</b></li>
    <p>Chaque mot-clé doit être présent dans au moins un sous-titre (h2, h3, h4, h5, h6).</p>

    <li><b>Tous les mots-clés dans les attributs alt des images</b></li>
    <p>Chaque mot-clé doit être présent dans au moins un attribut alt d'image.</p>

    <li><b>Densité de mots-clés</b></li>
    <p>Vérifie la densité des mots-clés dans le contenu (<code>nombre de mots-clés / nombre total de mots</code>).</p>
    <ul>
    	<li>0 % pour moins de 0,1 %</li>
    	<li>50 % pour 0,1 % à 0,5 %</li>
    	<li>100 % pour 0,5 % à 2,5 %</li>
    	<li>50 % pour 2,5 % à 5 %</li>
    	<li>0 % pour plus de 5 %</li>
    </ul>

    <li><b>Longueur du slug</b></li>
    <p>Des slugs plus courts sont préférables. Ce test réussira si le slug fait moins de 50 caractères.</p>

    <li><b>Liens externes</b></li>
    <p>Au moins un lien externe doit être présent dans l'article.</p>

    <li><b>Liens internes</b></li>
    <p>
    	Au moins un lien interne doit être présent dans l'article. Les liens vers tout sous-domaine de votre
    	domaine principal seront considérés comme des liens internes. Voir les <a href="/docs/writing#link-types"
    		>types de liens</a
    	>
    	pour plus d'informations. Les liens <code>internal-blog</code>, <code>internal-domain</code>, et
    	<code>internal-root-domain</code> sont considérés comme des liens internes.
    </p>

    <li><b>Images</b></li>
    <ul>
    	<li>70 % - 1 image</li>
    	<li>80 % - 2 images</li>
    	<li>90 % - 3 images</li>
    	<li>100 % - 4 images ou plus</li>
    </ul>

    <li><b>Toutes les images ont des attributs alt</b></li>
    <p>Toutes les images doivent avoir des attributs alt</p>

</ul>

<h2 id="link-analysis">Analyse des liens</h2>

L'outil d'analyse des liens dans l'éditeur d'articles analyse l'état des liens de votre article au fur et à mesure que vous écrivez. Il vous affichera un avertissement s'il y a des liens rompus, risqués ou de redirection dans votre article. Il vous montre également le [type de chaque lien](/docs/writing#link-types).

<DocsImage src="/images/docs/writing/link-analysis.png" alt="Analyse des liens" width={400} />
<h3 id="link-types">Types de liens</h3>

Hyvor Blogs classe les liens dans les types suivants.

<Table columns="2fr 3fr" hover>
	<TableRow head>
		<div>Type de lien</div>
		<div>Description</div>
	</TableRow>

    <TableRow>
    	<div><code>internal-blog</code></div>
    	<div>Liens vers d'autres articles/pages de votre blog</div>
    </TableRow>

    <TableRow>
    	<div><code>internal-domain</code></div>
    	<div>Liens vers le même domaine que votre blog, mais pas vers votre blog</div>
    </TableRow>

    <TableRow>
    	<div><code>internal-root-domain</code></div>
    	<div>Liens vers un domaine du domaine racine, mais pas vers le domaine de votre blog</div>
    </TableRow>

    <TableRow>
    	<div><code>external</code></div>
    	<div>Liens vers d'autres domaines</div>
    </TableRow>

    <TableRow>
    	<div><code>mail</code></div>
    	<div>Liens mailto (commence par <code>mailto:</code>)</div>
    </TableRow>

    <TableRow>
    	<div><code>tel</code></div>
    	<div>Liens tel (commence par <code>tel:</code>)</div>
    </TableRow>

    <TableRow>
    	<div><code>anchor</code></div>
    	<div>Liens vers des ancres sur la même page (commence par <code>#</code>)</div>
    </TableRow>

    <TableRow>
    	<div><code>other</code></div>
    	<div>Autres liens (<code>ftp:</code>, <code>data:</code>, javascript, etc.)</div>
    </TableRow>

</Table>

Accédez à **Console → Outils → Analyse des liens** pour

- voir un aperçu de tous les liens de votre blog
- consulter les résultats de l'analyse bimensuelle des liens
- modifier les paramètres des rapports par e-mail

<h3 id="link-analysis-accuracy">Précision de l'analyse des liens</h3>

Notre analyseur de liens est simple : il envoie des requêtes HTTP via curl pour vérifier l'état des liens. Cette approche permet une analyse rapide et précise. Cependant, certains serveurs et pare-feu peuvent bloquer ces requêtes, ce qui peut entraîner de faux positifs. Si vous trouvez un lien marqué comme rompu alors qu'il fonctionne réellement, vous pouvez cliquer sur le bouton ignorer pour qu'il ne soit plus pris en compte lors des analyses futures.

<h2 id="gpt-writing">Rédaction avec GPT</h2>

Nous avons intégré GPT 3.5 directement dans l'éditeur pour vous aider dans des tâches de génération de contenu par IA telles que

- Générer un plan de blog
- Rédiger un article de blog
- Rédiger un article
- et plus encore...

Vous pouvez utiliser les prompts par défaut dans la plupart des cas. Cependant, vous pouvez également personnaliser les prompts pour obtenir de meilleurs résultats.

<DocsImage src="/images/docs/writing/ai.gif" alt="Rédaction avec GPT" />

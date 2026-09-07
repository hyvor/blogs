<script lang="ts">
	import { DocsImage } from '@hyvor/design/marketing';
	import { Callout, Table, TableRow } from '@hyvor/design/components';
</script>

# Rédaction (Bases)

Apprenons à utiliser l'éditeur Hyvor Blogs et à publier votre **premier article !**.

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

<h2 id="gpt-writing">Rédaction avec GPT</h2>

Nous avons intégré GPT 3.5 directement dans l'éditeur pour vous aider dans des tâches de génération de contenu par IA telles que

- Générer un plan de blog
- Rédiger un article de blog
- Rédiger un article
- et plus encore...

Vous pouvez utiliser les prompts par défaut dans la plupart des cas. Cependant, vous pouvez également personnaliser les prompts pour obtenir de meilleurs résultats.

<DocsImage src="/images/docs/writing/ai.gif" alt="Rédaction avec GPT" />

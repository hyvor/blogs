<script>
	import { DocsImage } from '@hyvor/design/marketing';
</script>

# Tags

Les tags peuvent être utilisés pour **regrouper des articles similaires**. Vous pouvez attribuer un ou plusieurs tags à un article.

<h2 id="tag-index">Page d'index des tags</h2>

Chaque tag crée une page d'index (`/tag/{slug}`), qui liste les articles de ce tag. Cela facilite la navigation des utilisateurs parmi les articles d'un tag spécifique. Vous pouvez modifier l'URL de base des pages d'index des tags (`/tag/`) en [modifiant les routes](/docs/routes#customizing-other).

<h2 id="assign">Attribuer un tag à un article</h2>

Vous pouvez attribuer un tag à un article dans l'éditeur d'articles. Dans les paramètres de l'article, cliquez sur l'icône + à côté du champ des tags pour attribuer un tag. Ensuite, saisissez le nom du tag que vous souhaitez attribuer. Si le tag existe, il sera attribué à l'article. Sinon, vous pouvez créer un nouveau tag.

<DocsImage src="/images/docs/tags/tag-assign.png" alt="Attribuer un tag à un article" width={400} />

<h2 id="create">Créer un tag</h2>

Il existe deux façons de créer un tag :

1. [Dans l'éditeur d'articles](#create-in-post-editor)
2. [Dans les paramètres des tags](#create-in-settings)

<h3 id="create-in-post-editor">Créer un tag dans l'éditeur d'articles</h3>

Vous pouvez facilement créer un nouveau tag pendant la rédaction d'un article. Dans les paramètres de l'article, cliquez sur l'icône + à côté du champ des tags pour [attribuer un tag](#assign). Ensuite, saisissez le nom du tag que vous souhaitez créer. Cliquez ensuite sur le bouton **Créer un tag**.

<DocsImage src="/images/docs/tags/tag-post-create.png" alt="Créer un tag dans l'éditeur d'articles" />

<h3 id="create-in-settings">Créer un tag dans les paramètres des tags</h3>

Vous pouvez également créer un tag dans **Paramètres → Tags**.

<DocsImage src="/images/docs/tags/tags-create-settings.png" alt="Créer un tag dans les paramètres des tags" />

<h2 id="private">Tags privés</h2>

Les tags privés ne sont pas affichés publiquement sur votre blog. Ils sont utilisés uniquement à des fins internes. Par exemple, vous pouvez utiliser un tag `members-only` pour marquer les articles visibles uniquement par les membres en modifiant le code de votre thème, sans vouloir afficher ce tag sur la page d'index des tags.

Pour rendre un tag privé, cochez la case **Privé** dans les paramètres du tag lors de sa création ou de sa modification.

<DocsImage src="/images/docs/tags/tags-private.png" alt="Tags privés" width={600} />

<h2 id="update">Modifier les paramètres d'un tag</h2>

Vous pouvez modifier quelques paramètres pour un tag dans **Paramètres → Tags**.

- **Nom** : Le nom du tag. C'est ce que les utilisateurs voient.
- **Description** : Une courte description du tag. Elle peut être affichée sur la page d'index des tags.
- **Slug** : Le slug de la page d'index du tag.
- **Code personnalisé** : Le code personnalisé que vous ajoutez ici sera ajouté aux **articles de ce tag**, et non à la page d'index des tags.

<DocsImage src="/images/docs/tags/tag-edit.png" alt="Modifier un tag" width={400} />

<h2 id="delete">Supprimer un tag</h2>

Vous pouvez supprimer un tag dans **Paramètres → Tags**. Ce tag sera désassocié de tous les articles.

<DocsImage src="/images/docs/tags/tag-delete.png" alt="Supprimer un tag" width={400} />

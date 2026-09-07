<script lang="ts">
	import { DocsImage } from '@hyvor/design/marketing';
	import IconBrush from '@hyvor/icons/IconBrush';
	import IconPencil from '@hyvor/icons/IconPencil';

	import { Callout, Divider, Tag } from '@hyvor/design/components';
</script>

# Code personnalisé

Vous pouvez ajouter du code personnalisé pour personnaliser votre blog, ajouter des styles ou intégrer des services tiers comme les outils d'analyse.

- Tout code personnalisé peut contenir du code HTML ou Twig et a accès aux [variables de thème](/docs/themes-templates#variables).
- Le code d'en-tête sera ajouté avant la balise `</head>`.
- Le code de pied de page sera ajouté avant la balise `</body>`.
- Il existe trois façons d'ajouter du code personnalisé à votre blog :
  - **Blog** - ajouter du code à toutes les pages de votre blog.
  - **Article** - ajouter du code à un article spécifique.
  - **Tag** - ajouter du code à tous les articles ayant un tag spécifique.
- Le code personnalisé est ajouté dans l'ordre **Blog -> Article -> Tag**.

<h2 id="blog">1. Code personnalisé du blog</h2>

Vous pouvez ajouter du code personnalisé à l'ensemble du blog dans **Paramètres → Code personnalisé**. Ainsi, le code personnalisé sera ajouté à tous les modèles de votre blog. Cela est utile pour ajouter des services comme les outils d'analyse.

<DocsImage src="/images/docs/custom-code/custom-code-blog.png" alt="Code personnalisé dans les paramètres du blog" />

<h2 id="post">2. Code personnalisé d'un article</h2>

Vous pouvez également ajouter du code personnalisé à un article spécifique uniquement dans les **paramètres avancés de l'éditeur d'article**. Le code personnalisé ne sera alors ajouté qu'à cet article.

<DocsImage src="/images/docs/custom-code/custom-code-post.png" alt="Code personnalisé dans les paramètres de l'article" />

<h2 id="tag">3. Code personnalisé d'un tag</h2>

Vous pouvez également définir un code personnalisé pour un tag dans **Paramètres → Tags → <IconPencil /> → Code personnalisé**. Ce code personnalisé sera alors ajouté aux articles ayant ce tag (et non à la page du tag elle-même).

Par exemple, les articles ayant des animations SVG auront besoin d'une bibliothèque Javascript supplémentaire pour lire les animations SVG. Vous pouvez attribuer un tag « svg » à ces articles et créer un lien vers la bibliothèque Javascript dans le code personnalisé de ce tag.

<DocsImage src="/images/docs/custom-code/custom-code-tag.png" alt="Code personnalisé dans les paramètres du tag" />

<Divider margin={30} color="var(--border)" />

<Callout type="info">
	{#snippet icon()}
		<IconBrush />
	{/snippet}
	Vous pouvez également <a href="/docs/themes#editing">modifier votre thème</a> pour ajouter du code personnalisé à votre blog.
</Callout>

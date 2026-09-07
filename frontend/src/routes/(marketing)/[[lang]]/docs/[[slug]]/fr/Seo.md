<script lang="ts">
	import { Table, TableRow } from '@hyvor/design/components';
	import { DocsImage } from '@hyvor/design/marketing';
</script>

# SEO

Hyvor Blogs gère le SEO technique pour vous, vous permettant de vous concentrer sur l'essentiel -
l'écriture. Certaines fonctionnalités SEO peuvent être configurées dans <strong>Console &rarr; Paramètres &rarr; SEO</strong>.

<h2 id="meta">Balises Meta</h2>

Hyvor Blogs ajoute automatiquement des balises meta à votre blog. Elles aident les moteurs de recherche à mieux comprendre
votre blog et les réseaux sociaux à mieux afficher votre blog.

<h3 id="basic">Balises Meta de base</h3>

Ce sont les balises meta de base qui sont ajoutées à toutes les pages de votre blog.

```html
<title>My Blog</title>
<meta name="description" content="My Blog Description" />
<link rel="canonical" href="https://myblog.hyvorblogs.io" />
```

<h3 id="language-variants">Variantes linguistiques</h3>

Si vous avez [configuré plusieurs langues](/docs/languages), Hyvor Blogs
ajoutera automatiquement des balises `hreflang` aux pages d'index et de post.

```html
<link rel="alternate" href="https://myblog.hyvorblogs.io/fr" hreflang="fr" />
<link rel="alternate" href="https://myblog.hyvorblogs.io/es" hreflang="es" />
```

<h3 id="facebook-twitter-tags">Balises Facebook et Twitter</h3>

Ces balises aident les réseaux sociaux à générer des aperçus enrichis de votre blog et de vos posts.

```html
<!-- FACEBOOK (OG) -->
<meta property="og:site_name" />
<meta property="og:type" />
<meta property="og:title" />
<meta property="og:locale" />
<meta property="og:description" />
<meta property="og:url" />
<meta property="og:image" />
<!-- For Posts -->
<meta property="article:published_time" />
<meta property="article:modified_time" />
<meta property="article:author" />
<!-- Authors -->
<meta property="article:author" />
<meta property="article:section" />
<!-- Tags -->
<meta property="article:section" />

<!-- TWITTER -->
<meta name="twitter:card" />
<meta name="twitter:title" />
<meta name="twitter:description" />
<meta name="twitter:url" />
<meta name="twitter:image" />
<meta name="twitter:site" />
<!-- only if Twitter URL is set in blog settings -->
<meta name="twitter:creator" />
<!-- only if Twitter URL is set for the primary author -->
```

<h2 id="rich-schema">Schéma enrichi</h2>

Un schéma enrichi de <a href="https://developers.google.com/search/docs/appearance/structured-data/article" target="_blank" rel="nofollow">BlogPosting</a> est ajouté à tous les posts. Cela aide les moteurs de recherche à mieux comprendre vos posts et à les afficher
de manière plus pertinente dans les résultats de recherche.

```html
<script type="application/ld+json">
	{
		"@context": "https://schema.org",
		"@type": "BlogPosting",
		"headline": "How to start a blog",
		"datePublished": "2024-01-01T00:00:00Z",
		"dateModified": "2024-01-01T00:00:00Z",
		"author": [
			{
				"type": "@Person",
				"name": "John Doe",
				"url": "https://blog.hyvorblogs.io/author/john-doe"
			}
		],
		"image": ["https://blog.hyvorblogs.io/media/how-to-start-a-blog.png"]
	}
</script>
```

Le schéma enrichi est ajouté à tous les posts par défaut, mais vous pouvez le désactiver dans <strong>Console &rarr; Paramètres &rarr; SEO</strong>.

<DocsImage
	src="/images/docs/seo/rich-schema-tags.png"
	alt="Balises de schéma enrichi"
	style="max-height:400px"
/>

<h2 id="canonical">URLs canoniques</h2>

Hyvor Blogs suit un principe en ce qui concerne les URLs canoniques : <strong>une URL pour un contenu</strong>. Un post ne sera accessible qu'à partir d'une seule URL. Ce principe de base vous aide à éviter les problèmes courants
de contenu dupliqué et aide les moteurs de recherche à mieux comprendre votre blog.

<h3 id="custom-post-canonical">URLs canoniques personnalisées pour les posts</h3>

Dans certains cas, vous pouvez vouloir publier un post déjà publié ailleurs sur votre blog. Dans de tels
cas, vous pouvez définir une URL canonique personnalisée pour le post dans l'éditeur de post.

<DocsImage
	src="/images/docs/seo/canonical-setting.png"
	alt="Paramètre d'URL canonique"
	style="max-height:400px"
/>

<h2 id="robots">Robots.txt</h2>

Robots.txt est un fichier qui indique aux robots des moteurs de recherche quelles pages accéder ou non. Hyvor Blogs
est livré avec un robots.txt par défaut, qui devrait suffire pour la plupart des blogs.

```yaml
User-agent: *
Sitemap: {{ _blog.base_url }}/sitemap.xml
Disallow: /p/
```

Le fichier robots.txt par défaut est le suivant :

- `User-agent: *` - permet à tous les robots d'accéder à votre blog.
- `Sitemap: {{ _blog.base_url }}/sitemap.xml` - indique aux robots où trouver le sitemap.
- `Disallow: /p/` - empêche les robots d'accéder aux routes /p/ (pages d'aperçu de post).

Vous pouvez mettre à jour votre fichier robots.txt dans **Paramètres → SEO → Robots.txt**. Vous
pouvez également y utiliser des [variables de thème](/docs/themes-templates#variables) (ex :
`{{ _blog.base_url }}` dans le fichier robots.txt par défaut).

<h2 id="sitemap">Sitemap</h2>

Hyvor Blogs génère automatiquement des sitemaps. Vous pouvez trouver l'<a href="https://www.sitemaps.org/protocol.html#index" target="_blank" rel="nofollow">index du sitemap</a>
à l'adresse `/sitemap.xml` de votre blog. Vous pouvez soumettre cette URL aux moteurs de recherche pour les aider à découvrir
votre blog plus rapidement.

Format de l'index du sitemap :

```html
<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
	<sitemap><loc>https://blog.hyvorblogs.io/sitemap-pages.xml</loc></sitemap>
	<sitemap><loc>https://blog.hyvorblogs.io/sitemap-posts-1.xml</loc></sitemap>
	<sitemap><loc>https://blog.hyvorblogs.io/sitemap-posts-2.xml</loc></sitemap>
</sitemapindex>
```

L'index du sitemap renvoie vers d'autres sitemaps du blog.

- `sitemap-pages.xml` - contient les liens vers les pages et la page d'accueil.
- `sitemap-posts-[index].xml` - contient les liens vers les posts. Chaque fichier peut contenir jusqu'à 2500 URLs. La première page contient les URLs les plus anciennes. Dans le fichier, nous générons également automatiquement
  - `<image:image>` pour lier les images du post (uniquement les images téléchargées directement)
  - `<xhtml:link>` pour lier les variantes linguistiques du post

Voici un exemple de `sitemap-posts-[index].xml`.

```html
<?xml version="1.0" encoding="UTF-8"?>
<urlset
	xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
	xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
	xmlns:xhtml="http://www.w3.org/1999/xhtml"
>
	<url>
		<loc>https://blog.hyvorblogs.io/hello-world</loc>

		<xhtml:link rel="alternate" hreflang="en" href="https://blog.hyvorblogs.io/hello-world" />
		<xhtml:link rel="alternate" hreflang="fr" href="https://blog.hyvorblogs.io/fr/hello-world" />

		<image:image
			><image:loc>https://blog.hyvorblogs.io/media/hello-world.png</image:loc></image:image
		>
	</url>
</urlset>
```

<h2 id="prevent-indexing">Empêcher l'indexation</h2>

Voici la balise meta que vous pouvez utiliser pour empêcher les moteurs de recherche d'indexer une page.

```html
<meta name="robots" content="noindex" />
```

<h3 id="whole-blog">1. Blog entier</h3>

Pour empêcher les moteurs de recherche d'indexer votre **blog entier**, désactivez l'option
**Paramètres → SEO → Autoriser l'indexation**.
Hyvor Blogs ajoutera la balise meta ci-dessus à toutes les pages. Vous pouvez également ajouter manuellement cette balise meta
à votre thème ou dans le [code personnalisé](/docs/custom-code) de l'en-tête.

<h3 id="whole-blog">2. Post spécifique</h3>

Pour empêcher l'indexation d'un post, vous pouvez ajouter la balise meta ci-dessus au [code personnalisé](/docs/custom-code#post) de l'en-tête du post.

<h3 id="posts-of-tag">3. Posts d'un tag</h3>

Parfois, vous pouvez vouloir empêcher l'indexation des posts ayant un tag spécifique (ex : `no-index`). Dans ce cas, vous pouvez ajouter la balise meta ci-dessus au
[code personnalisé du tag](/docs/custom-code#tag).

<script lang="ts">
	import { Callout, Divider, Table, TableRow } from '@hyvor/design/components';
</script>

# API de données

L'API de données retourne les données publiques du blog.

- Aucune clé API n'est requise.
- Toutes les réponses sont au format JSON.
- Tous les points de terminaison utilisent la méthode HTTP `GET`.
- Le chemin de base est : `https://blogs.hyvor.com/api/data/v0/{subdomain}`
- Par exemple, si votre blog se trouve à `https://example.hyvor.com`, le chemin de base est
  `https://blogs.hyvor.com/api/data/v0/example`
  - Remplacez `{subdomain}` par le sous-domaine de votre blog.

<Callout type="info">
	<p>
		En plus d'appeler l'API de données via HTTP, il est possible de l'appeler dans les fichiers de modèle en utilisant
		la fonction Twig <a href="/docs/themes-templates#fetch-data">data()</a>. C'est la méthode
		préférée si vous voulez que des données servent à afficher une interface (ex : section des articles récents) dans votre blog, car la
		fonction <code>{`data()`}</code> appelle l'API de données en interne au moment du rendu du modèle,
		éliminant ainsi le besoin de requêtes HTTP supplémentaires.
	</p>
</Callout>

<h2 id="endpoints">Points de terminaison</h2>

**Objet unique**

- `/post` - un article/page
- `/tag`
- `/author`
- `/blog`- paramètres du blog

**Objets multiples**

- `/posts`
- `/posts/search` - rechercher des articles
- `/tags`
- `/authors`

<h2 id="response">Réponse</h2>

Pour les points de terminaison à objet unique, la réponse est un objet. Par exemple, le point de terminaison `/post` retourne un objet `Post` (voir ci-dessous pour les définitions d'objets).

```yaml
// A Post Object
{
    "id": 1000,
    "slug": "post",
    ...
}
```

Pour les points de terminaison à objets multiples, la réponse ressemble à ceci :

```yaml
{
    "data": [{}, {}], // array of objects
    "pagination": {} // a Pagination object
}
```

<h2 id="request">Requête</h2>

<h3 id="single-object">Points de terminaison à objet unique</h3>

Pour `/post`, `/tag`, et `/author`

<Table columns="1fr 2fr 1fr" hover>
	<TableRow head>
		<div>Paramètre</div>
		<div>Description</div>
		<div>Type</div>
	</TableRow>

    <TableRow>
    	<div><code>id</code></div>
    	<div>id de l'objet</div>
    	<div><code>integer</code></div>
    </TableRow>

    <TableRow>
    	<div>slug</div>
    	<div>slug de l'objet</div>
    	<div>string</div>
    </TableRow>

    <TableRow>
    	<div><code>language</code></div>
    	<div>Voir le <a href="/docs/api-data#language">paramètre language</a></div>
    	<div>string</div>
    </TableRow>

    <TableRow>
    	<div><code>keys</code></div>
    	<div>Voir le <a href="/docs/api-data#keys">paramètre keys</a></div>
    	<div>string</div>
    </TableRow>

</Table>

<Callout type="info">
	<p>Soit l'<code>id</code>, soit le <code>slug</code> est requis pour ces points de terminaison.</p>
</Callout>

Le point de terminaison `/blog` n'accepte que `language` et `keys` en entrée.

<h3 id="multi-object">Points de terminaison à objets multiples</h3>

`/posts`, `/posts/search`, `/tags`, et `/authors`

<Table columns="1fr 2fr 1fr 1fr" hover>
	<TableRow head>
		<div>Paramètre</div>
		<div>Description</div>
		<div>Type</div>
		<div>Défaut</div>
	</TableRow>

    <TableRow>
    	<div><code>language</code></div>
    	<div>Voir le <a href="/docs/api-data#language">paramètre language</a></div>
    	<div>string</div>
    	<div></div>
    </TableRow>

    <TableRow>
    	<div><code>limit</code></div>
    	<div>Voir le <a href="/docs/api-data#limit">paramètre limit</a></div>
    	<div>integer</div>
    	<div>25</div>
    </TableRow>

    <TableRow>
    	<div><code>page</code></div>
    	<div>Voir le <a href="/docs/api-data#page">paramètre page</a></div>
    	<div>integer</div>
    	<div>1</div>
    </TableRow>

    <TableRow>
    	<div><code>filter</code></div>
    	<div>Voir le <a href="/docs/api-data#filter">paramètre filter</a></div>
    	<div>string</div>
    	<div>""</div>
    </TableRow>

    <TableRow>
    	<div><code>sort</code></div>
    	<div>Voir le <a href="/docs/api-data#sort">paramètre sort</a></div>
    	<div>string</div>
    	<div>[VARIABLE]</div>
    </TableRow>

    <TableRow>
    	<div><code>keys</code></div>
    	<div>Voir le <a href="/docs/api-data#keys">paramètre keys</a></div>
    	<div>string</div>
    	<div></div>
    </TableRow>

</Table>

Le point de terminaison `/posts/search` a un paramètre `search` requis en plus des paramètres ci-dessus.

<Table columns="1fr 2fr 1fr 1fr" hover>
	<TableRow head>
		<div>Paramètre</div>
		<div>Description</div>
		<div>Type</div>
		<div>Défaut</div>
	</TableRow>

    <TableRow>
    	<div><code>search</code></div>
    	<div>valeur à rechercher</div>
    	<div>string</div>
    	<div></div>
    </TableRow>

</Table>

Le point de terminaison `/tags` dispose d'un paramètre `visibility` optionnel pour filtrer les étiquettes par visibilité. Notez que les [étiquettes privées](/docs/tags#private) ne sont pas destinées à être affichées publiquement sur le blog. Elles ne doivent être utilisées qu'à des fins internes (par ex. : afficher/masquer un widget dans le blog si l'étiquette est présente dans l'article).

<Table columns="1fr 2fr 1fr 1fr" hover>
	<TableRow head>
		<div>Paramètre</div>
		<div>Description</div>
		<div>Type</div>
		<div>Défaut</div>
	</TableRow>

    <TableRow>
    	<div><code>visibility</code></div>
    	<div>
    		<code>public</code> - uniquement les étiquettes publiques, <code>private</code> - uniquement les étiquettes privées,
    		<code>any</code> - toutes les étiquettes
    	</div>
    	<div>string</div>
    	<div>
    		<code>public</code>
    	</div>
    </TableRow>

</Table>

<h4 id="language">1. Le paramètre <code>language</code></h4>

Si votre blog a [plusieurs langues](/docs/languages), vous pouvez définir le paramètre `language` avec un code de langue (ex : `en`, `fr`) d'une langue de votre blog. Si ce paramètre n'est pas fourni, la langue principale du blog est utilisée. Cette langue sera utilisée pour localiser les chaînes dans les articles, les auteurs, les étiquettes et le blog.

<Callout type="info">
	<p>
		<b>Remarque</b> : Il existe une distinction importante entre les articles (<code>/post</code>,
		<code>/posts</code>, et <code>/posts/search</code>) et les autres points de terminaison lors de l'utilisation des langues.
		Disons que vous avez deux langues dans votre blog : <code>en</code> (principale) et
		<code>fr</code>. Si vous appelez le point de terminaison <code>/posts</code> avec le code de langue
		<code>fr</code>,
		<b>seuls les articles ayant une variante</b> <code>fr</code> <b>seront retournés</b>. Cependant, dans
		les autres points de terminaison (auteurs, étiquettes), tous les enregistrements seront retournés, qu'ils aient ou non une
		variante <code>fr</code>. Les traductions manquantes seront complétées par les chaînes de la langue principale. La raison en est que,
		lorsqu'une personne visite la page d'index <code>/fr</code> de votre blog, nous ne voulons afficher que les articles qui
		sont traduits en français. Nous ne voulons pas "revenir" au contenu original de l'article. Cependant, revenir aux
		données auteur/étiquettes est acceptable dans la plupart des cas. <br />
		<Divider />
		En d'autres termes, <code>language</code> dans les points de terminaison des articles fonctionne comme un filtre, tandis qu'il fonctionne comme un
		traducteur dans les autres points de terminaison.
	</p>
</Callout>

<h4 id="limit">2. Le paramètre <code>limit</code></h4>

Le paramètre `limit` peut être utilisé pour limiter le nombre d'enregistrements retournés dans les points de terminaison à objets multiples. La valeur par défaut est `25`. Le maximum est `250`.

<h4 id="page">3. Le paramètre <code>page</code></h4>

Le paramètre `page` peut être utilisé pour paginer les résultats. Cela fonctionne en combinaison avec le paramètre limit. La valeur par défaut est `1`.

```html
To get the first 20 results: /posts?limit=20 To get the next 20 results (page 2):
/posts?limit=20&page=2
```

<h4 id="filter">4. Le paramètre <code>filter</code></h4>

Exemple : `(published_at > 1639665890 & published_at < 1639695890) | is_featured=true`

Notre API de données utilise [Laravel FilterQ](https://github.com/hyvor/laravel-filterq) en interne, ce qui vous permet d'écrire une logique avancée comme dans l'exemple ci-dessus, en utilisant des opérateurs de comparaison et logiques.

Une condition se compose de trois parties :

- `key`
- `operator`
- `value`

<h5 id="operators">Opérateurs</h5>

- `=` - égal à
- `!=` - différent de
- `>` - supérieur à
- `<` - inférieur à
- `>=` - supérieur ou égal à
- `<=` - inférieur ou égal à

<h5 id="values">Valeurs</h5>

- `null`
- booléen : `true` ou `false`
- chaîne de caractères : `'hello'` ou `hello`
  - Les chaînes sans guillemets doivent correspondre à `[a-zA-Z_][a-zA-Z0-9_-]+` et ne peuvent pas être `true`, `false`, ou `null`.
- nombres : `250`, `-250`,` 2.5`

<h5 id="logical-operators">Opérateurs logiques</h5>

Vous pouvez utiliser des opérateurs logiques pour combiner plusieurs conditions.

- `|` - OU
- `&` - ET

Veuillez consulter la documentation [FilterQ Expressions](https://github.com/hyvor/laravel-filterq#filterq-expressions) si vous avez besoin de plus de détails.

##### Clés prises en charge pour le filtrage

<Table columns="1fr 2fr 1fr 1fr 1fr" hover>
	<TableRow head>
		<div>Point de terminaison</div>
		<div>Clé</div>
		<div>Opérateurs pris en charge</div>
		<div>Type de valeur</div>
		<div>Description</div>
	</TableRow>

    <TableRow>
    	<div><code>/posts</code></div>
    	<div><code>id</code></div>
    	<div>tous</div>
    	<div><code>integer</code></div>
    	<div></div>
    </TableRow>

    <TableRow>
    	<div></div>
    	<div><code>published_at</code></div>
    	<div>tous</div>
    	<div><code>date</code></div>
    	<div>Voir <a href="/docs/api-data#filter-date">Date</a></div>
    </TableRow>

    <TableRow>
    	<div></div>
    	<div><code>updated_at</code></div>
    	<div>tous</div>
    	<div><code>date</code></div>
    	<div>Voir <a href="/docs/api-data#filter-date">Date</a></div>
    </TableRow>

    <TableRow>
    	<div></div>
    	<div><code>created_at</code></div>
    	<div>tous</div>
    	<div><code>date</code></div>
    	<div>Voir <a href="/docs/api-data#filter-date">Date</a></div>
    </TableRow>

    <TableRow>
    	<div></div>
    	<div><code>is_featured</code></div>
    	<div><code>=</code>, <code>!=</code></div>
    	<div><code>boolean</code></div>
    	<div></div>
    </TableRow>

    <TableRow>
    	<div></div>
    	<div><code>slug</code></div>
    	<div><code>=</code>, <code>!=</code></div>
    	<div><code>string</code></div>
    	<div></div>
    </TableRow>

    <TableRow>
    	<div></div>
    	<div><code>featured_image_url</code></div>
    	<div><code>=</code>, <code>!=</code></div>
    	<div><code>null</code></div>
    	<div>uniquement pour vérifier si null ou non</div>
    </TableRow>

    <TableRow>
    	<div></div>
    	<div><code>canonical_url</code></div>
    	<div><code>=</code>, <code>!=</code></div>
    	<div><code>string</code></div>
    	<div>uniquement pour vérifier si null ou non</div>
    </TableRow>

    <TableRow>
    	<div></div>
    	<div><code>words</code></div>
    	<div>tous</div>
    	<div><code>integer</code></div>
    	<div></div>
    </TableRow>

    <TableRow>
    	<div></div>
    	<div><code>tag.id</code></div>
    	<div>tous</div>
    	<div><code>integer</code></div>
    	<div>Correspond à l'id des étiquettes de l'article</div>
    </TableRow>

    <TableRow>
    	<div></div>
    	<div><code>tag.slug</code></div>
    	<div><code>=</code>, <code>!=</code></div>
    	<div><code>string</code></div>
    	<div>Correspond au slug des étiquettes de l'article</div>
    </TableRow>

    <TableRow>
    	<div></div>
    	<div><code>author.id</code></div>
    	<div>tous</div>
    	<div><code>integer</code></div>
    	<div>Similaire à tag.id</div>
    </TableRow>

    <TableRow>
    	<div></div>
    	<div><code>author.slug</code></div>
    	<div><code>=</code>, <code>!=</code></div>
    	<div><code>string</code></div>
    	<div>Similaire à tag.slug</div>
    </TableRow>

    <TableRow>
    	<div><code>/tags</code> et <code>/authors</code></div>
    	<div><code>id</code></div>
    	<div>tous</div>
    	<div><code>integer</code></div>
    	<div></div>
    </TableRow>

    <TableRow>
    	<div></div>
    	<div><code>slug</code></div>
    	<div><code>=</code>, <code>!=</code></div>
    	<div><code>string</code></div>
    	<div></div>
    </TableRow>

    <TableRow>
    	<div></div>
    	<div><code>post_count</code></div>
    	<div>tous</div>
    	<div><code>integer</code></div>
    	<div></div>
    </TableRow>

    <TableRow>
    	<div></div>
    	<div><code>created_at</code></div>
    	<div>tous</div>
    	<div><code>date</code></div>
    	<div>Voir <a href="/docs/api-data#filter-date">Date</a></div>
    </TableRow>

</Table>

<h5 id="filter-date">Valeurs de date</h5>

Voici quelques valeurs valides pour les clés de date.

- `'2022-01-01'`
- `'yesterday'`
- `'first day of this year'`
- `'last day of next month'`
- `'+1 day'`
- `'-1 week'`
- `'next Thursday'`
- `1639655890` - Horodatage UNIX

Par exemple : Dans le point de terminaison `/posts`, vous pouvez utiliser `published_at>'-7 days'` pour obtenir les articles publiés au cours des 7 derniers jours.

<h5 id="filer-examples">Exemples de filtrage</h5>

<Callout type="info">
	<p>Notez que lors de l'appel de l'API via HTTP, la valeur du filtre doit être encodée en URL.</p>
</Callout>

Pour obtenir les articles écrits par Alex :

```ts
// filter
author.slug=alex

// URL-encoded
/posts?filter=author.slug%3Dalex
```

Pour obtenir les articles à la une :

```ts
// filter
is_featured=true

// URL-encoded
/posts?filter=is_featured%3Dtrue
```

Pour obtenir les articles ayant l'étiquette `audio` ou `video` :

```ts
// filter
tag.slug=audio|tag.slug=video

// URL-encoded
/posts?filter=tag.slug%3Daudio%7Ctag.slug%3Dvideo
```

Pour obtenir les étiquettes ayant au moins 5 articles :

```ts
// filter
posts_count>=5

// URL-encoded
/tags?filter=posts_count%3E%3D5
```

Pour obtenir les auteurs ajoutés après le 1er janvier 2020 :

```ts
// filter
created_at>='2020-01-01'

// URL-encoded
/authors?filter=created_at%3E%3D%272020-01-01%27
```

Pour obtenir les articles publiés au cours des 7 derniers jours.

```ts
// filter
published_at>'-7 days'

// URL-encoded
/posts?filter=published_at%3E%27-7%20days%27
```

<h4 id="sort">5. Le paramètre <code>sort</code></h4>

Voici une liste des valeurs de tri prises en charge. Vous pouvez en combiner plusieurs sous forme de valeurs séparées par des virgules, qui seront alors exécutées dans leur ordre, de manière similaire à `ORDER BY` en SQL.

<Table columns="2fr 2fr 2fr" hover>
	<TableRow head>
		<div>Point de terminaison</div>
		<div>Tri</div>
		<div>Description</div>
	</TableRow>

    <TableRow>
    	<div><code>/posts</code> Défaut <code>published_at DESC</code></div>
    	<div><code>published_at</code></div>
    	<div>Heure de publication de l'article</div>
    </TableRow>

    <TableRow>
    	<div></div>
    	<div><code>created_at</code></div>
    	<div>Heure de création de l'article</div>
    </TableRow>

    <TableRow>
    	<div></div>
    	<div><code>updated_at</code></div>
    	<div>Heure de la dernière mise à jour de l'article</div>
    </TableRow>

    <TableRow>
    	<div></div>
    	<div><code>id</code></div>
    	<div>ID de l'article</div>
    </TableRow>

    <TableRow>
    	<div></div>
    	<div><code>is_featured</code></div>
    	<div>Considérez ceci comme un entier, 1 pour vrai et 0 pour faux</div>
    </TableRow>

    <TableRow>
    	<div></div>
    	<div><code>title</code></div>
    	<div>par ordre alphabétique</div>
    </TableRow>

    <TableRow>
    	<div></div>
    	<div><code>words</code></div>
    	<div></div>
    </TableRow>

    <TableRow>
    	<div>
    		<code>/tags</code> et <code>/authors</code> Défaut <code>posts_count DESC</code>
    	</div>
    	<div><code>post_count</code></div>
    	<div>nombre d'articles de l'étiquette/auteur</div>
    </TableRow>

    <TableRow>
    	<div></div>
    	<div><code>created_at</code></div>
    	<div></div>
    </TableRow>

</Table>

La méthode de tri par défaut est `DESC`. Voici quelques exemples pour le paramètre sort.

- `published_at` - trié par published_at par ordre décroissant
- `published_at ASC` - trié par published_at par ordre croissant
- `is_featured DESC`, `published_at DESC` - les articles à la une en premier, puis triés par heure de publication par ordre décroissant. `DESC` est optionnel. `is_featured`, `published_at` est identique au précédent.

<h4 id="keys">6. Le paramètre <code>keys</code></h4>

Le paramètre `keys` peut être utilisé pour inclure ou exclure des clés des objets, de manière similaire à GraphQL. Tous les points de terminaison prennent en charge le paramètre `keys`.

Si vous appelez le point de terminaison `/posts`, avec `keys=id,content`, les objets d'article ne contiendront que ces deux clés.

```ts
{
    "id": 1000,
    "content": "<p></p>"
}
```

Utilisez `!` au début pour exclure des étiquettes. Par exemple, `keys=!content,description` exclura `content` et `description` de l'objet Post et toutes les autres clés seront incluses.

Disons que vous souhaitez uniquement obtenir l'ID de l'article et l'ID de l'étiquette des articles. Utilisez `keys=id,tags.id`. Vous obtiendrez des objets comme ceci.

```ts
{
    "id": 1000,
    "tags": [
        {
            "id": 2000
        }
    ]
}
```

<h2 id="objects">Objets</h2>

<Callout type="info">
	<p>
		Tous les horodatages sont au format <a href="https://www.unixtimestamp.com/">Horodatage Unix</a> (entier).
	</p>
</Callout>

<h3 id="post-object">Objet Post</h3>

```ts
{
    "id": 1000,

    "created_at": 1639655890,
    "updated_at": 1639655890,
    "published_at": 1639665890,

    "is_featured": false,
    "is_page": false,
    "slug": "hello-world",
    "content": "<p></p>",
    "title": "Hello World",
    "description": "This is a hello world page",
    "url": "https://subdomain.hyvorblogs.io/hello-world",
    "featured_image_url": "https://example.com/image.png",
    "canonical_url": null,
    "words": 500,
    "code_head": "",
    "code_foot": "",

    "language": language object,
    "variants": [ variant objects ],

    "tags": [ tag objects ],
    "tags_private": [ tag objects ],
    "authors": [ author objects ]
}
```

<Table columns="2fr 2fr 3fr" hover>
	<TableRow head>
		<div>Clé</div>
		<div>Type</div>
		<div>Description</div>
	</TableRow>

    <TableRow>
    	<div><code>id</code></div>
    	<div><code>integer</code></div>
    	<div>Un ID unique pour l'article</div>
    </TableRow>

    <TableRow>
    	<div><code>created_at</code></div>
    	<div><code>integer</code></div>
    	<div>Moment où l'article a été créé</div>
    </TableRow>

    <TableRow>
    	<div><code>updated_at</code></div>
    	<div><code>integer</code></div>
    	<div>Le moment où l'article ou ses métadonnées ont été mis à jour</div>
    </TableRow>

    <TableRow>
    	<div><code>published_at</code></div>
    	<div><code>integer</code></div>
    	<div>Heure de publication de l'article</div>
    </TableRow>

    <TableRow>
    	<div><code>is_featured</code></div>
    	<div><code>boolean</code></div>
    	<div>Si l'article est à la une. Il peut y avoir plusieurs articles à la une sur un blog</div>
    </TableRow>

    <TableRow>
    	<div><code>is_page</code></div>
    	<div><code>boolean</code></div>
    	<div>S'il s'agit d'une page. Voir <a href="/docs/writing#posts-pages">Articles et Pages</a></div>
    </TableRow>

    <TableRow>
    	<div><code>slug</code></div>
    	<div><code>string</code></div>
    	<div>Le slug d'URL de l'article</div>
    </TableRow>

    <TableRow>
    	<div><code>url</code></div>
    	<div><code>string</code></div>
    	<div>L'URL absolue de l'article, générée en fonction de l'hébergement du blog.</div>
    </TableRow>

    <TableRow>
    	<div><code>content</code></div>
    	<div><code>string</code></div>
    	<div>
    		Le contenu de l'article en HTML. Voir <a href="/docs/writing">Contenu et l'éditeur</a> pour voir les
    		balises HTML prises en charge
    	</div>
    </TableRow>

    <TableRow>
    	<div><code>title</code></div>
    	<div><code>string</code></div>
    	<div>Le titre de l'article, longueur maximale 256</div>
    </TableRow>

    <TableRow>
    	<div><code>description</code></div>
    	<div><code>string | null</code></div>
    	<div>La description (extrait) de l'article, longueur maximale 350, null si non défini</div>
    </TableRow>

    <TableRow>
    	<div><code>featured_image_url</code></div>
    	<div><code>string | null</code></div>
    	<div>L'URL absolue de l'image mise en avant. null si non défini</div>
    </TableRow>

    <TableRow>
    	<div><code>canonical_url</code></div>
    	<div><code>string | null</code></div>
    	<div>
    		Une URL absolue ou null. L'URL canonique est définie par l'auteur si l'article a été publié
    		ailleurs.
    	</div>
    </TableRow>

    <TableRow>
    	<div><code>words</code></div>
    	<div><code>integer</code></div>
    	<div>Nombre de mots dans le contenu</div>
    </TableRow>

    <TableRow>
    	<div><code>code_head</code></div>
    	<div><code>string</code></div>
    	<div>
    		<a href="/docs/custom-code">Code personnalisé</a> à ajouter avant <code>{`</head>`}</code>. Une chaîne vide
    		si rien n'est défini.
    	</div>
    </TableRow>

    <TableRow>
    	<div><code>code_foot</code></div>
    	<div><code>string</code></div>
    	<div>
    		<a href="/docs/custom-code">Code personnalisé</a> à ajouter avant <code>{`</body>`}</code>. Une chaîne vide
    		si rien n'est défini.
    	</div>
    </TableRow>

    <TableRow>
    	<div><code>language</code></div>
    	<div><code>object</code></div>
    	<div>Un <a href="/docs/api-data#language-object">objet Language</a></div>
    </TableRow>

    <TableRow>
    	<div><code>variants</code></div>
    	<div><code>array</code></div>
    	<div>Un tableau d'<a href="/docs/api-data#variant-object">objets Variant</a></div>
    </TableRow>

    <TableRow>
    	<div><code>tags</code></div>
    	<div><code>array</code></div>
    	<div>
    		Un tableau d'<a href="/docs/api-data#tag-object">objets Tag</a> publics. L'étiquette principale est à
    		l'index 0
    	</div>
    </TableRow>

    <TableRow>
    	<div><code>tags_private</code></div>
    	<div><code>array</code></div>
    	<div>
    		Un tableau d'<a href="/docs/api-data#tag-object">objets Tag</a> privés. Voir
    		<a href="/docs/tags#private">Étiquettes privées</a>
    	</div>
    </TableRow>

    <TableRow>
    	<div><code>authors</code></div>
    	<div><code>array</code></div>
    	<div>
    		Un tableau d'<a href="/docs/api-data#author-object">objets Author</a>. L'auteur principal est
    		à l'index 0
    	</div>
    </TableRow>

</Table>

<Callout type="info">
	<p>
		Dans les articles, l'attribut <b>id</b> est unique au niveau mondial dans Hyvor Blogs. L'attribut <b>slug</b> est
		unique au sein du blog.
	</p>
</Callout>

<h3 id="tag-object">Objet Tag</h3>

```ts
{
    "id": 2000,
    "created_at": 1639655890,
    "is_private": false,
    "name": "Hello World",
    "description": "Saying hello to the world",
    "slug": "hello-world",
    "url": "https://subdomain.hyvorblogs.io/tag/hello-world",
    "posts_count": 20,
    "code_head": null,
    "code_foot": "<p>some code</p>",

    "language": language object,
    "variants": [ variant objects ],
}
```

<Table columns="2fr 2fr 3fr" hover>
	<TableRow head>
		<div>Clé</div>
		<div>Type</div>
		<div>Description</div>
	</TableRow>

    <TableRow>
    	<div><code>id</code></div>
    	<div><code>integer</code></div>
    	<div>Un ID unique pour l'étiquette</div>
    </TableRow>

    <TableRow>
    	<div><code>created_at</code></div>
    	<div><code>integer</code></div>
    	<div>Le moment où l'étiquette a été créée</div>
    </TableRow>

    <TableRow>
    	<div><code>is_private</code></div>
    	<div><code>boolean</code></div>
    	<div>Si l'étiquette est privée. Voir <a href="/docs/tags#private">Étiquettes privées</a></div>
    </TableRow>

    <TableRow>
    	<div><code>name</code></div>
    	<div><code>string</code></div>
    	<div>Nom (ou titre) de l'étiquette</div>
    </TableRow>

    <TableRow>
    	<div><code>description</code></div>
    	<div><code>string | null</code></div>
    	<div>Description de l'étiquette</div>
    </TableRow>

    <TableRow>
    	<div><code>slug</code></div>
    	<div><code>string</code></div>
    	<div>Slug d'URL de l'étiquette (le chemin par défaut complet sera <code>{`/tag/{slug}`}</code>)</div>
    </TableRow>

    <TableRow>
    	<div><code>url</code></div>
    	<div><code>string</code></div>
    	<div></div>
    </TableRow>

    <TableRow>
    	<div><code>posts_count</code></div>
    	<div><code>integer</code></div>
    	<div>Nombre d'articles de l'étiquette</div>
    </TableRow>

    <TableRow>
    	<div><code>language</code></div>
    	<div><code>object</code></div>
    	<div>Un <a href="/docs/api-data#language-object">objet Language</a></div>
    </TableRow>

    <TableRow>
    	<div><code>variants</code></div>
    	<div><code>array</code></div>
    	<div>Un tableau d'<a href="/docs/api-data#variant-object">objets Variant</a></div>
    </TableRow>

</Table>

<h3 id="author-object">Objet Author</h3>

<Callout type="info">
	<p>
		Un auteur est un <a href="https://blogs.hyvor.com/docs/users">utilisateur</a> qui a écrit au moins un article
	</p>
</Callout>

```ts
{
    "id": 3000,
    "created_at": 1639655890,
    "slug": "blogger",
    "url": "https://subdomain.hyvorblogs.io/author/blogger",
    "name": "Blogger",
    "picture_url": "https://example.com/image.png",
    "bio": "I am a blogger",
    "website_url": "https://example.com",
    "location": "France",
    "social": social media object,
    "posts_count": 32,

    "language": language object,
    "variants": [ variant objects ],
}
```

<Table columns="2fr 2fr 3fr" hover>
	<TableRow head>
		<div>Clé</div>
		<div>Type</div>
		<div>Description</div>
	</TableRow>

    <TableRow>
    	<div><code>id</code></div>
    	<div><code>integer</code></div>
    	<div>Un ID unique pour l'auteur</div>
    </TableRow>

    <TableRow>
    	<div><code>created_at</code></div>
    	<div><code>integer</code></div>
    	<div>Le moment où l'auteur a été créé</div>
    </TableRow>

    <TableRow>
    	<div><code>slug</code></div>
    	<div><code>string</code></div>
    	<div>
    		Slug d'URL de l'auteur (le chemin par défaut complet sera <code>{`/author/{slug}`}</code>)
    	</div>
    </TableRow>

    <TableRow>
    	<div><code>url</code></div>
    	<div><code>string</code></div>
    	<div>URL complète de l'utilisateur</div>
    </TableRow>

    <TableRow>
    	<div><code>name</code></div>
    	<div><code>string</code></div>
    	<div>Nom de l'auteur. Longueur maximale 50</div>
    </TableRow>

    <TableRow>
    	<div><code>picture_url</code></div>
    	<div><code>string | null</code></div>
    	<div>L'URL absolue de la photo de l'auteur. Généralement, une petite image carrée</div>
    </TableRow>

    <TableRow>
    	<div><code>bio</code></div>
    	<div><code>string | null</code></div>
    	<div>Biographie de l'auteur. Longueur maximale 256</div>
    </TableRow>

    <TableRow>
    	<div><code>website_url</code></div>
    	<div><code>string | null</code></div>
    	<div>L'URL absolue du site web de l'auteur</div>
    </TableRow>

    <TableRow>
    	<div><code>location</code></div>
    	<div><code>string | null</code></div>
    	<div>Emplacement de l'auteur. Longueur maximale 30</div>
    </TableRow>

    <TableRow>
    	<div><code>social</code></div>
    	<div><code>object</code></div>
    	<div>Un <a href="/docs/api-data#social-media-object">objet Social Media</a></div>
    </TableRow>

    <TableRow>
    	<div><code>posts_count</code></div>
    	<div><code>integer</code></div>
    	<div>Nombre d'articles écrits par l'auteur</div>
    </TableRow>

    <TableRow>
    	<div><code>language</code></div>
    	<div><code>object</code></div>
    	<div>Un <a href="/docs/api-data#language-object">objet Language</a></div>
    </TableRow>

    <TableRow>
    	<div><code>variants</code></div>
    	<div><code>array</code></div>
    	<div>Un tableau d'<a href="/docs/api-data#variant-object">objets Variant</a></div>
    </TableRow>

</Table>

<h3 id="blog-object">Objet Blog</h3>

```ts
{
    "subdomain": "alex",
    "name": "My Blog",
    "description": "This is my blog hosted on Hyvor Blogs",
    "logo_url": "https://blog.hyvorblogs.io/media/logo.png",
    "icon_url": "https://blog.hyvorblogs.io/media/icon.png",
    "cover_url": "https://blog.hyvorblogs.io/media/cover.png",
    "url": "https://blog.hyvorblogs.io",
    "social": social media object,
    "nav_header": [
        {
            "name": "Home",
            "url": "/"
        },
        {
            "name": "About",
            "url": "/about"
        }
    ],
    "nav_footer": [
        {
            "name": "Privacy",
            "url": "/privacy"
        }
    ],

    "languages": [ language objects ],

    "code_head": "",
    "code_foot": "",

    "posts_count": 200,

    // the following are blog settings
    // which are used for generating header code, color themes
    // and footer branding
    "seo_indexing": true,
    "color_modes": "light",
    "color_mode_default": "light",

    // for cache busting
    "cache_version_styles": 1,
}
```

<Table columns="2fr 2fr 3fr" hover>
	<TableRow head>
		<div>Clé</div>
		<div>Type</div>
		<div>Description</div>
	</TableRow>

    <TableRow>
    	<div><code>subdomain</code></div>
    	<div><code>string</code></div>
    	<div>Sous-domaine du blog</div>
    </TableRow>

    <TableRow>
    	<div><code>name</code></div>
    	<div><code>string</code></div>
    	<div>Nom/titre du blog</div>
    </TableRow>

    <TableRow>
    	<div><code>description</code></div>
    	<div><code>string</code></div>
    	<div>Une courte description du blog (256 max)</div>
    </TableRow>

    <TableRow>
    	<div><code>logo_url</code></div>
    	<div><code>string | null</code></div>
    	<div>L'URL absolue de l'icône du blog. Généralement, une petite image carrée</div>
    </TableRow>

    <TableRow>
    	<div><code>cover_url</code></div>
    	<div><code>string | null</code></div>
    	<div>L'URL absolue de l'image mise en avant/de couverture</div>
    </TableRow>

    <TableRow>
    	<div><code>url</code></div>
    	<div><code>string | null</code></div>
    	<div>URL absolue du blog pour la langue actuelle</div>
    </TableRow>

    <TableRow>
    	<div><code>base_url</code></div>
    	<div><code>string</code></div>
    	<div>URL absolue du blog.</div>
    </TableRow>

    <TableRow>
    	<div><code>social</code></div>
    	<div><code>object</code></div>
    	<div>Un <a href="/docs/api-data#social-media-object">objet Social Media</a></div>
    </TableRow>

    <TableRow>
    	<div><code>nav_header</code>, <code>nav_footer</code></div>
    	<div><code>array of objects</code></div>
    	<div>Liens de navigation pour l'en-tête et le pied de page du blog.</div>
    </TableRow>

    <TableRow>
    	<div><code>languages</code></div>
    	<div><code>array of objects</code></div>
    	<div>
    		Toutes les langues disponibles du blog. Voir <a href="/docs/api-data#language-object"
    			>objet Language</a
    		>
    	</div>
    </TableRow>

    <TableRow>
    	<div><code>code_head</code>, <code>code_foot</code></div>
    	<div><code>string</code></div>
    	<div>
    		Code HTML personnalisé pour avant <code>{`</head>`}</code>, et <code>{`</body>`}</code> pour toutes les pages.
    	</div>
    </TableRow>

    <TableRow>
    	<div><code>posts_count</code></div>
    	<div><code>integer</code></div>
    	<div>Total des articles publiés</div>
    </TableRow>

</Table>

<h3 id="language-object">Objet Language</h3>

```ts
{
    "id": 1000,
    "code": "en",
    "name": "English",
    "is_primary": true,
    "direction": "ltr"
}
```

<Table columns="2fr 2fr 3fr" hover>
	<TableRow head>
		<div>Clé</div>
		<div>Type</div>
		<div>Description</div>
	</TableRow>

    <TableRow>
    	<div><code>id</code></div>
    	<div><code>integer</code></div>
    	<div>Un ID unique pour la langue</div>
    </TableRow>

    <TableRow>
    	<div><code>code</code></div>
    	<div><code>string</code></div>
    	<div>Code de la langue</div>
    </TableRow>

    <TableRow>
    	<div><code>name</code></div>
    	<div><code>string</code></div>
    	<div>Nom de la langue</div>
    </TableRow>

    <TableRow>
    	<div><code>is_primary</code></div>
    	<div><code>boolean</code></div>
    	<div>Si c'est la langue principale du blog</div>
    </TableRow>

    <TableRow>
    	<div><code>direction</code></div>
    	<div><code>string</code></div>
    	<div>Direction du texte. <code>ltr</code> ou <code>rtl</code></div>
    </TableRow>

</Table>

<h3 id="variant-object">Objet Variant</h3>

Un objet variant contient les données d'une variante linguistique d'un article, d'une étiquette ou d'un auteur.

```ts
{
    "language": {
        "id": 1001,
        "code": "fr",
        "name": "French",
        "is_primary": false,
        "direction": "ltr"
    },
    "url": "https://subdomain.hyvorblogs.io/fr/hello-world"
}
```

<Table columns="2fr 2fr 3fr" hover>
	<TableRow head>
		<div>Clé</div>
		<div>Type</div>
		<div>Description</div>
	</TableRow>

    <TableRow>
    	<div><code>language</code></div>
    	<div><code>object</code></div>
    	<div>Un <a href="/docs/api-data#language-object">objet Language</a></div>
    </TableRow>

    <TableRow>
    	<div><code>url</code></div>
    	<div><code>string</code></div>
    	<div>URL de la variante</div>
    </TableRow>

</Table>

<h3 id="pagination-object">Objet Pagination</h3>

Un objet pagination est inclus dans tous les points de terminaison à objets multiples (`/posts`, `/authors`, `/tags`).

```ts
{
    "total": 100,
    "pages": 10,
    "limit": 5,
    "page": 1,
    "page_prev": null,
    "page_next": 2,
}
```

<Table columns="2fr 2fr 3fr" hover>
	<TableRow head>
		<div>Clé</div>
		<div>Type</div>
		<div>Description</div>
	</TableRow>

    <TableRow>
    	<div><code>total</code></div>
    	<div><code>integer</code></div>
    	<div>Le nombre total de résultats possibles avec les filtres actuels</div>
    </TableRow>

    <TableRow>
    	<div><code>pages</code></div>
    	<div><code>integer</code></div>
    	<div>
    		Le nombre total de pages de pagination en fonction de la limite que vous avez définie. <code
    			>{`pages = round_to_upper(total/limit)`}</code
    		>
    	</div>
    </TableRow>

    <TableRow>
    	<div><code>limit</code></div>
    	<div><code>integer</code></div>
    	<div>Limite actuelle</div>
    </TableRow>

    <TableRow>
    	<div><code>page</code></div>
    	<div><code>integer</code></div>
    	<div>Page actuelle</div>
    </TableRow>

    <TableRow>
    	<div><code>page_prev</code></div>
    	<div><code>integer</code> ou <code>string</code></div>
    	<div>Numéro de la page précédente (<code>null</code> s'il n'y a pas de pages précédentes)</div>
    </TableRow>

    <TableRow>
    	<div><code>page_next</code></div>
    	<div><code>integer</code> ou <code>string</code></div>
    	<div>Numéro de la page suivante (<code>null</code> s'il n'y a pas de pages suivantes)</div>
    </TableRow>

</Table>

<h3 id="social-media-object">Objet Social Media</h3>

```ts
{
    "facebook": null,
    "twitter": "https://twitter.com/HyvorBlogs",
    "linkedin": "https://www.linkedin.com/company/30240435",
    "youtube": null,
    "instagram": null,
    "github": "https://github.com/hyvor",
    "tiktok": null
}
```

<h2 id="error-handling">Gestion des erreurs</h2>

En cas d'erreur, le code de statut HTTP sera un code de statut différent de 200.

Pour les erreurs 4xx, la réponse sera un objet JSON.

```ts
{
    "error": "ID is required",
    "error_code": "422"
}
```

Ces codes HTTP sont possibles :

- <cb>404 Not Found</cb> - Ressource non trouvée
  - 404 peut être retourné dans les points de terminaison à objet unique lorsque l'objet n'est pas trouvé
  - Assurez-vous que l'ID/slug (et la langue pour les articles) est correct
- **422 Unprocessable Entity** - Entrée invalide
  - Vérifiez les paramètres de requête
  - Vous pouvez trouver plus de détails dans la sortie JSON de l'erreur

Les erreurs 5xx signifient qu'un problème est survenu de notre côté. Consultez notre [page de statut](https://status.hyvor.com/) pour toute interruption de service. Si le problème persiste, [contactez-nous](/docs/support).

<h2 id="pages">Pages</h2>

Nous n'avons pas de points de terminaison distincts pour récupérer les [Pages](/docs/writing#posts-pages).

- Pour obtenir une seule page, appelez le point de terminaison `/post` avec l'ID ou le slug de la page.
- Pour obtenir plusieurs pages, appelez le point de terminaison `/posts` avec le paramètre `?pages=true`.
- `/posts/search` ne prend pas en charge la recherche de pages.

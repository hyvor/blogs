<script lang="ts">
	import { Callout, Table, TableRow } from '@hyvor/design/components';
</script>

# Routes

Les routes sont utilisées pour configurer la manière dont des requêtes spécifiques sont traitées. Une route fait correspondre le chemin de la requête et détermine quel contenu renvoyer à l'utilisateur. Par exemple, si le chemin de la requête est `/tag/my-tag`, la route détermine qu'elle doit afficher `tag.twig` et renvoyer la réponse à l'utilisateur.

- [Routes par défaut](/docs/routes#defaults)
- [Personnaliser les permaliens des articles/pages](/docs/routes#permalinks)
- [Personnaliser d'autres routes par défaut](/docs/routes#customizing-other)
- [Routes personnalisées](/docs/routes#custom)
- [Conflits de routes](/docs/routes#conflicts)
- [Suffixes et préfixes](/docs/routes#suffix-prefix)
- [Créer un site web](/docs/routes#website)
- [Plusieurs collections d'articles](/docs/routes#collections)

Paramètres des routes : **Console → Paramètres → Routes**

<h2 id="defaults">Routes par défaut</h2>

Un nouveau blog aura ces 5 routes par défaut.

<Table columns="2fr 2fr 3fr 2fr 2fr" hover>
	<TableRow head>
		<div>Nom de la route</div>
		<div>Correspondance</div>
		<div>Description</div>
		<div>Modèle</div>
		<div>Filtre d'articles</div>
	</TableRow>

    <TableRow>
    	<div><code>post</code></div>
    	<div><code>{`/{slug}`}</code></div>
    	<div>Correspond à un article</div>
    	<div>post</div>
    	<div></div>
    </TableRow>

    <TableRow>
    	<div><code>page</code></div>
    	<div><code>{`/{slug}`}</code></div>
    	<div>Correspond à une page</div>
    	<div>page,post</div>
    	<div></div>
    </TableRow>

    <TableRow>
    	<div><code>index</code></div>
    	<div><code>{`/`}</code></div>
    	<div>Page d'index principale (liste tous les articles)</div>
    	<div>index</div>
    	<div></div>
    </TableRow>

    <TableRow>
    	<div><code>tag</code></div>
    	<div><code>{`/tag/{slug}`}</code></div>
    	<div>Page d'index des tags (liste tous les articles d'un tag spécifique)</div>
    	<div>tag,index</div>
    	<div><code>{`tag.slug = {slug}`}</code></div>
    </TableRow>

    <TableRow>
    	<div><code>author</code></div>
    	<div><code>{`/author/{slug}`}</code></div>
    	<div>Page d'index des auteurs (liste tous les articles d'un auteur spécifique)</div>
    	<div>author,index</div>
    	<div><code>{`author.slug = {slug}`}</code></div>
    </TableRow>

</Table>

<Callout type="info">
	<p>
		Le filtre d'articles est une <a href="https://github.com/hyvor/laravel-filterq">expression FilterQ</a>
		pour filtrer les articles. Ces articles filtrés seront envoyés au modèle en tant que
		variable <code>_posts</code>.
		Elle n'est utilisée que dans les pages de listing comme index, tag, author. Les paramètres correspondants (<code
			>{`{slug}`}</code
		>) peuvent être utilisés dans cette expression.
	</p>
</Callout>

En plus de ces routes par défaut, il existe des routes spéciales, non personnalisables.

<Table columns="2fr 2fr" hover>
	<TableRow head>
		<div>Correspondance</div>
		<div>Description</div>
	</TableRow>

    <TableRow>
    	<div><code>{`/styles.css`}</code></div>
    	<div>Le fichier CSS principal du blog (généré automatiquement à partir des fichiers SCSS du thème)</div>
    </TableRow>

    <TableRow>
    	<div><code>{`/assets/{file_name}`}</code></div>
    	<div>Pour servir les fichiers du thème</div>
    </TableRow>

    <TableRow>
    	<div><code>{`/media/{file_name}`}</code></div>
    	<div>Pour servir les médias téléchargés</div>
    </TableRow>

    <TableRow>
    	<div><code>{`/p/{hash}`}</code></div>
    	<div>Pour prévisualiser les articles et les pages</div>
    </TableRow>

    <TableRow>
    	<div><code>{`/robots.txt`}</code></div>
    	<div><a href="/docs/seo#robots">Robots.txt</a></div>
    </TableRow>

    <TableRow>
    	<div>
    		<code>{`/sitemap.xml`}</code>, <code>{`/sitemap-pages.xml`}</code>,
    		<code>{`/sitemap-posts-x.xml`}</code>
    	</div>
    	<div><a href="/docs/seo#sitemap">Plan du site</a></div>
    </TableRow>

</Table>

<h2 id="permalinks">Personnaliser les permaliens des articles/pages</h2>

Vous pouvez modifier la valeur de correspondance des routes `post` et `page` pour changer les permaliens des articles/pages. Par défaut, elle ressemble à `/{slug}`. Vous pouvez la remplacer par une structure différente pouvant inclure la date, le tag et/ou le nom de l'auteur. Voici quelques exemples.

- `/{year}/{month}/{day}/{slug}`
- `/{tag}/{slug}`
- `/{author}/{slug}`

Notez que `{slug}` est toujours requis. Les espaces réservés suivants sont pris en charge.

Espaces réservés de base :

<Table columns="2fr 2fr" hover>
	<TableRow head>
		<div>Espace réservé</div>
		<div>Description</div>
	</TableRow>

    <TableRow>
    	<div><code>{`{slug}`}</code></div>
    	<div>Slug de l'article (toujours requis)</div>
    </TableRow>

    <TableRow>
    	<div><code>{`{tag}`}</code></div>
    	<div>Slug du premier tag de l'article</div>
    </TableRow>

    <TableRow>
    	<div><code>{`{author}`}</code></div>
    	<div>Slug de l'auteur de l'article</div>
    </TableRow>

</Table>

<Callout type="info">
	<p>
		Veuillez noter que si vous utilisez <code>{`{tag}`}</code> (ou <code>{`{author}`}</code>) dans
		l'URL de l'article/page, tous les articles <b>doivent avoir</b> au moins un tag (ou auteur). Sinon, l'URL
		de l'article/page affichera une erreur 404.
	</p>
</Callout>

Espaces réservés basés sur le temps :

- Représentent la **date de publication** de l'article
- Seul l'anglais en minuscules est pris en charge pour les noms de mois et de jours

<Table columns="2fr 2fr 2fr" hover>
	<TableRow head>
		<div>Espace réservé</div>
		<div>Description</div>
		<div>Exemple</div>
	</TableRow>

    <TableRow>
    	<div><code>{`{year}`}</code></div>
    	<div>Année sur 4 chiffres</div>
    	<div><code>2022</code></div>
    </TableRow>

    <TableRow>
    	<div><code>{`{year_short}`}</code></div>
    	<div>Année sur 2 chiffres</div>
    	<div><code>99</code> ou <code>22</code></div>
    </TableRow>

    <TableRow>
    	<div><code>{`{month}`}</code></div>
    	<div>Numéro du mois sur 2 chiffres</div>
    	<div><code>01</code> ou <code>12</code></div>
    </TableRow>

    <TableRow>
    	<div><code>{`{month_number}`}</code></div>
    	<div>Numéro du mois sans zéro initial</div>
    	<div><code>1</code> à <code>12</code></div>
    </TableRow>

    <TableRow>
    	<div><code>{`{month_short}`}</code></div>
    	<div>Nom court du mois</div>
    	<div><code>jan</code> à <code>dec</code></div>
    </TableRow>

    <TableRow>
    	<div><code>{`{month_long}`}</code></div>
    	<div>Nom complet du mois</div>
    	<div><code>january</code> à <code>december</code></div>
    </TableRow>

    <TableRow>
    	<div><code>{`{day}`}</code></div>
    	<div>Jour sur 2 chiffres</div>
    	<div><code>01</code> à <code>31</code></div>
    </TableRow>

    <TableRow>
    	<div><code>{`{day_number}`}</code></div>
    	<div>Numéro du jour sans zéro initial</div>
    	<div><code>1</code> à <code>31</code></div>
    </TableRow>

    <TableRow>
    	<div><code>{`{day_year}`}</code></div>
    	<div>Jour ordinal de l'année</div>
    	<div><code>1</code> à <code>365</code></div>
    </TableRow>

    <TableRow>
    	<div><code>{`{day_week}`}</code></div>
    	<div>Jour de la semaine en 3 lettres</div>
    	<div><code>mon</code> à <code>sun</code></div>
    </TableRow>

    <TableRow>
    	<div><code>{`{day_week_long}`}</code></div>
    	<div>Jour de la semaine</div>
    	<div><code>monday</code> à <code>sunday</code></div>
    </TableRow>

    <TableRow>
    	<div><code>{`{day_week_number}`}</code></div>
    	<div>Jour de la semaine sous forme de nombre</div>
    	<div>de <code>1</code> à <code>7</code></div>
    </TableRow>

    <TableRow>
    	<div><code>{`{hour}`}</code></div>
    	<div>Heure du jour, au format 24h</div>
    	<div><code>00</code> à <code>23</code></div>
    </TableRow>

    <TableRow>
    	<div><code>{`{minute}`}</code></div>
    	<div>Minute de l'heure</div>
    	<div><code>00</code> à <code>59</code></div>
    </TableRow>

    <TableRow>
    	<div><code>{`{second}`}</code></div>
    	<div>Seconde de la minute</div>
    	<div><code>00</code> à <code>59</code></div>
    </TableRow>

    <TableRow>
    	<div><code>{`{unix}`}</code></div>
    	<div>Horodatage UNIX en secondes</div>
    	<div><code>1448406000</code></div>
    </TableRow>

</Table>

<h2 id="customizing-other">Personnaliser d'autres routes par défaut</h2>

Comme pour `post` et `page`, les `routes`, d'autres routes par défaut (`index`, `tag`, `author`, et `search`) sont personnalisables dans une certaine mesure. Voici quelques idées :

- `/author/{slug}`→`/creator/{slug}`
- `/tag/{slug}`→`/category/{slug}`
- `/`→`/blog`

<Callout type="info">
	<p>
		Veuillez noter que vous (ou les développeurs de thèmes) pouvez également ajouter des routes personnalisées en ajoutant des fichiers <code
			>{`route-{route}`}</code
		>.twig aux fichiers de modèle. Voir <a href="/docs/themes-templates#custom-routes">ici</a> pour plus de
		détails.
	</p>
</Callout>

<h2 id="custom">Routes personnalisées</h2>

En plus des 6 routes par défaut, vous pouvez ajouter vos propres routes. Voici quelques exemples d'utilisation :

- Créer des pages de destination personnalisées
- Créer [de nouvelles collections d'articles](/docs/routes#collections)
- Créer des flux RSS personnalisés, par exemple pour un podcast

<Callout type="info">
	<p>
		Veuillez noter que vous (ou les développeurs de thèmes) pouvez également ajouter des routes personnalisées en ajoutant des fichiers <code
			>{`route-{route}`}</code
		>.twig aux fichiers de modèle. Voir <a href="/docs/themes-templates#custom-routes">ici</a> pour plus de
		détails.
	</p>
</Callout>

<h2 id="conflicts">Conflits de routes</h2>

En général, des conflits de routes peuvent survenir lorsque deux routes ou plus ont la même valeur de correspondance. Dans Hyvor Blogs, les routes `post` et `page` peuvent avoir les mêmes valeurs de correspondance. (Vous pouvez le constater, les valeurs par défaut de ces deux routes sont identiques : `/{slug}`). Cependant, les correspondances des autres routes ne peuvent pas avoir de valeurs dupliquées.

<h2 id="suffix-prefix">Suffixes et préfixes</h2>

Ces suffixes sont pris en charge :

- `/feed` - Pour le flux atom (`posts_filter` doit être défini)
- `/page/{page_number}` - Pour la pagination

Les correspondances peuvent être préfixées par un code de langue. Consultez la section langues pour configurer plusieurs [langues](/docs/languages) sur votre blog.

<h2 id="website">Créer un site web</h2>

Nous appelons généralement un site web « un blog » lorsqu'il contient des articles et que la page d'accueil les liste tous. C'est le comportement par défaut de Hyvor Blogs. Même en dehors de cet usage, vous pouvez utiliser Hyvor Blogs pour créer un site web général. Par exemple, vous pouvez créer une page de destination pour la page d'accueil, et placer votre blog dans le sous-répertoire `/blog`.

<h2 id="collections">Plusieurs collections d'articles</h2>

Par défaut, votre blog possède une seule collection d'articles, et tous les articles seront listés sur la page d'index. Que faire si vous souhaitez avoir des collections séparées, par exemple des articles de blog et des épisodes de podcast dans le blog ? Vous pouvez utiliser les routes et les [tags](/docs/tags) pour y parvenir.

- Nous ajoutons un tag `podcast` à tous les articles de podcast.
- Nous pouvons personnaliser la route `index` (`/`) et son modèle (`index.twig`) pour afficher un aperçu des articles de blog et des épisodes de podcast. Pour ce faire, vous devrez mettre à jour `index.twig` et utiliser notre [API de données](/docs/api-data) pour récupérer les articles séparément.
- Nous ajoutons deux nouvelles routes avec un nouveau modèle :
  - `/blog` -> pour lister les articles de blog (en utilisant le filtre `tag.slug != podcast`)
  - `/podcast` -> pour lister les épisodes de podcast (en utilisant le filtre `tag.slug = podcast`)

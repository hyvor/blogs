<script lang="ts">
	import { Table, TableRow, Callout } from '@hyvor/design/components';
</script>

# Modèles

Lorsqu'un blog reçoit une requête, nous faisons d'abord correspondre son chemin à une route (supposons la route `post` pour `/hello-world`). Ensuite, nous récupérons les données nécessaires depuis notre base de données, puis nous appelons le fichier de modèle Twig défini dans cette route (`post.twig`).

À l'intérieur de ce fichier, vous pouvez inclure d'autres fichiers ou même utiliser l'<a href="https://twig.symfony.com/doc/3.x/templates.html#template-inheritance" rel="nofollow">héritage</a>. Vous pouvez même appeler notre [API de données](/docs/api-data) pour récupérer plus de données (plus d'informations ci-dessous) !

<h2 id="twig">Twig</h2>

Nous utilisons <a href="https://twig.symfony.com/doc/3.x/" rel="nofollow">Twig 3.0</a> pour le templating. C'est un langage puissant avec de nombreuses balises, filtres et fonctions intégrés. Twig dispose également d'une documentation agréable et facile à suivre, ce qui est l'une des raisons pour lesquelles nous avons choisi Twig plutôt que d'autres langages de modèles. Si vous ne l'avez jamais utilisé, parcourez la page <a href="https://twig.symfony.com/doc/3.x/templates.html" rel="nofollow">Twig pour les concepteurs de modèles</a>, et vous aurez une idée de son fonctionnement. En gros, c'est du HTML avec des super-pouvoirs.

### Modèles

Ce dossier contient les fichiers de modèles. Il existe plusieurs types de fichiers de modèles

<Table columns="1fr 3fr 2fr" hover>
	<TableRow head>
		<div>Type</div>
		<div>Description</div>
		<div>Exemple</div>
	</TableRow>

    <TableRow>
    	<div>Principal</div>
    	<div>Ces fichiers de modèles sont rendus directement.</div>
    	<div><code>index.twig</code> <code>post.twig</code></div>
    </TableRow>

    <TableRow>
    	<div>Partiel</div>
    	<div>
    		Ces modèles ne sont pas rendus directement mais inclus dans les fichiers de modèles principaux. Ils commencent par
    		un trait de soulignement (<code>_</code>)
    	</div>
    	<div><code>_footer.twig</code></div>
    </TableRow>

    <TableRow>
    	<div>Route</div>
    	<div>
    		Ces modèles sont utilisés pour définir des routes personnalisées pour un blog. Le nom du fichier commence par <code
    			>route-</code
    		>. Voir <a href="/docs/themes-templates#custom-routes">routes personnalisées</a> ci-dessous
    	</div>
    	<div><code>route-authors.twig</code></div>
    </TableRow>

    <TableRow>
    	<div>Composant</div>
    	<div>
    		Ces modèles sont utilisés pour définir de nouvelles structures HTML pour des composants complexes comme les
    		aperçus de liens. Voir <a href="/docs/writing#link-bookmark">Signet de lien</a>.
    	</div>
    	<div><code>component-rich-link.twig</code></div>
    </TableRow>

</Table>

<h2 id="variables">Variables de thème</h2>

- Le développeur de thème (vous) crée le **thème**
- Le blogueur crée le contenu **(données)**
- HB combine le **thème** et les **données** et génère le blog

Lors du rendu des modèles twig, nous envoyons les données dans votre fichier de modèle sous forme d'objets. Vous utiliserez ces données pour générer une belle interface utilisateur.

Il y a 4 objets principaux dans HB : `Blog`, `Post`, `Tag`, et `Author`. Ces objets sont expliqués dans la page [API de données](/docs/api-data).

<Table columns="1fr 1fr 3fr" hover>
	<TableRow head>
		<div>Nom de la variable</div>
		<div>Routes disponibles</div>
		<div>Description</div>
	</TableRow>

    <TableRow>
    	<div><code>_blog</code></div>
    	<div>(toutes)</div>
    	<div>Un objet Blog, qui inclut toutes les données/paramètres au niveau du blog.</div>
    </TableRow>

    <TableRow>
    	<div><code>_lang</code></div>
    	<div>(toutes)</div>
    	<div>
    		Un objet Langue pour la langue <b>actuelle</b>. Doit également être placé dans
    		<code>{`<html lang="{{ _lang.code }}">`}</code>
    	</div>
    </TableRow>

    <TableRow>
    	<div><code>_config</code></div>
    	<div>(toutes)</div>
    	<div>Configuration du thème (<code>config.yaml</code>) sous forme d'objet</div>
    </TableRow>

    <TableRow>
    	<div><code>_route</code></div>
    	<div>(toutes)</div>
    	<div>Nom de la <a href="/docs/routes">route</a> actuelle</div>
    </TableRow>

    <TableRow>
    	<div><code>_posts</code></div>
    	<div>(toutes)</div>
    	<div>
    		Un tableau d'objets Post, filtrés par la valeur de filtre de la <a href="/docs/routes">route</a>
    	</div>
    </TableRow>

    <TableRow>
    	<div><code>_featured_post</code></div>
    	<div>index</div>
    	<div>Un tableau d'objets Posts (tous les articles en vedette)</div>
    </TableRow>

    <TableRow>
    	<div><code>_post</code></div>
    	<div>post et page</div>
    	<div>Un objet Post</div>
    </TableRow>

    <TableRow>
    	<div><code>_tag</code></div>
    	<div>tag</div>
    	<div>Un objet Tag (le tag actuel)</div>
    </TableRow>

    <TableRow>
    	<div><code>_author</code></div>
    	<div>author</div>
    	<div>Un objet Author (l'auteur actuel)</div>
    </TableRow>

    <TableRow>
    	<div><code>_branding</code></div>
    	<div>toutes</div>
    	<div>Booléen, indiquant s'il faut afficher la marque Hyvor Blogs.</div>
    </TableRow>

</Table>

Chaque route obtient des variables différentes. Nous préfixons chaque variable avec `_` afin qu'elle n'entre pas en conflit avec les variables que vous définissez dans les fichiers de thème (évidemment, vous ne devriez pas préfixer vos variables avec `_` dans le modèle Twig)

<h2 id="placeholders">Espaces réservés</h2>

Vous devez placer certains espaces réservés dans votre thème pour faire fonctionner certaines choses.

<Table columns="1fr 1fr 4fr" hover>
	<TableRow head>
		<div>Espace réservé</div>
		<div>Portées</div>
		<div>Description</div>
	</TableRow>

    <TableRow>
    	<div><code>_head</code></div>
    	<div>(toutes)</div>
    	<div>
    		à placer avant <code>{`</head>`}</code>. Nous ajoutons automatiquement les balises SEO, le lien styles.css, et
    		le code_head défini par le blogueur
    	</div>
    </TableRow>

    <TableRow>
    	<div><code>_foot</code></div>
    	<div>(toutes)</div>
    	<div>à placer avant <code>{`</body>`}</code>. Nous plaçons le code_foot défini par le blogueur</div>
    </TableRow>

    <TableRow>
    	<div><code>_comments</code></div>
    	<div>post et page</div>
    	<div>pour intégrer le système de commentaires</div>
    </TableRow>

    <TableRow>
    	<div><code>_comment_count</code> (facultatif)</div>
    	<div>post et page</div>
    	<div>
    		pour afficher le nombre de commentaires de cette page. Par exemple, certains thèmes affichent le nombre de commentaires en
    		haut avec un lien vers la section commentaires pour encourager davantage de commentaires. Fonctionne uniquement lorsque Hyvor Talk
    		est connecté
    	</div>
    </TableRow>

    <TableRow>
    	<div><code>_newsletter</code></div>
    	<div>post et page</div>
    	<div>pour intégrer le formulaire d'inscription à la newsletter</div>
    </TableRow>

</Table>

Il est absolument nécessaire d'envoyer tous les espaces réservés (sauf `_lang`) à travers le filtre `template` pour qu'ils soient rendus comme des modèles.

```html
{{ _head | template }}
```

<Callout type="info">
	<p>
		<code>{`{{ _head | template }}`}</code> est équivalent à
		<code>{`{{ include(template_from_string(_head)) }}`}</code>
		en Twig. Nous avons défini le filtre personnalisé <code>template</code> pour vous faciliter l'écriture,
		car il est utilisé fréquemment dans les modèles HB.
	</p>
</Callout>

<h2 id="twig-helpers">Assistants Twig</h2>

Nous fournissons quelques fonctions et filtres Twig personnalisés pour faciliter l'écriture des modèles.

<h3 id="helper-functions">Fonctions</h3>

- `data` - une fonction pour appeler l'API de données. Voir Récupération de données ci-dessous.

```html
{% set posts = data(endpoint="posts", filter="author.slug=user") %}
```

- `icon` - une fonction pour obtenir une icône.

```html
{{ icon('bootstrap', 'arrow-down', 20, 20) }}
```

Définition de la fonction : `icon(iconLibrary, iconName, width, height)`

- Tous les noms d'icônes sont en minuscules, et les mots sont séparés par `-` (`arrow-down`).
- Ces bibliothèques d'icônes sont prises en charge

<ul>
	<li><a href="https://icons.getbootstrap.com/" rel="nofollow">bootstrap</a></li>
	<li>
		<a href="https://fontawesome.com/icons" rel="nofollow">fontawesome</a>Icônes gratuites uniquement
	</li>

    <ul>
    	<li>ajouter <code>-regular</code> aux icônes normales (<code>calendar-regular</code>)</li>
    	<li>ajouter <code>-solid</code> aux icônes pleines (<code>calendar-solid</code>)</li>
    	<li>Ne rien ajouter pour les icônes de marque (<code>github</code>)</li>
    </ul>

    <li><a href="https://ionic.io/ionicons" rel="nofollow">ionicons</a></li>
    <li><a href="https://heroicons.com/" rel="nofollow">heroicons</a></li>
    <ul>
    	<li>ajouter <code>-solid</code> aux icônes pleines (<code>archive-solid</code>)</li>
    	<li>ajouter <code>-outline</code> aux icônes en contour (<code>archive-outline</code>)</li>
    </ul>

    <li><a href="https://primer.github.io/octicons" rel="nofollow">octicons</a></li>
    <li><a href="https://css.gg/" rel="nofollow">css.gg</a></li>

    <Callout type="info">
    	<p>
    		En interne, nous utilisons la bibliothèque open-source <a href="https://github.com/hyvor/php-svg-icons">php-svg-icons</a
    		>. Si vous avez besoin d'ajouter d'autres bibliothèques d'icônes, veuillez y envoyer une PR.
    	</p>
    </Callout>

</ul>

<h3 id="helper-filters">Filtres</h3>

<ul>
	<li><code>asset_url</code> - un filtre pour lier des ressources</li>

    <ul>
    	<li>Transforme un nom de fichier de ressource en son URL absolue.</li>
    	<li>Ajoute l'horodatage de dernière mise à jour comme paramètre de requête (pour contourner le cache du navigateur lors des mises à jour)</li>

```html
{{ 'script.js' | asset_url }}
```

```html
<script src="{{ 'script.js' | asset_url }}"></script>

// se transforme en :

<script src="https://subdomain.hyvorblogs.io/assets/script.js?v=12931923993"></script>
```

    </ul>

    <li><code>asset</code> - un filtre pour imprimer directement des ressources (uniquement pour les ressources textuelles comme les SVG)</li>

```html
{{ 'beauty.svg' | asset }}
```

    <li><code>pagination_page_url</code> - un filtre pour convertir un numéro de page en URL complète</li>

```html
<a href="{{ _pagination.page_prev | pagination_page_url }}">Page précédente</a>
```

    <li>
    	<code>lang</code> - un filtre pour les traductions. Apprenez-en plus dans
    	<a href="/docs/themes-internationalization">internationalisation</a>.
    </li>
    <li>
    	<code>lang_by_number</code> - Voir
    	<a href="/docs/themes-internationalization#lang-by-number"
    		>chaînes conditionnelles basées sur un nombre</a
    	>.
    </li>
    <li>
    	<code>language_variant_url</code> - Voir
    	<a href="/docs/themes-internationalization#language-switcher">sélecteur de langue</a>
    </li>

    <li>
    	<code>toc</code> - un filtre pour générer une table des matières à partir d'une chaîne HTML.

```html
{{ _post.content | toc }}
```

    	Par défaut, tous les titres sont inclus dans la table des matières. Vous pouvez définir les niveaux à
    	inclure comme suit :

```html
{{ _post.content | toc('2,3') }}
```

    </li>

</ul>

<Callout type="info">
	<p>
		La différence entre les fonctions et les filtres peut être assez déroutante dans Twig. Notre règle générale est
		d'utiliser les fonctions pour calculer des choses (<code>data</code> et <code>icon</code>) et d'utiliser les filtres
		lorsqu'on applique une transformation (<code>asset_url</code>, <code>asset</code>, etc.).
	</p>
</Callout>

<h2 id="fetch-data">Récupération de données</h2>

Utilisez la fonction `data` pour récupérer des données depuis notre [API de données](/docs/api-data).

```html
<!-- Récupérer les données -->
{% set recent_posts = data(endpoint="posts", sort="published_at DESC", limit="5") %}

<!-- Afficher l'interface -->
<div id="recent-posts">
	{% for post in recent_posts.data %} {% include '_recent-post-card.twig' with post %} {% endfor %}
</div>
```

Utilisez l'argument nommé `endpoint` pour définir le point de terminaison de l'API. Vous pouvez définir tous les autres paramètres simplement en les envoyant comme arguments nommés à la fonction Twig `data` (Ex : `sort="published_at DESC"`).

<h2 id="custom-routes">Routes personnalisées</h2>

Il existe deux façons d'ajouter des routes personnalisées :

- Le blogueur peut ajouter des routes personnalisées depuis la console ([voir la documentation](/docs/routes#custom)).
- Les développeurs de thèmes peuvent définir des routes personnalisées en ajoutant des fichiers nommés `route-{route}.twig` au dossier `templates`.

La première option est plus robuste, et elle offre un moyen plus facile de définir automatiquement les variables d'entrée `_posts` (par filtrage), `_tag`, `_author`, etc. afin que vous puissiez y accéder sans appeler l'API de données. Mais, en tant que développeur de thème, vous devrez utiliser la deuxième option.

Par exemple, disons que vous décidez que votre thème doit avoir une page listant tous les auteurs du blog. Vous pouvez ajouter un fichier `route-authors.twig` au dossier `templates`. Si le blog reçoit une requête vers `/authors`, ce modèle sera rendu automatiquement.

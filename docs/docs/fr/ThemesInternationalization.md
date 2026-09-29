<script lang="ts">
	import { Callout } from '@hyvor/design/components';
</script>

# Internationalisation

Nous ne nous attendons pas à ce que vous traduisiez les thèmes dans plusieurs langues, mais le thème doit être traduisible. Cela signifie que toutes les chaînes de caractères dans les fichiers de template **doivent être traduisibles**. C'est une exigence si vous prévoyez de [publier](https://blogs.hyvor.com/docs/themes-publishing) le thème.

<Callout type="info">
	<p>Si vous créez un thème privé pour un blog dans une seule langue, vous pouvez sauter cette partie.</p>
</Callout>

L'internationalisation est facile. Au lieu d'écrire ceci :

```html
<h1>Welcome</h1>
```

Vous devez écrire ceci :

```html
<h1>{{ 'welcome' | lang }}</h1>
```

Ici, `'welcome'` est une clé dans `en.yaml`. Et `lang` est un filtre Twig personnalisé défini par HB. HB affichera la bonne langue en fonction de la [langue du blog de l'utilisateur](/docs/languages).

<h2 id="lang-folder">Dossier lang</h2>

Le dossier `/lang` contient des fichiers de langue `.yaml`. Un fichier de langue peut ressembler à ceci :

```yaml
welcome: Welcome
```

<h2 id="english-required">L'anglais est requis</h2>

L'anglais (`en.yaml`) est la langue par défaut et il est requis. Vous pouvez également définir d'autres langues. Les codes de langue doivent être des <a href="https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes" rel="nofollow">codes ISO 639-1</a>.

Voyons un autre exemple :

```yaml
welcome: 'Welcome to our blog'
usersCount: '* users'
byAuthor: 'by {authorName}'
```

Dans les templates Twig, utilisez le filtre `lang` pour afficher ces chaînes avec les espaces réservés remplacés.

```html
<h1>
    {{ 'welcome' | lang }}
</h1>

<p>{{ 'byAuthor' | lang(authorName=_author.name) }}</p>

<p>{{ 'usersCount' | lang(2) }}</span>
```

Comme vous pouvez le voir, il existe deux types d'espaces réservés :

- `*`
  - À utiliser si la chaîne n'a qu'une seule entrée (dans la plupart des cas, un nombre)
- `nommé`
  - exemple : `{authorName}`
  - À utiliser si la chaîne a plusieurs entrées ou si les entrées sont des données pouvant être décrites par un nom.
  - Vous pouvez avoir plusieurs espaces réservés nommés dans la chaîne.
  - Dans le filtre Twig lang, utilisez des <a href="https://twig.symfony.com/doc/3.x/templates.html#named-arguments" rel="nofollow">arguments nommés</a> pour remplir les espaces réservés avec des données réelles.

<h2 id="lang-by-number">Chaînes conditionnelles basées sur un nombre</h2>

Parfois, vous pouvez vouloir afficher un message différent lorsqu'un nombre est zéro, un, ou plus d'un. Au lieu d'écrire un tas de conditions if, vous pouvez utiliser le filtre Twig personnalisé `lang_by_number`.

```yaml
# en.yaml
posts_num_zero: No Posts
posts_num_one: 1 Post
posts_num_multi: '* Posts'
```

```yaml
Number of posts:
{{ _pagination.total | lang_by_number(
    zero="posts_num_zero",
    one="posts_num_one",
    multi="posts_num_multi"
) }}
```

`_pagination.total` est un nombre. Le `*` dans `posts_num_multi` sera remplacé par le nombre donné.

<h2 id="how-tranlsations-work">Comment fonctionnent les traductions</h2>

Vous pouvez consulter le guide [languages](/docs/languages). Il explique comment changer la langue ou configurer plusieurs langues sur un blog.

Disons que le blogueur change la langue de son site en français (`fr`). Ensuite, nous vérifions si un `fr.yaml` est disponible dans le dossier `lang`. Si ce n'est pas le cas, nous afficherons simplement les chaînes en anglais. Cependant, n'importe qui peut facilement ajouter un `fr.yaml` depuis la Console (même quelqu'un sans connaissances techniques peut le faire). Les clés ne changent pas, seules les chaînes changent.

Voici à quoi ressemblerait une version `fr` du fichier ci-dessus.

```yaml
welcome: 'Bienvenue sur notre blog'
usersCount: '* utilisateurs'
byAuthor: 'par {authorName}'
```

<Callout type="info">
	<p>N'UTILISEZ PAS de clés imbriquées dans les fichiers de langue YAML. Gardez-le sous forme de simples paires clé-valeur.</p>
</Callout>

<h2 id="language-switcher">Sélecteur de langue</h2>

Généralement, vous voulez afficher un sélecteur de langue dans les blogs multilingues pour permettre aux visiteurs de basculer entre les langues.

```html
{% if _blog.languages | length > 1 %}
<script>
	function toggleLanguageDropdown() {
		document.querySelector('.dropdown').classList.toggle('open');
	}
</script>
<div class="language-switcher">
	<a class="current-language" onclick="toggleLanguageDropdown()">{{ _lang.code }}</a>
	<div class="dropdown">
		{% for lang in _blog.languages %}
		<a
			href="{{ lang.code | language_variant_url }}"
			class="{% if lang.code == _lang.code %}active{% endif %}"
			>{{ lang.name }}</a
		>
		{% endfor %}
	</div>
</div>
{% endif %}
```

- `{% if _blog.languages | length > 1 %}` vérifie si le blog a plus d'une langue. Il n'est pas nécessaire d'avoir un sélecteur de langue dans les blogs à langue unique.
- `_lang` est la langue actuelle. Ainsi, `{{ _lang.code }}` affiche le code de la langue actuelle.
- `{% for lang in _blog.languages %}` parcourt toutes les langues du blog et affiche un élément `<a>` pour chaque langue dans le menu déroulant.
- Le filtre [helper](/docs/themes-templates#twig-helpers) `language_variant_url` est utilisé pour générer l'URL. Ce filtre trouve la meilleure variante linguistique possible de la page actuelle. Par exemple, si la page actuelle est un article, cette fonction donnera l'URL de la variante linguistique de cet article uniquement si cette variante existe. Sinon, l'URL de la page d'index (dans le code de langue donné) sera retournée.

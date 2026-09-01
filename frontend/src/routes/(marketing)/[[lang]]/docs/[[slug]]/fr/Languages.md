<script lang="ts">
	import { DocsImage } from '@hyvor/design/marketing';
	import { Table, TableRow } from '@hyvor/design/components';
</script>

# Langues

Hyvor Blogs propose une prise en charge intégrée du multilinguisme. Ce guide vous aidera à configurer correctement les langues de votre blog. Les paramètres de langue se trouvent dans **Paramètres &rarr; Langues**.

<h2 id="primary-language">Langue principale</h2>

**L'anglais (en)** est la langue principale des blogs nouvellement créés. Si vous bloguez dans une langue différente, il est important de modifier la langue dans les paramètres de langue afin d'indiquer aux utilisateurs, navigateurs et robots d'indexation la langue de votre blog.

Chaque langue de votre blog possède un code, un nom et une direction.

- **Code** - Le code de langue doit être une valeur valide d'<a href="https://developer.mozilla.org/en-US/docs/Web/HTML/Global_attributes/lang" target="_blank" rel="nofollow">attribut HTML lang</a>. Voici quelques exemples valides
  - `en`
  - `en-US`
  - `en-GB`
  - `fr`
  - `fr-FR`
- **Nom** - Le nom de la langue est le texte qui explique le code de langue. Nous vous recommandons de l'écrire dans l'alphabet natif. Certains thèmes peuvent utiliser le nom pour afficher un message comme « Cet article est traduit en Español, 简体中文, et Nederlands ».
- **Direction** - De gauche à droite ou de droite à gauche. Définir cette option sur RTL modifiera l'interface en conséquence. Tous les thèmes officiels sont conçus avec la prise en charge du RTL.

<h2 id="mutiple-languages">Configuration de plusieurs langues</h2>

Tout dans Hyvor Blogs est conçu pour prendre en charge plusieurs langues. La plupart des textes peuvent être traduits depuis l'interface de la Console, tandis que certains textes (comme les textes du thème) doivent être traduits dans des fichiers YAML.

<h3 id="add-language">Étape 1 : Ajouter une langue</h3>

Allez dans **Paramètres &rarr; Langues** et cliquez sur le bouton **Ajouter une langue** pour ajouter une nouvelle langue.

<DocsImage src="/images/docs/languages/add-language.png" alt="Add Language" />

Lorsque vous ajoutez une nouvelle langue, tous les articles seront traduisibles dans cette langue. Les langues non principales auront des pages d'index, par exemple, `/fr` pour le français. Toutes les [routes de votre blog](/docs/routes) seront disponibles dans la nouvelle langue dans le même format.

<h3 id="translate-posts">Étape 2 : Traduire les articles</h3>

Dans l'éditeur d'articles, vous verrez désormais une option pour passer d'une langue à l'autre. Lorsque vous basculez vers une nouvelle langue pour la première fois dans un article, une nouvelle « variante » de brouillon sera créée.

<DocsImage
	src="/images/docs/languages/translate-post-variant.gif"
	alt="Translate post - create draft variant"
/>

Ensuite, vous pouvez traduire le contenu, le titre, le slug et la description de votre article dans la nouvelle langue. Si vous utilisez le slug `bonjour-monde` pour la variante française, l'URL de l'article sera `/fr/bonjour-monde`.

<h3 id="translate-data">Étape 3 : Traduire les données</h3>

Toutes les données de votre blog peuvent être traduites. Par exemple, le nom du blog, la description, les ancres de navigation, etc. Voici les endroits où vous pouvez traduire les données.

<Table columns="1fr 1fr">
	<TableRow head>
		<div>Données</div>
		<div>Où traduire</div>
	</TableRow>
	<TableRow>
		<div>Nom du blog, description</div>
		<div>Paramètres → Général</div>
	</TableRow>
	<TableRow>
		<div>Ancres de navigation</div>
		<div>Paramètres → Navigation</div>
	</TableRow>
	<TableRow>
		<div>Slug, description, titre de l'article</div>
		<div>Éditeur d'articles</div>
	</TableRow>
	<TableRow>
		<div>Nom, biographie de l'auteur</div>
		<div>Paramètres → Utilisateurs</div>
	</TableRow>
	<TableRow>
		<div>Nom, description de l'étiquette</div>
		<div>Paramètres → Étiquettes</div>
	</TableRow>
</Table>

Les paramètres traduisibles auront une interface comme celle-ci. Créez d'abord une variante, puis traduisez les données.

<DocsImage src="/images/docs/languages/translate-data.gif" alt="Translate data" />

<h3 id="translate-theme">Étape 4 : Traduire le thème</h3>

Tous les thèmes officiels sont conçus avec la prise en charge du multilinguisme. Vous pouvez traduire les textes du thème dans des fichiers YAML.

1. Allez dans la section **Thème &rarr; lang** de la Console. Tous les fichiers de langue se trouvent dans le dossier `lang`. `en.yaml` est le fichier de langue par défaut.
2. Copiez le contenu du fichier `en.yaml`.
3. Créez un nouveau fichier avec le code de langue dans laquelle vous souhaitez traduire. Par exemple, `fr.yaml` pour le français. Ensuite, collez le contenu copié dans le nouveau fichier.
4. Enfin, commencez à traduire les textes.
   - Les fichiers YAML contiennent des paires `clé : valeur`.
   - Conservez les clés telles quelles. Ne traduisez que les valeurs.
   - `*` et `{key}` sont des espaces réservés. Ne les traduisez pas.
   - Vous pouvez entourer la valeur de guillemets doubles (") si vous souhaitez utiliser des caractères spéciaux comme `:`, `*`, etc.

Exemple :

`en.yaml`

```yaml
comments: Comments
posts_num_multi: '* Posts'
author: 'by {name}'
```

`fr.yaml`

```yaml
comments: Commentaires
posts_num_multi: '* Articles'
author: 'par {name}'
```

<h2 id="technical-seo">SEO technique</h2>

Voici quelques travaux effectués en coulisses par Hyvor Blogs pour s'assurer que les robots des moteurs de recherche comprennent vos pages multilingues. Vous n'avez rien à faire pour cela.

HB ajoute l'attribut lang à la balise `<html>` de toutes les pages en utilisant le code de langue que vous avez défini (c'est pourquoi l'utilisation des codes de langue corrects est importante).

```html
<html lang="en"></html>
```

De plus, HB ajoutera des balises alternatives `hreflang`. Par exemple, si vous avez trois langues (`en`, `fr`, `es`), la page d'index en anglais (/) aura ces balises.

```html
<link rel="alternate" href="https://yourblog.com/fr" hreflang="fr" />
<link rel="alternate" href="https://yourblog.com/es" hreflang="es" />
```

Pour les articles, nous ajouterons ces balises alternatives **uniquement si** les variantes traduites sont **publiées**.

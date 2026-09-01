<script>
	import { Table, TableRow } from '@hyvor/design/components';
	import { DocsImage } from '@hyvor/design/marketing';
</script>

# Importer depuis un Sitemap

Cette méthode vous permet d'importer des articles de blog depuis **N'IMPORTE QUELLE PLATEFORME**, à condition que :

- Tous vos articles aient une structure HTML similaire, et que les données puissent être extraites du HTML à l'aide de balises meta et, éventuellement, de sélecteurs CSS.
- Vous disposiez d'un sitemap (XML ou TXT) contenant toutes les URL de vos articles. Si vous n'avez pas de sitemap, vous pouvez facilement en générer un à l'aide d'un outil en ligne. Ou, si vous avez une liste d'URL dans un fichier TXT, vous pouvez également l'utiliser.

<h2 id="how">Comment ça fonctionne</h2>

- Soumettez votre sitemap (`.xml` ou `.txt`)
- Fournissez des sélecteurs CSS pour nous aider à extraire les données de vos pages HTML
- Testez votre CSS sur quelques-unes de vos pages pour vous assurer que les données sont correctement analysées
- Enfin, importez depuis le sitemap

<h2 id="data">Données pouvant être extraites</h2>

Notre outil d'importation est capable d'extraire les données suivantes de vos pages HTML :

<Table columns="1fr 2fr">
	<TableRow head>
		<div>Donnée</div>
		<div>Méthode d'extraction</div>
	</TableRow>
	<TableRow>
		<div>Titre de l'article</div>
		<div>Sélecteur CSS ou <code>{`<title>`}</code></div>
	</TableRow>
	<TableRow>
		<div>Description de l'article</div>
		<div>Sélecteur CSS ou <code>{`<meta name="description">`}</code></div>
	</TableRow>
	<TableRow>
		<div>Contenu de l'article</div>
		<div>Sélecteur CSS. Voir <a href="#content">importation du contenu</a></div>
	</TableRow>
	<TableRow>
		<div>Date de publication de l'article</div>
		<div>Sélecteur CSS ou <code>{`<meta property="article:published_time">`}</code></div>
	</TableRow>
	<TableRow>
		<div>Image mise en avant de l'article</div>
		<div><code>{`<meta property="og:image">`}</code></div>
	</TableRow>
	<TableRow>
		<div>Slug de l'article</div>
		<div>depuis l'URL</div>
	</TableRow>
</Table>

Il **ne prend pas en charge** l'importation des données suivantes :

- Étiquettes - aucune étiquette ne sera ajoutée aux articles
- Auteurs - le propriétaire du blog sera ajouté en tant qu'auteur de tous les articles

<h2 id="css-selectors">Sélecteurs CSS</h2>

Chaque blog a une structure différente. Par conséquent, nous devons savoir comment extraire les données de vos pages HTML. Vous pouvez fournir des sélecteurs CSS pour chaque type de donnée.

Par exemple, dans ce blog :

<DocsImage src="/images/docs/import/css-selectors.png" alt="Sélecteurs CSS" />

Vous pouvez définir les sélecteurs CSS suivants :

- **Titre de l'article** : `h1`
- **Contenu de l'article** : `section.post-content`
- **Date de publication de l'article** : `time.publish-date`

Notez que seul le sélecteur de contenu de l'article est requis. Les autres sélecteurs sont optionnels. Si vous ne fournissez pas de sélecteur pour un type de donnée, nous essaierons de l'extraire de la page HTML à l'aide des balises meta comme expliqué dans le tableau ci-dessus.

<h2 id="content">Contenu</h2>

L'outil d'importation détectera automatiquement la plupart des styles (gras, italique) et blocs (paragraphes, citations) à partir de balises HTML génériques.

Il ne prend actuellement pas en charge l'importation des types de blocs suivants :

- Signet de lien
- HTML/Twig personnalisé

Il a une prise en charge limitée pour les types de blocs suivants :

- Intégration - Nous essaierons d'importer les iframes en tant qu'intégrations (ex : intégration Youtube). Cependant, nous ne pouvons pas garantir que cela fonctionnera pour toutes les intégrations.

<h3 id="content-exclude">Exclure du contenu</h3>

Vous pouvez exclure certaines parties de votre contenu à l'aide de sélecteurs CSS. Par exemple, si vous avez des publicités dans vos articles de blog, vous pouvez les exclure en ajoutant un sélecteur CSS dans **Exclure le contenu de l'article** :

```css
.post-content > .ad
```

Pour exclure plusieurs éléments, séparez-les à l'aide d'une virgule :

```css
.ad, .newsletter-signup
```

<h2 id="import-images">Importer des images</h2>

Si vous migrez complètement vers Hyvor Blogs, il est possible que les images ne soient plus disponibles sur le serveur d'origine. Par conséquent, nous vous recommandons d'importer les images vers Hyvor Blogs. Pour ce faire, assurez-vous de garder l'option Importer les images activée. Ensuite, nous importerons les images mises en avant et toutes les images du contenu des articles dans la [médiathèque](/docs/media) de votre blog. L'image doit faire moins de 50 Mo pour être importée.

<h2 id="test-import">Tester et importer !</h2>

Une fois que vous avez fourni toutes les informations requises, vous pouvez tester vos sélecteurs CSS sur quelques-uns de vos articles. Si tout est correct, vous pouvez importer tous les articles depuis le sitemap.

Si vous rencontrez des problèmes, n'hésitez pas à nous contacter

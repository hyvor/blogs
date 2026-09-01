<script lang="ts">
	import { DocsImage } from '@hyvor/design/marketing';
	import { Callout } from '@hyvor/design/components';
</script>

# Vue d'ensemble

Les thèmes de Hyvor Blogs sont entièrement personnalisables. Si vous avez de l'expérience avec HTML, CSS et
Javascript, vous pouvez facilement créer votre propre thème à partir de zéro. Cette page est une vue d'ensemble pour vous aider à
démarrer. Tous les thèmes officiels se trouvent dans le dépôt <a href="https://github.com/hyvor/hyvor-blogs-themes" rel="nofollow">hyvor-blogs-themes</a>. N'hésitez pas à consulter le code source des autres thèmes.

Voici quelques termes couramment utilisés dans cette documentation :

- **Développeur de thème** - la personne qui développe un thème (ce doit être vous !).
- **Blogueur** - La personne qui possède le blog. Elle peut installer le thème que vous créez et le modifier via la console.
- **Sous-domaine** - Partie sous-domaine de `{subdomain}`.hyvorblogs.io qui est attribuée au blogueur.
- **Route** - Routes du blog qui déterminent comment restituer une page ou quelle sortie renvoyer pour un chemin d'URL spécifique. Voir [routes](/docs/routes)
- **HB** - Hyvor Blogs
- **Rendu** - Combinaison d'un thème (modèle) avec les données du blog et retour d'une sortie HTML. Voir l'image ci-dessous.

<DocsImage src="/images/docs/overview/theme-dev-rendering.png" alt="Theme Rendering" />

<h2 id="basics">Notions de base</h2>

- Le langage de templating est <a href="https://twig.symfony.com/doc/" rel="nofollow">Twig 3.0</a>
- Le style prend en charge <a href="https://sass-lang.com/" rel="nofollow">SCSS</a>, mais vous pouvez simplement utiliser du CSS
- Les fichiers de configuration et de langue sont écrits en <a href="https://yaml.org/" rel="nofollow">YAML</a>.

<h2 id="routes">Routes</h2>

Pour commencer le développement de thème, il est essentiel de comprendre le fonctionnement des routes dans Hyvor Blogs. Les routes
sont des configurations au niveau du blog, ce qui signifie qu'elles sont configurées par le blogueur. Hyvor Blogs est fourni
avec des routes par défaut qui suffisent généralement pour un blog simple.

Avant de continuer, nous vous recommandons de lire notre guide [Routes](/docs/routes) pour bien comprendre
le fonctionnement des routes.

<h2 id="single-css-file">Fichier CSS unique</h2>

Il n'existe qu'un seul fichier CSS dans le blog, `styles.css`.

<h2 id="flashload">Flashload</h2>

[Flashload](https://github.com/hyvor/flashload) est ajouté à tous les blogs par défaut.
Il est donc important de garder Flashload à l'esprit lors de la conception des thèmes. Veuillez prendre une minute
pour lire la
[documentation de Flashload](https://github.com/hyvor/flashload#readme) afin de comprendre comment
cela fonctionne.

Pourquoi Flashload ? Les rechargements de navigateur sont lents. Ils chargent plusieurs fois les mêmes ressources CSS/JS, ce qui ralentit
le rendu des pages. Flashload commence à charger d'autres pages avant même que l'utilisateur clique sur le lien.
Cela rend la navigation plus fluide. Cela transforme simplement le blog en une **Single Page Application (SPA)** !

Nous avons appris précédemment qu'il n'existe qu'un seul `styles.css` pour un blog, contenant tout le
CSS du blog. Ce `styles.css` doit être chargé à l'intérieur du
`<head>`
de la page. Lorsque l'utilisateur navigue vers une autre page, Flashload envoie une requête AJAX vers ce chemin et
précharge la page HTML. Ensuite, il met à jour **uniquement la partie** `<body>`. (Rappelez-vous, nous avons déjà tout le CSS chargé
lors de la première requête, donc nous ne voulons pas le
charger à nouveau).

La règle simple est d'ajouter les ressources partagées du blog dans `<head>`.

<h2 id="caching">Mise en cache</h2>

Un autre comportement important de HB est que nous utilisons la mise en cache de manière INTENSIVE. Nous utilisons une technique appelée **mise en cache à la première requête**.

- Quelqu'un demande le chemin `/hello-world` d'un blog.
- Nous n'avons aucune sortie en cache pour ce chemin. Nous récupérons les données de notre base de données, les combinons avec le modèle, générons la sortie HTML, et renvoyons la réponse à l'utilisateur. En arrière-plan, nous enregistrons la sortie HTML générée dans notre cache.
- Lorsqu'une autre personne demande le même chemin, la sortie HTML est directement envoyée depuis le cache. Elle ne passe pas par le processus de rendu.

Lorsqu'on utilise un cache, vider le cache est l'élément le plus important. Nous devons nous assurer que du
contenu obsolète n'est pas livré lorsque quelque chose change. Voici les événements pour lesquels nous vidons le cache pour chaque
portée.

- chaque fois que des données quelconques sont modifiées dans le blog
- chaque fois que le thème est modifié
- le 1er janvier

Et,

- les routes `/search` et `/p/{hash}` (pages d'aperçu) sont toujours dynamiques, jamais mises en cache.

<Callout type="info">
	<p>
		⚠️ La mise en cache rend le blog super rapide. Cependant, elle impose certaines limitations au développement de thème.
		Vous ne pouvez pas restituer de données dynamiques comme la « date actuelle » avec Twig. À cause du cache, les utilisateurs peuvent voir une
		date ancienne. Si c'est absolument nécessaire, vous devez utiliser Javascript pour restituer du contenu dynamique dans le
		navigateur de l'utilisateur. Cependant, afficher la « date de publication » d'un article fonctionne bien car nous vidons le cache
		chaque fois que l'article est mis à jour. De même, afficher l'année en cours fonctionnera, car nous nous
		assurerons de vider le cache le 1er janvier.
	</p>
</Callout>

<h2 id="starting-developement">Démarrer le développement</h2>

Configurons votre environnement de développement local.

- Tout d'abord, vous avez besoin d'un blog DEV. Créez-en un sur [/console/new/dev](/console/new/dev). Les blogs DEV sont similaires aux blogs normaux dans Hyvor Blogs, cependant ils sont gratuits, et ne peuvent être utilisés que pour le développement de thème.
- Vous obtiendrez un sous-domaine au format `dev-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`. Nous en aurons besoin plus tard.

Pour les étapes suivantes, vous avez besoin de <a href="https://nodejs.org/en/" rel="nofollow">Node.js</a> (et npm).
En tant que développeur front-end, nous espérons que vous l'avez déjà installé :)

- Ensuite, installez notre outil CLI via npm

```bash
npm install -g hyvor-blogs-cli
```

- Créez un nouveau répertoire sur votre ordinateur, qui contiendra tous les fichiers et configurations du thème.

```bash
mkdir my-theme
```

- Faites `cd` vers le dossier du thème et exécutez `hyvor-blogs-cli init`

```bash
cd my-theme
hyvor-blogs-cli init
```

Cette commande créera la structure de dossiers suivante à l'intérieur de votre dossier de thème.

```
/
    /assets
    /lang
        en.yaml
    /styles
        index.scss
    /templates
        @base.twig
        author.twig
        index.twig
        post.twig
        tag.twig
    .env
    config.def.yaml
    config.yaml
```

Vous pouvez également créer manuellement cette structure de dossiers, si vous le souhaitez.

- Ensuite, ouvrez le fichier `.env` et mettez à jour `SUBDOMAIN` avec le sous-domaine de votre blog DEV (que vous avez créé précédemment).

```bash
SUBDOMAIN=dev-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
```

- Ensuite, exécutez la commande `hyvor-blogs-cli` pour servir votre blog

```bash
hyvor-blogs-cli
```

- Ouvrez votre blog (dev-xxx.hyvorblogs.io) dans votre navigateur pour voir le thème.

<Callout type="info">
	<p>
		<b>Comment ça fonctionne :</b> La commande <code>hyvor-blogs-cli</code> exécute un processus qui surveille les
		changements de vos fichiers locaux et les synchronise avec notre environnement de production. Ainsi, chaque fois que vous ajoutez, modifiez ou supprimez un
		fichier dans votre dossier de thème, il sera synchronisé avec les fichiers de thème de votre blog DEV.
	</p>

    <p>
    	<b>Avis de sécurité 1 :</b> Comme tous les fichiers de votre répertoire de thème sont synchronisés avec notre système
    	de production, n'y ajoutez jamais de fichiers confidentiels.
    </p>

    <p>
    	<b>Avis de sécurité 2 :</b> Ne partagez pas publiquement votre sous-domaine DEV. Cela permettrait à d'autres utilisateurs de
    	modifier les fichiers de thème de votre blog DEV. Si vous utilisez GIT pour le versionnage, assurez-vous d'ajouter
    	<code>.env</code>
    	à <code>.gitignore</code>.
    </p>

</Callout>

<h2 id="folder-structure">Structure des dossiers</h2>

Comme vous le voyez, il y a quatre dossiers dans un dossier de thème HB. Les dossiers imbriqués ne sont **pas pris en charge**.

```
/
    /templates
    /styles
    /assets
    /lang
    config.yaml
```

- **templates** : Tous les fichiers de modèles Twig vont ici. Voir [templates](/docs/themes-templates).
- **styles** : Tous les fichiers SCSS vont ici. Voir [styling](/docs/themes-styles).
- **assets** : Vous pouvez ajouter des SVG, PNG, fichiers de police ou fichiers Javascript ici. Tous les fichiers de ce répertoire sont accessibles publiquement via la route /assets `/{file_name}`. Tout type de fichier est pris en charge.
- **lang** : Tous les fichiers de langue vont ici. Voir [internationalization](/docs/themes-internationalization).

Le dossier racine contient les fichiers de configuration. Voir [configuration](/docs/themes-config).

<script lang="ts">
	import { Callout } from '@hyvor/design/components';
</script>

<h1 id="headless">Sans tête (Headless)</h1>

Vous pouvez utiliser Hyvor Blogs uniquement comme **CMS headless** - en rédigeant et gérant le contenu dans Hyvor Blogs, tout en affichant le blog vous-même avec votre propre framework (Next.js, SvelteKit, Astro, etc.) ou même une application mobile native.

<Callout type="info">
	Cette page présente une vue d'ensemble de l'approche, et non un tutoriel complet étape par étape. Consultez la
	<a href="/docs/api-data">référence de l'API Data</a> pour la liste complète des points de terminaison, objets et paramètres de requête
	utilisés ci-dessous.
</Callout>

<h2 id="why">Pourquoi passer en mode headless ?</h2>

- Vous avez déjà un frontend (site marketing, application, site de documentation) et vous voulez que les articles de blog y vivent, sur le même domaine et avec le même système de design.
- Vous voulez un contrôle total sur le routage, la mise en page et le rendu - au-delà de ce que permettent les [thèmes](/docs/themes-overview).
- Vous construisez une application mobile ou un autre client non-web qui a besoin du contenu du blog sous forme de données.

Si vous voulez simplement un blog hébergé avec une apparence personnalisée, écrire un [thème personnalisé](/docs/themes-overview) est généralement plus simple que de passer en mode headless - les thèmes s'exécutent toujours entièrement sur l'infrastructure de Hyvor Blogs (hébergement, mise en cache, balises SEO, redirections) pour vous. Le mode headless a du sens lorsque le blog doit faire partie d'une application existante que vous possédez déjà.

<h2 id="how-it-works">Comment ça fonctionne</h2>

1. Créez un blog sur Hyvor Blogs et rédigez vos articles, tags et auteurs comme d'habitude dans l'éditeur.
2. Votre application frontend récupère les données du blog au moment de la compilation ou de la requête à l'aide de l'[API Data](/docs/api-data), une API JSON publique en lecture seule.
3. Votre application affiche ces données dans des pages en utilisant vos propres composants, votre routage et votre style.

Aucune clé API n'est requise pour l'API Data, vous pouvez donc l'appeler directement depuis le navigateur, depuis un serveur, ou au moment de la compilation dans un générateur de site statique.

<h2 id="fetching-a-list">1. Récupérer une liste d'articles</h2>

Utilisez le point de terminaison `/posts` sur le chemin de base de l'API Data de votre blog (`https://blogs.hyvor.com/api/data/v0/{subdomain}`) :

```ts
const res = await fetch('https://blogs.hyvor.com/api/data/v0/example/posts?limit=10');
const { data: posts, pagination } = await res.json();

// posts[0] -> { id, slug, title, description, published_at, url, tags, authors, ... }
```

Utilisez le paramètre [`keys`](/docs/api-data#keys) pour éviter de récupérer trop de données - pour une page de liste, vous n'avez généralement pas besoin du HTML complet du champ `content` :

```ts
/posts?keys=id,slug,title,description,published_at,featured_image_url,tags
```

La pagination, le filtrage et le tri fonctionnent tous de la même manière que partout ailleurs dans l'API Data - voir [page](/docs/api-data#page), [filter](/docs/api-data#filter) et [sort](/docs/api-data#sort).

<h2 id="fetching-a-post">2. Récupérer un seul article</h2>

Utilisez le point de terminaison `/post` avec soit `slug`, soit `id` :

```ts
const res = await fetch('https://blogs.hyvor.com/api/data/v0/example/post?slug=hello-world');
const post = await res.json();

// post.content is sanitized HTML, ready to render
```

Le champ `content` est du HTML généré par l'éditeur de Hyvor Blogs. Affichez-le directement (par exemple `{@html post.content}` dans Svelte, ou `dangerouslySetInnerHTML` dans React) - il est déjà nettoyé (sanitized). Les images, intégrations et autres médias référencés dans le contenu utilisent des URLs absolues, afin qu'ils s'affichent correctement quel que soit l'endroit où vous hébergez votre frontend.

<h2 id="routing">3. Routage</h2>

Comme vous n'utilisez pas de thème Hyvor Blogs, **vous êtes propriétaire de la structure des URL**. Un modèle courant consiste en une route dynamique comme `/blog/[slug]` dans votre application qui appelle `/post?slug=...` pour afficher la page, et une route de liste comme `/blog` qui appelle `/posts` pour construire un index. Comme Hyvor Blogs ne sert pas ces pages, les fonctionnalités qui dépendent du fait que Hyvor Blogs génère les pages pour vous - comme les [redirections](/docs/redirects) automatiques, les [routes personnalisées](/docs/routes), ou le [SEO](/docs/seo) au niveau du thème - ne s'appliquent pas ; vous êtes responsable des balises SEO, des plans de site et des redirections vous-même dans votre propre application.

<h2 id="build-vs-request-time">4. Récupération au moment de la compilation vs. de la requête</h2>

- **Les générateurs de sites statiques** (Astro, Next.js static export, prérendu SvelteKit) peuvent récupérer tous les articles au moment de la compilation via `/posts`, générer une page statique par article, et reconstruire lorsque le contenu change (par exemple via un [webhook](/docs/webhooks) qui déclenche un redéploiement).
- **Les applications rendues côté serveur ou côté client** peuvent appeler l'API Data directement à chaque requête, puisqu'elle ne nécessite aucune authentification et que les réponses sont du JSON peu coûteux et pouvant être mis en cache.

<Callout type="info">
	<p>
		Utilisez les <a href="/docs/webhooks">webhooks</a> pour être averti lorsque des articles sont publiés ou mis à jour, afin de
		pouvoir invalider un cache ou déclencher une reconstruction plutôt que d'interroger l'API en boucle.
	</p>
</Callout>

<h2 id="multi-language">5. Plusieurs langues (optionnel)</h2>

Si votre blog utilise [plusieurs langues](/docs/languages), passez le paramètre `language` à la fois sur les requêtes de liste et d'article unique pour obtenir la bonne variante, et utilisez le tableau `variants` de chaque objet pour construire les liens du sélecteur de langue.

<h2 id="example-stack">Exemple de stack</h2>

Une configuration headless minimale ressemble généralement à ceci :

- Contenu : rédigé et publié dans Hyvor Blogs comme d'habitude.
- Frontend : n'importe quel framework, récupérant les données depuis l'[API Data](/docs/api-data).
- Déploiement : votre propre hébergement (Vercel, Netlify, votre propre serveur, etc.) - indépendant de l'hébergement de Hyvor Blogs.

À partir de là, la [référence de l'API Data](/docs/api-data) contient la liste complète des points de terminaison, objets et paramètres (filtrage, tri, pagination, sélection de champs) dont vous aurez besoin pour construire des pages de liste, des pages de tags/auteurs et une recherche.

# API Console

L'API Console vous permet d'effectuer des tâches administratives d'un blog. C'est la même API que nous utilisons en interne dans la Console. Vous pouvez l'utiliser pour automatiser certaines tâches ou même créer une mini-console entièrement nouvelle par vous-même.

<h2 id="calling-the-api">Appeler l'API</h2>

- Chemin de base de l'API : `https://blogs.hyvor.com/api/console/v0/blog/{subdomain}`
- Créez une clé d'API Console depuis la Console et envoyez-la en tant qu'en-tête `X-API-KEY`.
- Les points de terminaison de l'API Console utilisent les méthodes HTTP suivantes.
  - `GET` - pour obtenir des données, généralement un tableau de ressources
  - `POST` - pour créer une ressource
  - `PATCH` - pour mettre à jour partiellement ou complètement une ressource
  - `DELETE` - pour supprimer une ressource
- Tout comme notre [API Data](/docs/api-data), l'API Console renvoie toujours un objet ou un tableau d'objets, au format JSON
- Les paramètres de requête peuvent être définis en JSON (recommandé) ou comme des paramètres de requête habituels (dans la chaîne de requête ou le corps HTTP)
- Dans cette documentation, les objets, paramètres de requête et réponses sont écrits comme des interfaces <a href="https://www.typescriptlang.org/" rel="nofollow">Typescript</a> afin de rendre les déclarations de type concises.

<!-- <h2 id="authenticating-user">Authentification de l'utilisateur</h2>

Actuellement, l'API Console est toujours authentifiée en tant que propriétaire du blog. Nous ajouterons prochainement l'authentification pour d'autres [utilisateurs](/docs/users). -->

<h2 id="categories">Catégories</h2>

L'API Console dispose de nombreux points de terminaison et est classée selon la "ressource" à laquelle vous souhaitez accéder ou que vous souhaitez gérer. La plupart des catégories disposent d'opérations CRUD, mais certaines peuvent avoir davantage de points de terminaison pour des tâches spécifiques. Ces objets sont définis au sein de la catégorie. Notez également que les objets de l'API Console sont différents des objets de l'[API Data](/docs/api-data).

Accédez directement à chaque catégorie :

- [Blog](/docs/api-console#blog)
- [Articles & Pages](/docs/api-console#posts)
- [Tags](/docs/api-console#tags)
- [Utilisateurs](/docs/api-console#users)
- [Médias](/docs/api-console#media)
- [Navigation](/docs/api-console#navigation)
- [Langues](/docs/api-console#language)
- [Redirections](/docs/api-console#redirect)
- [Webhooks](/docs/api-console#webhook)
- [Fichiers du thème](/docs/api-console#theme-files)
- [Export](/docs/api-console#export)
- [Analyse de liens](/docs/api-console#link-analysis)
- [Route](/docs/api-console#route)
- [Divers](/docs/api-console#misc)

<h3 id="blog">Blog</h3>

Points de terminaison :

- `GET /blog` - Obtenir les données du blog
- `PATCH /blog` - Mettre à jour les données du blog
- `POST /blog/variant` - Créer une variante de blog
- `PATCH /blog/variant` - Mettre à jour une variante de blog

Objets :

- [Blog](/docs/api-console#blog-object)
- [BlogVariant](/docs/api-console#blog-variant-object)

<h4 id="get-blog">Obtenir les données du blog</h4>

`GET /blog`

```ts
type Request = {};
type Response = Blog;
```

<h4 id="update-blog">Mettre à jour les données du blog</h4>

`PATCH /blog`

```ts
type Request = Partial<Blog>; // sauf id et variants
type Response = Blog;
```

<h4 id="create-blog-variant">Créer une variante de blog</h4>

`POST /blog/variant`

```ts
type Request = {
	language_id: number;
};
type Response = BlogVariant;
```

<h4 id="update-blog-variant">Mettre à jour une variante de blog</h4>

`PATCH /blog/variant`

```ts
type Request = {
	language_id: number;
	name?: string;
	description?: string;
};
type Response = BlogVariant;
```

<h3 id="posts">Articles & Pages</h3>

Points de terminaison :

- `GET /posts` - Obtenir les articles
- `GET /pages` - Obtenir les pages
- `POST /post` - Créer un article/une page
- `GET /post/{id}` - Obtenir un article/une page
- `PATCH /post/{id}` - Mettre à jour un article/une page
- `DELETE /post/{id}` - Supprimer un article/une page
- `POST /post/{id}/variant` - Créer une variante d'article
- `PATCH /post/{id}/variant` - Mettre à jour une variante d'article
- `POST /post/{id}/variant/publish` - Publier une variante d'article
- `POST /post/{id}/variant/unpublish` - Dépublier une variante d'article
- `DELETE /post/{id}/variant` - Supprimer une variante d'article
- `PATCH /post/{id}/tags` - Mettre à jour les tags d'un article
- `PATCH /post/{id}/authors` - Mettre à jour les auteurs d'un article

Objets :

- [Post](/docs/api-console#post-object)
- [PostVariant](/docs/api-console#post-variant-object)
- [PostListItem](/docs/api-console#post-list-item-object)

<h4 id="get-posts">Obtenir les articles</h4>

Obtient les articles avec filtrage. Les paramètres de filtrage sont similaires à ceux de la Console. Renvoie un objet [PostListItem](/docs/api-console#post-list-item-object) léger par article, plutôt que l'objet complet [Post](/docs/api-console#post-object) - récupérez `GET /post/{id}` pour obtenir l'article complet.

`GET /posts`

```ts
type Request = {
	status?: 'featured' | 'published' | 'draft' | 'scheduled';
	author_id?: number;
	tag_id?: number;
	start_timestamp?: number; // horodatage unix
	end_timestamp?: number; // horodatage unix
	search?: string;
	language_id?: number; // par défaut la langue principale du blog
	limit?: number; // par défaut 50, max 100
	offset?: number;
};
type Response = PostListItem[];
```

<h4 id="get-pages">Obtenir les pages</h4>

Même forme légère [PostListItem](/docs/api-console#post-list-item-object) que `GET /posts`.

`GET /pages`

```ts
type Request = {};
type Response = PostListItem[];
```

<h4 id="create-post">Créer un article/une page</h4>

Crée un article brouillon vide. Une variante d'article sera créée à partir de la langue principale du blog.

`POST /post`

```ts
type Request = {
	is_page?: boolean; // par défaut false
};
type Response = Post;
```

<h4 id="get-post">Obtenir un article/une page</h4>

`GET /post/{id}`

```ts
type Request = {};
type Response = Post;
```

<h4 id="update-post">Mettre à jour un article/une page</h4>

`PATCH /post/{id}`

```ts
type Request = {
	is_featured?: boolean;
	featured_image_url?: string | null;
	canonical_url?: string | null;
	code_head?: string | null;
	code_foot?: string | null;
	published_at?: number | null; // horodatage unix
};
type Response = Post;
```

<h4 id="delete-post">Supprimer un article/une page</h4>

`DELETE /post/{id}`

```ts
type Request = {};
type Response = {};
```

<h4 id="create-post-variant">Créer une variante d'article</h4>

`POST /post/{id}/variant`

```ts
type Request = {
	language_id: number;
};
type Response = PostVariant;
```

<h4 id="update-post-variant">Mettre à jour une variante d'article</h4>

`PATCH /post/{id}/variant`

```ts
type Request = {
	language_id: number;
	slug?: string; // max 255 caractères
	content?: string | null;
	content_unsaved?: string | null;
	title?: string | null; // max 255 caractères
	description?: string | null; // max 255 caractères
};
type Response = PostVariant;
```

`content` et `content_unsaved` doivent être au format JSON ProseMirror. Consultez le [point de terminaison Obtenir le JSON ProseMirror](/docs/api-console#get-prosemirror-json) pour convertir du HTML en JSON ProseMirror.

<h4 id="publish-post-variant">Publier une variante d'article</h4>

`POST /post/{id}/variant/publish`

```ts
type Request = {
	language_id: number;
};
type Response = PostVariant;
```

Publie une variante d'article. Si la variante n'a pas de slug, un slug est automatiquement généré à partir du titre. Si l'article n'a pas d'heure `published_at`, elle est définie sur maintenant. Nécessite la portée `posts.publish.own`.

<h4 id="unpublish-post-variant">Dépublier une variante d'article</h4>

`POST /post/{id}/variant/unpublish`

```ts
type Request = {
	language_id: number;
};
type Response = PostVariant;
```

Remet le statut de la variante à `draft`. Fonctionne aussi bien sur les variantes publiées que programmées. Nécessite la portée `posts.publish.own`.

<h4 id="delete-post-variant">Supprimer une variante d'article</h4>

`DELETE /post/{id}/variant`

```ts
type Request = {
	language_id: number;
};
type Response = {};
```

<h4 id="update-post-tags">Mettre à jour les tags d'un article</h4>

`PATCH /post/{id}/tags`

```ts
type Request = {
	ids: number[]; // IDs des tags
};
type Response = {};
```

<h4 id="update-post-authors">Mettre à jour les auteurs d'un article</h4>

`PATCH /post/{id}/authors`

```ts
type Request = {
	ids: number[]; // IDs des auteurs (utilisateurs)
};
type Response = {};
```

<h3 id="tags">Tags</h3>

Points de terminaison :

- `GET /tags` - Obtenir ou rechercher des tags
- `POST /tag` - Créer un tag
- `PATCH /tag/{id}` - Mettre à jour un tag
- `DELETE /tag/{id}` - Supprimer un tag
- `POST /tag/{id}/variant` - Créer une variante de tag
- `PATCH /tag/{id}/variant` - Mettre à jour une variante de tag
- `DELETE /tag/{id}/variant` - Supprimer une variante de tag

Objets :

- [Tag](/docs/api-console#tag-object)
- [TagVariant](/docs/api-console#tag-variant-object)

<h4 id="get-tags">Obtenir ou rechercher des tags</h4>

Liste les tags, avec une recherche optionnelle par nom (langue principale).

`GET /tags`

```ts
type Request = {
	limit?: number; // par défaut 50, max 100
	offset?: number;
	search?: string; // filtre les tags par nom (langue principale)
};
type Response = Tag[];
```

<h4 id="create-tag">Créer un tag</h4>

`POST /tag`

```ts
type Request = {
	name: string; // nom pour la variante de langue principale
	is_private: boolean; // par défaut false
};
type Response = Tag;
```

<h4 id="update-tag">Mettre à jour un tag</h4>

`PATCH /tag/{id}`

```ts
type Request = {
	is_private?: boolean;
	slug?: string;
	code_head?: string | null;
	code_foot?: string | null;
};
type Response = Tag;
```

<h4 id="delete-tag">Supprimer un tag</h4>

`DELETE /tag/{id}`

```ts
type Request = {};
type Response = {};
```

<h4 id="create-tag-variant">Créer une variante de tag</h4>

`POST /tag/{id}/variant`

```ts
type Request = {
	language_id: number;
};
```

<h4 id="update-tag-variant">Mettre à jour une variante de tag</h4>

`PATCH /tag/{id}/variant`

```ts
type Request = {
	language_id: number;
	name?: string;
	description?: string | null;
};
```

<h4 id="delete-tag-variant">Supprimer une variante de tag</h4>

`DELETE /tag/{id}/variant`

```ts
type Request = {
	language_id: number;
};
```

<h3 id="users">Utilisateurs</h3>

Points de terminaison :

- `GET /users` - Obtenir les utilisateurs
- `GET /users/search` - Rechercher des utilisateurs
- `POST /user` - Créer un utilisateur
- `POST /user/guest` - Créer un utilisateur invité
- `PATCH /user/{id}` - Mettre à jour un utilisateur
- `DELETE /user/{id}` - Supprimer un utilisateur
- `POST /user/{id}/variant` - Créer une variante d'utilisateur
- `PATCH /user/{id}/variant` - Mettre à jour une variante d'utilisateur
- `DELETE /user/{id}/variant` - Supprimer une variante d'utilisateur

Objets :

- [User](/docs/api-console#user-object)
- [UserVariant](/docs/api-console#user-variant-object)

<h4 id="get-users">Obtenir les utilisateurs</h4>

`GET /users`

```ts
type Request = {
	offset?: number;
};
type Response = User[];
```

<h4 id="search-users">Rechercher des utilisateurs</h4>

Recherche des utilisateurs par nom.

`GET /users/search`

```ts
type Request = {
	search: string;
};
type Response = User[];
```

<h4 id="create-user">Créer un utilisateur</h4>

`POST /user`

```ts
type Request = {
	username_or_email: string;
	role: 'owner' | 'admin' | 'editor' | 'writer' | 'contributor';
};
type Response = User;
```

<h4 id="create-guest-user">Créer un utilisateur invité</h4>

`POST /user/guest`

```ts
type Request = {
	name: string;
};
type Response = User;
```

<h4 id="update-user">Mettre à jour un utilisateur</h4>

`PATCH /user/{id}`

```ts
type Request = {
	hyvor_user_id?: number;
	role?: 'owner' | 'admin' | 'editor' | 'writer' | 'contributor';
	status: 'active' | 'blocked';
	slug: string;
	email?: string;
	website_url?: string;
	picture_url?: string;
	social_facebook?: string;
	social_twitter?: string;
	social_linkedin?: string;
	social_youtube?: string;
	social_tiktok?: string;
	social_instagram?: string;
	social_github?: string;
};
type Response = User;
```

<h4 id="delete-user">Supprimer un utilisateur</h4>

`DELETE /user/{id}`

```ts
type Request = {};
type Response = {};
```

<h4 id="create-user-variant">Créer une variante d'utilisateur</h4>

`POST /user/{id}/variant`

```ts
type Request = {};
type Response = UserVariant;
```

<h4 id="update-user-variant">Mettre à jour une variante d'utilisateur</h4>

`PATCH /user/{id}/variant`

```ts
type Request = {
	name?: string;
	bio?: string;
	location?: string;
};
type Response = UserVariant;
```

<h4 id="delete-user-variant">Supprimer une variante d'utilisateur</h4>

`DELETE /user/{id}/variant`

```ts
type Request = {};
type Response = {};
```

<h3 id="media">Médias</h3>

Points de terminaison :

- `GET /media` - Obtenir les médias
- `POST /media` - Créer un média
- `POST /media/from-url` - Créer un média à partir d'une URL
- `DELETE /media/{id}` - Supprimer une navigation
- `GET /media/unsplash/search` - Obtenir des médias depuis unsplash
- `PATCH /media` - Modifier un média

Objets :

- [Media](/docs/api-console#media-object)

<h4 id="get-media">Obtenir les médias</h4>

`GET /media`

```ts
type Request = {
	limit: number;
	offset: number;
	search?: string;
	extensions?: string[];
	type?: string;
};
type Response = Media[];
```

<h4 id="create-media">Créer un média</h4>

`POST /media`

```ts
type Request = {
	file: File;
	post_id: number;
};
type Response = Media;
```

<h4 id="create-media-from-url">Créer un média à partir d'une URL</h4>

`POST /media/from-url`

```ts
type Request = {
	url: string;
	post_id?: number;
};
type Response = Media;
```

<h4 id="delete-media">Supprimer un média</h4>

`DELETE /media/{id}`

```ts
type Request = {};
type Response = {};
```

<h4 id="update-media">Modifier un média</h4>

`PATCH /media/{id}`

```ts
type Request = Partial<Media>;
type Response = Media;
```

<h3 id="navigation">Navigation</h3>

Points de terminaison :

- `GET /navigations` - Obtenir les navigations
- `PATCH /navigations/sort` - Mettre à jour l'ordre des navigations
- `POST /navigation` - Créer une navigation
- `PATCH /navigation/{id}` - Mettre à jour une navigation
- `DELETE /navigation/{id}` - Supprimer une navigation
- `POST /navigation/{id}/variant` - Créer une variante de navigation
- `PATCH /navigation/{id}/variant` - Mettre à jour une variante de navigation
- `DELETE /navigation/{id}/variant` - Supprimer une variante de navigation

Objets :

- [Navigation](/docs/api-console#navigation-object)
- [NavigationVariant](/docs/api-console#navigation-variant-object)

<h4 id="get-navigations">Obtenir les navigations</h4>

`GET /navigations`

```ts
type Request = {};
type Response = Navigation[];
```

<h4 id="sort-navigations">Mettre à jour l'ordre des navigations</h4>

`PATCH /navigations/sort`

```ts
type Request = {
	ids?: number[];
};
type Response = {};
```

<h4 id="create-navigation">Créer une navigation</h4>

`POST /navigation`

```ts
type Request = {
	url: string;
	name: string;
	type: 'header' | 'footer';
};
type Response = Navigation;
```

<h4 id="update-navigation">Mettre à jour une navigation</h4>

`PATCH /navigation/{id}`

```ts
type Request = {
	url: string;
	type: 'header' | 'footer';
};
type Response = Navigation;
```

<h4 id="delete-navigation">Supprimer une navigation</h4>

`DELETE /navigation/{id}`

```ts
type Request = {};
type Response = {};
```

<h4 id="create-navigation-variant">Créer une variante de navigation</h4>

`POST /navigation/{id}/variant`

```ts
type Request = {
	language_id: number;
	name?: string;
};
type Response = NavigationVariant;
```

<h4 id="update-navigation-variant">Mettre à jour une variante de navigation</h4>

`PATCH /navigation/{id}/variant`

```ts
type Request = {
	language_id: number;
	name: string;
};
type Response = NavigationVariant;
```

<h4 id="delete-navigation-variant">Supprimer une variante de navigation</h4>

`DELETE /navigation/{id}/variant`

```ts
type Request = {
	language_id: number;
};
type Response = {};
```

<h3 id="language">Langue</h3>

Points de terminaison :

- `GET /languages` - Obtenir les langues
- `POST /language` - Créer une langue
- `PATCH /language/{id}` - Mettre à jour une langue
- `DELETE /language/{id}` - Supprimer une langue

Objets :

- [Language](/docs/api-console#language-object)

<h4 id="get-languages">Obtenir les langues</h4>

`GET /languages`

```ts
type Request = {};
type Response = Languages[];
```

<h4 id="create-language">Créer une langue</h4>

`POST /language`

```ts
type Request = {
	code: string; // max 12 caractères
	name: string; // max 255 caractères
	direction: 'ltr' | 'rtl';
};
type Response = Language;
```

<h4 id="updata-language">Mettre à jour une langue</h4>

`PATCH /language/{id}`

```ts
type Request = {
	code: string; // max 12 caractères
	name: string; // max 255 caractères
	direction: 'ltr' | 'rtl';
};
type Response = Language;
```

<h4 id="delete-language">Supprimer une langue</h4>

`DELETE /language/{id}`

```ts
type Request = {};
type Response = {};
```

<h3 id="redirect">Redirection</h3>

Points de terminaison :

- `GET /redirects` - Obtenir les redirections
- `POST /redirect` - Créer une redirection
- `PATCH /redirect/{id}` - Mettre à jour une redirection
- `DELETE /redirect/{id}` - Supprimer une redirection

Objets :

- [Redirect](/docs/api-console#redirect-object)

<h4 id="get-redirects">Obtenir les redirections</h4>

`GET /redirects`

```ts
type Request = {
	search?: string;
	limit?: number;
	offset?: number;
};
type Response = Redirect[];
```

<h4 id="create-redirect">Créer une redirection</h4>

`POST /redirect`

```ts
type Request = {
	dynamic: boolean;
	path: string;
	to: string;
	type: 'temporary' | 'permanent';
};
type Response = Redirect;
```

<h4 id="update-redirect">Mettre à jour une redirection</h4>

`PATCH /redirect/{id}`

```ts
type Request = {
	path?: string;
	to?: string;
	type?: 'temporary' | 'permanent';
};
type Response = Redirect;
```

<h4 id="delete-redirect">Supprimer une redirection</h4>

`DELETE /redirect/{id}`

```ts
type Request = {};
type Response = {};
```

<h3 id="webhook">Webhook</h3>

Points de terminaison :

- `GET /webhooks` - Obtenir les webhooks
- `POST /webhook` - Créer un webhook
- `PATCH /webhook/{id}` - Mettre à jour un webhook
- `DELETE /webhook/{id}` - Supprimer un webhook

Objets :

- [Webhook](/docs/api-console#webhook-object)

<h4 id="get-webhooks">Obtenir les webhooks</h4>

`GET /webhooks`

```ts
type Request = {};
type Response = Webhook[];
```

<h4 id="create-webhook">Créer un webhook</h4>

`POST /webhook`

```ts
type Request = {
	url: string;
	events: 'cache.single' | 'cache.templates' | 'cache.all'[];
};
type Response = Webhook;
```

<h4 id="updata-webhook">Mettre à jour un webhook</h4>

`PATCH /webhook/{id}`

```ts
type Request = {
	url?: string;
	events?: 'cache.single' | 'cache.templates' | 'cache.all'[];
};
type Response = Webhook;
```

<h4 id="delete-webhook">Supprimer un webhook</h4>

`DELETE /webhook/{id}`

```ts
type Request = {};
type Response = {};
```

<h3 id="theme-files">Fichiers du thème</h3>

Points de terminaison :

- `GET /theme/files` - Obtenir les fichiers du thème
- `POST /theme/file` - Créer un fichier de thème
- `PATCH /theme/file/{id}` - Mettre à jour un fichier de thème
- `DELETE /theme/file/{id}` - Supprimer un fichier de thème

Objets :

- [FileObject](/docs/api-console#file-object)

<h4 id="get-theme-files">Obtenir les fichiers du thème</h4>

`GET /theme/files`

```ts
type Request = {};
type Response = FileObject[];
```

<h4 id="create-theme-file">Créer un fichier de thème</h4>

`POST /theme/file`

```ts
type Request = {
	folder: 'templates' | 'assets' | 'styles' | 'lang';
	name: string;
	content?: string;
	file: File;
};
type Response = FileObject;
```

<h4 id="updata-theme-file">Mettre à jour un fichier de thème</h4>

`PATCH /theme/file/{id}`

```ts
type Request = {
	name?: string;
	content?: string;
};
type Response = FileObject;
```

<h4 id="delete-theme-file">Supprimer un fichier de thème</h4>

`DELETE /theme/file/{id}`

```ts
type Request = {};
type Response = {};
```

<h3 id="export">Export</h3>

Points de terminaison :

- `GET /exports` - Obtenir les exports
- `POST /export` - Créer un export

Objets :

- [ExportObject](/docs/api-console#export-object)

<h4 id="get-exports">Obtenir les exports</h4>

`GET /exports`

```ts
type Request = {};
type Response = ExportObject[];
```

<h4 id="create-export">Créer un export</h4>

`POST /export`

```ts
type Request = {};
type Response = ExportObject;
```

<h3 id="link-analysis">Analyse de liens</h3>

Points de terminaison :

- `POST /link-analysis/check-urls` - Vérifier un lien de variante d'article
- `PATCH /link-analysis/ignore-link` - Ignorer un lien
- `GET /link-analysis/stats` - Obtenir les statistiques des liens
- `GET /link-analysis/links` - Obtenir les liens
- `GET /link-analysis/checks` - Obtenir les vérifications
- `POST /link-analysis/check` - Créer une vérification

Objets :

- [Link](/docs/api-console#link-object)
- [Check](/docs/api-console#check-object)

<h4 id="check-variant-urls">Vérifier un lien de variante d'article</h4>

`POST /link-analysis/check-urls`

```ts
type Request = {
	post_variant_id: number;
	urls: string[];
	force?: boolean;
};
type Response = LinkObject[];
```

<h4 id="ignore-link">Ignorer un lien</h4>

`PATCH /link-analysis/ignore-link`

```ts
type Request = {
	post_variant_id: number;
	urls: string[];
	status: boolean;
};
type Response = LinkObject;
```

<h4 id="get-link-stats">Obtenir les statistiques des liens</h4>

`GET /link-analysis/stats`

```ts
type Request = {};
type Response = {
	counts: number;
};
```

<h4 id="get-links">Obtenir les liens</h4>

`GET /link-analysis/links`

```ts
type Request = {
	type?: 'ok' | 'broken' | 'ignored' | 'redirected';
	limit?: number;
	offset?: number;
};
type Response = LinkObject[];
```

<h4 id="get-checks">Obtenir les vérifications</h4>

`GET /link-analysis/checks`

```ts
type Request = {
	limit?: number;
	offset?: number;
};
type Response = CheckObject[];
```

<h4 id="create-check">Créer une vérification</h4>

`POST /link-analysis/check`

```ts
type Request = {};
type Response = CheckObject;
```

<h3 id="route">Route</h3>

Points de terminaison :

- `GET /routes` - Obtenir les routes
- `POST /route` - Créer une route
- `PATCH /route/{id}` - Mettre à jour une route
- `DELETE /route/{id}` - Supprimer une route

Objets :

- [Route](/docs/api-console#route-object)

<h4 id="get-routes">Obtenir les routes</h4>

`GET /routes`

```ts
type Request = {};
type Response = Route[];
```

<h4 id="create-route">Créer une route</h4>

`POST /route`

```ts
type Request = {
	name: string;
	match: string;
	template: string;
	post_filter?: string;
	content_type?: string;
};
type Response = Route;
```

<h4 id="update-route">Mettre à jour une route</h4>

`PATCH /route/{id}`

```ts
type Request = {
	name: string;
	match: string;
	template: string;
	post_filter?: string;
	content_type?: string;
};
type Response = Route;
```

<h4 id="delete-route">Supprimer une route</h4>

`DELETE /route/{id}`

```ts
type Request = {};
type Response = {};
```

<h3 id="misc">Divers</h3>

Points de terminaison :

- `GET /misc/themes` - Obtenir tous les thèmes
- `GET /misc/prosemirror/json` - Obtenir le json prosemirror
- `DELETE /blog/cache` - Supprimer le cache du blog
- `DELETE /blog` - Supprimer le blog

<h4 id="get-all-themes">Obtenir tous les thèmes</h4>

`GET /misc/themes`

```ts
type Request = {};
type Response = Theme[];
```

<h4 id="get-prosemirror-json">Obtenir le JSON prosemirror à partir du HTML</h4>

`GET /misc/prosemirror/json`

```ts
type Request = {
	html: string;
};
type Response = {
	json: string;
};
```

<h4 id="delete-blog-cache">Supprimer le cache du blog</h4>

`DELETE /blog/cache`

```ts
type Request = {
	type: 'all' | 'template' | 'paths';
	paths?: string[];
};
type Response = {};
```

<h4 id="delete-blog">Supprimer le blog</h4>

Supprime le blog de manière réversible (soft-delete). Le blog et ses données sont définitivement supprimés 30 jours plus tard. Nécessite la portée `blog.delete`.

`DELETE /blog`

```ts
type Request = {};
type Response = {};
```

<h2 id="objects">Objets</h2>
<h3 id="blog-object">Objet Blog</h3>

```ts
interface Blog {
	id: number;
	created_at: number;
	is_blocked: boolean;
	subdomain: string;
	type: 'default' | 'dev';
	hosting_at: 'subdomain' | 'domain' | 'self';
	hosting_domain: string | null;
	hosting_url: string | null;

	embeddable: boolean;
	embedding_domains: string | null;

	logo_url: string | null;
	cover_url: string | null;

	social_facebook: string | null;
	social_twitter: string | null;
	social_linkedin: string | null;
	social_youtube: string | null;
	social_tiktok: string | null;
	social_instagram: string | null;
	social_github: string | null;

	code_head: string | null;
	code_foot: string | null;

	seo_indexing: boolean;
	seo_robots_txt: string | null;
	seo_external_links_follow: 'follow' | 'nofollow';
	comments_code: string | null;
	newsletter_code: string | null;

	color_modes: 'light' | 'dark' | 'both';
	color_mode_default: 'light' | 'dark' | 'os';

	syntax_on: boolean;
	syntax_line_numbers: boolean;
	syntax_theme: string | null;

	flashload: boolean;
	variants: BlogVariant[];
}
```

<h3 id="blog-variant-object">Objet BlogVariant</h3>

```ts
interface BlogVariant {
	language_id: number;
	name: string | null;
	description: string | null;
}
```

<h3 id="post-object">Objet Post</h3>

```ts
interface Post {
	id: number;
	preview_id: string;
	created_at: number;
	updated_at: number;
	published_at: number | null;

	is_featured: boolean;
	is_page: boolean;

	featured_image_url: string | null;
	canonical_url: string | null;
	code_head: string | null;
	code_foot: string | null;

	variant_statuses: {
		id: number;
		language_id: number;
		status: 'draft' | 'published' | 'scheduled';
	}[];

	tags: Tag[];
	authors: User[];
}
```

`variant_statuses` vous indique uniquement quelles langues un article possède et leur statut. Récupérez `GET /post/{id}?variant_language_code=...` pour obtenir l'objet complet [PostVariant](/docs/api-console#post-variant-object) (contenu, titre, champs SEO, etc.) pour une seule langue.

<h3 id="post-variant-object">Objet PostVariant</h3>

```ts
interface PostVariant {
	language_id: number;

	slug: string | null;
	status: 'draft' | 'published' | 'scheduled';
	url: string;

	content: string | null;
	content_unsaved: string | null;
	title: string | null;
	description: string | null;
}
```

<h3 id="post-list-item-object">Objet PostListItem</h3>

Renvoyé par `GET /posts` et `GET /pages`. Un résumé léger par article : `slug`, `url`, `title`, et `link_analysis` reflètent la variante de la langue demandée (ou la langue principale du blog), et `tags`/`authors` sont uniquement leurs noms dans la langue principale. Récupérez `GET /post/{id}` pour obtenir l'objet complet [Post](/docs/api-console#post-object), incluant les tags et les auteurs.

```ts
interface PostListItem {
	id: number;
	created_at: number;
	updated_at: number;
	published_at: number | null;

	is_featured: boolean;
	is_page: boolean;

	slug: string | null;
	url: string | null;
	title: string | null;
	link_analysis: Record<string, number>;

	variant_statuses: {
		language_id: number;
		status: 'draft' | 'published' | 'scheduled';
	}[];

	tags: string[]; // noms des tags, langue principale
	authors: string[]; // noms des auteurs, langue principale
}
```

<h3 id="tag-object">Objet Tag</h3>

```ts
interface Tag {
	id: number;
	created_at: number;
	updated_at: number;
	is_private: boolean;
	slug: string;
	posts_count: number;
	code_head: string | null;
	code_foot: string | null;

	variants: TagVariant[];
}
```

<h3 id="tag-variant-object">Objet TagVariant</h3>

```ts
interface TagVariant {
	language_id: number;
	url: string | null;
	name: string | null;
	description: string | null;
}
```

<h3 id="user-object">Objet User</h3>

```ts
interface User {
	id: number;
	created_at: number;
	updated_at: number;

	hyvor_user_id: number | null;

	status: 'invited' | 'active' | 'blocked';
	role: 'owner' | 'admin' | 'editor' | 'writer' | 'contributor';
	slug: string;
	posts_count: number;
	email: string;

	picture_url: string | null;
	website_url: string | null;

	social_facebook: string | null;
	social_twitter: string | null;
	social_linkedin: string | null;
	social_youtube: string | null;
	social_tiktok: string | null;
	social_instagram: string | null;
	social_github: string | null;

	variants: UserVariant[];
}
```

<h3 id="user-variant-object">Objet UserVariant</h3>

```ts
interface UserVariant {
	language_id: number;
	url: string;
	name: string | null;
	bio: string | null;
	location: string | null;
}
```

<h3 id="media-object">Objet Media</h3>

```ts
interface Media {
	id: number;
	uploaded_at: number;
	name: string;
	url: string;
	original_name: string;
	extension: string;
}
```

<h3 id="navigation-object">Objet Navigation</h3>

```ts
interface Navigation {
	id: number;
	created_at: number;
	url: string;
	type: NavigationType;
	sort: number;
	variants: NavigationVariant[];
}
```

<h3 id="navigation-variant-object">Objet NavigationVariant</h3>

```ts
interface NavigationVariant {
	language_id: number;
	name: string | null;
}
```

<h3 id="language-object">Objet Language</h3>

```ts
interface Language {
	id: number;
	code: string;
	name: string;
	is_primary: boolean;
}
```

<h3 id="redirect-object">Objet Redirect</h3>

```ts
interface Redirect {
	id: number;
	created_at: number;
	path: string;
	to: string;
	type: 'temporary' | 'permanent';
}
```

<h3 id="webhook-object">Objet Webhook</h3>

```ts
interface Webhook {
	id: number;
	url: string;
	events: string[];
	secret: string;
}
```

<h3 id="route-object">Objet Route</h3>

```ts
interface Route {
	id: number;
	created_at: number;
	name: string;
	match: string;
	template: string;
	posts_filter: string | null;
	content_type: string | null;
	is_enabled: boolean;
}
```

<h3 id="file-object">Objet File</h3>

```ts
interface FileObject {
	id: number;
	name: string;
	content: string | null;
	folder: 'templates' | 'assets' | 'styles' | 'lang';
}
```

<h3 id="export-object">Objet Export</h3>

```ts
interface Export {
	id: number;
	createdf_at: number;
	format: 'hyvor_blogs' | 'wordpress';
	status: 'pending' | 'completed' | 'failed';
	url: string | null;
	error?: string;
}
```

<h3 id="theme-object">Objet Theme</h3>

```ts
interface Theme {
	id: number;
	type: 'original' | 'ported';
	name: string;
}
```

<h3 id="link-object">Objet Link</h3>

```ts
interface LinkObject {
	id: number;
	url: string;
	full_url: string;
	status_code: number;
	status_type: 'ok' | 'broken' | 'redirect' | 'ignored';
	ignored: boolean;
	post_id: number;
	post_variant_id: number;
	post_variant_language_id: number;
	post_variant_title: string;
}
```

<h3 id="check-object">Objet Check</h3>

```ts
interface CheckObject {
	id: number;
	created_at: number;
	status: 'pending' | 'completed' | 'failed';
	error: string | null;
	post_count: number;
	post_variants_count: number;
	page_count: number;
	page_variants_count: number;
	links_total_count: number;
	links_ok_count: number;
	links_broken_count: number;
	links_redirect_count: number;
	links_ignored_count: number;
}
```

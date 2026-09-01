<script lang="ts">
	import { Table, TableRow } from '@hyvor/design/components';
</script>

# API de livraison

L'API de livraison vous fournit des informations sur la façon de « servir une requête ». Cette API est le pilier de [l'hébergement d'un blog sur un sous-répertoire](/docs/subdirectory).

**Point de terminaison de l'API** : `https://blogs.hyvor.com/api/delivery/v0/{subdomain}`

Remplacez `{subdomain}` par le sous-domaine de votre blog.

<h2 id="request">Requête</h2>

- Requête `GET` vers le point de terminaison de l'API
- Définissez le paramètre de requête `api_key` avec une clé API de livraison valide. Vous pouvez en créer une dans **Paramètres &rarr; Clés API**.
- Définissez le paramètre de requête `path` avec le chemin du blog pour lequel vous souhaitez obtenir des informations. Par exemple, si vous voulez obtenir des informations sur `https://myblog.com/hello-world`, définissez `path` sur `/hello-world/`.

<h2 id="response">Réponse</h2>

Une réponse réussie sera l'un des objets JSON suivants :

<h3 id="file">1. Fichier</h3>

Cet objet est renvoyé lorsque le chemin correspond à un fichier.

```json
{
	"type": "file",
	"at": 1661590503,
	"cache": true,
	"status": 200,
	"file_type": "template",
	"content": "SGVsbG8gV29ybGQ=",
	"mime_type": "text/html",
	"cache_control": "no-cache, private"
}
```

<h3 id="redirect">2. Redirection</h3>

Cet objet est renvoyé lorsque le chemin correspond à une redirection.

```json
{
	"type": "redirect",
	"at": 1661590503,
	"cache": true,
	"status": 301,
	"to": "https://example.com"
}
```

Propriétés communes :

<Table columns="1fr 1fr 2fr">
	<TableRow head>
		<div>Clé</div>
		<div>Type</div>
		<div>Description</div>
	</TableRow>
	<TableRow>
		<div><code>type</code></div>
		<div><code>string</code></div>
		<div>
			<code>file</code> ou <code>redirect</code>
		</div>
	</TableRow>
	<TableRow>
		<div><code>at</code></div>
		<div><code>integer</code></div>
		<div>Horodatage UNIX de la création de l'objet</div>
	</TableRow>
	<TableRow>
		<div><code>cache</code></div>
		<div><code>boolean</code></div>
		<div>
			Indique si l'objet de réponse doit être mis en cache dans les serveurs proxy/intermédiaires. <code>false</code> pour
			les routes d'aperçu d'article.
		</div>
	</TableRow>
	<TableRow>
		<div><code>status</code></div>
		<div><code>integer</code></div>
		<div>
			Code de statut HTTP de la réponse. Peut être <code>200</code>, <code>301</code>,
			<code>302</code>, ou <code>404</code>.
		</div>
	</TableRow>
</Table>

Propriétés du fichier :

<Table columns="1fr 1fr 2fr">
	<TableRow head>
		<div>Clé</div>
		<div>Type</div>
		<div>Description</div>
	</TableRow>
	<TableRow>
		<div><code>file_type</code></div>
		<div><code>string</code></div>
		<div>
			<code>template</code>, <code>asset</code>, ou <code>media</code>
		</div>
	</TableRow>
	<TableRow>
		<div><code>content</code></div>
		<div><code>string</code></div>
		<div>Contenu du fichier encodé en Base64</div>
	</TableRow>
	<TableRow>
		<div><code>mime_type</code></div>
		<div><code>string</code></div>
		<div>Type MIME du fichier (pour l'en-tête Content-Type)</div>
	</TableRow>
	<TableRow>
		<div><code>cache_control</code></div>
		<div><code>string</code></div>
		<div>Valeur de l'en-tête HTTP Cache-Control</div>
	</TableRow>
</Table>

Propriétés de redirection :

<Table columns="1fr 1fr 2fr">
	<TableRow head>
		<div>Clé</div>
		<div>Type</div>
		<div>Description</div>
	</TableRow>
	<TableRow>
		<div><code>to</code></div>
		<div><code>string</code></div>
		<div>URL vers laquelle rediriger</div>
	</TableRow>
</Table>

<script>
	import { Table, TableRow, Text } from '@hyvor/design/components';
</script>

# Webhooks

Les webhooks sont un moyen d'être notifié lorsqu'un événement se produit sur votre blog.

- Un blog peut avoir jusqu'à 5 webhooks
- Chaque webhook peut s'abonner à un ou plusieurs événements

<Table columns="1fr 2fr 1fr">
	<TableRow head>
		<div>Événement</div>
		<div>Déclenché</div>
		<div>Données</div>
	</TableRow>
	<TableRow>
		<div><code>blog.updated</code></div>
		<div>Un paramètre du blog est mis à jour</div>
		<div>
			<code>{'{ blog: '}<a href="/docs/api-console#blog-object">Blog</a>{' }'}</code>
		</div>
	</TableRow>

    <div class="separator"></div>

    <div class="title">Article</div>

    <TableRow>
    	<div><code>post.created</code></div>
    	<div>Un nouvel article est créé</div>
    	<div>
    		<code>{'{ post: '}<a href="/docs/api-console#post-object">Post</a>{' }'}</code>
    	</div>
    </TableRow>
    <TableRow>
    	<div><code>post.updated</code></div>
    	<div>Un article est mis à jour</div>
    	<div>
    		<code>{'{ post: '}<a href="/docs/api-console#post-object">Post</a>{' }'}</code>
    	</div>
    </TableRow>
    <TableRow>
    	<div><code>post.deleted</code></div>
    	<div>Un article est supprimé</div>
    	<div>
    		<code>{'{ post: '}<a href="/docs/api-console#post-object">Post</a>{' }'}</code>
    	</div>
    </TableRow>
    <TableRow>
    	<div><code>post.variant.published</code></div>
    	<div>Une variante d'article est publiée</div>
    	<div>
    		<code>
    			{'{ post: '}
    			<a href="/docs/api-console#post-object">Post</a>,
    			variant: <a href="/docs/api-console#post-variant-object">PostVariant</a>,
    			{' }'}
    		</code>
    	</div>
    </TableRow>
    <TableRow>
    	<div><code>post.variant.unpublished</code></div>
    	<div>Une variante d'article est dépubliée</div>
    	<div>
    		<code>
    			{'{ post: '}
    			<a href="/docs/api-console#post-object">Post</a>,
    			variant: <a href="/docs/api-console#post-variant-object">PostVariant</a>,
    			{' }'}
    		</code>
    	</div>
    </TableRow>

    <div class="separator"></div>

    <div class="title">Étiquettes</div>

    <!-- All with tag. -->

    <TableRow>
    	<div><code>tag.created</code></div>
    	<div>Une nouvelle étiquette est créée</div>
    	<div>
    		<code>{'{ tag: '}<a href="/docs/api-console#tag-object">Tag</a>{' }'}</code>
    	</div>
    </TableRow>
    <TableRow>
    	<div><code>tag.updated</code></div>
    	<div>Une étiquette est mise à jour</div>
    	<div>
    		<code>{'{ tag: '}<a href="/docs/api-console#tag-object">Tag</a>{' }'}</code>
    	</div>
    </TableRow>
    <TableRow>
    	<div><code>tag.deleted</code></div>
    	<div>Une étiquette est supprimée</div>
    	<div>
    		<code>{'{ tag: '}<a href="/docs/api-console#tag-object">Tag</a>{' }'}</code>
    	</div>
    </TableRow>

    <div class="separator"></div>

    <!-- All tag events with users -->

    <div class="title">Utilisateurs</div>

    <TableRow>
    	<div><code>user.created</code></div>
    	<div>Un nouvel utilisateur est créé</div>
    	<div>
    		<code>{'{ user: '}<a href="/docs/api-console#user-object">User</a>{' }'}</code>
    	</div>
    </TableRow>
    <TableRow>
    	<div><code>user.updated</code></div>
    	<div>Un utilisateur est mis à jour</div>
    	<div>
    		<code>{'{ user: '}<a href="/docs/api-console#user-object">User</a>{' }'}</code>
    	</div>
    </TableRow>
    <TableRow>
    	<div><code>user.deleted</code></div>
    	<div>Un utilisateur est supprimé</div>
    	<div>
    		<code>{'{ user: '}<a href="/docs/api-console#user-object">User</a>{' }'}</code>
    	</div>
    </TableRow>

    <div class="separator"></div>
    <div class="title">Médias</div>

    <TableRow>
    	<div><code>media.created</code></div>
    	<div>Un élément média est ajouté</div>
    	<div>
    		<code>{'{ media: '}<a href="/docs/api-console#media-object">Media</a>{' }'}</code>
    	</div>
    </TableRow>

    <TableRow>
    	<div><code>media.deleted</code></div>
    	<div>Un élément média est supprimé</div>
    	<div>
    		<code>{'{ media: '}<a href="/docs/api-console#media-object">Media</a>{' }'}</code>
    	</div>
    </TableRow>

    <div class="separator"></div>

    <div class="title">Autre</div>

    <TableRow>
    	<div><code>navigation.changed</code></div>
    	<div>Les <a href="/docs/navigation-links">liens de navigation du blog</a> ont changé</div>
    	<div>
    		<code
    			>{'{ navigation: '}<a href="/docs/api-console#navigation-object">Navigation</a
    			>{'[] }'}</code
    		>
    	</div>
    </TableRow>

    <TableRow>
    	<div><code>routes.changed</code></div>
    	<div>Les <a href="/docs/routes">routes du blog</a> ont changé</div>
    	<div>
    		<code>{'{ routes: '}<a href="/docs/api-console#route-object">Route</a>{'[] }'}</code>
    	</div>
    </TableRow>

    <TableRow>
    	<div><code>languages.changed</code></div>
    	<div>Les <a href="/docs/languages">langues du blog</a> ont changé</div>
    	<div>
    		<code>{'{ languages: '}<a href="/docs/api-console#language-object">Language</a>{'[] }'}</code>
    	</div>
    </TableRow>

    <div class="separator"></div>
    <div class="title">Vidage du cache</div>

    <TableRow>
    	<div><code>cache.single</code></div>
    	<div>Lorsque le cache d'un seul chemin doit être vidé (styles.css, ressources, médias, etc.)</div>
    	<div>
    		<code>{`{ path: string }`}</code>
    	</div>
    </TableRow>
    <TableRow>
    	<div><code>cache.templates</code></div>
    	<div>
    		Lorsque le cache de tous les chemins générés par modèle doit être vidé (index, articles, flux, etc.)
    	</div>
    	<div>
    		<Text small light>Objet vide</Text>
    	</div>
    </TableRow>
    <TableRow>
    	<div><code>cache.all</code></div>
    	<div>Lorsque tout le cache doit être vidé</div>
    	<div>
    		<Text small light>Objet vide</Text>
    	</div>
    </TableRow>

</Table>

<h2 id="request">Requête du webhook</h2>

Lorsqu'un événement se produit, une requête **POST** est envoyée à l'URL du webhook. Le corps de la
requête est un objet JSON contenant les propriétés suivantes. La propriété `data` correspond aux
données de l'événement. Consultez le tableau ci-dessus pour connaître la structure des données de chaque événement.

```json
{
	"subdomain": "my-subdomain",
	"timestamp": 1645208678,
	"event": "cache.single",
	"data": {}
}
```

<h2 id="security">Sécurité</h2>

Chaque requête de webhook inclut un en-tête `X-Signature` contenant une signature HMAC-SHA256
du corps de la requête, signée avec la clé secrète de votre webhook. Vous trouverez ce secret dans la Console,
dans les paramètres de votre webhook.

Pour vérifier la signature :

- Hachez le corps de la requête avec HMAC-SHA256 en utilisant la clé secrète de votre webhook.
- Comparez le hachage obtenu avec la valeur de l'en-tête `X-Signature`.

```js
const signature = request.headers['x-signature'];
const expected = crypto
	.createHmac('sha256', env.HB_WEBHOOK_SECRET)
	.update(JSON.stringify(request.body))
	.digest('hex');

if (signature !== expected) {
	return 'Unauthorized';
}
```

<h2 id="response">Réponse et nouvelles tentatives</h2>

Nous attendons un **code de réponse HTTP 200** de votre serveur pour marquer le webhook comme réussi.
Si nous recevons un autre code de réponse ou si nous ne parvenons pas à joindre vos serveurs, nous réessaierons d'envoyer
le webhook 3 fois de plus après

- 1 minute
- 5 minutes
- 30 minutes

Si toutes les tentatives échouent, nous marquerons ce webhook comme échoué et ne l'enverrons plus automatiquement.

<style>
	.separator {
		margin: 15px 0;
		border-bottom: 1px solid var(--border);
	}
	.title {
		padding: 0 16px;
		font-weight: 600;
		margin-bottom: 8px;
	}
</style>

<script>
	import { Table, TableRow, Tooltip } from '@hyvor/design/components';
</script>

# Intégration Hyvor Post

Hyvor Blogs s'intègre nativement avec [Hyvor Post](https://post.hyvor.com) pour offrir un système de newsletter pour vos blogs.

- [Fonctionnalités](#features)
- [Tarification](#pricing)
- [Connecter Hyvor Post](#connect)
- [Correspondance des accès](#access)
- [Déploiements auto-hébergés](#self-hosted)

<h2 id="features">Fonctionnalités</h2>

- Intégrez facilement le formulaire d'inscription sur votre blog.
- Accédez à toutes les fonctionnalités de Hyvor Post directement depuis la Console Hyvor Blogs : envoi de numéros, gestion des abonnés, consultation des statistiques, etc.
- Synchronisation automatique des accès de Hyvor Blogs vers Hyvor Post.

<h2 id="pricing">Tarification</h2>

Tous les forfaits Hyvor Blogs incluent une <span class="info"><Tooltip text="Cette licence gratuite vous permet d'utiliser Hyvor Post sans coût supplémentaire.">licence gratuite</Tooltip></span> pour Hyvor Post, comme suit :

<Table columns="1fr 1fr" style="bordered">
	<TableRow head>
		<div>Forfait Hyvor Blogs</div>
		<div>Quota d'emails Hyvor Post</div>
	</TableRow>
	<TableRow>
		<div>Essai</div>
		<div><i>emails de test uniquement</i></div>
	</TableRow>
	<TableRow>
		<div>Personnel</div>
		<div>5k emails/mois</div>
	</TableRow>
	<TableRow>
		<div>Starter</div>
		<div>15k emails/mois</div>
	</TableRow>
	<TableRow>
		<div>Growth</div>
		<div>50k emails/mois</div>
	</TableRow>
	<TableRow>
		<div>Premium</div>
		<div>150k emails/mois</div>
	</TableRow>
</Table>

Si vous devez envoyer plus d'emails, vous avez deux options : mettre à niveau votre forfait Hyvor Blogs ou souscrire un abonnement Hyvor Post séparé. Si vous souscrivez un abonnement Hyvor Post séparé, votre quota total d'emails sera la somme du quota gratuit (de Hyvor Blogs) et du quota de l'abonnement (de l'abonnement Hyvor Post).

<h2 id="connect">Connecter Hyvor Post</h2>

Pour connecter Hyvor Post à votre blog, vous disposez de deux méthodes : activer l'intégration lors de la création du blog ou la connecter plus tard dans **Paramètres → Intégrations → Hyvor Post**.

Lorsque vous connectez Hyvor Post, une nouvelle newsletter sera créée au sein de votre organisation. Vous pouvez gérer la newsletter depuis la Console Hyvor Blogs ou directement depuis la [Console Hyvor Post](https://post.hyvor.com).

<h2 id="access">Correspondance des accès</h2>

[Les utilisateurs de votre blog](/docs/users) auront accès à la Console Hyvor Post en fonction de leur rôle. Hyvor Blogs synchronisera automatiquement l'accès vers la newsletter connectée.

<Table columns="1fr 1fr" style="bordered">
	<TableRow head>
		<div>Rôle Hyvor Blogs</div>
		<div>Rôle Hyvor Post</div>
	</TableRow>
	<TableRow>
		<div>Administrateur du blog</div>
		<div>Utilisateur</div>
	</TableRow>
	<TableRow>
		<div>Éditeur</div>
		<div>Utilisateur</div>
	</TableRow>
	<TableRow>
		<div>Auteur</div>
		<div><i>Aucun</i></div>
	</TableRow>
	<TableRow>
		<div>Contributeur</div>
		<div><i>Aucun</i></div>
	</TableRow>
</Table>

<h2 id="self-hosted">Déploiements auto-hébergés</h2>

L'intégration Hyvor Post n'est disponible que sur Hyvor Blogs Cloud. Dans les déploiements auto-hébergés, vous pouvez tout de même ajouter des newsletters en collant un code d'intégration (provenant de Hyvor Post ou d'un autre système de newsletter) directement dans **Paramètres → Commentaires et newsletters**.

<style>
	span.info :global(.tooltip-wrap) {
		text-decoration: underline dotted;
	}
</style>

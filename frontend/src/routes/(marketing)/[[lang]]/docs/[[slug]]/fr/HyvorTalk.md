<script lang="ts">
	import { Table, TableRow, Tooltip } from '@hyvor/design/components';
</script>

# Intégration Hyvor Talk

Hyvor Blogs s'intègre nativement avec [Hyvor Talk](https://talk.hyvor.com), une plateforme de commentaires respectueuse de la vie privée, pour ajouter des commentaires à vos articles de blog.

- [Fonctionnalités](#features)
- [Tarification](#pricing)
- [Connexion à Hyvor Talk](#connect)
- [Code d'intégration](#embed-code)
- [Correspondance des accès](#access)
- [Déploiements auto-hébergés](#self-hosted)

<h2 id="features">Fonctionnalités</h2>

- Ajoute automatiquement à vos articles un widget de commentaires respectueux de la vie privée et sans publicité.
- Gérez les commentaires, les modérateurs et les paramètres directement depuis la console Hyvor Blogs ou la console Hyvor Talk.
- Synchronisation automatique des accès de Hyvor Blogs vers Hyvor Talk.

<h2 id="pricing">Tarification</h2>

Tous les forfaits Hyvor Blogs incluent une <span class="info"><Tooltip text="Cette licence gratuite vous permet d'utiliser Hyvor Talk sans coût supplémentaire.">licence gratuite</Tooltip></span> pour Hyvor Talk comme suit :

<!-- translator: do not translate plan names -->
<Table columns="1fr 1fr" style="bordered">
	<TableRow head>
		<div>Forfait Hyvor Blogs</div>
		<div>Crédits Hyvor Talk</div>
	</TableRow>
	<TableRow>
		<div>Essai</div>
		<div>1k crédits/mois</div>
	</TableRow>
	<TableRow>
		<div>Personnel</div>
		<div>10k crédits/mois</div>
	</TableRow>
	<TableRow>
		<div>Starter</div>
		<div>25k crédits/mois</div>
	</TableRow>
	<TableRow>
		<div>Growth</div>
		<div>100k crédits/mois</div>
	</TableRow>
	<TableRow>
		<div>Premium</div>
		<div>250k crédits/mois</div>
	</TableRow>
</Table>

Si vous avez besoin de plus de crédits, vous avez deux options : soit mettre à niveau votre forfait Hyvor Blogs, soit démarrer un abonnement Hyvor Talk séparé. Si vous démarrez un abonnement Hyvor Talk séparé, votre allocation totale de crédits sera la somme de l'allocation gratuite (de Hyvor Blogs) et de l'allocation de l'abonnement (de l'abonnement Hyvor Talk).

<h2 id="connect">Connexion à Hyvor Talk</h2>

Pour connecter Hyvor Talk à votre blog, allez dans **Paramètres &rarr; Intégrations &rarr; Hyvor Talk** et cliquez sur **Se connecter maintenant**.

Lorsque vous vous connectez, un nouveau site web est créé au sein de votre organisation sur Hyvor Talk, et votre blog y est lié. Vous pouvez gérer les commentaires depuis la console Hyvor Blogs ou directement depuis la [console Hyvor Talk](https://talk.hyvor.com).

La déconnexion supprime entièrement le site web de Hyvor Talk, ainsi que tous ses commentaires, modérateurs et paramètres. Assurez-vous donc que c'est bien ce que vous souhaitez avant de vous déconnecter.

<h2 id="embed-code">Code d'intégration</h2>

Une fois connecté, Hyvor Blogs ajoute automatiquement le code d'intégration Hyvor Talk à la [variable `_comments`](/docs/themes-templates#placeholders) de votre blog, que les thèmes affichent généralement sous le contenu de l'article.

Vous pouvez personnaliser le code d'intégration dans **Paramètres &rarr; Intégrations &rarr; Hyvor Talk** - par exemple, pour modifier le tri des commentaires ou d'autres [options d'intégration](https://talk.hyvor.com/docs/embed). Vous pouvez le réinitialiser à tout moment à sa valeur par défaut.

<h2 id="access">Correspondance des accès</h2>

Les [utilisateurs de votre blog](/docs/users) auront accès à la console Hyvor Talk en fonction de leur rôle. Hyvor Blogs synchronisera automatiquement cet accès avec le site web connecté à mesure que des utilisateurs sont ajoutés, supprimés ou changent de rôle.

<Table columns="1fr 1fr" style="bordered">
	<TableRow head>
		<div>Rôle Hyvor Blogs</div>
		<div>Rôle Hyvor Talk</div>
	</TableRow>
	<TableRow>
		<div>Administrateur du blog</div>
		<div>Administrateur</div>
	</TableRow>
	<TableRow>
		<div>Éditeur</div>
		<div>Modérateur</div>
	</TableRow>
	<TableRow>
		<div>Rédacteur</div>
		<div><i>Aucun</i></div>
	</TableRow>
	<TableRow>
		<div>Contributeur</div>
		<div><i>Aucun</i></div>
	</TableRow>
</Table>

<h2 id="self-hosted">Déploiements auto-hébergés</h2>

L'intégration Hyvor Talk n'est disponible que sur Hyvor Blogs Cloud. Dans les déploiements auto-hébergés, vous pouvez tout de même ajouter des commentaires en collant un code d'intégration (de Hyvor Talk ou d'un autre système de commentaires) directement dans **Paramètres &rarr; Commentaires et newsletters**.

<style>
	span.info :global(.tooltip-wrap) {
		text-decoration: underline dotted;
	}
</style>

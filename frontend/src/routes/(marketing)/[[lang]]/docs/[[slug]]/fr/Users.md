<script lang="ts">
	import { Table, TableRow } from '@hyvor/design/components';
	import { DocsImage } from '@hyvor/design/marketing';
</script>

# Utilisateurs

Vous pouvez ajouter des utilisateurs à votre blog pour collaborer à la création et à la gestion de contenu.

<h2 id="adding-users">Ajout d'utilisateurs</h2>

Pour ajouter un nouvel utilisateur,

1. Allez sur [hyvor.com/account/org/members](https://hyvor.com/account/org/members) et ajoutez le nouvel utilisateur à votre organisation.
2. Assignez le nouvel utilisateur à n'importe quel blog de votre organisation.

<DocsImage src="/images/docs/users/add-user.png" alt="Ajout d'utilisateurs" />

<h2 id="roles">Rôles</h2>

Il existe 5 rôles pour les utilisateurs. Chaque utilisateur a accès à des fonctionnalités en fonction de son rôle.

<Table columns="2fr 1fr 1fr 1fr 1fr" hover style="bordered">
	<TableRow head>
		<div>Fonctionnalité</div>
		<div>Admin</div>
		<div>Éditeur</div>
		<div>Rédacteur</div>
		<div>Contributeur</div>
	</TableRow>

    <TableRow>
    	<div>Écrire des articles</div>
    	<div>✔️</div>
    	<div>✔️</div>
    	<div>✔️</div>
    	<div>✔️</div>
    </TableRow>

    <TableRow>
    	<div>Publier des articles</div>
    	<div>✔️</div>
    	<div>✔️</div>
    	<div>✔️</div>
    	<div>✔️</div>
    </TableRow>

    <TableRow>
    	<div>Publier/modifier les articles d'autres utilisateurs</div>
    	<div>✔️</div>
    	<div>✔️</div>
    	<div>✔️</div>
    	<div></div>
    </TableRow>

    <TableRow>
    	<div>Téléverser des médias</div>
    	<div>✔️</div>
    	<div>✔️</div>
    	<div>✔️</div>
    	<div>✔️</div>
    </TableRow>

    <TableRow>
    	<div>Créer/modifier des tags</div>
    	<div>✔️</div>
    	<div>✔️</div>
    	<div>✔️</div>
    	<div></div>
    </TableRow>

    <TableRow>
    	<div>Ajouter/supprimer des utilisateurs</div>
    	<div>✔️</div>
    	<div>✔️</div>
    	<div></div>
    	<div></div>
    </TableRow>

    <TableRow>
    	<div>Modifier le thème</div>
    	<div>✔️</div>
    	<div>✔️</div>
    	<div></div>
    	<div></div>
    </TableRow>

    <TableRow>
    	<div>Modifier les paramètres du blog</div>
    	<div>✔️</div>
    	<div>✔️</div>
    	<div></div>
    	<div></div>
    </TableRow>

    <TableRow>
    	<div>Supprimer le blog</div>
    	<div>✔️</div>
    	<div></div>
    	<div></div>
    	<div></div>
    </TableRow>

</Table>

Paramètres des utilisateurs : **Console → Paramètres → Utilisateurs**.

<script lang="ts">
	import { Table, TableRow } from '@hyvor/design/components';
	import { DocsImage } from '@hyvor/design/marketing';
</script>

# Users

You can add users to your blog to collaborate on content creation and management.

<h2 id="adding-users">Adding Users</h2>

To add a new user,

1. Go to [hyvor.com/account/org/members](https://hyvor.com/account/org/members) and add the new user to your organization.
2. Assign the new user to any blog under your organization.

<DocsImage src="/images/docs/users/add-user.png" alt="Adding Users" />

<h2 id="roles">Roles</h2>

There are 5 roles for users. Each user has access to features based on their role.

<Table columns="2fr 1fr 1fr 1fr 1fr" hover style="bordered">
	<TableRow head>
		<div>Feature</div>
		<div>Admin</div>
		<div>Editor</div>
		<div>Writer</div>
		<div>Contributor</div>
	</TableRow>

    <TableRow>
    	<div>Write posts</div>
    	<div>✔️</div>
    	<div>✔️</div>
    	<div>✔️</div>
    	<div>✔️</div>
    </TableRow>

    <TableRow>
    	<div>Publish posts</div>
    	<div>✔️</div>
    	<div>✔️</div>
    	<div>✔️</div>
    	<div>✔️</div>
    </TableRow>

    <TableRow>
    	<div>Publish/edit others' posts</div>
    	<div>✔️</div>
    	<div>✔️</div>
    	<div>✔️</div>
    	<div></div>
    </TableRow>

    <TableRow>
    	<div>Upload media</div>
    	<div>✔️</div>
    	<div>✔️</div>
    	<div>✔️</div>
    	<div>✔️</div>
    </TableRow>

    <TableRow>
    	<div>Create/edit tags</div>
    	<div>✔️</div>
    	<div>✔️</div>
    	<div>✔️</div>
    	<div></div>
    </TableRow>

    <TableRow>
    	<div>Add/remove users</div>
    	<div>✔️</div>
    	<div>✔️</div>
    	<div></div>
    	<div></div>
    </TableRow>

    <TableRow>
    	<div>Edit Theme</div>
    	<div>✔️</div>
    	<div>✔️</div>
    	<div></div>
    	<div></div>
    </TableRow>

    <TableRow>
    	<div>Change blog settings</div>
    	<div>✔️</div>
    	<div>✔️</div>
    	<div></div>
    	<div></div>
    </TableRow>

    <TableRow>
    	<div>Delete Blog</div>
    	<div>✔️</div>
    	<div></div>
    	<div></div>
    	<div></div>
    </TableRow>

</Table>

Users settings: **Console → Settings → Users**.

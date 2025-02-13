<script lang="ts">
	import { Table, TableRow } from '@hyvor/design/components';
</script>

<h1 id="users">Users</h1>

<h2 id="roles">Roles</h2>
<p>There are 6 roles for users. Each user has access to features based on their role.</p>

<Table columns="2fr 1fr 1fr 1fr 1fr 1fr" hover>
	<TableRow head>
		<div>Feature</div>
		<div>Owner</div>
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
		<div>✔️</div>
	</TableRow>

	<TableRow>
		<div>Publish posts</div>
		<div>✔️</div>
		<div>✔️</div>
		<div>✔️</div>
		<div>✔️</div>
		<div></div>
	</TableRow>

	<TableRow>
		<div>Publish/edit others' posts</div>
		<div>✔️</div>
		<div>✔️</div>
		<div>✔️</div>
		<div></div>
		<div></div>
	</TableRow>

	<TableRow>
		<div>Moderate comments</div>
		<div>✔️</div>
		<div>✔️</div>
		<div>✔️</div>
		<div></div>
		<div></div>
	</TableRow>

	<TableRow>
		<div>Upload media</div>
		<div>✔️</div>
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
		<div></div>
	</TableRow>

	<TableRow>
		<div>Add/remove users</div>
		<div>✔️</div>
		<div>✔️</div>
		<div></div>
		<div></div>
		<div></div>
	</TableRow>

	<TableRow>
		<div>Edit Theme</div>
		<div>✔️</div>
		<div>✔️</div>
		<div></div>
		<div></div>
		<div></div>
	</TableRow>

	<TableRow>
		<div>Change blog settings</div>
		<div>✔️</div>
		<div>✔️</div>
		<div></div>
		<div></div>
		<div></div>
	</TableRow>

	<TableRow>
		<div>Delete Blog</div>
		<div>✔️</div>
		<div></div>
		<div></div>
		<div></div>
		<div></div>
	</TableRow>
</Table>

<p>Users settings: <b>Console → Settings → Users</b>.</p>

<h2 id="language-access">Languages Access</h2>
<p>
	Currently, all your users have access to all languages. We are working on introducing languages
	access settings. Stay tuned!
</p>

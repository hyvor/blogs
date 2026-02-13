<script lang="ts">
	import type { Language } from '../../../../lib/types';

	interface Props {
		language: Language;
	}

	let { language }: Props = $props();
</script>

Are you sure to delete the {language.name} ({language.code}) language? This will
<strong>permanently</strong>
delete all {language.name} translations in:

<ul>
	<li>
		<strong>Blog</strong> (name and description)
	</li>
	<li>
		<strong>Posts</strong>
	</li>
	<li>
		<strong>Users</strong>
	</li>
	<li>
		<strong>Tags</strong>
	</li>
	<li>
		<strong>Navigations</strong>
	</li>
</ul>

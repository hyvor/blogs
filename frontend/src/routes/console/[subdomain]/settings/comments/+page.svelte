<script lang="ts">
	import { Link, SplitControl, Text } from '@hyvor/design/components';
	import { blogStore, updateBlogStore } from '../../../lib/stores/blogStore';
	import BlogSettingsSave from '../BlogSettingsSave.svelte';
	import CodemirrorEditor from '../../../lib/components/CodemirrorEditor/CodemirrorEditor.svelte';
	import { consoleUrlWithBlog } from '../../../lib/consoleUrl';
</script>

<BlogSettingsSave keys={['comments_code', 'newsletter_code']} />

<div class="settings">
	<SplitControl label="Comments Embed Code">
		<div slot="caption">
			Paste the embed code from your commenting system here. You can use Twig <Link
				href="/docs/themes-templates#variables"
				style="display:inline"
				target="_blank">route variables</Link
			> if needed. To connect Hyvor Talk, go to <Link
				href={consoleUrlWithBlog('/integrations/hyvor-talk')}
				style="display:inline">Integrations &rarr; Hyvor Talk</Link
			>.
		</div>

		<CodemirrorEditor
			value={$blogStore.comments_code || ''}
			id="comments"
			ext="twig"
			on:change={(e) => updateBlogStore({ comments_code: e.detail })}
		/>

		<div style="margin-top:10px;">
			<Text light small>
				This will be added to the end of the post content, depending on the theme.
			</Text>
		</div>
	</SplitControl>

	<SplitControl label="Newsletter Signup Form Code">
		<div slot="caption">
			Paste the embed code provided by a email newsletter service here (for the sign up form). You
			can use Twig <Link
				style="display:inline;"
				href="/docs/themes-templates#variables"
				target="_blank">route variables</Link
			> if needed. To connect Hyvor Talk, go to <Link
				href={consoleUrlWithBlog('/integrations/hyvor-talk')}
				style="display:inline">Integrations &rarr; Hyvor Talk</Link
			>.
		</div>

		<CodemirrorEditor
			value={$blogStore.newsletter_code || ''}
			id="newsletter"
			ext="twig"
			on:change={(e) => updateBlogStore({ newsletter_code: e.detail })}
		/>

		<div style="margin-top:10px;">
			<Text light small>
				Your theme will decide where to show this form. If you want to show it in a specific place,
				you may also edit your theme files.
			</Text>
		</div>
	</SplitControl>
</div>

<style>
	.settings {
		flex: 1;
		overflow: auto;
		padding: 25px 30px;
	}
	.settings :global(.split-control) {
		flex-direction: column;
	}

	.settings :global(.CodeMirror) {
		min-height: 200px;
	}
</style>

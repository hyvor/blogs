<script lang="ts">
	import { Callout, Link, SplitControl, Switch, Text } from '@hyvor/design/components';
	import { blogStore, updateBlogStore } from '../../../../lib/stores/blogStore';
	import BlogSettingsSave from '../BlogSettingsSave.svelte';
	import CodemirrorEditor from '../../../../lib/components/CodemirrorEditor/CodemirrorEditor.svelte';

	function handleFlashloadChange(e: any) {
		updateBlogStore({ flashload: e.target.checked });
	}
</script>

<BlogSettingsSave keys={['code_head', 'code_foot', 'flashload']} />

<div class="settings">
	<div class="intro">
		<Callout type="info">
			Head and foot code will be applied to your <strong>whole blog</strong>. See <Link
				href="https://blogs.hyvor.com/docs/custom-code"
				target="_blank">docs</Link
			> for other ways to add custom code. You can use HTML or <Link
				href="/docs/themes-templates#twig"
				target="_blank">Twig</Link
			> in the code fields. <Link href="/docs/themes-templates#variables" target="_blank"
				>These variables</Link
			> are available in Twig.
		</Callout>
	</div>

	<div class="code">
		<SplitControl label="Head Code">
			{#snippet caption()}
				<div>Added right before the &lt;/head&gt; tag. Best place for styles and meta tags.</div>
			{/snippet}

			<CodemirrorEditor
				value={$blogStore.code_head || ''}
				id="comments"
				ext="twig"
				onchange={(value) => updateBlogStore({ code_head: value })}
			/>
		</SplitControl>

		<SplitControl label="Foot Code">
			{#snippet caption()}
				<div>
					Added right before the &lt;/body&gt; tag. Best place for scripts (analytics, etc.).
				</div>
			{/snippet}

			<CodemirrorEditor
				value={$blogStore.code_foot || ''}
				id="newsletter"
				ext="twig"
				onchange={(value) => updateBlogStore({ code_foot: value })}
			/>
		</SplitControl>
	</div>

	<SplitControl label="Flashload">
		{#snippet caption()}
			<div>
				<Link href="https://github.com/hyvor/flashload" target="_blank">Flashload</Link> adds a script
				to make navigation between pages faster by preloading pages when hovering over links. You may
				want to disable this if you are using other Javascript-heavy features.
			</div>
		{/snippet}

		<Switch checked={$blogStore.flashload} on:change={handleFlashloadChange} />
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
	.intro {
		padding: 15px;
	}
</style>

<script lang="ts">
	import { Button, Loader, Table, TableRow } from '@hyvor/design/components';
	import { onMount } from 'svelte';
	import IconCaretDown from '@hyvor/icons/IconCaretDown';
import IconCaretRight from '@hyvor/icons/IconCaretRight';


	interface DataType {
		languageTags: string;
		languagesCount: number;
		themeTags: string;
		themesCount: number;
		previews: string;
	}

	let data: null | DataType = $state(null);
	let showPreview = $state(false);

	onMount(() => {
		fetch('/api/special/syntax')
			.then((res) => res.json())
			.then((res) => {
				data = res;
			});
	});
</script>

<h1 id="syntax-highlighting">Syntax Highlighting</h1>
<p>Hyvor Blogs has a powerful syntax highlighter with the following features:</p>

<ul>
	<li>Line numbering</li>
	<li>Line highlighting</li>
	<li>Diff (+ and -)</li>
	<li>Line focusing</li>
</ul>

<p>
	Most importantly, syntax highlighting is done at the time of rendering posts in our back-end.
	Therefore, it does not require any additional Javascript or CSS. To change syntax highlighting
	settings, go to <code>Console → Settings → Post Content</code>.
</p>

<div class="dynamic">
	<h2 id="languages">Languages</h2>

	{#if data === null}
		<Loader block padding={40}>Loading languages</Loader>
	{:else}
		<p>
			Our syntax highlighter supports {data.languagesCount} programming languages:
		</p>
		<div class="language-tags">
			<div>Supported Languages</div>
			{@html data.languageTags}
		</div>
	{/if}

	<h2 id="themes">Themes</h2>

	{#if data === null}
		<Loader block padding={40}>Loading themes</Loader>
	{:else}
		<p>
			Hyvor Blogs supports {data.themesCount} VS Code themes.
		</p>

		<div class="language-tags themes">
			<div>Supported Themes</div>
			{@html data.themeTags}
		</div>

		<p>
			<Button size="small" on:click={() => (showPreview = !showPreview)}>
				Show theme previews
				{#snippet end()}
							
						{#if showPreview}
							<IconCaretDown size={12} />
						{:else}
							<IconCaretRight size={12} />
						{/if}
					
							{/snippet}
			</Button>
		</p>

		{#if showPreview}
			<div id="theme-previews">
				{@html data.previews}
			</div>
		{/if}
	{/if}
</div>

<h2 id="adding">Adding Code Blocks to Your Post</h2>
<p>See <a href="/docs/writing#code-block">Code Block</a> in Writing.</p>

<h2 id="annotations">Annotations</h2>
<p>
	Annotations are used for highlighting, focusing, and numbering lines. You can add annotations to
	the code block in the Editor. Let's see some examples.
</p>

<Table columns="2fr 3fr" hover>
	<TableRow head>
		<div>Annotation</div>
		<div>Description</div>
	</TableRow>

	<TableRow>
		<div><code>h=1</code></div>
		<div>Highlights the first line</div>
	</TableRow>

	<TableRow>
		<div><code>h=1-5</code></div>
		<div>Highlights line 1 to 5</div>
	</TableRow>

	<TableRow>
		<div><code>h=1,5,6</code></div>
		<div>Highlights line 1, 5, and 6</div>
	</TableRow>

	<TableRow>
		<div><code>h=1-4,7</code></div>
		<div>Highlights line 1 to 4, and then 7</div>
	</TableRow>

	<TableRow>
		<div><code>f=1</code></div>
		<div>Focuses number 1 (Works exactly as highlight)</div>
	</TableRow>

	<TableRow>
		<div><code>+=12</code></div>
		<div>Highlights the 12th line in green (for Diff add)</div>
	</TableRow>

	<TableRow>
		<div><code>-=20</code></div>
		<div>Highlights the 20th line in red (for Diff remove)</div>
	</TableRow>

	<TableRow>
		<div><code>renumber=4:21</code></div>
		<div>Number of the 4th line will be changed to 21. The next line will have 22</div>
	</TableRow>

	<TableRow>
		<div><code>renumber=6:null</code></div>
		<div>Number of the 6th line will be hidden</div>
	</TableRow>

	<TableRow>
		<div><code>h=1 +=12</code></div>
		<div>You can have multiple space separated annotations</div>
	</TableRow>

	<TableRow>
		<div><code>numbers=true</code></div>
		<div>Enable line numbers (to override global settings)</div>
	</TableRow>

	<TableRow>
		<div><code>numbers=false</code></div>
		<div>Disable line numbers (to override global settings)</div>
	</TableRow>
</Table>

<h2 id="tips">Tips</h2>
<ul>
	<li>
		Under the hood, Hyvor Blogs use <a href="https://github.com/shikijs/shiki" rel="nofollow"
			>Shiki</a
		> for syntax highlighting. Therefore, we can support and VSCode-supported language or theme. If you
		want to add any, contact us.
	</li>
	<li>
		Colors for syntax comes from our side, but styles like padding, margins, space between lines,
		and font sizes comes from the <a href="/docs/theme">theme</a> of your blog.
	</li>
</ul>

<style lang="scss">
	.dynamic {
		:global(.language-tags) {
			background: #fafafa;
			border-radius: 20px;
			padding: 20px;
			:global(div) {
				font-weight: 600;
				margin-bottom: 10px;
			}
			:global(span) {
				display: inline-block;
				padding: 2px 8px;
				margin-right: 4px;
				margin-bottom: 4px;
				background: #eaeaea;
				border-radius: 20px;
				font-size: 12px;
			}
		}
		:global(#theme-previews) {
			margin: 20px 0;
			:global(pre code) {
				display: block;
				padding: 20px 0;
				line-height: 1.5;
				font-family:
					Consolas,
					Menlo,
					Monaco,
					source-code-pro,
					Courier New,
					monospace;
				font-size: 13.6px;
				background-color: transparent;
				:global(.line) {
					padding: 0 20px;
				}
				:global(.line-number) {
					margin-right: 1rem;
				}
			}
			:global(pre) {
				padding: 0 !important;
				margin-top: 10px !important;
				border-radius: 20px !important;
			}

			:global(.theme-key) {
				font-weight: 600;
				font-size: 20px;
			}
		}
	}
</style>

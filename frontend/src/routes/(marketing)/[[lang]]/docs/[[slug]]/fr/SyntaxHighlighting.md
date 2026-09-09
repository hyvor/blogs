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
		fetch('/api/public/highlighting-docs')
			.then((res) => res.json())
			.then((res) => {
				data = res;
			});
	});
</script>

<h1 id="syntax-highlighting">Coloration syntaxique</h1>

Hyvor Blogs dispose d'un puissant surligneur syntaxique avec les fonctionnalités suivantes :

- Numérotation des lignes
- Surlignage des lignes
- Diff (+ et -)
- Focus sur une ligne

Plus important encore, la coloration syntaxique est effectuée au moment du rendu des articles sur notre back-end.
Elle ne nécessite donc aucun JavaScript ou CSS supplémentaire. Pour modifier les paramètres de
coloration syntaxique, allez dans `Console → Settings → Post Content`.

<div class="dynamic">
	<h2 id="languages">Langages</h2>

    {#if data === null}
    	<Loader block padding={40}>Chargement des langages</Loader>
    {:else}
    	<p>
    		Notre surligneur syntaxique prend en charge {data.languagesCount} langages de programmation :
    	</p>
    	<div class="language-tags">
    		<div>Langages pris en charge</div>
    		{@html data.languageTags}
    	</div>
    {/if}

    <h2 id="themes">Thèmes</h2>

    {#if data === null}
    	<Loader block padding={40}>Chargement des thèmes</Loader>
    {:else}
    	<p>
    		Hyvor Blogs prend en charge {data.themesCount} thèmes VS Code.
    	</p>

    	<div class="language-tags themes">
    		<div>Thèmes pris en charge</div>
    		{@html data.themeTags}
    	</div>

    	<p>
    		<Button size="small" on:click={() => (showPreview = !showPreview)}>
    			Afficher les aperçus des thèmes
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

<h2 id="adding">Ajouter des blocs de code à votre article</h2>

Voir [Bloc de code](/docs/editor#code-block) dans l'Éditeur.

<h2 id="annotations">Annotations</h2>

Les annotations sont utilisées pour surligner, mettre en focus et numéroter les lignes. Vous pouvez ajouter des annotations au
bloc de code dans l'Éditeur. Voyons quelques exemples.

<Table columns="2fr 3fr" hover>
	<TableRow head>
		<div>Annotation</div>
		<div>Description</div>
	</TableRow>

    <TableRow>
    	<div><code>h=1</code></div>
    	<div>Surligne la première ligne</div>
    </TableRow>

    <TableRow>
    	<div><code>h=1-5</code></div>
    	<div>Surligne les lignes 1 à 5</div>
    </TableRow>

    <TableRow>
    	<div><code>h=1,5,6</code></div>
    	<div>Surligne les lignes 1, 5 et 6</div>
    </TableRow>

    <TableRow>
    	<div><code>h=1-4,7</code></div>
    	<div>Surligne les lignes 1 à 4, puis la ligne 7</div>
    </TableRow>

    <TableRow>
    	<div><code>f=1</code></div>
    	<div>Met le focus sur le numéro 1 (fonctionne exactement comme le surlignage)</div>
    </TableRow>

    <TableRow>
    	<div><code>+=12</code></div>
    	<div>Surligne la 12e ligne en vert (pour un ajout de Diff)</div>
    </TableRow>

    <TableRow>
    	<div><code>-=20</code></div>
    	<div>Surligne la 20e ligne en rouge (pour une suppression de Diff)</div>
    </TableRow>

    <TableRow>
    	<div><code>renumber=4:21</code></div>
    	<div>Le numéro de la 4e ligne sera changé en 21. La ligne suivante aura le numéro 22</div>
    </TableRow>

    <TableRow>
    	<div><code>renumber=6:null</code></div>
    	<div>Le numéro de la 6e ligne sera masqué</div>
    </TableRow>

    <TableRow>
    	<div><code>h=1 +=12</code></div>
    	<div>Vous pouvez avoir plusieurs annotations séparées par des espaces</div>
    </TableRow>

    <TableRow>
    	<div><code>numbers=true</code></div>
    	<div>Active la numérotation des lignes (pour remplacer les paramètres globaux)</div>
    </TableRow>

    <TableRow>
    	<div><code>numbers=false</code></div>
    	<div>Désactive la numérotation des lignes (pour remplacer les paramètres globaux)</div>
    </TableRow>

</Table>

<h2 id="notes">Notes</h2>

- Les couleurs de la syntaxe proviennent de notre côté, mais les styles comme le padding, les marges, l'espace entre les lignes et la taille de la police proviennent du [thème](/docs/themes) de votre blog.
- En interne, nous utilisons des grammaires TextMate pour la coloration syntaxique.

<style>
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

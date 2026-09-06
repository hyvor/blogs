<script lang="ts">
	import { DocsImage } from '@hyvor/design/marketing';
	import { Callout, Table, TableRow } from '@hyvor/design/components';
</script>

# Analyseur de liens

L'outil d'analyse de liens dans l'éditeur d'articles analyse le statut des liens dans votre article au fur et à mesure que vous écrivez. Il vous affichera un avertissement s'il y a des liens brisés, risqués ou de redirection dans votre article. Il vous montre également le [type de chaque lien](#link-types).

<DocsImage src="/images/docs/writing/link-analysis.png" alt="Analyse de liens" width={400} />

<h2 id="link-types">Types de liens</h2>

Hyvor Blogs classe les liens dans les catégories suivantes.

<Table columns="2fr 3fr" hover>
	<TableRow head>
		<div>Type de lien</div>
		<div>Description</div>
	</TableRow>

    <TableRow>
    	<div><code>internal-blog</code></div>
    	<div>Liens vers d'autres articles/pages de votre blog</div>
    </TableRow>

    <TableRow>
    	<div><code>internal-domain</code></div>
    	<div>Liens vers le même domaine que votre blog, mais pas vers votre blog</div>
    </TableRow>

    <TableRow>
    	<div><code>internal-root-domain</code></div>
    	<div>Liens vers n'importe quel domaine du domaine racine, mais pas vers le domaine de votre blog</div>
    </TableRow>

    <TableRow>
    	<div><code>external</code></div>
    	<div>Liens vers d'autres domaines</div>
    </TableRow>

    <TableRow>
    	<div><code>mail</code></div>
    	<div>Liens mailto (commence par <code>mailto:</code>)</div>
    </TableRow>

    <TableRow>
    	<div><code>tel</code></div>
    	<div>Liens tel (commence par <code>tel:</code>)</div>
    </TableRow>

    <TableRow>
    	<div><code>anchor</code></div>
    	<div>Liens vers des ancres dans la même page (commence par <code>#</code>)</div>
    </TableRow>

    <TableRow>
    	<div><code>other</code></div>
    	<div>Autres liens (<code>ftp:</code>, <code>data:</code>, javascript, etc.)</div>
    </TableRow>

</Table>

Allez dans **Console → Outils → Analyse de liens** pour

- voir un aperçu de tous les liens de votre blog
- consulter les résultats de l'analyse de liens bihebdomadaire
- modifier les paramètres du rapport par e-mail

<h3 id="link-analysis-accuracy">Précision de l'analyse de liens</h3>

Notre analyseur de liens est simple : il envoie des requêtes HTTP via curl pour vérifier le statut des liens. Cette approche permet une analyse rapide et précise. Cependant, certains serveurs et pare-feux peuvent bloquer ces requêtes, ce qui peut entraîner des faux positifs. Si vous trouvez un lien marqué comme brisé mais qui fonctionne en réalité, vous pouvez cliquer sur le bouton d'ignorer pour l'ignorer lors des futures analyses.
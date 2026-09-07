<script lang="ts">
	import { DocsImage } from '@hyvor/design/marketing';
	import { Callout } from '@hyvor/design/components';
</script>

# Analyseur SEO

L'outil d'analyse SEO dans l'éditeur d'articles vous donnera des suggestions pour améliorer le référencement de votre article. Il fonctionne sur la base de règles prédéfinies.

<Callout type="info" title="Avertissement">
    	L'analyse SEO n'est <b>qu'une suggestion</b>. Obtenir un score plus élevé ne suffira pas à
    	faire remonter vos articles dans les résultats de recherche. D'autres facteurs affectent
    	également votre référencement, comme les backlinks, l'autorité de domaine, etc. Cependant,
    	ces suggestions vous aideront à commettre moins d'erreurs de SEO.
</Callout>

<DocsImage src="/images/docs/writing/seo.png" alt="Analyse SEO" width={400} />

<br />

Pour commencer à analyser votre article, ajoutez un mot-clé principal pour votre article. Vous pouvez également ajouter des mots-clés secondaires. Hyvor Blogs analysera ensuite le contenu et les métadonnées de votre article et vous donnera des suggestions en temps réel pour améliorer le SEO de votre article.

Voici les tests que Hyvor Blogs effectuera sur votre article :

<ul>
	<li><b>Mot-clé principal dans le titre</b></li>
	<ul>
		<li>100% si le mot-clé principal est au début du titre</li>
		<li>75% si le mot-clé principal se trouve dans les 50 premiers caractères du titre</li>
		<li>49% si le mot-clé principal se trouve après les 50 premiers caractères du titre</li>
		<li>0% si le mot-clé principal n'est pas dans le titre</li>
	</ul>

    <li><b>Mot-clé principal dans la description</b></li>
    <li><b>Mot-clé principal dans le slug</b></li>
    <p>
    	Si le mot-clé principal est <code>blogging platforms</code>, nous vérifions la présence de
    	<code>blogging-platforms</code>
    	dans le slug. Il est recommandé de définir un <b>slug court avec des tirets</b>. Dans ce cas, le score
    	sera :
    </p>
    <ul>
    	<li>100% si le slug correspond exactement à <code>blogging-platforms</code></li>
    	<li>75% si le slug contient <code>blogging-platforms</code> avec d'autres mots</li>
    </ul>

    <li><b>Mot-clé principal au début du contenu</b></li>
    <p>
    	Si votre contenu comporte plus de 300 mots, le mot-clé principal doit se trouver dans les 10 premiers
    	pourcents du contenu. S'il comporte moins de 300 mots, il doit se trouver quelque part dans le contenu.
    </p>

    <li><b>Longueur du contenu</b></li>
    <p>(La meilleure longueur de contenu dépend du sujet, ce qui n'est pas pris en compte ici)</p>
    <ul>
    	<li>0% pour moins de 400 mots</li>
    	<li>1% pour chaque tranche de 25 mots après 400 mots (2500+ mots = 100%)</li>
    </ul>

    <li><b>Tous les mots-clés dans le contenu</b></li>
    <p>Tous les mots-clés doivent être présents dans le contenu de l'article.</p>

    <li><b>Tous les mots-clés dans les sous-titres</b></li>
    <p>Chaque mot-clé doit être présent dans au moins un sous-titre (h2, h3, h4, h5, h6).</p>

    <li><b>Tous les mots-clés dans les attributs alt des images</b></li>
    <p>Chaque mot-clé doit être présent dans au moins un attribut alt d'image.</p>

    <li><b>Densité des mots-clés</b></li>
    <p>Vérifie la densité des mots-clés dans le contenu (<code>keywords count / total words</code>).</p>
    <ul>
    	<li>0% pour moins de 0,1%</li>
    	<li>50% pour 0,1% à 0,5%</li>
    	<li>100% pour 0,5% à 2,5%</li>
    	<li>50% pour 2,5% à 5%</li>
    	<li>0% pour plus de 5%</li>
    </ul>

    <li><b>Longueur du slug</b></li>
    <p>Des slugs plus courts sont préférables. Ce test réussira si le slug fait moins de 50 caractères.</p>

    <li><b>Liens externes</b></li>
    <p>Au moins un lien externe doit être présent dans l'article.</p>

    <li><b>Liens internes</b></li>
    <p>
    	Au moins un lien interne doit être présent dans l'article. Les liens vers tout sous-domaine de votre
    	domaine principal seront considérés comme des liens internes. Consultez <a href="/docs/link-analyzer#link-types"
    		>les types de liens</a
    	>
    	pour plus d'informations. Les liens <code>internal-blog</code>, <code>internal-domain</code>, et
    	<code>internal-root-domain</code> sont considérés comme des liens internes.
    </p>

    <li><b>Images</b></li>
    <ul>
    	<li>70% - 1 image</li>
    	<li>80% - 2 images</li>
    	<li>90% - 3 images</li>
    	<li>100% - 4 images ou plus</li>
    </ul>

    <li><b>Toutes les images ont des attributs alt</b></li>
    <p>Toutes les images doivent avoir des attributs alt</p>

</ul>

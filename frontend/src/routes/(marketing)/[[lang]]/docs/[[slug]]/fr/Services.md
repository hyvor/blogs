<script lang="ts">
	import { Callout, TabNav, TabNavItem, Table, TableRow, Link } from '@hyvor/design/components';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	let active = $state('comments');
</script>

# Services

<TabNav>
	<TabNavItem name="comments" active={active === 'comments'} onclick={() => (active = 'comments')}
		>Commentaires</TabNavItem
	>

    <TabNavItem
    	name="newsletter"
    	active={active === 'newsletter'}
    	onclick={() => (active = 'newsletter')}>Newsletter</TabNavItem
    >

    <TabNavItem
    	name="analytics"
    	active={active === 'analytics'}
    	onclick={() => (active = 'analytics')}>Analytique</TabNavItem
    >

    <TabNavItem
    	name="memberships"
    	active={active === 'memberships'}
    	onclick={() => (active = 'memberships')}>Adhésions</TabNavItem
    >

    <TabNavItem name="forms" active={active === 'forms'} onclick={() => (active = 'forms')}
    	>Formulaires</TabNavItem
    >

</TabNav>

{#if active === 'comments'}
<div>
<p>
Ajouter un système de commentaires à votre blog permet à vos lecteurs de partager leurs opinions
et de participer aux conversations sur le blog. Cela peut augmenter l'autorité de votre blog.
</p>
<p>
<a href="https://talk.hyvor.com">Hyvor Talk</a> est notre propre plateforme de commentaires. Hyvor Blogs
s'intègre directement à Hyvor Talk pour permettre les commentaires sur votre blog. Vous pouvez utiliser Hyvor Talk
<b>GRATUITEMENT</b> sur <a href="/pricing">tous les plans</a>.
</p>

    	<!--  <Callout type="info">
            <ul>
                <li>Connect your blog to Hyvor Talk, It's easy and fast</li>
                <li>Use the same HYVOR account (for the owner)</li>
                <li>Completely free</li>
                <li>Fast, secure, and privacy-focused</li>
                </ul>
        </Callout> -->
    	<p>Paramètres des commentaires : <b>Console → Intégrations → Hyvor Talk</b>.</p>

    	<h2>Ajouter des commentaires</h2>
    	<p>
    		Pour ajouter des commentaires à votre blog, vous devez ajouter le code fourni par le système de commentaires dans le
    		champ « Code d'intégration des commentaires ». Cela ajoutera les commentaires en bas de chaque article. Vous pouvez
    		trouver le code dans le tableau de bord du système de commentaires.
    	</p>
    	<p>Voici quelques systèmes de commentaires populaires :</p>
    	<ul>
    		<li><a href="https://talk.hyvor.com" target="_blank" rel="nofollow">Hyvor Talk</a></li>
    		<li><a href="https://disqus.com" target="_blank" rel="nofollow">Disqus</a></li>
    		<li><a href="https://commento.io" target="_blank" rel="nofollow">Commento</a></li>
    		<li><a href="https://getreplybox.com/" rel="nofollow">GetReplyBox</a></li>
    	</ul>
    	<p>Vous pouvez également personnaliser le thème pour ajouter le code d'intégration manuellement.</p>
    </div>

{:else if active === 'newsletter'}
<div>
<p>Une newsletter est un excellent moyen de fidéliser une audience et de partager des informations utiles.</p>

    	<h2>Ajout</h2>
    	<p>Pour ajouter un formulaire d'inscription par e-mail à votre blog,</p>
    	<ul>
    		<li>Tout d'abord, inscrivez-vous à un <a href="/docs/newsletter#services">service de newsletter</a>.</li>
    		<li>Copiez le code HTML fourni.</li>
    		<li>
    			Collez-le dans <b>Console → Paramètres → Commentaires & Newsletter → Code du formulaire d'inscription à la newsletter</b>.
    		</li>
    	</ul>

    	<h2>Positionnement</h2>
    	<p>
    		Tous les <a href="/themes">thèmes</a> de Hyvor Blogs sont conçus pour avoir un emplacement dédié au formulaire
    		d'inscription à la newsletter. Si vous souhaitez changer cette position, vous devrez
    		<a href="/docs/theme#editing">modifier votre thème</a> et changer la position de
    		<a href="/docs/themes-templates#placeholders">l'espace réservé</a> à la newsletter. Dans vos fichiers de modèle, trouvez
    		et coupez ce code <code>{`{{ _newsletter | template }}`}</code>, puis collez-le à l'endroit où vous
    		souhaitez que le formulaire d'inscription apparaisse.
    	</p>

    	<h2>Services de newsletter</h2>

    	<Table columns="1fr 1fr" hover>
    		<TableRow head>
    			<div>Service</div>
    			<div>Tutoriel d'intégration</div>
    		</TableRow>

    		<TableRow>
    			<div>
    				<a href="https://convertkit.com/" rel="nofollow" target="_blank">ConvertKit</a>
    			</div>
    			<div>
    				<Link href="https://hyvor.com/blog/add-convertkit" target="_blank"
    					>{#snippet end()}
    						<IconBoxArrowUpRight />
    					{/snippet}Voir</Link
    				>
    			</div>
    		</TableRow>
    		<TableRow>
    			<div>
    				<a href="https://mailchimp.com/en-gb/" rel="nofollow" target="_blank">MailChimp</a>
    			</div>
    			<div>
    				<Link href="https://hyvor.com/blog/how-to-add-mailchimp" target="_blank"
    					>{#snippet end()}
    						<IconBoxArrowUpRight />
    					{/snippet}Voir</Link
    				>
    			</div>
    		</TableRow>
    		<TableRow>
    			<div>
    				<a href="https://emailoctopus.com/" rel="nofollow" target="_blank">EmailOctopus</a>
    			</div>
    			<!-- <div><Link href="https://hyvor.com/blog/how-to-add-mailchimp"><IconBoxArrowInUpRight slot="end" />View</Link></div> -->
    		</TableRow>
    		<TableRow
    			><div>
    				<a href="https://moosend.com/" rel="nofollow" target="_blank">Mossend</a>
    			</div></TableRow
    		>
    		<TableRow
    			><div>
    				<a href="https://www.mailerlite.com/" rel="nofollow" target="_blank">MailerLite</a>
    			</div></TableRow
    		>
    	</Table>

    	<p>
    		Nous ne sommes affiliés à aucun de ces services. Il existe de nombreux autres services de newsletter
    		non mentionnés ici.
    	</p>
    </div>

{:else if active === 'analytics'}
<div>
<p>
C'est intéressant de connaître certaines mesures sur le trafic de votre blog. Vous pouvez facilement
intégrer des services d'analyse au blog à l'aide de code personnalisé.
</p>

    	<h2>Comment ajouter</h2>
    	<ul>
    		<li>Tout d'abord, inscrivez-vous à un <a href="/docs/analytics#services">service d'analyse</a>.</li>
    		<li>Copiez le code HTML fourni.</li>
    		<li>Collez-le dans <b>Console → Paramètres → Code personnalisé → Code de pied de page</b>.</li>
    	</ul>

    	<h2>Services d'analyse</h2>
    	<Table columns="1fr 1fr" hover>
    		<TableRow head>
    			<div>Service</div>
    			<div>Tutoriel pour le blog</div>
    		</TableRow>

    		<TableRow>
    			<div>
    				<a href="https://analytics.google.com/analytics" rel="nofollow" target="_blank"
    					>Google Analytics</a
    				>
    			</div>
    			<div>
    				<Link href="https://hyvor.com/blog/add-google-analytics-to-your-blog" target="_blank"
    					>{#snippet end()}
    						<IconBoxArrowUpRight />
    					{/snippet}Voir</Link
    				>
    			</div>
    		</TableRow>
    		<TableRow>
    			<div>
    				<a href="https://www.cloudflare.com/analytics" rel="nofollow" target="_blank"
    					>Cloudflare Analytics</a
    				>
    			</div>
    		</TableRow>
    		<TableRow>
    			<div><a href="https://matomo.org/" rel="nofollow" target="_blank">Matomo</a></div>
    		</TableRow>
    		<TableRow>
    			<div>
    				<a href="https://usefathom.com/" rel="nofollow" target="_blank">Fathom</a>
    			</div>
    		</TableRow>
    		<TableRow
    			><div>
    				<a href="https://plausible.io/" rel="nofollow" target="_blank">Plausible Analytics</a>
    			</div></TableRow
    		>
    		<TableRow
    			><div>
    				<a href="https://simpleanalytics.io/" rel="nofollow" target="_blank">Simple Analytics</a>
    			</div></TableRow
    		>
    		<TableRow
    			><div>
    				<a href="https://posthog.com/" rel="nofollow" target="_blank">Posthog</a>
    			</div></TableRow
    		>
    	</Table>

    	<p>
    		Nous ne sommes affiliés à aucun de ces services. Il existe de nombreux autres outils d'analyse
    		non mentionnés ici.
    	</p>

    	<h2>Confidentialité</h2>
    	<p>
    		Certaines de ces plateformes suivent les <b>utilisateurs</b> tandis que d'autres ne suivent que les <b>visites</b>. Avant
    		de suivre les utilisateurs, vous aurez besoin de leur consentement (à l'aide d'une <b>bannière de cookies</b>). Veuillez
    		consulter la documentation de chaque service pour en savoir plus sur la manière dont ils traitent les données personnelles.
    	</p>
    </div>

{:else if active === 'memberships'}
<div>
<p>
Ajouter des adhésions (connexion, inscription, abonnements, etc.) est un moyen simple de permettre à vos lecteurs
de se connecter à votre blog, ainsi que de vous permettre de proposer du contenu restreint et de générer des
revenus d'abonnement. Étant donné que ces fonctionnalités sont dynamiques, Hyvor Blogs ne prend pas en charge et ne prendra pas en charge
nativement les adhésions, car nous nous efforçons de garder votre blog statique.
</p>

    	<h2>Plateformes d'adhésion</h2>

    	<Table columns="1fr 1fr" hover>
    		<TableRow head>
    			<div>Service</div>
    			<div>Tutoriel pour le blog</div>
    		</TableRow>

    		<TableRow>
    			<div>
    				<a href="https://memberstack.com/" rel="nofollow" target="_blank">Memberstack</a>
    			</div>
    			<div>
    				<Link href="https://hyvor.com/blog/add-memberstack" target="_blank"
    					>{#snippet end()}
    						<IconBoxArrowUpRight />
    					{/snippet}Voir</Link
    				>
    			</div>
    		</TableRow>

    		<TableRow>
    			<div>
    				<a href="https://www.memberspace.com/" rel="nofollow" target="_blank">Memberspace</a>
    			</div>
    			<!-- <div><Link href="https://hyvor.com/blog/add-memberspace"><IconBoxArrowInUpRight slot="end" />View</Link></div> -->
    		</TableRow>
    	</Table>

    	<Callout type="info"
    		><p>
    			Notez que l'intégration de ces plateformes nécessite, dans la plupart des cas, d'écrire du code Javascript.
    			Si vous souhaitez simplement rester en contact avec vos utilisateurs, intégrer un formulaire d'inscription à la <a
    				href="https://blogs.hyvor.com/docs/newsletter">newsletter</a
    			> serait une option meilleure et plus simple.
    		</p></Callout
    	>
    </div>

{:else}
<div>
<p>
Vous voudrez peut-être collecter des données auprès de vos visiteurs. Intégrer un service de formulaires est le moyen le plus simple
de le faire.
</p>

    	<h2>Comment intégrer des formulaires</h2>
    	<ul>
    		<li>
    			Tout d'abord, inscrivez-vous à un <a href="/docs/forms#forms">service de formulaires</a>. Ils vous permettent de créer
    			des formulaires personnalisés selon vos préférences
    		</li>
    		<li>Ensuite, ajoutez le code d'intégration du formulaire à votre blog :</li>
    		<ul>
    			<li>
    				Pour ajouter un formulaire à un article ou une page, utilisez les <a href="/docs/writing#custom-html"
    					>blocs HTML/Twig personnalisés</a
    				>.
    			</li>
    			<li>
    				Pour ajouter un formulaire à un endroit spécifique du blog, vous devrez <a
    					href="/docs/theme#editing">modifier le thème</a
    				>.
    			</li>
    		</ul>
    	</ul>

    	<h2>Services de formulaires</h2>

    	<Table columns="1fr 1fr">
    		<TableRow head>
    			<div>Service</div>
    			<div>Tutoriel pour le blog</div>
    		</TableRow>

    		<TableRow>
    			<div>
    				<a href="https://www.google.com/forms" rel="nofollow" target="_blank">Google Forms</a>
    			</div>
    			<div>
    				<Link href="https://hyvor.com/blog/add-google-forms" target="_blank"
    					>{#snippet end()}
    						<IconBoxArrowUpRight />
    					{/snippet}Voir</Link
    				>
    			</div>
    		</TableRow>

    		<TableRow>
    			<div>
    				<a href="https://forms.office.com/" rel="nofollow" target="_blank">Microsoft Forms</a>
    			</div>
    			<div>
    				<Link href="https://hyvor.com/blog/add-microsoft-forms" target="_blank"
    					>{#snippet end()}
    						<IconBoxArrowUpRight />
    					{/snippet}Voir</Link
    				>
    			</div>
    		</TableRow>

    		<TableRow>
    			<div>
    				<a href="https://www.typeform.com/" rel="nofollow" target="_blank">Typeform</a>
    			</div>
    			<div>
    				<Link href="https://hyvor.com/blog/add-microsoft-forms" target="_blank"
    					>{#snippet end()}
    						<IconBoxArrowUpRight />
    					{/snippet}Voir</Link
    				>
    			</div>
    		</TableRow>

    		<TableRow>
    			<div><a href="https://www.wufoo.com/" rel="nofollow" target="_blank">Wufoo</a></div>
    			<div>
    				<Link href="https://hyvor.com/blog/add-wufoo" target="_blank"
    					>{#snippet end()}
    						<IconBoxArrowUpRight />
    					{/snippet}Voir</Link
    				>
    			</div>
    		</TableRow>

    		<TableRow>
    			<div>
    				<a href="https://www.jotform.com/" rel="nofollow" target="_blank">JotForm</a>
    			</div>
    		</TableRow>

    		<TableRow>
    			<div>
    				<a href="https://www.formsite.com/" rel="nofollow" target="_blank">Formsite</a>
    			</div>
    		</TableRow>

    		<TableRow>
    			<div>
    				<a href="https://paperform.co/" rel="nofollow" target="_blank">Paperform</a>
    			</div>
    		</TableRow>

    		<TableRow>
    			<div>
    				<a href="https://www.zoho.com/forms/" rel="nofollow" target="_blank">Zoho Forms</a>
    			</div>
    		</TableRow>
    	</Table>

    	<p>
    		Nous ne sommes affiliés à aucun de ces services. Il existe de nombreux autres services de formulaires
    		non mentionnés ici.
    	</p>
    </div>

{/if}

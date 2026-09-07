<script lang="ts">
	import { Callout } from '@hyvor/design/components';
	import IconLightbulb from '@hyvor/icons/IconLightbulb';
	import { DocsImage } from '@hyvor/design/marketing';
</script>

# Domaine personnalisé

Découvrez comment configurer un domaine personnalisé (par exemple `blog.example.com` ou `example.com`) pour votre blog.

<Callout type="info">
	{#snippet icon()}
		<IconLightbulb />
	{/snippet}
	La configuration d'un domaine personnalisé vous aidera à <strong>construire votre propre marque</strong> et à
	<strong>éviter de dépendre de notre plateforme</strong> au cas où vous souhaiteriez passer à une autre plateforme à l'
	avenir.
</Callout>

<h2 id="prerequisites">Prérequis</h2>

- Un **nom de domaine**.
- L'accès aux **paramètres DNS** de votre domaine pour créer des enregistrements DNS.
- L'accès au [rôle d'administrateur](/docs/users#roles) sur votre blog.

<h2 id="blog-setitngs">Étape 1 : Mettre à jour les paramètres du blog</h2>

- Allez dans [Console](/console) &rarr; Paramètres &rarr; Hébergement.
- Cliquez sur **Configurer un domaine personnalisé**
- Entrez votre domaine personnalisé
- Cliquez sur Enregistrer

<DocsImage
	src="/images/docs/custom-domain/custom-domain-settings.png"
	alt="Paramètres du domaine personnalisé"
/>

<Callout type="info">
	{#snippet icon()}
		<IconLightbulb />
	{/snippet}
	Vous pouvez ajouter votre propre certificat TLS et votre clé privée pour votre domaine en sélectionnant l'option <strong>Bring Your Own</strong>. Cependant, nous vous recommandons d'utiliser l'option <strong>Automatique</strong> et de laisser Hyvor Blogs gérer votre certificat TLS via <strong>Let's Encrypt</strong>.
</Callout>

<!--
- Set **Hosted at** to **Custom Domain**.
- Then, set your custom domain name.
- Click **Save**.
 -->
<h2 id="dns">Étape 2 : Mettre à jour les enregistrements DNS</h2>

Rendez-vous dans les paramètres DNS de votre bureau d'enregistrement de domaine et créez soit un enregistrement **CNAME** (recommandé - plus simple et plus fiable), soit un enregistrement **A** avec les détails ci-dessous.

<!-- TODO -->

Et voilà ! Votre blog est maintenant disponible sur votre domaine personnalisé.

<h2 id="cloudflare">Utilisation de Cloudflare</h2>

Si vous utilisez Cloudflare pour votre domaine, utilisez l'une des options suivantes.

- (Recommandé) Activez le proxy (nuage orange) et réglez le <a href="https://developers.cloudflare.com/ssl/origin-configuration/ssl-modes/" target="_blank" rel="nofollow">mode de chiffrement</a> sur **Complet** ou **Complet (strict)**. Cela activera le CDN mondial de Cloudflare pour votre blog, pour une meilleure mise en cache et de meilleures performances.
- Désactivez le proxy (nuage gris)

<h2 id="troubleshoot">Dépannage</h2>

Si votre blog avec domaine personnalisé se charge indéfiniment ou renvoie d'autres codes d'erreur, veuillez vérifier les points suivants.

- Assurez-vous de ne pas avoir d'autres enregistrements `A` ou `AAAA` avec le même nom d'hôte que votre domaine personnalisé.
- Si vous avez configuré des enregistrements `CAA` pour votre domaine, assurez-vous d'avoir autorisé ZeroSSL à émettre des certificats pour votre domaine (voir <a href="https://help.zerossl.com/hc/en-us/articles/360060119753-Invalid-CAA-Records" target="_blank">ce guide</a>). **CEPENDANT**, veuillez noter qu'en cas de changement de notre fournisseur de certificats, vous devrez peut-être mettre à jour vos enregistrements CAA en conséquence. Par conséquent, si possible, nous vous recommandons de ne pas utiliser d'enregistrements CAA pour votre domaine.

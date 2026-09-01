<script>
	import { DocsImage } from '@hyvor/design/marketing';
	import { Callout } from '@hyvor/design/components';
	import IconExclamationOctagonFill from '@hyvor/icons/IconExclamationOctagonFill';
</script>

# Redirections

Les redirections sont utilisées pour rediriger un visiteur d'un chemin de votre blog vers une autre URL. Vous pouvez configurer les redirections dans **Paramètres → Redirections** dans la Console.

<DocsImage src="/images/docs/redirect/create-redirect.png" alt="Créer une redirection" />

- **Dynamique** - Fait correspondre des modèles à l'aide d'expressions régulières. Voir [Redirections dynamiques](#dynamic).
- **De** - Le chemin à partir duquel vous souhaitez rediriger. Par exemple, `/path`. Pour les redirections dynamiques, vous pouvez utiliser des expressions régulières.
- **Vers** - Où rediriger le visiteur. Cela peut être un chemin au sein de votre blog ou une URL externe.
- **Type** - Permanente (HTTP 301) ou Temporaire (HTTP 302). Cela détermine le <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Status#redirection_messages" rel="nofollow" target="_blank">code de réponse HTTP</a>.

<h2 id="dynamic">Redirections dynamiques</h2>

Avec les redirections dynamiques, vous pouvez faire correspondre un chemin de manière dynamique à l'aide d'un modèle. C'est utile lorsque vous souhaitez rediriger plusieurs chemins suivant un modèle vers une seule destination. Par exemple, vous pouvez rediriger toutes les requêtes commençant par `/author/` vers un site externe.

<Callout type="info">
	{#snippet icon()}
		<IconExclamationOctagonFill />
	{/snippet}
	Vous ne pouvez avoir que jusqu'à 5 redirections dynamiques par blog.
</Callout>

<DocsImage src="/images/docs/redirect/dynamic-redirect.png" alt="Exemple 1" />

Voici comment créer une redirection dynamique :

- Activez l'option **Dynamique**
- Définissez **De** avec une expression régulière que vous souhaitez faire correspondre (nous prenons en charge la syntaxe PCRE2).
- Définissez **Vers** avec l'URL vers laquelle vous souhaitez rediriger. Vous pouvez utiliser des groupes capturés ici, comme `$1`.
- Choisissez le **Type** de redirection et cliquez sur **Ajouter**.

<h3 id="dynamic-examples">Exemples de redirections dynamiques</h3>

1. Pour rediriger toutes les requêtes commençant par `/author/` vers un site externe :

- **De :** `/author/(.*)`
- **Vers :** `https://externalsite.com`

2. Pour rediriger toutes les requêtes commençant par `/author/` vers un site externe, en conservant le reste du chemin :

- **De :** `/author/(.*)`
- **Vers :** `https://externalsite.com/$1`

3. Pour rediriger toutes les requêtes commençant par `/author/` suivies d'un autre `/` vers un site externe, avec quelques modifications dans la structure d'origine du chemin :

- **De :** `/author/([^/]+)/(.*)`
- **Vers :** `https://externalsite.com/$1/somedirectory/$2`

4. Rediriger les chemins se terminant par `/` vers le même chemin sans la barre oblique finale :

- **De :** `/(.*)/$`
- **Vers :** `https://yourblog.com/$1`

Si vous avez besoin d'aide avec les expressions régulières, n'hésitez pas à contacter le support.

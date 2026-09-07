<script lang="ts">
	import { Table, TableRow, Divider, Text, Tag, Button } from '@hyvor/design/components';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
</script>

# Hébergement en sous-répertoire

Selon la plupart des experts SEO, un sous-répertoire comme `/blog` est le meilleur endroit pour héberger votre blog. Hyvor Blogs prend en charge plusieurs méthodes pour héberger votre blog dans un sous-répertoire.

<h2 id="docker">1. Docker</h2>

Si vous utilisez déjà Docker sur votre site web, vous pouvez utiliser notre image Docker officielle pour héberger votre Hyvor Blog dans un sous-répertoire. Vous pouvez utiliser un reverse proxy comme Caddy, Nginx ou Traefik pour router le trafic du blog vers le conteneur Docker.

<p>
	<Button
		href="https://hyvor.com/blog/docker-subdirectory-blog"
		as="a"
		color="input"
		style="text-decoration:none"
		target="_blank"
	>
		Voir l'article de blog <IconBoxArrowUpRight slot="end" />
	</Button>
	<Text small light style="margin-left:5px">sur hyvor.com/blog</Text>
</p>

<Divider color="var(--border)" margin={40} />

<h2 id="cloudflare-workers">
   2. Cloudflare Workers <Tag color="blue">#nocode</Tag>
</h2>

Si vous utilisez déjà Cloudflare pour votre domaine, l'utilisation de <a href="https://workers.cloudflare.com/" rel="nofollow" target="_blank">Cloudflare Workers</a> est de loin la méthode la plus simple pour héberger votre blog dans un sous-répertoire. Vous pouvez le configurer en quelques minutes sans écrire une seule ligne de code.

<p>
	<Button
		href="https://hyvor.com/blog/cloudflare-workers-blog"
		as="a"
		color="input"
		style="text-decoration:none"
		target="_blank"
	>
		Voir l'article de blog <IconBoxArrowUpRight slot="end" />
	</Button>
	<Text small light style="margin-left:5px">sur hyvor.com/blog</Text>
</p>

<Divider color="var(--border)" margin={40} />

<h2 id="web-frameworks">3. Frameworks web</h2>

Si votre site principal est construit avec un framework web comme NextJS ou Laravel, vous pouvez utiliser le même framework pour héberger votre blog. Vous pouvez configurer votre blog pour qu'il soit servi directement depuis le framework et utiliser un cache (système de fichiers, redis, etc.) pour améliorer les performances.

Nous disposons actuellement de bibliothèques pour les frameworks suivants.

<Table columns="1fr 1fr 1fr">
	<TableRow head>
		<div>Framework</div>
		<div>Tutoriel de blog</div>
		<div>Package (Github)</div>
	</TableRow>
	<TableRow>
		<div>NextJS</div>
		<div>
			<a href="https://hyvor.com/blog/nextjs-blog" target="_blank">
				Voir <IconBoxArrowUpRight />
			</a>
		</div>
		<div>
			<a href="https://github.com/hyvor/hyvor-blogs-serve-web" target="_blank">
				Voir <IconBoxArrowUpRight />
			</a>
		</div>
	</TableRow>
	<TableRow>
		<div>Laravel</div>
		<div>
			<a href="https://hyvor.com/blog/laravel-blog" target="_blank">
				Voir <IconBoxArrowUpRight />
			</a>
		</div>
		<div>
			<a href="https://github.com/hyvor/hyvor-blogs-laravel" target="_blank">
				Voir <IconBoxArrowUpRight />
			</a>
		</div>
	</TableRow>
	<TableRow>
		<div>Symfony</div>
		<div>
			<a href="https://hyvor.com/blog/symfony-blog" target="_blank">
				Voir <IconBoxArrowUpRight />
			</a>
		</div>
		<div>
			<a href="https://github.com/hyvor/hyvor-blogs-symfony" target="_blank">
				Voir <IconBoxArrowUpRight />
			</a>
		</div>
	</TableRow>
</Table>

Si votre framework n'est pas pris en charge, vous pouvez gérer manuellement les requêtes et servir le blog depuis votre framework en utilisant :

- [Delivery API](/docs/api-delivery) - Apprenez comment servir une requête depuis votre framework.
- [Webhooks](/docs/webhooks) - À utiliser pour invalider le cache lorsque le blog est mis à jour.

Nous vous recommandons de consulter le code des packages ci-dessus pour avoir une idée de la façon de servir le blog depuis votre framework. Si vous avez besoin d'aide, veuillez nous contacter.

<Divider color="var(--border)" margin={40} />

<h2 id="reverse-proxy">4. Reverse Proxy</h2>

Si vous utilisez un serveur web comme Nginx ou Caddy, vous pouvez utiliser un reverse proxy pour servir le blog depuis un sous-répertoire.

<Table columns="1fr 1fr">
	<TableRow head>
		<div>Serveur</div>
		<div>Tutoriel de blog</div>
	</TableRow>
	<TableRow>
		<div>Caddy</div>
		<div>
			<a href="https://hyvor.com/blog/caddy-blog" target="_blank">
				Voir <IconBoxArrowUpRight />
			</a>
		</div>
	</TableRow>
	<TableRow>
		<div>Nginx</div>
		<div></div>
	</TableRow>
</Table>

<script>
	import { Tag } from '@hyvor/design/components';
	import FeatureSectionTitle from '../FeatureSectionTitle.svelte';

	import logoCfWorkers from './cf-workers.svg';
	import logoLaravel from './laravel.svg';
	import logoNext from './next.svg';
	import logoDocker from './docker.svg';
	import logoSymfony from './symfony.svg';
	import Type from './Type.svelte';
	import iconHosting from './host-anywhere.svg';
</script>

<FeatureSectionTitle
	title="Host Anywhere"
	subtitle="Subdomain, custom domain, sub-directory... anywhere!"
	icon={iconHosting}
/>

<div class="types hds-container">
	<Type
		name="Default Subdomain"
		domain="subdomain.hyvorblogs.io"
		features={[
			'Best option to <strong>get started</strong>',
			'Available for all blogs by default'
		]}
	/>

	<Type
		name="Custom Domain"
		domain="blog.example.com"
		features={[
			'Best option for a <strong>professional blog</strong>',
			'<a href="https://blogs.hyvor.com/docs/custom-domain" target="_blank">Easy to setup</a> by changing DNS records.',
			'Root (<b>example.com</b>) or subdomain (<strong>blog.example.com</strong>)',
			'Free SSL certificate'
		]}
	/>

	<Type
		name="Sub-directory"
		domain="example.com/blog"
		features={[
			'Best option for <strong>SEO</strong>',
			'Serve your blog from your web app',
			'Reverse proxy support'
		]}
	>
		<div
			style="
                margin-top:15px;
                border-top:1px solid var(--accent-light);
                margin-left:-25px;
                margin-right:-25px;
            "
		></div>

		<div class="sub-dir-note">
			Use one of the following methods to host your blog in a sub-directory:
		</div>

		<div class="methods">
			<a href="https://hyvor.com/blog/cloudflare-workers-blog" target="_blank">
				<img src={logoCfWorkers} alt="Cloudflare Workers Logo" />
				<span>
					Cloudflare <br /> Workers <br />
					<Tag color="blue" size="small">No-code</Tag>
				</span>
			</a>

			<a href="https://hyvor.com/blog/docker-subdirectory-blog" target="_blank">
				<img src={logoDocker} alt="Docker Logo" />
				<span> Docker </span>
			</a>

			<a href="https://hyvor.com/blog/nextjs-blog" target="_blank">
				<img src={logoNext} alt="Next.js Logo" />
				<span> Next.js </span>
			</a>

			<a href="https://hyvor.com/blog/laravel-blog" target="_blank">
				<img src={logoLaravel} alt="Laravel Logo" />
				<span> Laravel </span>
			</a>

			<a href="https://hyvor.com/blog/symfony-blog" target="_blank">
				<img src={logoSymfony} alt="Symfony Logo" />
				<span> Symfony </span>
			</a>
		</div>
	</Type>
</div>

<style lang="scss">
	.types {
		display: flex;
		gap: 20px;
		flex-direction: column;
		width: 700px;
		max-width: 100%;
		margin: auto;
		margin-top: 40px;
	}
	.methods {
		flex: 1;
		display: flex;
		justify-content: center;
		gap: 25px;
		padding: 15px;
		a {
			display: inline-flex;
			flex-direction: column;
			align-items: center;
		}
		img {
			width: 30px;
			height: 30px;
			margin-bottom: 10px;
		}
		span {
			text-align: center;
			font-size: 14px;
		}
	}
	.sub-dir-note {
		margin-top: 15px;
		margin-bottom: 15px;
		color: var(--text-light);
		font-size: 14px;
	}
</style>

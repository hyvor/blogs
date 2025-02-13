<script lang="ts">
	import { Tag } from '@hyvor/design/components';

	type CustomerType = 'personal' | 'business' | 'news';

	interface Customer {
		image: string;
		name: string;
		type: CustomerType;
		url: string;
		country?: string;
		color?: string;
	}

	const customers: Customer[] = [
		// {
		//     image: 'hyvorblog_masfzs.webp',
		//     type: 'business',
		//     name: 'HYVOR Blog',
		//     url: 'hyvor.com/blog',
		//     country: '🇫🇷',
		// },
		{
			name: 'Times of Madeira',
			type: 'news',
			url: 'www.timesofmadeira.com',
			country: '🇵🇹',
			image: 'timesof_madeira_exngfb.webp',
			color: '#ffd5d4'
		},
		{
			name: 'Kyzon',
			type: 'business',
			image: 'kyzon.webp',
			url: 'kyzonsolutions.com/insights',
			country: '🇦🇺',
			color: '#fee8ff'
		},
		{
			name: 'Wild Workflows',
			type: 'business',
			image: 'wildworkflows-blog.webp',
			url: 'blog.wildworkflows.com',
			country: '🇬🇧',
			color: '#ebeae6'
		},
		{
			name: 'The Personal Philosopher',
			type: 'personal',
			image: 'pernalphilosopher.webp',
			url: 'thepersonalphilosopher.com'
		},
		{
			name: 'Subdivisions',
			type: 'business',
			image: 'subdivisions_ckyoai.webp',
			url: 'blog.subdivisions.com',
			country: '🇺🇸',
			color: '#e8f0ff'
		},
		{
			name: 'AlongX',
			type: 'business',
			image: 'alongx.webp',
			url: 'blog.alongx.com',
			country: '🇮🇳'
		},
		{
			name: 'PureType.ai',
			type: 'business',
			image: 'puretype.webp',
			url: 'blog.puretype.ai',
			country: '🇨🇦',
			color: '#cad9d4'
		},
		{
			name: 'Riccardo Padovani',
			type: 'personal',
			image: 'rpadovani.webp',
			url: 'rpadovani.com',
			country: '🇩🇪',
			color: '#fff0ea'
		},
		{
			name: 'JobPosts.ai',
			type: 'business',
			image: 'job-posts_g0pjhw.png',
			url: 'jobposts.ai/blog',
			color: '#f9f3e8'
		},
		{
			name: "Marko's Way",
			type: 'business',
			image: 'markos-way.webp',
			url: 'markosway.com/blog',
			color: '#fff9e6'
		},
		{
			name: 'Unhabit',
			type: 'business',
			image: 'unhabit.webp',
			url: 'blog.unhabit.me',
			color: '#ebebcf'
		},
		{
			name: 'Rbnsn',
			type: 'personal',
			image: 'rbnsn.webp',
			url: 'www.rbnsn.uk/blog',
			country: '🇬🇧',
			color: '#ffece4'
		},
		{
			name: 'Deflekt',
			type: 'business',
			image: 'deflekt_ik0u1p.webp',
			url: 'deflekt.ai/blog',
			country: '🇳🇱',
			color: '#f2ebff'
		},
		{
			name: 'David Hooper',
			type: 'personal',
			image: 'davidhooper_zb90qt.webp',
			url: 'davidhooper.net',
			country: '🇺🇸',
			color: '#efefef'
		},
		{
			name: 'Conservatruth Blog',
			type: 'news',
			image: 'consevatruth-blog_kglswp.webp',
			url: 'conservatruthblog.com',
			country: '🇺🇸',
			color: '#ffe9e5'
		},
		{
			name: 'Praca ze smakiem',
			type: 'business',
			image: 'pracazesmakiem_ctcqtv.webp',
			url: 'pracazesmakiem.pl',
			country: '🇵🇱',
			color: '#d6e7f9'
		},
		{
			name: 'Enthuziastic',
			type: 'personal',
			image: 'enthu.webp',
			url: 'enthu.com/blog',
			country: '🇮🇳'
		},
		{
			name: 'Tiger22',
			type: 'business',
			image: 'tiger22.webp',
			url: 'tiger22.com',
			color: '#f7e3e7'
		},
		{
			name: 'Wireframe',
			type: 'personal',
			image: 'wireframe.webp',
			url: 'wireframe.blog',
			color: '#f4e8f9'
		}
	];

	interface Props {
		max?: number | null;
	}

	let { max = null }: Props = $props();
	if (max) {
		customers.splice(max);
	}

	function getTagColor(type: CustomerType) {
		switch (type) {
			case 'business':
				return 'blue';
			case 'personal':
				return 'green';
			case 'news':
				return 'orange';
		}
	}
</script>

<div class="wrap">
	{#each customers as customer}
		<a
			class="customer"
			style:background-color={customer.color || '#eee'}
			href="//{customer.url}"
			target="_blank"
			rel="nofollow noopener noreferrer"
		>
			<img
				src="https://res.cloudinary.com/dqabfne6s/image/upload/c_scale,w_800/v1687716078/blogs.hyvor.com/customer-screenshots/{customer.image}"
				alt={customer.url}
			/>

			<div class="data">
				<div class="name-wrap">
					<div class="name">{customer.name} {customer.country || ''}</div>
					<div class="url">
						{customer.url}
					</div>
				</div>

				<div class="type">
					<Tag color={getTagColor(customer.type)}>{customer.type.toUpperCase()}</Tag>
				</div>
			</div>
		</a>
	{/each}
</div>

<style lang="scss">
	.wrap {
		width: 100%;
		padding: 0 20px;
		display: flex;
		flex-wrap: wrap;
		justify-content: flex-start;
		gap: 16px;
	}

	.customer {
		width: calc(50% - 8px);
		position: relative;
		padding: 25px;
		border-radius: 20px;
		transition: 0.1s;
		&:hover {
			transform: translateY(-10px);
		}

		img {
			max-width: 100%;
			border-radius: 20px;
			aspect-ratio: 16 / 9;
			transition: 0.2s ease-in-out;
		}

		.data {
			margin-top: 20px;
			display: flex;
			align-items: center;
		}

		.name-wrap {
			flex: 1;
			display: flex;
			flex-direction: column;
		}

		.name {
			font-weight: 600;
			font-size: 18px;
		}

		.url {
			margin-top: 1px;
			color: var(--text-light);
		}

		@media screen and (max-width: 920px) {
			width: 100%;
		}
	}
</style>

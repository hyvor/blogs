<script lang="ts">
	import { Testimonials } from '@hyvor/design/marketing';
	import { getMarketingI18n } from '../[[lang]]/marketingLang';
	import type { ComponentProps } from 'svelte';

	const I18n = getMarketingI18n();

	const t = (key: string) => I18n.t(`homepage.testimonials.reviews.${key}` as never);

	const reviews: ComponentProps<typeof Testimonials>['reviews'] = [
		{
			type: 'text',
			name: 'Riccardo Padovani',
			role: t('riccardo.role'),
			company: 'Google',
			summary: t('riccardo.summary'),
			quote: t('riccardo.quote'),
			imageUrl: '/images/homepage/testmonials/riccardo-p.webp'
		},
		{
			type: 'video',
			name: 'Christian Santos',
			role: t('christian.role'),
			company: 'Organizely.io',
			companyUrl: 'https://organizely.io/blog',
			videoUrl:
				'https://res.cloudinary.com/dqabfne6s/video/upload/v1788363647/organizely-review.mov',
			posterUrl:
				'https://res.cloudinary.com/dqabfne6s/image/upload/v1788363809/organizely-thumbnail_etbmqd.png',
			summary: t('christian.summary')
		},
		{
			type: 'video',
			name: 'Ashutosh Jain',
			role: t('ashutosh.role'),
			company: 'Enthuziastic',
			companyUrl: 'https://enthu.com',
			videoUrl:
				'https://res.cloudinary.com/dqabfne6s/video/upload/v1788006279/blogs.hyvor.com/reviews-images/enthusiastic-review_nq9vcj.mp4',
			posterUrl:
				'https://res.cloudinary.com/dqabfne6s/image/upload/v1788006855/blogs.hyvor.com/reviews-images/enthu-poster_bsx0za.png',
			summary: t('ashutosh.summary')
		},
		{
			type: 'text',
			name: 'Leo Selie',
			role: t('leo.role'),
			company: 'Flow Digital',
			imageUrl: '/images/homepage/testmonials/leo-s.webp',
			summary: t('leo.summary'),
			quote: t('leo.quote')
		},
		{
			type: 'video',
			name: 'James Yang',
			role: t('james.role'),
			company: 'Kyzon Solutions',
			videoUrl:
				'https://res.cloudinary.com/dqabfne6s/video/upload/v1788442866/kyzon-testimonial_sevpfe.mp4',
			posterUrl:
				'https://res.cloudinary.com/dqabfne6s/image/upload/v1788442170/kyzon-review-post_nl91hf.png',
			summary: t('james.summary')
		},
		{
			type: 'text',
			name: 'Robert Levesque',
			role: t('robert.role'),
			company: 'PROinspection Québec',
			summary: t('robert.summary'),
			quote: t('robert.quote')
		},
		{
			type: 'text',
			name: 'Manoj P.',
			role: t('manoj.role'),
			summary: t('manoj.summary'),
			quote: t('manoj.quote')
		}
	];
</script>

<Testimonials
	label={I18n.t('homepage.testimonials.label')}
	title={I18n.t('homepage.testimonials.title')}
	{reviews}
/>

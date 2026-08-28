<script lang="ts">
	import { Testimonials } from '@hyvor/design/marketing';
	import { getMarketingI18n } from '../[[lang]]/marketingLang';

	const I18n = getMarketingI18n();

	interface TextReview {
		type: 'text';
		name: string;
		role: string;
		quote: string;
	}

	interface VideoReview {
		type: 'video';
		name: string;
		role: string;
		// populate with a real clip once we have one recorded; the poster
		// photo above is a generic stand-in, not a real customer
		videoUrl?: string;
		posterUrl?: string;
	}

	type Review = TextReview | VideoReview;

	const reviews: Review[] = [
		{
			type: 'text',
			name: 'Lionel S.',
			role: 'Blogger',
			quote:
				'I need a simple, easy-to-use, fast, beautiful and mature blogging tool that resolves the WordPress bloat. Hyvor Blogs handles this beautifully.'
		},
		{
			type: 'video',
			name: 'Video testimonial',
			role: 'Coming soon'
		},
		{
			type: 'text',
			name: 'Manoj P.',
			role: 'Senior Application Engineer',
			quote:
				'The platform offers a seamless and user-friendly experience for both bloggers and readers. The customization options are extensive, allowing bloggers to create a unique and visually appealing blog.'
		}
	];
</script>

<Testimonials
	label={I18n.t('homepage.testimonials.label')}
	title={I18n.t('homepage.testimonials.title')}
	{reviews}
/>

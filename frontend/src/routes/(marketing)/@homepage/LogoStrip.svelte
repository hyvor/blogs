<script lang="ts">
	import { LogoStrip } from '@hyvor/design/marketing';

	const base = '/images/homepage/logo-stripe';

	const logos = [
		{ name: 'Enthuziastic', src: `${base}/enthuziastic.svg`, width: 132, height: 22 },
		{ name: 'OpenVM', src: `${base}/openvm.svg`, width: 96, height: 30 },
		{ name: 'Subdivisions', src: `${base}/subdivisions.svg`, width: 135, height: 35 },
		{ name: 'Univerbal', src: `${base}/univerbal.svg`, width: 111, height: 28 },
		{ name: 'Organizely', src: `${base}/organizely.svg`, width: 25, height: 30 },
		{
			name: 'Kyzon',
			src: `${base}/kyzon.svg`,
			width: 120,
			height: 30,
			color: true
		}
	];
</script>

<LogoStrip
	{logos}
	label="Trusted by 100+ bloggers & businesses"
	background="#574443"
	marquee={true}
/>

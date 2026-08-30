<script lang="ts">
	import { LogoStrip } from '@hyvor/design/marketing';
	import logoAqeez from '$lib/img/customers/aqeez.svg';
	import logoSubdivisions from '$lib/img/customers/subdivisions.svg';
	import logoTimesOfMadeira from '$lib/img/customers/timesofmadeira.svg';
	import logoJobPosts from '$lib/img/customers/jobposts.webp';
	import yogakiddy from '$lib/img/customers/yogakiddy.png';
	import logoUniverbal from '$lib/img/customers/univerbal.svg';

	const logos = [
		{ name: 'Aqeez', src: logoAqeez },
		{ name: 'Times of Madeira', src: logoTimesOfMadeira, height: 52 },
		{ name: 'Subdivisions', src: logoSubdivisions },
		{ name: 'Job Posts', src: logoJobPosts, width: 100, height: 26 },
		{ name: 'Univerbal', src: logoUniverbal, width: 110, height: 42 }
	];
</script>

<LogoStrip {logos} label="Trusted by 100+ bloggers & businesses" background="#574443" />

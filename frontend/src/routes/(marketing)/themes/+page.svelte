<script>
	import { Button } from '@hyvor/design/components';
	import ThemesPreview from '../@components/ThemesPreview.svelte';
</script>

<!-- <div class="hds-container theme-service">
   <p>Need a theme that matches your brand? We can develop a theme for you at your request for your requirements.</p>
    <Button href="/theme-service" variant="outline" size="large">
        Request a Theme Now
    </Button>
</div> -->

<div class="wrap">
	<ThemesPreview />
</div>

<style lang="scss">
	.wrap {
		height: calc(100vh - var(--header-height));
		padding: 15px;
	}

	// .theme-service {
	//     margin-top: 50px;
	//     text-align: center;

	//     p {
	//         font-size: 20px;
	//         margin-bottom: 20px;
	//     }

	//     button {
	//         margin-top: 20px;
	//     }
	// }
</style>

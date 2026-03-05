<script>
	import Footer from './(marketing)/Footer.svelte';
	import Header from './(marketing)/Header.svelte';

	import { Button } from '@hyvor/design/components';
</script>

<Header />
<main>
	<h1 class="heading">404 - Page Not Found</h1>
	<p class="description">
		Sorry, the page you are looking for might have been removed, had its name changed, or is
		temporarily unavailable.
	</p>
	<Button as="a" size="small" href="/">Go to Home</Button>
</main>

<Footer />

<style>
	main {
		padding: 20px;
		text-align: center;
		height: calc(100vh - 100px);
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
	}
	.heading {
		font-size: 38px;
		margin: 0;
	}
	.description {
		/*reduce the width of the text to fit the screen*/
		max-width: 100%;
		width: 450px;
	}
</style>

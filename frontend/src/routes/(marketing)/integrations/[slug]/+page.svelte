<script lang="ts">
	import { DocsContent } from '@hyvor/design/marketing';

   export let data;
    // import { integrations } from '../integrations';
    import GuideHead from '../GuideHead.svelte';
</script>


<svelte:head>
	<title>{data.name}</title>
	<meta name="description" content={data.title} />  
    <!-- add a description instead subtitle to above line -->
	<link rel="canonical" href={`https://blogs.hyvor.com/integrations/${data.slug}`} />
</svelte:head>

<GuideHead
    title={data.title}
    integrationIcon={data.icon}
    description={data.description}
/>


<div class="content-wrap">
    <DocsContent>
        <svelte:component this={data.component} />
    </DocsContent>
</div>

<style lang="scss">
    .content-wrap {
        width: 850px;
        max-width: 100%;
        margin: auto;
        margin-top: 40px;
    }
</style>
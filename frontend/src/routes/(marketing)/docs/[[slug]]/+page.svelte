<script lang="ts">
    import { 
        Docs, 
        DocsNav as Nav, 
        DocsNavCategory as NavCategory, 
        DocsNavItem as NavItem, 
        DocsContent as Content
    } from '@hyvor/design/marketing';
	import { categories } from "./docs";
    export let data;
</script>

<svelte:head>
    <title>
        {data.name} - Hyvor Blogs Docs
    </title>
    <link rel="canonical" href="https://blogs.hyvor.com/docs{data.slug ? '/'  + data.slug : ''}">
</svelte:head>

<div class="docs-wrap">
    <Docs>
        <Nav slot="nav">
            {#each categories as category}
                <NavCategory name={category.name}>
                    {#each category.pages as page}
                        <NavItem 
                            href={page.slug === '' ? '/docs' : `/docs/${page.slug}`}
                        >{page.name}</NavItem>
                    {/each}
                </NavCategory>
            {/each}
        </Nav>
        <Content slot="content">
            <svelte:component this={data.component} />
        </Content>
    </Docs>
</div>

<style>
    .docs-wrap :global(.nav-items a.active) {
        background-color:var(--accent-light-mid)!important;
    }
</style>
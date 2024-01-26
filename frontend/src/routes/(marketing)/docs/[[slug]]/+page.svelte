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
</svelte:head>

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
<script>
	import BlogNav from "./BlogNav.svelte";
</script>


<div id="nav-wrap">
    <div id="nav">
        <BlogNav />
    </div>

    <div id="nav-bottom">
        This is the bottom
    </div>
</div>

<style>

    #nav-wrap {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    #nav {
        border-radius: var(--box-radius);
        background: var(--box-background);
        box-shadow: var(--box-shadow);
    }

    #nav-bottom {
        flex: 1;
        display: flex;
        align-items: flex-end;
    }

</style>
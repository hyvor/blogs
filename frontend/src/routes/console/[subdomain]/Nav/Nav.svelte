<script>
	import BottomButtons from './BottomButtons/BottomButtons.svelte';
	import BlogNav from "./BlogNav.svelte";
</script>


<div id="nav-wrap">
    <div id="nav">
        <BlogNav />
    </div>

    <BottomButtons />
</div>

<style lang="scss">

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

</style>
<script>
	import Editor from "./Editor/Editor.svelte";
    import ThemeNav from "./ThemeNav.svelte";
</script>


<div class="theme">
    <div class="left hds-box">
        <ThemeNav />
    </div>
    <div class="right hds-box">
        <Editor />
    </div>
</div>

<style>

    .theme {
        display: flex;
        height: 100%;
    }

    .left {
        width: 315px;
        margin-right: 15px;
        display: flex;
        flex-direction: column;
        flex-shrink: 0;
        height: 100%;
    }

    .right {
        flex: 1;
        min-width: 0;
        position: relative;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

</style>
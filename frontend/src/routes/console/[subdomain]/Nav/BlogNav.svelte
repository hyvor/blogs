<script>
	import { IconThreeDots } from "@hyvor/icons";
    import { blogStore } from "../../lib/stores";
</script>

<a class="current-blog" href="/console/select">

    <div class="name-url">
        <div class="name">
            { $blogStore.variants[0]?.name || 'Unnamed' }
        </div>
        <div class="url">
            { $blogStore.url.replace(/https?:\/\//, '') }
        </div>
    </div>


    <div class="current-icon">
        <IconThreeDots />
    </div>

</a>

<div class="nav-items">

    

</div>


<style>

    .current-blog {
        display: flex;
        align-items: center;
        padding: 10px 20px;
        cursor: pointer;
        border-radius: var(--box-radius);
        margin: 10px;
    }

    .current-blog:hover {
        background-color: var(--hover);
    }

    .name-url {
        min-width: 0;
        overflow: hidden;
        flex: 1;
    }

    .url {
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 0.8rem;
        color: var(--text-light);
        white-space: nowrap;
    }

    .current-icon {
        margin-left: 4px;
    }

</style>
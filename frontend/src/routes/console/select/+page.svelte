<script lang="ts">
	import { Button } from "@hyvor/design/components";
    import { authUserStore, blogListStore } from "../lib/stores";
    import { IconCaretRight, IconGripVertical } from '@hyvor/icons';
	import { flip } from "svelte/animate";
	import { dndzone, SOURCES, TRIGGERS	 } from 'svelte-dnd-action';
	import type { BlogList } from "../lib/types";
	import { saveSort } from "../lib/actions/blogActions";
    import arrowSvg from "./arrow.svg";

	const flipDurationMs = 200;
	let dragDisabled = true;

    let items = $blogListStore;
    
    function saveOrder(newItems: BlogList[]) {
        blogListStore.set(newItems);
        saveSort(newItems.map(blog => blog.id));
    }

    function handleConsider(e: any) {
		const {items: newItems, info: {source, trigger}} = e.detail;
		items = newItems;
		// Ensure dragging is stopped on drag finish via keyboard
		if (source === SOURCES.KEYBOARD && trigger === TRIGGERS.DRAG_STOPPED) {
			dragDisabled = true;
		}
	}
    function handleFinalize(e: any) {
		const {items: newItems, info: {source}} = e.detail;
		items = newItems;
        saveOrder(newItems);
		// Ensure dragging is stopped on drag finish via pointer (mouse, touch)
		if (source === SOURCES.POINTER) {
			dragDisabled = true;
		}
	}
    function startDrag(e: any) {
		// preventing default to prevent lag on touch devices (because of the browser checking for screen scrolling)
		e.preventDefault();
		dragDisabled = false;
	}
	function handleKeyDown(e: any) {
		if ((e.key === "Enter" || e.key === " ") && dragDisabled) dragDisabled = false;
	}

</script>

<div class="wrap">

    
    <div class="selector">

        <div class="user-account">

            <div class="left">

                <img 
                    src={$authUserStore.picture_url} 
                    alt="{ $authUserStore.name }'s profile picture"
                />

                <div class="name-username">
                    <div class="name">
                        { $authUserStore.name }
                    </div>
                    {#if $authUserStore.username}
                        <div class="username">
                            @{ $authUserStore.username }
                        </div>
                    {/if}
                </div>

            </div>

            <div>
                <Button as="a" href="/api/auth/logout" color="soft">
                    Logout
                </Button>
            </div>

        </div>

        <div class="selector-box">

            <div class="title">
                Select a blog
            </div>

            <div 
                class="blogs-list"
                use:dndzone="{{ 
                    items, 
                    dragDisabled, 
                    flipDurationMs,
                    dropTargetStyle: {
                        outline: 'none',
                        background: 'var(--hover)',
                    }
                }}"
                on:finalize={handleFinalize}
                on:consider={handleConsider}
            >

                {#each items as blog (blog.id)}
                
                    <a 
                        class="blog-row" 
                        href="/console/{blog.subdomain}"
                        animate:flip="{{ duration: flipDurationMs }}"
                    >

                        <button 
                            class="dragger"
                            style={dragDisabled ? 'cursor: grab' : 'cursor: grabbing'}
                            tabindex={dragDisabled? 0 : -1} 
                            aria-label="drag-handle"
                            on:mousedown={startDrag}
                            on:touchstart={startDrag}
                            on:keydown={handleKeyDown}
                            on:click={e => e.preventDefault()}
                        >
                            <IconGripVertical />

                            <img src={arrowSvg} class="arrow" />
                        </button>

                        <div class="left">
                            <div class="name">
                                {blog.name}
                            </div>
                            <div class="url">
                                <a href={blog.url} target="_blank">
                                    {blog.url.replace(/https?:\/\//, '')}
                                </a>
                            </div>
                        </div>


                        <div class="right">

                            <div class="metadata">

                            </div>

                            <div class="icon">
                                <IconCaretRight />
                            </div>
                        </div>

                    </a>

                {/each}
            </div>

            <div class="footer">
                <Button as="a" href="/console/new">
                    Create a new blog
                </Button>
            </div>

        </div>

    </div>

</div>


<style>
    .wrap {
        display: flex;
        justify-content: center;
        align-items: flex-start;
        height: 100vh;
        width: 100%;
    }

    .user-account {
        padding: 30px 25px;
        display: flex;
    }
    .user-account .left {
        flex: 1;
        display: flex;
        align-items: center;
    }

    .user-account img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
    }
    .name-username {
        display: flex;
        justify-content: center;
        margin-left: 10px;
        flex-direction: column;
    }
    .username {
        font-size: 0.8rem;
        color: var(--text-light);
    }

    .selector {
        width: 600px;
        max-width: 100%;
    }
    .selector-box {
        background: var(--box-background);
        box-shadow: var(--box-shadow);
        border-radius: var(--box-radius);
    }
    .title {
        padding: 25px;
        font-size: 1.2rem;
        font-weight: 600;
        text-align: center;
    }
    .footer {
        padding: 25px;
        display: flex;
        justify-content: center;
    }
    .dragger {
        padding: 0 5px;
        margin-right: 10px;
    }
    .blogs-list {

    }
    .blog-row {
        padding: 10px 20px;
        display: flex;
        align-items: center;
        cursor: pointer;
    }
    .blog-row:hover {
        background: var(--hover);
    }
    .blog-row .left {
        width: 50%;
    }
    .right {
        width: 50%;
        display: flex;
        align-items: center;
    }
    .metadata {
        flex: 1;
    }
    .name {
        font-weight: 600;
    }
    .url {
        font-size: 0.9rem;
        color: var(--text-light);
    }
    .url a:hover {
        text-decoration: underline;
    }
</style>


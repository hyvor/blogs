<script lang="ts">
    import type { Snippet, Component } from 'svelte';
    import IconCaretDownFill from '@hyvor/icons/IconCaretDownFill';
    interface Props {
        title?: string;
        content?: string;
        isOpen?: boolean;
        onToggle?: (id: string) => void;
        id: string;
        children?: Snippet;
        color?: string;
        icon?: Component;
        link?: string;
        width?: string;
    }

    let { title, content, isOpen = false, onToggle, id, icon, link }: Props = $props();
    const Icon = icon;

    function handleClick() {
        if (onToggle) {
            onToggle(id);
        }
    }
</script>

<div class="accordion-item">
    <button class="accordion-header" class:open={isOpen} onclick={handleClick}>
        {#if icon}
			<span class="icon">
				<Icon size={20} />
			</span>
        {/if}
        <span class="title">{title}</span>
        <span class="chevron" class:rotated={isOpen}>
            <IconCaretDownFill />
        </span>
    </button>

    {#if isOpen}
        <div class="accordion-content">
            <div class="content-text">
                {content}
                <a class="hds-link" href={link}>
                    Read more
                </a>
            </div>
        </div>
    {/if}
</div>

<style>
    .accordion-item {
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        overflow: hidden;
        background: white;
    }

    .accordion-header {
        width: 100%;
        padding: 16px 20px;
        background: none;
        border: none;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        transition: background-color 0.2s ease;
        font-size: 16px;
    }

    .accordion-header:hover {
        background-color: #f9fafb;
    }

    .accordion-header.open {
        background-color: #f3f4f6;
        border-bottom: 1px solid #e5e7eb;
    }

    .title {
        font-weight: 500;
        text-align: left;
    }

    .chevron {
        display: flex;
        align-items: center;
        transition: transform 0.3s ease;
        color: #6b7280;
    }

    .chevron.rotated {
        transform: rotate(180deg);
    }

    .accordion-content {
        animation: slideDown 0.3s ease-out;
    }

    .content-text {
        padding: 20px;
        color: #4b5563;
        line-height: 1.6;
        border-top: 1px solid #f3f4f6;
        background-color: #fafafa;
    }

    .icon {
        display: inline-block;
        margin-right: 10px;
        vertical-align: middle;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Focus styles for accessibility */
    /*.accordion-header:focus {*/
    /*    outline: 2px solid #3b82f6;*/
    /*    outline-offset: 2px;*/
    /*}*/
</style>

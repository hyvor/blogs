<script lang="ts">
	import { Tag } from "@hyvor/design/components";
    
    interface Props {
        color: 'green' | 'blue' | 'orange' | 'red';
        children?: import('svelte').Snippet;
    }

    let { color, children }: Props = $props();
</script>

<div class="feature-section">
    <Tag size="large" {color}>
        {@render children?.()}
    </Tag>
</div>

<style>
    .feature-section {
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 30px;
        margin-top:60px;
    }

    @media (max-width: 992px) {
        .feature-section {
            text-align: center;
        }
    }
</style>
<script lang="ts">
	import { Tag } from "@hyvor/design/components";
    
    export let color: 'green' | 'blue' | 'orange' | 'red';
</script>

<div class="feature-section">
    <Tag size="large" {color}>
        <slot />
    </Tag>
</div>

<style>
    .feature-section {
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 30px;
        margin-top:60px;
    }
</style>
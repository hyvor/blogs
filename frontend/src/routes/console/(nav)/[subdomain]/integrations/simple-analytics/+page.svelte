<script lang="ts">
    import { onMount } from 'svelte';
    import { SplitControl, Switch } from '@hyvor/design/components';
    import BlogSettingsSave from "../../settings/BlogSettingsSave.svelte";
    import { blogStore, updateBlogStore } from "../../../../lib/stores/blogStore";

    function handleSwitchChange(e: any) {
        updateBlogStore({ integration_simple_analytics_enabled: e.target.checked });
    }


</script>

<BlogSettingsSave keys={['integration_simple_analytics_enabled']} />
<div class="integrations">
    <SplitControl label="Simple Analytics" caption="Enable Simple Analytics integration">
        <Switch
            checked={$blogStore.integration_simple_analytics_enabled}
            on:change={handleSwitchChange}
            disabled={$blogStore.type === 'temp'}
        />
    </SplitControl>
</div>

<style>
    .integrations{
        padding: 25px 45px;
    }
</style>

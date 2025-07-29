<script lang="ts">
    import { SplitControl, Switch, Link } from '@hyvor/design/components';
    import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
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

        <div class="note">
            For more information, visit the <Link href="https://docs.simpleanalytics.com/" target="_blank" underline={false} rel="noopener noreferrer">Simple Analytics docs
            {#snippet end()}
            <IconBoxArrowUpRight size={14} />
            {/snippet}
        </Link>.
        </div>
    </SplitControl>
</div>

<style>
    .integrations{
        padding: 25px 45px;
    }

    .note {
        margin-top: 20px;
    }
</style>

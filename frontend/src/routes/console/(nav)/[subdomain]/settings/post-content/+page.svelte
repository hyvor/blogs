<script lang="ts">
	import { ActionList, ActionListItem, Button, Dropdown, InputGroup, Radio, SplitControl, Switch } from "@hyvor/design/components";
	import { blogStore, updateBlogStore } from "../../../../lib/stores/blogStore";
	import BlogSettingsSave from "../BlogSettingsSave.svelte";
	import { IconCaretDown } from "@hyvor/icons";
	import { getConfig } from "../../../../lib/config";

    function handleSyntaxOnChange(e: any) {
        updateBlogStore({ syntax_on: e.target.checked });
    }

    function handleSyntaxLineNumbersOnChange(e: any) {
        updateBlogStore({ syntax_line_numbers: e.target.checked });
    }

    function handleHeadingAnchorsOnChange(e: any) {
        updateBlogStore({ heading_anchors: e.target.checked });
    }

    function handleHighlightThemeSelect(theme: string) {
        updateBlogStore({ syntax_theme: theme });
        showSyntaxThemes = false;
    }

    let showSyntaxThemes = $state(false);

</script>

<BlogSettingsSave 
    keys={['syntax_on', 'syntax_line_numbers', 'syntax_theme', 'heading_anchors']}
/>

<div class="settings">
        
    <SplitControl
        label="Syntax Highlighting"
        caption="Highlight code blocks in your posts."
    >

        {#snippet nested()}
                <div >

                <SplitControl
                    label="Enabled"
                >
                    <Switch
                        checked={$blogStore.syntax_on}
                        on:change={handleSyntaxOnChange}
                    />
                </SplitControl>

                <SplitControl
                    label="Theme"
                >
                    <Dropdown width={275} bind:show={showSyntaxThemes}>

                        {#snippet trigger()}
                                        <Button  color="input">
                                { $blogStore.syntax_theme || 'nord' }
                                {#snippet end()}
                                                <IconCaretDown  />
                                            {/snippet}
                            </Button>
                                    {/snippet}

                        {#snippet content()}
                                        <ActionList 
                                 
                                style="max-height:300px;overflow:auto"
                            >
                                {#each getConfig().highlight_themes as theme}
                                    <ActionListItem
                                        on:select={() => handleHighlightThemeSelect(theme)}
                                        style="background-color: {($blogStore.syntax_theme || 'nord') === theme ? 'var(--accent-light-mid)' : 'transparent'}"
                                    >
                                        {theme}
                                    </ActionListItem>
                                {/each}
                            </ActionList>
                                    {/snippet}

                    </Dropdown>
                </SplitControl>

                <SplitControl
                    label="Line Numbers"
                >
                    <Switch
                        checked={$blogStore.syntax_line_numbers}
                        on:change={handleSyntaxLineNumbersOnChange}
                    />
                </SplitControl>

            </div>
            {/snippet}


    </SplitControl>

    <SplitControl
        label="Heading Anchors"
        caption="Add anchors to headings with IDs"
    >

        <Switch
            checked={$blogStore.heading_anchors}
            on:change={handleHeadingAnchorsOnChange}
        />

    </SplitControl>

</div>

<style>
    .settings {
        flex: 1;
        overflow: auto;
        padding: 25px 30px;
    }
</style>
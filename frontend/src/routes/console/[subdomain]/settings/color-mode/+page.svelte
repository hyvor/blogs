<script lang="ts">
	import { FormControl, InputGroup, Radio, SplitControl, Switch } from "@hyvor/design/components";
	import { blogStore, updateBlogStore } from "../../../lib/stores/blogStore";
	import CodemirrorEditor from "../../../lib/components/CodemirrorEditor/CodemirrorEditor.svelte";
	import BlogSettingsSave from "../BlogSettingsSave.svelte";

    function handleColorModeChange(value: 'light' | 'dark' | 'both') {
        updateBlogStore({ color_modes: value });
    }


    function handleColorModeDefaultChange(value: 'light' | 'dark' | 'os') {
        updateBlogStore({ color_mode_default: value });
    }

</script>

<BlogSettingsSave 
    keys={['color_modes', 'color_mode_default']}
/>

<div class="settings">
        
    <SplitControl
        label="Color Mode(s)"
        caption="Which color mode(s) should be available to your readers?"
    >


        <InputGroup>

            <Radio 
                value="light" 
                group={$blogStore.color_modes}
                on:change={e => handleColorModeChange('light')}
            >
                Light
            </Radio>

            <Radio
                value="dark" 
                group={$blogStore.color_modes}
                on:change={e => handleColorModeChange('dark')}
            >
                Dark
            </Radio>

            <Radio
                value="both" 
                group={$blogStore.color_modes}
                on:change={e => handleColorModeChange('both')}
            >
                Both
            </Radio>

        </InputGroup>

    </SplitControl>

    <SplitControl
        label="Default Color Mode"
        caption="Which color mode should be the default?"
    >

        <InputGroup>

            <Radio 
                value="os"
                group={$blogStore.color_mode_default}
                on:change={e => handleColorModeDefaultChange('os')}
            >
                User's Operating System (OS) Preference
            </Radio>

            <Radio 
                value="light" 
                group={$blogStore.color_mode_default}
                on:change={e => handleColorModeDefaultChange('light')}
            >
                Light
            </Radio>

            <Radio 
                value="dark" 
                group={$blogStore.color_mode_default}
                on:change={e => handleColorModeDefaultChange('dark')}
            >
                Dark
            </Radio>


        </InputGroup>

    </SplitControl>

</div>

<style>
    .settings {
        flex: 1;
        overflow: auto;
        padding: 25px 30px;
    }
</style>
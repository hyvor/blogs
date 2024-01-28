<script lang="ts">
	import { InputGroup, Radio, SplitControl, Switch } from "@hyvor/design/components";
	import { blogStore, updateBlogStore } from "../../../lib/stores/blogStore";
	import BlogSettingsSave from "../../settings/BlogSettingsSave.svelte";

    function handleLinkAnalysisEnabledChange(e: any) {
        updateBlogStore({
            link_analysis_enabled: e.target.checked
        });
    }

    function handleEmailReportChange(e: any) {
        updateBlogStore({
            link_analysis_email_report: e.target.value
        });
    }

</script>

<BlogSettingsSave
    keys={['link_analysis_enabled', 'link_analysis_email_report']}
/>

<div class="controls">

    <SplitControl
        label="Automated Link Analysis"
        caption="Run a full-blog link analysis every 2 weeks automatically."
    >

        <Switch 
            checked={$blogStore.link_analysis_enabled}
            on:change={handleLinkAnalysisEnabledChange}
        />

    </SplitControl>

    <SplitControl
        label="Send Email Reports"
        caption="Send an email report after a full-blog analysis."
    >

        <InputGroup>
            <Radio
                name="email-report"
                value="never"
                group={$blogStore.link_analysis_email_report}
                on:change={handleEmailReportChange}
            >
                Never
            </Radio>

            <Radio
                name="email-report"
                value="broken"
                group={$blogStore.link_analysis_email_report}
                on:change={handleEmailReportChange}
            >
                When Broken Links Found
            </Radio>

            <Radio
                name="email-report"
                value="always"
                group={$blogStore.link_analysis_email_report}
                on:change={handleEmailReportChange}
            >
                Always
            </Radio>
        </InputGroup>

    </SplitControl>

</div>

<style>
    .controls {
        margin-top: 15px;
    }
</style>
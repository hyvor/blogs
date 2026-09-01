<script lang="ts">
	import { InputGroup, Radio, SplitControl, Switch } from '@hyvor/design/components';
	import { blogStore, updateBlogStore } from '../../../../lib/stores/blogStore';
	import BlogSettingsSave from '../../settings/BlogSettingsSave.svelte';
	import DebugTool from './DebugTool.svelte';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

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

<BlogSettingsSave keys={['link_analysis_enabled', 'link_analysis_email_report']} />

<div class="controls">
	<SplitControl
		label={i18n.t('console.tools.linkAnalysis.settings.automated')}
		caption={i18n.t('console.tools.linkAnalysis.settings.automatedCaption')}
	>
		<Switch
			checked={$blogStore.link_analysis_enabled}
			on:change={handleLinkAnalysisEnabledChange}
		/>
	</SplitControl>

	<SplitControl
		label={i18n.t('console.tools.linkAnalysis.settings.emailReports')}
		caption={i18n.t('console.tools.linkAnalysis.settings.emailReportsCaption')}
	>
		<InputGroup>
			<Radio
				name="email-report"
				value="never"
				group={$blogStore.link_analysis_email_report}
				on:change={handleEmailReportChange}
			>
				{i18n.t('console.tools.linkAnalysis.settings.never')}
			</Radio>

			<Radio
				name="email-report"
				value="broken"
				group={$blogStore.link_analysis_email_report}
				on:change={handleEmailReportChange}
			>
				{i18n.t('console.tools.linkAnalysis.settings.whenBroken')}
			</Radio>

			<Radio
				name="email-report"
				value="always"
				group={$blogStore.link_analysis_email_report}
				on:change={handleEmailReportChange}
			>
				{i18n.t('console.tools.linkAnalysis.settings.always')}
			</Radio>
		</InputGroup>
	</SplitControl>

	<!-- <SplitControl label="Debug Tool" caption="Use this tool to get debug information on a link">
		<DebugTool />
	</SplitControl> -->
</div>

<style>
	.controls {
		margin-top: 15px;
	}
</style>

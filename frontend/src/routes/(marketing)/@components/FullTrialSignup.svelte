<script lang="ts">
	import { FullTrialSignup } from '@hyvor/design/marketing';
	import { getMarketingI18n } from '../[[lang]]/marketingLang';

	const I18n = getMarketingI18n();

	interface Props {
		title?: string;
		[key: string]: any;
	}

	let { title = I18n.t('homepage.trialSignup.title'), ...rest }: Props = $props();
</script>

<FullTrialSignup
	badge={I18n.t('homepage.trialSignup.badge')}
	{title}
	description={I18n.t('homepage.trialSignup.description')}
	button={{ href: '/console?signup', label: I18n.t('homepage.trialSignup.button') }}
	checks={[
		I18n.t('homepage.trialSignup.check1'),
		I18n.t('homepage.trialSignup.check2'),
		I18n.t('homepage.trialSignup.check3')
	]}
	{...rest}
/>

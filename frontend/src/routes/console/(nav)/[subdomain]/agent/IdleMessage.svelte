<script lang="ts">
	import IconRobot from '@hyvor/icons/IconRobot';
	import IconFileText from '@hyvor/icons/IconFileText';
	import IconChatDots from '@hyvor/icons/IconChatDots';
	import IconPencilSquare from '@hyvor/icons/IconPencilSquare';
	import IconShieldCheck from '@hyvor/icons/IconShieldCheck';
	import { getI18n } from '../../../lib/i18n';

	const i18n = getI18n();
	const T = i18n.T;

	const cans = $derived([
		{ icon: IconFileText, text: i18n.t('console.agent.idle.canSearchPosts') },
		{ icon: IconChatDots, text: i18n.t('console.agent.idle.canAnswerQuestions') },
		{ icon: IconPencilSquare, text: i18n.t('console.agent.idle.canSuggestEdits') }
	]);
</script>

<div class="idle">
	<div class="icon">
		<IconRobot size={32} />
	</div>

	<div class="title">{i18n.t('console.agent.idle.title')}</div>

	<div class="cans">
		{#each cans as can}
			<div class="can-item">
				<div class="can-icon">
					<can.icon size={12} />
				</div>
				<div class="can-text">{can.text}</div>
			</div>
		{/each}
		<div class="can-item safe">
			<div class="can-icon safe">
				<IconShieldCheck size={12} />
			</div>
			<div class="can-text">
				<T key="console.agent.idle.cannotEdit" params={{ strong: { element: 'strong' } }} />
			</div>
		</div>
	</div>
</div>

<style lang="scss">
	.idle {
		width: 100%;
		height: 100%;
		display: flex;
		align-items: center;
		justify-content: center;
		flex-direction: column;
		gap: 12px;
		padding: 20px;
		text-align: center;
	}

	.icon {
		width: 60px;
		height: 60px;
		border-radius: 50%;
		display: flex;
		align-items: center;
		justify-content: center;
		background-color: var(--accent-light-mid);
		color: var(--accent);
		margin-bottom: 6px;
	}

	.title {
		font-size: 15px;
		font-weight: 600;
		margin-bottom: 6px;
	}

	.cans {
		display: flex;
		flex-direction: column;
		gap: 5px;
		width: 400px;
		max-width: 100%;
		margin-top: 10px;
	}

	.can-item {
		display: flex;
		align-items: center;
		gap: 10px;
		padding: 4px 14px;
		background-color: var(--box-background);
		text-align: left;
	}

	.can-icon {
		flex-shrink: 0;
		width: 26px;
		height: 26px;
		border-radius: 50%;
		display: flex;
		align-items: center;
		justify-content: center;
		background-color: var(--accent-light-mid);
		color: var(--accent);
	}

	.can-icon.safe {
		background-color: var(--green-light, var(--accent-light-mid));
		color: var(--green);
	}

	.can-text {
		font-size: 13px;
		line-height: 18px;
		color: var(--text-light);
	}

	.can-text :global(strong) {
		color: var(--text);
	}
</style>

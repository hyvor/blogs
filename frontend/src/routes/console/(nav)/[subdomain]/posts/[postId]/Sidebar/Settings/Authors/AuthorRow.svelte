<script lang="ts">
	import { Avatar } from '@hyvor/design/components';
	import type { User } from '../../../../../../../lib/types';
	import { getPrimaryLanguage } from '../../../../../../../lib/stores/languagesStore';
	import { postStore } from '../../../../postStore';
	import { getI18n } from '../../../../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		user: User;
		onclick: () => void;
	}

	let { user, onclick }: Props = $props();

	const primaryLanguage = getPrimaryLanguage();
	const variant = user.variants.find((v) => v.language_id === primaryLanguage.id)!;
	const alreadyAuthor = $postStore.authors.find((a) => a.id === user.id);
</script>

<div
	class="author-row"
	class:already={alreadyAuthor}
	{onclick}
	onkeyup={(e) => e.key === 'Enter' && e.currentTarget.click()}
	role="button"
	tabindex="0"
>
	<div class="left">
		<Avatar size={16} src={user.picture_url} alt={variant.name || ''} />
		<span class="name">
			{variant.name || i18n.t('console.postEditor.settings.unknownUser')}
		</span>
	</div>
	<div class="right">
		<span class="posts-count">
			{i18n.t('console.postEditor.settings.posts', { count: user.posts_count })}
		</span>
	</div>
</div>

<style>
	.author-row {
		padding: 6px 14px;
		display: flex;
		align-items: center;
		border-radius: var(--box-radius);
		cursor: pointer;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}

	.author-row.already {
		opacity: 0.3;
		pointer-events: none;
	}

	.author-row:hover {
		background-color: var(--hover);
	}

	.left {
		display: flex;
		align-items: center;
		flex: 1;
		gap: 5px;
		overflow: hidden;
		margin-right: 4px;
	}

	.left :global(img) {
		flex-shrink: 0;
	}

	.name {
		font-size: 14px;
		min-width: 0;
	}

	.posts-count {
		font-size: 12px;
		color: var(--text-light);
	}
</style>

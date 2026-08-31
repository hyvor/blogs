import { beforeNavigate } from '$app/navigation';
import { get, writable } from 'svelte/store';
import {
	selectedThemeFileStore,
	themeFilesOriginalStore,
	themeFilesStore,
	updateThemeFileStore
} from './themeStore';
import consoleApi from '../../../lib/consoleApi';
import { updateFile } from './themeActions';
import { toast } from '@hyvor/design/components';
import { getI18n } from '../../../lib/i18n';

export function useIsFileEditingCheck() {
	// read during component init, while the i18n context is still available
	const i18n = getI18n();

	beforeNavigate(async (navigation) => {
		let hasChanges = false;

		get(themeFilesStore).forEach((file) => {
			const original = get(themeFilesOriginalStore).find(
				(originalFile) => originalFile.id === file.id
			);
			if (original && original.content !== file.content) hasChanges = true;
		});

		if (hasChanges) {
			// TODO: Upgrade to HDS confirm
			if (!confirm(i18n.t('console.theme.unsavedLeaveConfirm'))) {
				navigation.cancel();
			}
		}
	});
}

export const fileSavingState = writable<'none' | 'loading' | 'success' | 'error'>('none');

export function saveCurrentFile() {
	fileSavingState.set('loading');

	const file = get(selectedThemeFileStore);

	function reset() {
		fileSavingState.set('none');
	}

	if (!file) return reset();

	return updateFile(file.id, {
		content: file.content
	})
		.then((res) => {
			updateThemeFileStore(file.id, res, true);
			fileSavingState.set('success');
		})
		.catch((e) => {
			fileSavingState.set('error');
			toast.error(e.message);
		});
}

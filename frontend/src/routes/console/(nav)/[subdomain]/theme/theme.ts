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

export function useIsFileEditingCheck() {
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
			if (!confirm('Are you sure you want to leave this page? Some files are not saved.')) {
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

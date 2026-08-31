<script lang="ts">
	import { Button, ButtonGroup, Loader, toast } from '@hyvor/design/components';
	import { blogOriginalStore, blogStore } from '../../../lib/stores/blogStore';
	import type { Blog, BlogVariant } from '../../../lib/types';
	import { updateBlog, updateBlogVariant } from '../../../lib/actions/blogActions';
	import { beforeNavigate } from '$app/navigation';
	import { getI18n } from '../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		keys?: (keyof Blog)[];
		variantKeys?: (keyof BlogVariant)[];
		outsideChanges?: Partial<Blog>;
		beforeSave?: null | (() => boolean);
		afterSave?: null | ((b: Blog) => void);
		onError?: null | ((message: string, code: number) => void);
	}

	let {
		keys = [],
		variantKeys = [],
		outsideChanges = {},
		beforeSave = null,
		afterSave = null,
		onError = null
	}: Props = $props();

	let loadingState: 'none' | 'loading' | 'success' | 'error' = $state('none');

	beforeNavigate((navigation) => {
		if (should) {
			if (!confirm('You have unsaved changes. Are you sure you want to leave?')) {
				navigation.cancel();
			}
		}
	});

	function getShouldSave(blog: Blog, blogOriginal: Blog, $outsideChanges: Partial<Blog>) {
		if (Object.keys($outsideChanges).length > 0) {
			return true;
		}

		if (keys.some((key) => blog[key] !== blogOriginal[key])) {
			return true;
		}

		if (
			blog.variants.some((variant, i) =>
				variantKeys.some((key) => variant[key] !== blogOriginal.variants[i]![key])
			)
		) {
			return true;
		}

		return false;
	}

	async function handleSave() {
		if (beforeSave && !beforeSave()) {
			return;
		}

		loadingState = 'loading';

		const variantChanges: Record<number, Partial<BlogVariant>> = {};
		$blogStore.variants.forEach((variant) => {
			const original = $blogOriginalStore.variants.find(
				(v) => v.language_id === variant.language_id
			);

			if (!original) return;

			const changes: Partial<BlogVariant> = {};
			variantKeys.map((key) => {
				if (variant[key] !== original[key]) {
					(changes as any)[key] = variant[key];
				}
			});

			if (Object.keys(changes).length !== 0) {
				variantChanges[variant.language_id] = changes;
			}
		});

		for (const [languageId, changes] of Object.entries(variantChanges)) {
			try {
				await updateBlogVariant(Number(languageId), changes, true);
			} catch (e) {
				toast.error(i18n.t('console.settings.failedToUpdateVariant'));
				loadingState = 'error';
				return;
			}
		}

		let blogUpdate: Partial<Blog> = {};
		keys.map((key) => {
			if ($blogStore[key] !== $blogOriginalStore[key]) {
				(blogUpdate as any)[key] = $blogStore[key];
			}
		});

		blogUpdate = {
			...blogUpdate,
			...outsideChanges
		};

		try {
			const newBlog = await updateBlog(blogUpdate, true);

			if (afterSave) {
				afterSave(newBlog);
			}

			loadingState = 'success';
		} catch (e: any) {
			loadingState = 'error';

			if (onError) {
				onError(e.message as string, e.code as number);
			} else {
				toast.error(e.message);
			}
		}
	}

	function handleDiscard() {
		$blogStore = { ...$blogOriginalStore };
	}
	let should = $derived(getShouldSave($blogStore, $blogOriginalStore, outsideChanges));
</script>

<div class="save">
	<span class="loader-wrap">
		<Loader state={loadingState} size="small" />
	</span>

	<ButtonGroup>
		<Button color="gray" disabled={!should} variant="invisible" on:click={handleDiscard}
			>{i18n.t('console.settings.discard')}</Button
		>

		<Button disabled={!should} on:click={handleSave}>{i18n.t('console.common.save')}</Button>
	</ButtonGroup>
</div>

<style>
	.save {
		padding: 15px 30px;
		text-align: right;
		border-bottom: 1px solid var(--border);
	}
	.loader-wrap {
		display: inline-flex;
		align-items: center;
		height: 100%;
		vertical-align: middle;
		width: 20px;
	}
</style>

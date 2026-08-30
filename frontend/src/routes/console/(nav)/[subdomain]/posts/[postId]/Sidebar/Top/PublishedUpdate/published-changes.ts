import { get } from 'svelte/store';
import {
	documentStore,
	postOriginalStore,
	postVariantOriginalStore,
	postStore,
	postVariantStore
} from '../../../../postStore';
import type { Post, PostVariant, Tag, User } from '../../../../../../../lib/types';
import { hasIdArrayChanged } from '../../Settings/settingsHelpers';

export function getPublishedChanges() {
	// checks the post and the current variant for changes

	const post = get(postStore);
	const postOriginal = get(postOriginalStore);

	const postVariant = get(postVariantStore);
	const postVariantOriginal = get(postVariantOriginalStore);

	const changes = {
		post: {} as Partial<Post>,
		variant: {} as Partial<PostVariant>,
		tags: undefined as undefined | Tag[],
		authors: undefined as undefined | User[]
	};

	const postKeys: (keyof Post)[] = [
		'is_featured',
		'featured_image_url',
		'canonical_url',
		'code_head',
		'code_foot'
	];

	const postVariantKeys: (keyof PostVariant)[] = ['slug', 'title', 'description', 'published_at', 'content_updated_at'];

	postKeys.forEach((key) => {
		if (post[key] !== postOriginal[key]) {
			// @ts-ignore
			changes.post[key] = post[key];
		}
	});

	postVariantKeys.forEach((key) => {
		if (postVariant[key] !== postVariantOriginal[key]) {
			// @ts-ignore
			changes.variant[key] = postVariant[key];
		}
	});

	if (hasIdArrayChanged(post.tags, postOriginal.tags)) {
		changes.tags = post.tags;
	}
	if (hasIdArrayChanged(post.authors, postOriginal.authors)) {
		changes.authors = post.authors;
	}

	const document = get(documentStore);

	if (
		document.checkpoint_content !== null &&
		document.checkpoint_content !== postVariantOriginal.content
	) {
		changes.variant.content = document.checkpoint_content;
	}

	return changes;
}

export function hasPublishedChanges() {
	const changes = getPublishedChanges();
	return (
		Object.keys(changes.post).length > 0 ||
		Object.keys(changes.variant).length > 0 ||
		changes.tags !== undefined ||
		changes.authors !== undefined
	);
}

export function finishUpdating() {
	// no longer editing - content_unsaved stays populated, it's the live editable document
	// TODO:
	// updatePostEditingStatusValue('isEditingPublished', false);
}

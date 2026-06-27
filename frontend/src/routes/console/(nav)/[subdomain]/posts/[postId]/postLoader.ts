import type { Post } from '../../../../lib/types';

// keyed by post id (as string, matching page.params.postId)
const PRELOADED_POSTS: Record<string, Post> = {};

export function setPreloadedPosts(posts: Post[]) {
	for (const post of posts) {
		setPreloadedPost(post);
	}
}

export function setPreloadedPost(post: Post) {
	PRELOADED_POSTS[post.id] = post;
}

export function getPreloadedPost(postId: string): Post | null {
	const post = PRELOADED_POSTS[postId];
	if (post) {
		delete PRELOADED_POSTS[postId];
		return post;
	}
	return null;
}

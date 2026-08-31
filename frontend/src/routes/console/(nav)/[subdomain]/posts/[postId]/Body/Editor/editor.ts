import { getSchema, type EditorConfig } from '@hyvor/richtext';
import {
	AUDIO_EXTENSIONS,
	getMedia,
	IMAGE_EXTENSIONS,
	uploadMedia
} from '../../../../tools/media/mediaActions';
import { getUnfold } from '../../../../../../lib/actions/urlDataActions';
import consoleApi from '../../../../../../lib/consoleApi';
import type { UnsplashImage } from '../../../../../../lib/types';

// all nodes enabled
export const schema = getSchema({
	suggestions: true
});

export const editorConfig: EditorConfig = {
	colorButtonBackground: '#5A8387',
	colorButtonText: '#ffffff',

	codeBlockConfig: {
		language: true,
		fileName: true,
		annotations: true,
		annotationsUrl: null
	},

	image: {
		oversizedNoteText: 'Image size is larger than the image preview in the editor.'
	},

	uploadFileConfig: {
		uploader: async (file, name, type) => {
			const media = await uploadMedia(file, name);
			return {
				url: media.url
			};
		},
		maxFileSizeInMB: 10,
		mediaLoad: (page, type) =>
			getMedia(type === 'audio' ? AUDIO_EXTENSIONS : IMAGE_EXTENSIONS, null, 50, (page - 1) * 50),
		unsplashSearch: (search, page) =>
			consoleApi.get<UnsplashImage[]>({
				endpoint: '/media/unsplash/search',
				data: {
					search,
					page
				}
			}),
		excalidraw: true
	},

	embed: async (url) => {
		try {
			await getUnfold(url, 'embed');
		} catch {
			return null;
		}

		// unfold privacy iframe (prevent running JS on origin)
		return `/api/public/unfold/iframe?url=${encodeURIComponent(url)}`;
	},

	bookmark: async (url) => {
		const data = await getUnfold(url, 'link');

		let siteName = data.site_url;
		try {
			siteName = new URL(data.site_url || data.final_url || url).hostname;
		} catch {
			// keep site_url as-is
		}

		return {
			url: data.final_url || data.url,
			title: data.title,
			description: data.description,
			siteName,
			siteUrl: data.site_url,
			thumbnailUrl: data.thumbnail_url,
			iconUrl: data.icon_url
		};
	}
};

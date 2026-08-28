import { getSchema, type EditorConfig } from '@hyvor/richtext';
import { uploadMedia } from '../../../../tools/media/mediaActions';
import { getUnfold } from '../../../../../../lib/actions/urlDataActions';

// all nodes enabled
export const schema = getSchema({
	suggestions: true,
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

	fileMaxSizeInMB: 10,
	fileUploader: async (file, name, type) => {
		const media = await uploadMedia(file, name);
		return {
			url: media.url
		};
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

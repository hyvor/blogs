import { EditorView } from 'prosemirror-view';
import { Plugin } from 'prosemirror-state';
import { get } from 'svelte/store';
// import { blogStore } from '../../../routes/console/lib/stores/blogStore';
// import { uploadMedia } from '../../../routes/console/(nav)/[subdomain]/tools/media/mediaActions';

export default function pasteImagesPlugin() {
	return new Plugin({
		props: {
			handlePaste: (view, e, slice) => {
				const content = slice.content;

				const images: string[] = [];

				content.descendants((node) => {
					if (node.type.name === 'image') {
						// Do not upload images that are already hosted on the blog
						
						// TODO: fix this
						// const mediaPath = get(blogStore).url + '/media';
						// if (node.attrs.src.startsWith(mediaPath)) return;

						images.push(node.attrs.src);
					}
				});

				setTimeout(() => {
					uploadAndReplaceImages(images, view);
				}, 100);
			}
		}
	});
}

async function uploadAndReplaceImages(imageUrls: string[], view: EditorView) {
	return;
	// TODO: fix this
	/* for (const url of imageUrls) {
		fetch(url)
			.then((res) => res.blob())
			.then((blob) => {
				if (blob.type.indexOf('image') === -1) return;

				uploadMedia(blob).then((media) => {
					replaceImage(url, media.url, view);
				});
			});
	} */
}

function replaceImage(currentUrl: string, newUrl: string, view: EditorView) {
	view.state.doc.descendants((node, pos) => {
		if (node.type.name === 'image' && node.attrs.src === currentUrl) {
			const tr = view.state.tr.setNodeAttribute(pos, 'src', newUrl);
			view.dispatch(tr);
		}
	});
}

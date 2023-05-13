import {Slice} from "prosemirror-model";
import {EditorView} from "prosemirror-view";
import {Plugin} from "prosemirror-state";
import mediaLogic from "../../../logic/mediaLogic";
import getSubdomain from "../../../logic-helpers/subdomain";
import {Media} from "../../../types";
import { getBlogUrl } from "../../../lib/blog-helpers";

export default function pasteImagesPlugin() {

    return new Plugin({
        props: {
            handlePaste: (view, e, slice) => {

                const content = slice.content;

                const images : string[] = [];

                content.descendants((node) => {
                    if (node.type.name === 'image') {
                        const blogUrl = getBlogUrl(getSubdomain(), '') + 'media';
                        if (node.attrs.src.startsWith(blogUrl))
                            return;
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

    const { uploadImageFromUrl, uploadImage } = mediaLogic({subdomain: getSubdomain()}).actions

    for (const url of imageUrls) {

        // if base64
        if (url.startsWith('data:image')) {
            const fetched = await fetch(url);
            const blob = await fetched.blob();
            uploadImage({
                file: blob,
                onUpload: (media: Media) => replaceImage(url, media.url, view)
            })
        } else {

            uploadImageFromUrl({
                url,
                onUpload: (media: Media) => replaceImage(url, media.url, view)
            });

        }

    }

}

function replaceImage(currentUrl: string, newUrl: string, view: EditorView) {

    view.state.doc.descendants((node, pos) => {

        if (node.type.name === 'image' && node.attrs.src === currentUrl) {
            const tr = view.state.tr.setNodeAttribute(pos, 'src', newUrl);
            view.dispatch(tr);
        }

    });

}
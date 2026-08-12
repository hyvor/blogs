import { getSchema, type EditorConfig } from "@hyvor/richtext";
import { uploadMedia } from "../../../../tools/media/mediaActions";

// all nodes enabled
export const schema = getSchema();

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
        if (type !== 'image') {
            return null;
        }
        const media = await uploadMedia(file, name);
        return {
            url: media.url
        };
    }
};
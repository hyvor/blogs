import type { ExcalidrawElement } from "@excalidraw/excalidraw/types/element/types";
import type { Media, UnsplashImage } from "../../types";
import type { AppState } from "@excalidraw/excalidraw/types/types";

export type SelectFromType = 'upload' | 'media' | 'unsplash' | 'excalidraw';
export type UploadType = 'paste' | 'dnd' | 'browse' | 'url';

export interface SelectedFile {
    type: 'image' | 'audio' | 'other',
    from: SelectFromType,
    url: string | Blob,

    upload?: {
        type: UploadType,
        originalUrl?: string,
    },
    unsplash?: UnsplashImage,
    excalidraw?: {
        elements: readonly ExcalidrawElement[],
        appState: AppState,
    },
    media?: Media,
}

export const VALID_MIME_TYPES = [
    'image/gif', 
    'image/jpeg', 
    'image/png',
    'image/svg+xml', 
    'image/webp',
    'image/apng', 
    'image/avif'
];

export const VALID_MIME_TYPES_NAMES = VALID_MIME_TYPES.map(
    m => m.split('/')[1]?.split('+')[0]
);
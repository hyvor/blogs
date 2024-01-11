import type { ExcalidrawElement } from "@excalidraw/excalidraw/types/element/types";
import type { UnsplashImage } from "../../types";
import type { AppState } from "@excalidraw/excalidraw/types/types";

export type SelectFromType = 'upload' | 'media' | 'unsplash' | 'excalidraw';
export type UploadType = 'paste' | 'dnd' | 'browse' | 'url';

export interface SelectedImage {
    from: SelectFromType,
    url: string,
    uploadType?: UploadType,

    unsplash?: UnsplashImage,
    excalidraw?: {
        elements: readonly ExcalidrawElement[],
        appState: AppState,
    }
}
import type { UnsplashImage } from "../../types";

export type SelectFromType = 'upload' | 'media' | 'unsplash' | 'excalidraw';
export type UploadType = 'paste' | 'dnd' | 'browse' | 'url';

export interface SelectedImage {
    from: SelectFromType,
    url: string,
    uploadType?: UploadType,
    unsplash?: UnsplashImage,
}
import type { Media } from "../../types";

export type SelectFromTypeAudio = 'upload' | 'media';
export type UploadTypeAudio = 'paste' | 'dnd' | 'browse' | 'url';

export interface SelectedAudio {
    from: SelectFromTypeAudio,
    src: string | Blob,
    
    upload?: {
        type: UploadTypeAudio,
        originalSrc?: string,
    },
    media?: Media,
}


export const VALID_MIME_TYPES_AUDIO = [
    'audio/mpeg',
    'audio/ogg',
    'audio/wav',
    'audio/webm'
];

export const VALID_MIME_TYPES_NAMES_AUDIO = VALID_MIME_TYPES_AUDIO.map(
    m => m.split('/')[1]?.split('+')[0]
);
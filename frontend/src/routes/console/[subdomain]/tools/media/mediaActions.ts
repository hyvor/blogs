import consoleApi from "../../../lib/consoleApi";
import { type Media } from "../../../lib/types";

export type FileType = 'all' | 'images' | 'videos' | 'documents' | 'audio' | 'archives' | 'custom';

export const IMAGE_EXTENSIONS = [
    'png', 
    'jpg', 'jpeg', 'jfif', 'pjpeg', 
    'pjp', 'gif', 'apng', 'avif', 
    'svg', 'webp'
];

export function getExtensionsByFileType(fileType: FileType, customExtensions: string[]) {
    switch (fileType) {
        case 'all':
            return null;
        case 'images':
            return IMAGE_EXTENSIONS;
        case 'videos':
            return ['mp4', 'mov', 'avi', 'wmv', 'flv', 'webm'];
        case 'documents':
            return ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf', 'txt'];
        case 'audio':
            return ['mp3', 'wav', 'ogg', 'wma', 'flac', 'aac'];
        case 'archives':
            return ['zip', 'rar', '7z', 'tar', 'gz', 'bz2'];
        case 'custom':
            return customExtensions;
    }
}


export function getMedia(
    extensions: string[] | null = null,
    search: string | null = null,
    offset: number = 0,
) {

    return consoleApi.get<Media[]>({
        endpoint: '/media',
        data: {
            search,
            extensions,
            limit: 50,
            offset,
        }
    })

}
import consoleApi from '../../../../lib/consoleApi';
import { type Media } from '../../../../lib/types';

export type FileType = 'all' | 'images' | 'videos' | 'documents' | 'audio' | 'archives' | 'custom';

export const IMAGE_EXTENSIONS = [
	'png',
	'jpg',
	'jpeg',
	'jfif',
	'pjpeg',
	'pjp',
	'gif',
	'apng',
	'avif',
	'svg',
	'webp'
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
	limit: number = 50,
	offset: number = 0
) {
	return consoleApi.get<Media[]>({
		endpoint: '/media',
		data: {
			search,
			extensions,
			limit,
			offset
		}
	});
}

export function uploadMedia(file: File | Blob, name: string | null = null) {
	const formData = new FormData();
	formData.append('file', file);
	formData.append('name', name || '');
	return consoleApi.post<Media>({
		endpoint: '/media',
		data: formData
	});
}

export function updateMedia(id: number, data: Partial<Media>) {
	return consoleApi.patch<Media>({
		endpoint: `/media/${id}`,
		data
	});
}

export function deleteMedia(id: number) {
	return consoleApi.delete({
		endpoint: `/media/${id}`
	});
}

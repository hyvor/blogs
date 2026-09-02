import {
	uploadFile,
	type FileUploaderConfig,
	type FileUploaderUploadedFile
} from '@hyvor/design/components';
import type { UploadFileConfig } from '@hyvor/richtext';
import {
	AUDIO_EXTENSIONS,
	getMedia,
	IMAGE_EXTENSIONS,
	uploadMedia
} from '../(nav)/[subdomain]/tools/media/mediaActions';
import { getConfig } from './config';
import consoleApi from './consoleApi';
import type { UnsplashImage } from './types';

const uploader: FileUploaderConfig['uploader'] = async (file, name) => {
	const media = await uploadMedia(file, name);
	return { url: media.url };
};

const mediaLoad: FileUploaderConfig['mediaLoad'] = (page, type) =>
	getMedia(type === 'audio' ? AUDIO_EXTENSIONS : IMAGE_EXTENSIONS, null, 50, (page - 1) * 50);

const unsplashSearch: FileUploaderConfig['unsplashSearch'] = (search, page) =>
	consoleApi.get<UnsplashImage[]>({
		endpoint: '/media/unsplash/search',
		data: {
			search,
			page
		}
	});

function maxUploadSizeInMB() {
	return getConfig().limits.max_upload_size / (1024 * 1024);
}

export const editorUploadFileConfig: UploadFileConfig = {
	uploader,
	maxFileSizeInMB: 10,
	mediaLoad,
	unsplashSearch,
	excalidraw: true
};

export function uploadImage(): Promise<FileUploaderUploadedFile | null> {
	return uploadFile({
		type: 'image',
		uploader,
		maxFileSizeInMB: maxUploadSizeInMB(),
		mediaLoad,
		unsplashSearch,
		excalidraw: true
	});
}

export function uploadImageOnly(): Promise<FileUploaderUploadedFile | null> {
	return uploadFile({
		type: 'image',
		uploader,
		maxFileSizeInMB: maxUploadSizeInMB()
	});
}

export function uploadToMediaLibrary(): Promise<FileUploaderUploadedFile | null> {
	return uploadFile({
		type: 'file',
		uploader,
		maxFileSizeInMB: maxUploadSizeInMB()
	});
}

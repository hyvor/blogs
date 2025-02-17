import type { Node } from 'prosemirror-model';
import { type Component, mount, unmount } from 'svelte';
import schema from '../../schema';
import IconBookmark from '@hyvor/icons/IconBookmark';
import IconCardImage from '@hyvor/icons/IconCardImage';
import IconCode from '@hyvor/icons/IconCode';
import IconCodeSlash from '@hyvor/icons/IconCodeSlash';
import IconHr from '@hyvor/icons/IconHr';
import IconLightbulb from '@hyvor/icons/IconLightbulb';
import IconLink45deg from '@hyvor/icons/IconLink45deg';
import IconListUl from '@hyvor/icons/IconListUl';
import IconQuote from '@hyvor/icons/IconQuote';
import IconSoundwave from '@hyvor/icons/IconSoundwave';
import IconTable from '@hyvor/icons/IconTable';
import IconTypeH2 from '@hyvor/icons/IconTypeH2';
import IconTypeH3 from '@hyvor/icons/IconTypeH3';

// import FileUploader from '../../../../routes/console/lib/components/FileUploader/FileUploader.svelte';
// import type { SelectedFile } from '../../../../routes/console/lib/components/FileUploader/image-uploader';
import EmbedCreator from './Embed/EmbedCreator.svelte';
import BookmarkCreator from './Bookmark/BookmarkCreator.svelte';

export interface SlashOption {
	name: string;
	description: string;
	icon: Component;
	keywords: string[];
	node: string | (() => Promise<Node | null>);
	attrs?: Record<string, unknown>;
}

const options: SlashOption[] = [
	{
		name: 'Heading - Large',
		description: 'To divide main sections of the post',
		icon: IconTypeH2,
		keywords: ['heading', 'large', 'title', 'h1', 'h2'],
		node: 'heading',
		attrs: { level: 2 }
	},
	{
		name: 'Heading - Medium',
		description: 'To divide small sections of the post',
		icon: IconTypeH3,
		keywords: ['heading', 'medium', 'title', 'h2', 'h3', 'h4'],
		node: 'heading',
		attrs: { level: 3 }
	},
	{
		name: 'Image',
		description: 'Add an image',
		icon: IconCardImage,
		keywords: ['image', 'picture', 'upload'],
		node: selectImage
	},
	{
		name: 'Embed',
		description: 'Embed content from YouTube, Twitter, etc.',
		icon: IconLink45deg,
		keywords: [
			'embed',
			'rich',
			'video',
			'youtube',
			'twitter',
			'facebook',
			'instagram',
			'reddit',
			'github'
		],
		node: createEmbed
	},
	{
		name: 'Audio',
		description: 'Add an audio',
		icon: IconSoundwave,
		keywords: ['audio', 'sound', 'upload'],
		node: selectAudio
	},
	{
		name: 'Code Block',
		description: 'A block of code',
		icon: IconCode,
		keywords: ['code', 'snippet'],
		node: 'code_block'
	},
	{
		name: 'Quote',
		description: 'Capture a quote',
		icon: IconQuote,
		keywords: ['quote', 'blockquote'],
		node: createQuote
	},
	{
		name: 'Callout',
		description: 'Write something standing out',
		icon: IconLightbulb,
		keywords: ['alert', 'notice', 'callout', 'aside'],
		node: 'callout'
	},
	{
		name: 'Link Bookmark',
		description: 'Link preview as a bookmark',
		icon: IconBookmark,
		keywords: ['bookmark', 'link'],
		node: createBookmark
	},
	{
		name: 'Divider',
		description: 'Divide sections with a horizontal line',
		icon: IconHr,
		keywords: ['hr', 'divider', 'horizontal', 'line'],
		node: 'horizontal_rule'
	},
	{
		name: 'Custom HTML/Twig',
		description: 'Add custom HTML (or Twig)',
		icon: IconCodeSlash,
		keywords: ['html', 'twig', 'code', 'custom'],
		node: 'custom_html'
	},
	{
		name: 'Table of Contents',
		description: 'Add a table of contents',
		icon: IconListUl,
		keywords: ['toc', 'table of contents', 'contents', 'outline', 'index', 'menu'],
		node: 'toc'
	},
	{
		name: 'Table',
		description: 'Add a table',
		icon: IconTable,
		keywords: ['table', 'spreadsheet'],
		node: createTable
	}
];

// finds options by best guess
export function findOptions(match: string): SlashOption[] {
	const matchWords = match
		.toLowerCase()
		.split(/\W+/)
		.filter((str) => str !== '');

	const matched = [];

	for (let i = 0; i < options.length; i++) {
		const { keywords } = options[i]!;

		for (let x = 0, l = keywords.length; x < l; x++) {
			for (let y = 0, yLen = match ? matchWords.length : 1; y < yLen; y++) {
				if (match === '' || keywords[x]!.indexOf(matchWords[y]!) > -1) {
					const item = { ...options[i], score: 0 };
					item.score = x + y;
					if (matched.filter((x) => x.name === item.name).length === 0) {
						matched.push(item);
					}
				}
			}
		}
	}

	return matched.sort((a, b) => a.score - b.score) as SlashOption[];
}

function selectImage() {
	// TODO: abstract this
	return; 
	
	// return new Promise<Node | null>((resolve) => {
	// 	const div = document.createElement('div');
	// 	document.body.appendChild(div);

	// 	const selector = mount(FileUploader, {
	// 		target: div,
	// 		props: {
	// 			type: 'image',
	// 			onselect: (selected) => {
	// 				destroy();
	// 				return resolve(
	// 					schema.nodes.figure!.create({}, [
	// 						schema.nodes.image!.create({ src: selected.url }),
	// 						schema.nodes.figcaption!.create()
	// 					])
	// 				);
	// 			},
	// 			onclose: () => {
	// 				destroy();
	// 				resolve(null);
	// 			}
	// 		}
	// 	});

	// 	function destroy() {
	// 		unmount(selector);
	// 		div.remove();
	// 	}
	// });
}

function selectAudio() {

	// TODO: abstract this

	// return new Promise<Node | null>((resolve) => {
	// 	const div = document.createElement('div');
	// 	document.body.appendChild(div);

	// 	const selector = mount(FileUploader, {
	// 		target: div,
	// 		props: {
	// 			type: 'audio',
	// 			onselect: (selected) => {
	// 				destroy();
	// 				return resolve(schema.nodes.audio!.create({ src: selected.url }));
	// 			},
	// 			onclose: () => {
	// 				destroy();
	// 				resolve(null);
	// 			}
	// 		}
	// 	});

	// 	function destroy() {
	// 		unmount(selector);
	// 		div.remove();
	// 	}
	// });
}

function createQuote() {
	return Promise.resolve(schema.nodes.blockquote!.create({}, [schema.nodes.paragraph!.create()]));
}

function createEmbed() {
	return new Promise<Node | null>((resolve) => {
		const div = document.createElement('div');
		document.body.appendChild(div);

		const creator = mount(EmbedCreator, {
			target: div,
			props: {
				onclose: () => {
					destroy();
					resolve(null);
				},
				oncreate: (url: string) => {
					destroy();
					resolve(
						schema.nodes.figure!.create({}, [
							schema.nodes.embed!.create({ url }),
							schema.nodes.figcaption!.create()
						])
					);
				},
				oncreatebookmark: (url: string) => {
					destroy();
					resolve(
						schema.nodes.figure!.create({}, [
							schema.nodes.bookmark!.create({ url: url }),
							schema.nodes.figcaption!.create()
						])
					);
				},
				oncreatehtmlblock: (html: string) => {
					destroy();
					resolve(schema.nodes.custom_html!.create({ html }));
				}
			}
		});

		function destroy() {
			unmount(creator);
			div.remove();
		}
	});
}

function createBookmark(url: string = '') {
	return new Promise<Node | null>((resolve) => {
		const div = document.createElement('div');
		document.body.appendChild(div);

		const creator = mount(BookmarkCreator, {
			target: div,
			props: {
				url,
				onclose: () => {
					destroy();
					resolve(null);
				},
				oncreate: (url: string) => {
					destroy();
					resolve(
						schema.nodes.figure!.create({}, [
							schema.nodes.bookmark!.create({ url }),
							schema.nodes.figcaption!.create()
						])
					);
				}
			}
		});

		function destroy() {
			unmount(creator);
			div.remove();
		}
	});
}

function createTable() {
	const rows = [];
	for (let i = 0; i < 3; i++) {
		const cells = [];
		for (let j = 0; j < 3; j++) {
			cells.push(schema.nodes.table_cell!.create({}, [schema.nodes.paragraph!.create()]));
		}
		rows.push(schema.nodes.table_row!.create({}, cells));
	}

	return Promise.resolve(schema.nodes.table!.create({}, [...rows]));
}

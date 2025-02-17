import { DOMParser } from 'prosemirror-model';
import { expect, test } from 'vitest';
import schema from '../../schema';
import { generateToc } from './toc';

function getToc(html: string, levels = [1, 2, 3, 4]) {
	const htmlDoc = document.implementation.createHTMLDocument();
	htmlDoc.body.innerHTML = html;
	const doc = DOMParser.fromSchema(schema).parse(htmlDoc);

	return generateToc(doc, levels);
}

test('toc tests', () => {
	const toc = getToc(`
        <h1 id="id-1">First Title</h1>
        <h2>1.1</h2>
        <h3>1.1.1</h3>
        <h2>1.2</h2>
        <h3>1.2.1</h3>
        <h3>1.2.2</h3>
        <h4>1.2.2.1</h4>
        <h5>1.2.2.1.1</h5>
        <h3>1.2.3</h3>
        <h2>1.3</h2>
        <h1>Second title</h1>
        <h2>2.1</h2>
    `);

	// h1 first
	const firstTitle = toc[0]!;
	expect(firstTitle.id).toBe('id-1');
	expect(firstTitle.title).toBe('First Title');

	expect(firstTitle.children.length).toBe(3);
	expect(firstTitle.children[0]!.title).toBe('1.1');

	// h1 second
	expect(toc[1]!.title).toBe('Second title');
	expect(toc[1]!.id).toBe(null);

	expect(toc).toStrictEqual([
		{
			level: 1,
			id: 'id-1',
			title: 'First Title',
			children: [
				{
					level: 2,
					id: null,
					title: '1.1',
					children: [
						{
							level: 3,
							id: null,
							title: '1.1.1',
							children: []
						}
					]
				},
				{
					level: 2,
					id: null,
					title: '1.2',
					children: [
						{
							level: 3,
							id: null,
							title: '1.2.1',
							children: []
						},
						{
							level: 3,
							id: null,
							title: '1.2.2',
							children: [
								{
									level: 4,
									id: null,
									title: '1.2.2.1',
									// level 5 is not included
									children: []
								}
							]
						},
						{
							level: 3,
							id: null,
							title: '1.2.3',
							children: []
						}
					]
				},
				{
					level: 2,
					id: null,
					title: '1.3',
					children: []
				}
			]
		},
		{
			level: 1,
			id: null,
			title: 'Second title',
			children: [
				{
					level: 2,
					id: null,
					title: '2.1',
					children: []
				}
			]
		}
	]);
});

test('when the first heading is lower', () => {
	const toc = getToc(`
        <h3 id="my-3">h3</h3>
        <h4>h4</h4>
        <h1>h1</h1>
        <h2>h2</h2>
    `);

	expect(toc).toStrictEqual([
		{
			level: 3,
			id: 'my-3',
			title: 'h3',
			children: [
				{
					level: 4,
					id: null,
					title: 'h4',
					children: []
				}
			]
		},
		{
			level: 1,
			id: null,
			title: 'h1',
			children: [
				{
					level: 2,
					id: null,
					title: 'h2',
					children: []
				}
			]
		}
	]);
});

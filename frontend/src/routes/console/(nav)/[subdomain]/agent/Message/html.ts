import { marked } from 'marked';
// @ts-ignore
import DOMPurify from 'dompurify';

export function getPurifiedHtmlFromMarkdown(content: string) {
	if (!content) return '';

	const renderer = {
		link(href: string, title: string | null | undefined, text: string) {
			return `<a href="${href}" target="_blank" rel="noreferrer noopener">${text}</a>`;
		}
	};

	const m = marked.use({
		renderer
	});

	return DOMPurify.sanitize(m(content) as string, {
		ADD_ATTR(attributeName, tagName) {
			if (attributeName === 'target' && tagName === 'a') {
				return true;
			}
			return false;
		}
	});
}

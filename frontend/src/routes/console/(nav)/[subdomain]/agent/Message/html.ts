import { marked } from 'marked';
// @ts-ignore
import DOMPurify from 'dompurify';

export function getPurifiedHtmlFromMarkdown(content: string) {
	if (!content) return '';

	const m = marked.use({
		renderer: {
			link(link) {
				return `<a href="${link.href}" target="_blank" rel="noreferrer noopener">${link.text}</a>`;
			}
		}
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

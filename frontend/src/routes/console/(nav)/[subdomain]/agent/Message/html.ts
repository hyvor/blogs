import { marked } from 'marked';
// @ts-ignore
import DOMPurify from 'dompurify';

export function getPurifiedHtmlFromMarkdown(content: string) {
    if (!content) return '';
    return DOMPurify.sanitize(marked(content) as string);
}
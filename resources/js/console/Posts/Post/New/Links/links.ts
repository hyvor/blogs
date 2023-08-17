import { getDocFromContent } from "../../ProseMirror/helpers";
import {parse} from "tldts";

type LinkType = 
    'internal-blog' | // inside the blog
    'internal-domain' | // not inside the blog, but inside the domain
    'internal-root-domain' | // not inside the blog or subdomain, but inside the root domain
    'external' | // outside the root domain
    'mail' | // mailto:
    'tel' | // tel:
    'anchor' | // #anchor
    'other'; // anything else

export interface Link {
    type: LinkType,
    href: string
}

export function getLinksMarksFromContent(content: string | null, blogUrl: string) {

    const doc = getDocFromContent(content);
    const links: Link[] = [];

    doc.descendants(node => {

        const marks = node.marks;
        if (marks.length === 0) return;

        node.marks.forEach(mark => {

            if (mark.type.name !== 'link') return;
            const href = mark.attrs.href;
            if (!href) return;

            const type = getLinkType(href, blogUrl);
            if (!type) return;

            links.push({
                type,
                href
            });

        });

    });

    return links;

}

export function getLinkType(href: string, blogUrl: string) : LinkType {

    if (href.startsWith('mailto:')) return 'mail';
    if (href.startsWith('tel:')) return 'tel';
    if (href.startsWith('#')) return 'anchor';

    const baseUrl = blogUrl.endsWith('/') ? blogUrl : blogUrl + '/';
    const hrefFullUrl = (new URL(href, baseUrl));

    if (hrefFullUrl.toString().startsWith(baseUrl)) return 'internal-blog';
    if (hrefFullUrl.protocol !== 'https:' && hrefFullUrl.protocol !== 'http:') return 'other';

    const { domain: blogDomain, subdomain: blogSubdomain } = parse(blogUrl);
    const { domain: hrefDomain, subdomain: hrefSubdomain } = parse(hrefFullUrl.toString());

    if (blogDomain === hrefDomain) {
        return blogSubdomain === hrefSubdomain ? 
            'internal-domain' : 
            'internal-root-domain'; 
    } else {
        return 'external';
    }

}
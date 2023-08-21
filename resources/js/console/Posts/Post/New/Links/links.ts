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
    index: number,
    type: LinkType,
    href: string,
    anchor: string | null
}

export function getLinksFromContent(content: string | null, blogUrl: string) : Link[] {

    const doc = getDocFromContent(content);
    const links: Link[] = [];

    doc.descendants(node => {

        const marks = node.marks;
        if (marks.length === 0) return;

        node.marks.forEach((mark, i) => {

            if (mark.type.name !== 'link') return;
            const href = mark.attrs.href;
            if (!href) return;

            const type = getLinkType(href, blogUrl);
            if (!type) return;

            links.push({
                index: i,
                type,
                href: getFullUrl(href, blogUrl).toString(),
                anchor: node.textContent ? node.textContent : null
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
    const hrefFullUrl = getFullUrl(href, blogUrl);

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

export function getFullUrl(href: string, blogUrl: string) : URL {
    const baseUrl = blogUrl.endsWith('/') ? blogUrl : blogUrl + '/';
    return new URL(href, baseUrl);
}


type TextOrContent = string | Array<object>

function getTextOrContent(t: TextOrContent) {
    return typeof t === 'string' ? [pmc.text(t)] : t;
}

export class pmc {

    static doc(content: Array<object> | object) {
        return JSON.stringify({
            type: 'doc',
            content: Array.isArray(content) ? content : [content],
        });
    }

    static p(text: TextOrContent) {
        return {
            type: 'paragraph',
            content: getTextOrContent(text),
        };
    }

    static h(text: TextOrContent, level: number = 2, id: string|null = null) {
        return {
            type: 'heading',
            attrs: {
                level,
                id,
            },
            content: getTextOrContent(text),
        };
    }

    static docP(content: TextOrContent) {
        return pmc.doc([pmc.p(content)]);
    }

    static text(text: string, marks: Array<object> = []) {
        return {
            type: 'text',
            text,
            marks,
        };
    }

    static link(url: string, anchor: string) {
        return pmc.text(anchor, [
            {
                type: 'link',
                attrs: {
                    href: url,
                },
            }
        ]);
    }

    static img(url: string, alt: string|null = null) {
        return {
            type: 'figure',
            content: [
                {
                    type: 'image',
                    attrs: {
                        src: url,
                        alt,
                    },
                }
            ]
        };
    }

}
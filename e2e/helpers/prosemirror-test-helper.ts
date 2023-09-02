

export class pmc {

    static p(content: string) {
        return JSON.stringify({
            type: 'doc',
            content: [
                {
                    type: 'paragraph',
                    content: [
                        {
                            type: 'text',
                            text: content,
                        },
                    ],
                },
            ],
        });
    }

}
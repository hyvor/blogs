
export function createEmbed(schema, url) {
    return schema.nodes.figure.create({}, [
        schema.nodes.embed.create({ url }),
        schema.nodes.figcaption.create()
    ])
}

export function createImage(schema, url) {
    return schema.nodes.figure.create({}, [
        schema.nodes.image.create({ url }),
        schema.nodes.figcaption.create()
    ]);
}

export function createQuote(schema) {
    return schema.nodes.blockquote.create({}, [
        schema.nodes.paragraph.create()
    ])
}
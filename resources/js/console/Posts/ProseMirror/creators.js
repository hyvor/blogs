
export function createRich(schema, url) {
    schema.nodes.figure.create({}, [
        schema.nodes.rich.create({ url }),
        schema.nodes.figcaption.create()
    ])
}

export function createImage(schema, url) {
    schema.nodes.figure.create({}, [
        schema.nodes.image.create({ url }),
        schema.nodes.figcaption.create()
    ]);
}
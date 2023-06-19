
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

export function createTable(schema) {
    const rows = [];
    for (let i = 0; i < 3; i++) {
        const cells = [];
        for (let j = 0; j < 3; j++) {
            cells.push(
                schema.nodes.table_cell.create(
                    {},
                    [
                        schema.nodes.paragraph.create()
                    ]
                )
            )
        }
        rows.push(
            schema.nodes.table_row.create(
                {},
                cells
            )
        )
    }

    return schema.nodes.table.create(
        {},
        [...rows]
    )
}

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
    // Create a simple table with paragraph cells and a header row
    return schema.nodes.table.create(
        {},
        [
            schema.nodes.table_row.create(
                {},
                [
                    schema.nodes.table_cell.create(
                        {},
                        [
                            schema.nodes.paragraph.create()
                        ]
                    ),
                    schema.nodes.table_cell.create(
                        {},
                        [
                            schema.nodes.paragraph.create()
                        ]
                    )
                ]
            ),
            schema.nodes.table_row.create(
                {},
                [
                    schema.nodes.table_cell.create(
                        {},
                        [
                            schema.nodes.paragraph.create()
                        ]
                    ),

                    schema.nodes.table_cell.create(
                        {},
                        [
                            schema.nodes.paragraph.create()
                        ]
                    )
                ]
            )
        ]
    )
}
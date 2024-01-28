import { Schema } from "prosemirror-model";

export function createEmbed(schema: Schema, url: string) {
    return schema.nodes.figure.create({}, [
        schema.nodes.embed.create({ url }),
        schema.nodes.figcaption.create()
    ])
}

export function createImage(schema: Schema, url: string) {
    return schema.nodes.figure.create({}, [
        schema.nodes.image.create({ src: url }),
        schema.nodes.figcaption.create()
    ]);
}

export function createQuote(schema: Schema) {
    return schema.nodes.blockquote.create({}, [
        schema.nodes.paragraph.create()
    ])
}

export function createTable(schema: Schema) {
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
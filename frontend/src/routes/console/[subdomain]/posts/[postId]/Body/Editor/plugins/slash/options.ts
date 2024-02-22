import type { Node } from "prosemirror-model";
import type { ComponentType } from "svelte";
import schema from "../../../../../../../lib/prosemirror/schema";
import { IconBookmark, IconCardImage, IconCode, IconCodeSlash, IconHr, IconLightbulb, IconLink45deg, IconListUl, IconQuote, IconTable, IconTypeH2, IconTypeH3 } from "@hyvor/icons";
import ImageUploader from "../../../../../../../lib/components/ImageUploader/ImageUploader.svelte";
import type { SelectedImage } from "../../../../../../../lib/components/ImageUploader/image-uploader";
import EmbedCreator from "./Embed/EmbedCreator.svelte";
import BookmarkCreator from "./Bookmark/BookmarkCreator.svelte";

export interface SlashOption {
    name: string,
    description: string,
    icon: ComponentType,
    keywords: string[],
    node: string | (() => Promise<Node | null>),
    attrs?: Record<string, any>,
}

const options: SlashOption[] = [
    {
        name: "Heading - Large",
        description: "To divide main sections of the post",
        icon: IconTypeH2,
        keywords: ["heading", "large", "title", "h1", "h2"],
        node: "heading",
        attrs: { level: 2 },
    },
    {
        name: "Heading - Medium",
        description: "To divide small sections of the post",
        icon: IconTypeH3,
        keywords: ["heading", "medium", "title", "h2", "h3", "h4"],
        node: "heading",
        attrs: { level: 3 },
    },
    {
        name: "Image",
        description: "Add an image",
        icon: IconCardImage,
        keywords: ["image", "picture", "upload"],
        node: selectImage,
    },
    {
        name: "Embed",
        description: "Embed content from 1500+ platforms",
        icon: IconLink45deg,
        keywords: [
            "embed",
            "rich",
            "video",
            "audio",
            "file",
            "youtube",
            "twitter",
            "soundcloud",
            "spotify",
            "github",
            "maps",
            "codepen",
        ],
        node: createEmbed,
    },
    {
        name: "Code Block",
        description: "A block of code",
        icon: IconCode,
        keywords: ["code", "snippet"],
        node: "code_block",
    },
    {
        name: "Quote",
        description: "Capture a quote",
        icon: IconQuote,
        keywords: ["quote", "blockquote"],
        node: createQuote,
    },
    {
        name: "Callout",
        description: "Write something standing out",
        icon: IconLightbulb,
        keywords: ["alert", "notice", "callout", "aside"],
        node: "callout",
    },
    {
        name: "Link Bookmark",
        description: "Link preview as a bookmark",
        icon: IconBookmark,
        keywords: ["bookmark", "link"],
        node: createBookmark,
    },
    {
        name: "Divider",
        description: "Divide sections with a horizontal line",
        icon: IconHr,
        keywords: ["hr", "divider", "horizontal", "line"],
        node: "horizontal_rule",
    },
    {
        name: "Custom HTML/Twig",
        description: "Add custom HTML (or Twig)",
        icon: IconCodeSlash,
        keywords: ["html", "twig", "code", "custom"],
        node: "custom_html",
    },
    {
        name: 'Table of Contents',
        description: 'Add a table of contents',
        icon: IconListUl,
        keywords: ['toc', 'table of contents', 'contents', 'outline', 'index', 'menu'],
        node: "toc",
    },
    {
        name: "Table",
        description: "Add a table",
        icon: IconTable,
        keywords: ["table", "spreadsheet"],
        node: createTable,
    },
];

// finds options by best guess
export function findOptions(match: string) : SlashOption[] {
    
    const matchWords = match
        .toLowerCase()
        .split(/\W+/)
        .filter((str) => str !== "");
    
    const matched = [];

    for (var i = 0; i < options.length; i++) {
        let { keywords } = options[i]!;

        for (var x = 0, l = keywords.length; x < l; x++) {
            for (
                var y = 0, yLen = match ? matchWords.length : 1;
                y < yLen;
                y++
            ) {
                if (match === "" || keywords[x]!.indexOf(matchWords[y]!) > -1) {
                    const item = { ...options[i], score: 0 };
                    item.score = x + y;
                    if (
                        matched.filter((x) => x.name === item.name).length === 0
                    ) {
                        matched.push(item);
                    }
                }
            }
        }
    }

    return matched.sort((a, b) => a.score - b.score) as SlashOption[];
}


function selectImage() {

    return new Promise<Node | null>((resolve, reject) => {

        const div = document.createElement("div");
        document.body.appendChild(div);

        const selector = new ImageUploader({
            target: div,
        });

        function destroy() {
            selector.$destroy();
            div.remove();
        }

        selector.$on('close', () => {
            destroy();
            resolve(null);
        })

        selector.$on('select', (e: CustomEvent<SelectedImage>) => {
            destroy();
            return resolve(
                schema.nodes.figure!.create({}, [
                    schema.nodes.image!.create({ src: e.detail.url }),
                    schema.nodes.figcaption!.create()
                ])
            )
        });

    });

}

function createQuote() {
    return Promise.resolve(schema.nodes.blockquote!.create({}, [
        schema.nodes.paragraph!.create()
    ]));
}

function createEmbed() {

    return new Promise<Node | null>((resolve, reject) => {

        const div = document.createElement("div");
        document.body.appendChild(div);

        const creator = new EmbedCreator({
            target: div,
        });

        function destroy() {
            creator.$destroy();
            div.remove();
        }

        creator.$on('close', () => {
            destroy();
            resolve(null);
        })

        creator.$on('create', (e: CustomEvent<string>) => {
            destroy();
            return resolve(
                schema.nodes.figure!.create({}, [
                    schema.nodes.embed!.create({ url: e.detail }),
                    schema.nodes.figcaption!.create()
                ])
            )
        });

    });

}

function createBookmark() {

    return new Promise<Node | null>((resolve, reject) => {

        const div = document.createElement("div");
        document.body.appendChild(div);

        const creator = new BookmarkCreator({
            target: div,
        });

        function destroy() {
            creator.$destroy();
            div.remove();
        }

        creator.$on('close', () => {
            destroy();
            resolve(null);
        })

        creator.$on('create', (e: CustomEvent<string>) => {
            destroy();
            return resolve(
                schema.nodes.figure!.create({}, [
                    schema.nodes.bookmark!.create({ url: e.detail }),
                    schema.nodes.figcaption!.create()
                ])
            )
        });

    });

}

function createTable() {

    const rows = [];
    for (let i = 0; i < 3; i++) {
        const cells = [];
        for (let j = 0; j < 3; j++) {
            cells.push(schema.nodes.table_cell!
                    .create({}, [schema.nodes.paragraph!.create()]))
        }
        rows.push(
            schema.nodes.table_row!.create(
                {},
                cells
            )
        )
    }

    return Promise.resolve(schema.nodes.table!.create(
        {},
        [...rows]
    ));

}
import type { Node } from "prosemirror-model";
import type { ComponentType } from "svelte";
import type schema from "../../../../../../../lib/prosemirror/schema";
import { IconBookmark, IconCardImage, IconCode, IconCodeSlash, IconHr, IconLightbulb, IconLink45deg, IconQuote, IconTable, IconTypeH2, IconTypeH3 } from "@hyvor/icons";

export interface SlashOption {
    name: string,
    description: string,
    icon: ComponentType,
    keywords: string[],
    node: string | ((s: typeof schema) => Promise<Node>),
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
        // node: selectImage,
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
        // node: createEmbed,
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
        // node: createQuote,
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
        node: "bookmark",
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
        name: "Table",
        description: "Add a table",
        icon: IconTable,
        keywords: ["table", "spreadsheet"],
        // node: createTable,
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
import {Mark, type MarkSpec, Node, type NodeSpec, Schema} from "prosemirror-model"
import { addListNodes } from "prosemirror-schema-list"
import { tableNodes } from "prosemirror-tables"

/**
 * Copied and changed from
 * https://github.com/ProseMirror/prosemirror-schema-basic
 */

// :: Object
// [Specs](#model.NodeSpec) for the nodes defined in this schema.
export const nodes = {
    // :: NodeSpec The top level document node.
    doc: {
        content: "block+"
    } as NodeSpec,

    // :: NodeSpec A plain paragraph textblock. Represented in the DOM
    // as a `<p>` element.
    paragraph: {
        content: "inline*",
        group: "block",
        selectable: false,
        draggable: true,
        parseDOM: [{tag: "p"}],
        toDOM() { return ['p', 0] }
    } as NodeSpec,

    // :: NodeSpec A blockquote (`<blockquote>`) wrapping one or more blocks.
    blockquote: {
        content: "block+",
        group: "block",
        defining: true,
        selectable: false,
        parseDOM: [{tag: "blockquote"}],
        toDOM() { return ["blockquote", 0] }
    } as NodeSpec,

    // :: NodeSpec A horizontal rule (`<hr>`).
    horizontal_rule: {
        group: "block",
        parseDOM: [{tag: "hr"}],
        toDOM() { return ['hr'] }
    } as NodeSpec,

    // :: NodeSpec A heading textblock, with a `level` attribute that
    // should hold the number 2 to 6. Parsed and serialized as `<h1>` to
    // `<h6>` elements.
    heading: {
        attrs: {
            level: {default: 1},
            id: {default: null},
        },
        content: "inline*",
        group: "block",
        defining: true,
        draggable: false,
        selectable: false,
        parseDOM: [
            {tag: "h1", getAttrs(h: HTMLElement) {return {id: h.id, level: 1}}},
            {tag: "h2", getAttrs(h: HTMLElement) {return {id: h.id, level: 2}}},
            {tag: "h3", getAttrs(h: HTMLElement) {return {id: h.id, level: 3}}},
            {tag: "h4", getAttrs(h: HTMLElement) {return {id: h.id, level: 4}}},
            {tag: "h5", getAttrs(h: HTMLElement) {return {id: h.id, level: 5}}},
            {tag: "h6", getAttrs(h: HTMLElement) {return {id: h.id, level: 6}}}
        ],
        toDOM(node: Node) { return ["h" + node.attrs.level, {id: node.attrs.id}, 0] }
    } as NodeSpec,

    // :: NodeSpec A code listing. Disallows marks or non-text inline
    // nodes by default. Represented as a `<pre>` element with a
    // `<code>` element inside of it.
    code_block: {
        attrs: {
            language: {default: null},
            annotations: {default: null},
            name: {default: null},
        },
        content: "text*",
        marks: "",
        group: "block",
        code: true,
        defining: true,
        selectable: false,
        parseDOM: [{tag: "pre", preserveWhitespace: "full"}],
        toDOM() { return ["pre", ["code", 0]] }
    } as NodeSpec,
    
    custom_html: {
        content: "text*",
        marks: "",
        group: "block",
        code: true,
        defining: true,
        selectable: false,
        parseDOM: [{tag: "custom", preserveWhitespace: "full"}],
        toDOM() { return ["custom", 0] }
    } as NodeSpec,

    // :: NodeSpec The text node.
    text: {
        group: "inline"
    } as NodeSpec,

    figure: {
        content: "(image|embed) figcaption",
        group: "block",
        selectable: false,
        draggable: true,
        parseDOM: [
            {
                tag: "figure",
            }
        ],
        toDOM() { 
            return ["figure", 0] 
        }
    } as NodeSpec,
    image: {
        attrs: {
            src: {default: null},
            alt: {default: null},
            width: {default: null},
            height: {default: null}
        },
        inline: false,
        draggable: false,
        selectable: false,
        //group: "figure",
        parseDOM: [{
          tag: "img[src]", 
          getAttrs(img: HTMLElement) {
            return {
                src: img.getAttribute("src"),
                alt: img.getAttribute("alt"),
                width: img.getAttribute("width"),
                height: img.getAttribute("height")
            };
          }
        }],
        toDOM(node: Node) {
          return ["img", {...node.attrs}]; 
        }
    } as NodeSpec,
    audio: {
        attrs: {
            src: {default: null}
        },
        inline: false,
        selectable: false,
        group: "block",
        atom: true,
        parseDOM: [{
          tag: "audio[src]", 
          getAttrs(audio: HTMLElement) {
            return {
                src: audio.getAttribute("src"),
            };
          }
        }],
        toDOM(node: Node) {
          return ["audio", {...node.attrs}]; 
        }
    } as NodeSpec,
    embed: {
        attrs: {
            url: {default: null}
        },
        // content: "text*",
        //group: "block",
        // atom: true,
        selectable: true,
        draggable: false,
        parseDOM: [{
            tag: "x-embed[data-url]",
            getAttrs(div: HTMLElement) {
                return {
                    url: div.dataset.url
                }
            }
        }],
        toDOM(node: Node) {
            return ["x-embed", {
                "data-url": node.attrs.url
            }]
        }
    } as NodeSpec,

    figcaption: {
        content: "inline*",
        //group: "figure",
        selectable: false,
        parseDOM: [{tag: "figcaption"}],
        toDOM() { return ["figcaption", 0]; },
    } as NodeSpec,

    callout: {
        attrs: {
            emoji: {default: "💡"},
            bg: {default: "#f1f1ef"},
            fg: {default: "#000000"}
        },
        content: "inline*",
        group: "block",
        defining: true,
        selectable: false,
        parseDOM: [{
            tag: "aside",
            getAttrs(aside: HTMLElement) {
                return {
                    emoji: aside.dataset.emoji,
                    bg: aside.style.backgroundColor,
                    fg: aside.style.color
                }
            }
        }],
        toDOM(node: Node) { return ["aside", {
            'data-emoji': node.attrs.emoji,
            style: `background-color: ${node.attrs.bg}; color: ${node.attrs.fg}`
        }, 0] }
    } as NodeSpec,

    bookmark: {
        attrs: {
            url: {default: null}
        },
        //atom: true,
        //draggable: true,
        selectable: true,
        group: "block",
        parseDOM: [{
            tag: "bookmark[data-url]",
            getAttrs(div: HTMLElement) {
                return {
                    url: div.dataset.url
                }
            }
        }],
        toDOM(node: Node) {
            return ["bookmark", {
                "data-url": node.attrs.url
            }]
        }
    } as NodeSpec,

    // :: NodeSpec A hard line break, represented in the DOM as `<br>`.
    hard_break: {
        inline: true,
        group: "inline",
        selectable: false,
        parseDOM: [{tag: "br"}],
        toDOM() { return ['br'] }
    } as NodeSpec,

    toc: {
        attrs: { 
            levels: {default: [1,2,3,4,5,6]} 
        },
        group: "block",
        inline: false,
        draggable: true,
        selectable: true,
        atom: true,
    },

     ...tableNodes({
        tableGroup: "block",
        cellContent: "block+",
        cellAttributes: {}
    })
}

// :: Object [Specs](#model.MarkSpec) for the marks in the schema.
/**
 * Marks with background color should come first https://discuss.prosemirror.net/t/marks-priority/4463
 */
export const marks = {

    // :: MarkSpec Code font mark. Represented as a `<code>` element.
    code: {
        parseDOM: [{tag: "code"}],
        toDOM() { return ["code", 0] }
    } as MarkSpec,

    highlight: {
        parseDOM: [{tag: "mark"}],
        toDOM() { return ["mark", 0] }
    } as MarkSpec,

    // :: MarkSpec A link. Has `href` and `title` attributes. `title`
    // defaults to the empty string. Rendered and parsed as an `<a>`
    // element.
    link: {
        attrs: {
            href: {},
        },
        inclusive: false,
        parseDOM: [{tag: "a[href]", getAttrs(dom: HTMLElement) {
            return {
                href: dom.getAttribute("href")
            }
        }}],
        toDOM(mark: Mark) { let {href} = mark.attrs; return ["a", {href}, 0] }
    } as MarkSpec,

    // :: MarkSpec An emphasis mark. Rendered as an `<em>` element.
    // Has parse rules that also match `<i>` and `font-style: italic`.
    em: {
        parseDOM: [{tag: "i"}, {tag: "em"}, {style: "font-style=italic"}],
        toDOM() { return ["em", 0] }
    } as MarkSpec,

    // :: MarkSpec A strong mark. Rendered as `<strong>`, parse rules
    // also match `<b>` and `font-weight: bold`.
    strong: {
        parseDOM: [
            {tag: "strong"},
            // This works around a Google Docs misbehavior where
            // pasted content will be inexplicably wrapped in `<b>`
            // tags with a font-weight normal.
            {tag: "b", getAttrs: (node: HTMLElement) => node.style.fontWeight != "normal" && null},
            {style: "font-weight", getAttrs: (value: string) => /^(bold(er)?|[5-9]\d{2,})$/.test(value) && null}
        ],
        toDOM() { return ["strong", 0] }
    } as MarkSpec,


    // `<s>` for strike
    strike: {
        parseDOM: [{tag: "s"}, {tag: "strike"}, {tag: "del"}],
        toDOM() { return ["s", 0] }
    } as MarkSpec,

    sup: {
        parseDOM: [{tag: "sup"}],
        toDOM() { return ["sup", 0] }
    } as MarkSpec,

    sub: {
        parseDOM: [{tag: "sub"}],
        toDOM() { return ["sub", 0] }
    } as MarkSpec,

    comment: {
        parseDOM: [{tag: "comment"}],
        toDOM() { return ["comment", 0] }
    } as MarkSpec,
}

// :: Schema
// This schema roughly corresponds to the document schema used by
// [CommonMark](http://commonmark.org/), minus the list elements,
// which are defined in the [`prosemirror-schema-list`](#schema-list)
// module.
//
// To reuse elements from this schema, extend or read from its
// `spec.nodes` and `spec.marks` [properties](#model.Schema.spec).

const schemaWithoutList = new Schema({nodes, marks});

export default new Schema({
    nodes: addListNodes(schemaWithoutList.spec.nodes, "block+", "block"),
    marks: schemaWithoutList.spec.marks
});
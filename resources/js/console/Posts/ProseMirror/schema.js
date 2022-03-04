import {Schema} from "prosemirror-model"
import { addListNodes } from "./list"

/**
 * Copied and changed from
 * https://github.com/ProseMirror/prosemirror-schema-basic
 */

const pDOM = ["p", 0], blockquoteDOM = ["blockquote", 0], hrDOM = ["hr"], brDOM = ["br"]

// :: Object
// [Specs](#model.NodeSpec) for the nodes defined in this schema.
export const nodes = {
    // :: NodeSpec The top level document node.
    doc: {
        content: "block+"
    },

    // :: NodeSpec A plain paragraph textblock. Represented in the DOM
    // as a `<p>` element.
    paragraph: {
        content: "inline*",
        group: "block",
        selectable: false,
        parseDOM: [{tag: "p"}],
        toDOM() { return pDOM }
    },

    // :: NodeSpec A blockquote (`<blockquote>`) wrapping one or more blocks.
    blockquote: {
        content: "block+",
        group: "block",
        defining: true,
        selectable: false,
        parseDOM: [{tag: "blockquote"}],
        toDOM() { return blockquoteDOM }
    },

    // :: NodeSpec A horizontal rule (`<hr>`).
    horizontal_rule: {
        group: "block",
        parseDOM: [{tag: "hr"}],
        toDOM() { return hrDOM }
    },

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
        selectable: false,
        parseDOM: [
            {tag: "h1", getAttrs(h) {return {id: h.id, level: 1}}},
            {tag: "h2", getAttrs(h) {return {id: h.id, level: 2}}},
            {tag: "h3", getAttrs(h) {return {id: h.id, level: 3}}},
            {tag: "h4", getAttrs(h) {return {id: h.id, level: 4}}},
            {tag: "h5", getAttrs(h) {return {id: h.id, level: 5}}},
            {tag: "h6", getAttrs(h) {return {id: h.id, level: 6}}}
        ],
        toDOM(node) { return ["h" + node.attrs.level, {id: node.attrs.id}, 0] }
    },

    // :: NodeSpec A code listing. Disallows marks or non-text inline
    // nodes by default. Represented as a `<pre>` element with a
    // `<code>` element inside of it.
    code_block: {
        attrs: {
            language: {default: null},
            data: {default: {}},
        },
        content: "text*",
        marks: "",
        group: "block",
        code: true,
        defining: true,
        selectable: false,
        parseDOM: [{tag: "pre", preserveWhitespace: "full"}],
        toDOM() { return ["pre", ["code", 0]] }
    },

    // :: NodeSpec The text node.
    text: {
        group: "inline"
    },

    figure: {
        content: "(rich|image)+ figcaption",
        group: "block",
        selectable: true,
        draggable: true,
        parseDOM: [
            {
                tag: "figure",
            }
        ],
        toDOM() { 
            return ["figure", 0] 
        }
    },
    image: {
        attrs: {
            src: {default: null},
            alt: {default: null}, 
            title: {default: null},
            width: {default: null},
            height: {default: null}
        },
        inline: false,
        draggable: false,
        selectable: false,
        group: "figure",
        parseDOM: [{
          tag: "img[src]", 
          getAttrs(img) {
            return {
                src: img.src, 
                alt: img.alt, 
                title: img.title,
                width: img.width,
                height: img.height
            }; 
          }
        }],
        toDOM(node) {
          return ["img", {...node.attrs}]; 
        }
    },
    rich: {
        attrs: {
            url: {default: null}
        },
        content: "text*",
        group: "figure",
        atom: true,
        selectable: false,
        parseDOM: [{
            tag: "div.rich[data-url]",
            getAttrs(div) {
                return {
                    url: div.dataset.url
                }
            }
        }],
        toDOM(node) {
            return ["div", {
                "data-url": node.attrs.url,
                class: "rich"
            }]
        }
    },

    figcaption: {
        content: "inline*",
        group: "figure",
        selectable: false,
        parseDOM: [{tag: "figcaption"}],
        toDOM() { return ["figcaption", 0]; },
    },

    callout: {
        attrs: {
            emoji: {default: "💡"}
        },
        content: "inline*",
        group: "block",
        defining: true,
        selectable: false,
        parseDOM: [{tag: "aside"}],
        toDOM() { return ["aside", 0] }
    },

    // :: NodeSpec A hard line break, represented in the DOM as `<br>`.
    hard_break: {
        inline: true,
        group: "inline",
        selectable: false,
        parseDOM: [{tag: "br"}],
        toDOM() { return brDOM }
    }
}

const emDOM = ["em", 0], 
    strongDOM = ["strong", 0], 
    codeDOM = ["code", 0],
    strikeDOM = ["s", 0],
    supDOM = ["sup", 0],
    subDOM = ["sub", 0];

// :: Object [Specs](#model.MarkSpec) for the marks in the schema.
/**
 * Marks with background color should come first https://discuss.prosemirror.net/t/marks-priority/4463
 */
export const marks = {

    // :: MarkSpec Code font mark. Represented as a `<code>` element.
    code: {
        parseDOM: [{tag: "code"}],
        toDOM() { return codeDOM }
    },

    mark: {
        parseDOM: [{tag: "mark"}],
        toDOM() { return ["mark", 0] }
    },

    // :: MarkSpec A link. Has `href` and `title` attributes. `title`
    // defaults to the empty string. Rendered and parsed as an `<a>`
    // element.
    link: {
        attrs: {
            href: {},
            title: {default: null}
        },
        inclusive: false,
        parseDOM: [{tag: "a[href]", getAttrs(dom) {
            return {href: dom.getAttribute("href"), title: dom.getAttribute("title")}
        }}],
        toDOM(node) { let {href, title} = node.attrs; return ["a", {href, title}, 0] }
    },

    // :: MarkSpec An emphasis mark. Rendered as an `<em>` element.
    // Has parse rules that also match `<i>` and `font-style: italic`.
    em: {
        parseDOM: [{tag: "i"}, {tag: "em"}, {style: "font-style=italic"}],
        toDOM() { return emDOM }
    },

    // :: MarkSpec A strong mark. Rendered as `<strong>`, parse rules
    // also match `<b>` and `font-weight: bold`.
    strong: {
        parseDOM: [{tag: "strong"},
                             // This works around a Google Docs misbehavior where
                             // pasted content will be inexplicably wrapped in `<b>`
                             // tags with a font-weight normal.
                             {tag: "b", getAttrs: node => node.style.fontWeight != "normal" && null},
                             {style: "font-weight", getAttrs: value => /^(bold(er)?|[5-9]\d{2,})$/.test(value) && null}],
        toDOM() { return strongDOM }
    },


    // `<s>` for strike
    s: {
        parseDOM: [{tag: "s"}, {tag: "strike"}, {tag: "del"}],
        toDOM() { return strikeDOM }
    },

    sup: {
        parseDOM: [{tag: "sup"}],
        toDOM() { return supDOM }
    },

    sub: {
        parseDOM: [{tag: "sub"}],
        toDOM() { return subDOM }
    },


}

// :: Schema
// This schema roughly corresponds to the document schema used by
// [CommonMark](http://commonmark.org/), minus the list elements,
// which are defined in the [`prosemirror-schema-list`](#schema-list)
// module.
//
// To reuse elements from this schema, extend or read from its
// `spec.nodes` and `spec.marks` [properties](#model.Schema.spec).


const schemaWithoutList = new Schema({nodes, marks})

export default new Schema({
    nodes: addListNodes(schemaWithoutList.spec.nodes, "block*", "block"),
    marks: schemaWithoutList.spec.marks
})
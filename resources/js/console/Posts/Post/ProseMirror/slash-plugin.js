import {NodeSelection, Plugin, TextSelection} from "prosemirror-state"

/**
 * React icons are used to save duplicate loading
 */
import { renderToString } from 'react-dom/server';
import { Bookmark, CardImage, Code, CodeSlash, Gear, Hr, Lightbulb, Link45deg, Quote, TypeH2, TypeH3, } from "react-bootstrap-icons";
import { createImage, createQuote, createEmbed } from "./creators";


const matchable = [
    {
        name: "Heading - Large",
        description: "To divide main sections of the post",
        icon: <TypeH2 />,
        keywords: ['heading', 'large', 'title', 'h1', 'h2'],
        node: 'heading',
        attrs: {level: 2}
    },
    {
        name: "Heading - Medium",
        description: "To divide small sections of the post",
        icon: <TypeH3 />,
        keywords: ['heading', 'medium', 'title', 'h2', 'h3', 'h4'],
        node: 'heading',
        attrs: {level: 3}
    },
    {
        name: "Image",
        description: "Add an image",
        icon: <CardImage />,
        keywords: ['image', 'picture', 'upload'],
        node: createImage,
        selectNode: true
    },
    {
        name: "Embed",
        description: "Embed content from 1500+ platforms",
        icon: <Link45deg />,
        keywords: [
            'embed', 'rich',
            'video', 'audio', 'file',
            'youtube', 'twitter', 'soundcloud', 'spotify', 'github', 'maps', 'codepen'
        ],
        node: 'embed'
    },
    {
        name: "Code Block",
        description: "A block of code",
        icon: <Code />,
        keywords: ['code', 'snippet'],
        node: 'code_block'
    },
    {
        name: "Quote",
        description: "Capture a quote",
        icon: <Quote />,
        keywords: ['quote', 'blockquote'],
        node: createQuote
    },
    {
        name: "Callout",
        description: "Write something standing out",
        icon: <Lightbulb />,
        keywords: ['alert', 'notice', 'callout'],
        node: 'callout'
    },
    {
        name: "Link Bookmark",
        description: "Link preview as a bookmark",
        icon: <Bookmark />,
        keywords: ['bookmark', 'link'],
        node: 'bookmark',
        selectNode: true
    },
    {
        name: "Divider",
        description: "Divide sections with a horizontal line",
        icon: <Hr />,
        keywords: ['hr', 'divider', 'horizontal', 'line'],
        node: 'horizontal_rule',
        selectNode: true
    },
    {
        name: "Custom HTML/Twig",
        description: "Add custom HTML (or Twig)",
        icon: <CodeSlash />,
        keywords: ['html', 'twig', 'code', 'custom'],
        node: 'custom_html'
    },
    /*{
        name: "Custom Node",
        description: "Add pre-defined custom node",
        icon: <Gear />,
        keywords: ['custom'],
        node: 'custom_node'
    },*/
]


// finds matches by best guess
function findMatches(match) {
    const matchWords = match.toLowerCase().split(/\W+/).filter(str => str !== "")
    const matched = [];
    for (var i = 0; i < matchable.length; i++) {
        let {keywords} = matchable[i];

        for (var x = 0, l = keywords.length; x < l; x++) {
            for (var y = 0, yLen = match ? matchWords.length : 1; y < yLen; y++) {
                if (
                    match === "" ||
                    keywords[x].indexOf(matchWords[y]) > -1
                ) {
                    const item = {...matchable[i]}
                    item.score = x + y;
                    if (matched.filter(x => x.name === item.name).length === 0) {
                        matched.push(item);
                    }
                }
            }
        }
    }

    return matched.sort((a, b) => a.score - b.score);
}


export default function slashPlugin(schema) {
    return new Plugin({
        view(editorView) { return new SlashPlugin(editorView, schema) }
    })
}

class SlashPlugin {

    isOpen = false;

    constructor(view, schema) {
        this.items = [];
        this.view = view;
        this.schema = schema;

        this.slashView = document.createElement("div")
        this.slashView.className = "pm-slash-view"
        view.dom.parentNode.appendChild(this.slashView)


        this.handleKeyDown = this.handleKeyDown.bind(this)
    }
  
    update(view, lastState) {
        let {selection} = view.state

        if (
            lastState && 
            lastState.doc.eq(view.state.doc)
        ) {
            return
        }
        
        if (selection.from !== selection.to)
            return this.hide();

        let {$from} = selection;

        const parent = $from.parent
    
        if (!parent || parent.type.name !== 'paragraph') {
            return this.hide();
        }

        const text = parent.firstChild?.text;

        if (!text)
            return this.hide();

        const match = text.match(/^\/(.*)/);

        if (!match)
            return this.hide();

        const matches = findMatches(match[1]);

        if (!matches.length)
            return this.hide();

        if (!this.isOpen && text === "/") {
            this.open();
        }

        this.show(view, matches);
    }

    hide() {
        this.isOpen = false;
        this.slashView.classList.remove("open");
        this.removeEvents();
    }

    open() {
        this.isOpen = true;
        this.slashView.classList.add("open");
        this.addEvents();
    }

    show(view, matches) {

        this.slashView.innerHTML = "";

        var _self = this;

        matches.forEach(m => {
            var item = document.createElement("div");
            item.className = "match-item";

            var icon = document.createElement("div");
            icon.innerHTML = m.icon ? renderToString(m.icon) : null
            icon.className = "item-icon";

            var nameWrap = document.createElement("div");
            nameWrap.className = "item-name-wrap";

            var name = document.createElement("div");
            name.innerHTML = m.name;
            name.className = "item-name";

            var description = document.createElement("div");
            description.innerHTML = m.description
            description.className = "item-desc";

            item.appendChild(icon)
            item.appendChild(nameWrap)
            nameWrap.appendChild(name)
            nameWrap.appendChild(description)

            item.onclick = function() {

                let node = m.node;
                let createdNode;
                if (typeof node === 'function') {
                    createdNode = node(_self.schema)
                } else {
                    createdNode = _self.schema.nodes[node].create(m.attrs || {});
                }

                let {$from, to} = view.state.selection, pos
                let same = $from.sharedDepth(to)
                pos = $from.before(same)
                const nodeSel = NodeSelection.create(view.state.doc, pos);

                const tr = view.state.tr

                view.dispatch(
                    tr.replaceWith(nodeSel.from, nodeSel.to, createdNode)
                )

                const tr2 = view.state.tr

                view.dispatch(
                    tr2.setSelection(
                        m.selectNode ?
                            NodeSelection.create(tr.doc, pos) :
                            TextSelection.create(tr.doc, pos + 1)
                    ).scrollIntoView()
                )

                /**
                 * In bookmark and embed,
                 * we want to focus the input instead of the view
                 */
                if (m.node !== 'bookmark' && m.name !== 'Embed')
                    view.focus();

            }

            item.onmouseover = function() {
                _self.activateItem(item)
            }

            this.slashView.appendChild(item)
        });

        this.activateFirstItem();

        const posTop = view.coordsAtPos(view.state.selection.from).top;

        // The box in which the slash view is positioned, to use as base
        const wrapPos = this.slashView.offsetParent.getBoundingClientRect()
        const viewPos = view.dom.getBoundingClientRect() 

        this.slashView.style.top = (posTop - wrapPos.top) + "px";
        this.slashView.style.left = (viewPos.left - wrapPos.left) + "px";

        this.slashView.classList.add("bottom");
        this.slashView.classList.remove("top");

    }

    addEvents() {
        window.addEventListener("keydown", this.handleKeyDown, true)
    }

    removeEvents() {
        window.removeEventListener("keydown", this.handleKeyDown, true)
    }

    handleKeyDown(event) {
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            this.activateNext();
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            this.activatePrevious();
        } else if (event.key === 'Enter') {
            event.preventDefault();
            const active = this.getActiveItem();
            active && active.click();
        } else if (event.key === 'Escape') {
            this.hide();
        }
    }

    getItems() {
        return this.slashView.querySelectorAll('.match-item');
    }
    getActiveItem() {
        return this.slashView.querySelector('.match-item.active');
    }

    activateFirstItem() {
        const items = this.getItems();
        this.activateItem(items[0]);
    }

    activateItem(item, scroll) {
        const items = this.getItems();
        items.forEach(i => i.classList.remove("active"));
        item.classList.add("active");
        scroll && item.scrollIntoView(false);
    }

    activateNext() {
        const active = this.getActiveItem();
        if (active && active.nextSibling) {
            this.activateItem(active.nextSibling, true)
        } else {
            this.activateItem(this.getItems()[0], true);
        }
    }
    activatePrevious() {
        const active = this.getActiveItem();
        if (active && active.previousSibling) {
            this.activateItem(active.previousSibling, true)
        } else {
            const items = this.getItems()
            this.activateItem(items[items.length - 1], true);
        }
    }
  
    destroy() { this.slashView.remove() }
}

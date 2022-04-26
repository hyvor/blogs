import { NodeSelection, TextSelection } from "prosemirror-state";
import api from "../../lib/api";
import subdomainLogic from "../../logic/subdomainLogic";
import schema from "./schema";


export default class Bookmark {

    constructor(node, view, getPos) {
        this.node = node;
        this.view = view;
        this.getPos = getPos;

        this.dom = document.createElement("bookmark")
        this.renderNode(node);
    }

    renderNode(node) {

        const url = node.attrs.url;

        this.dom.innerHTML = "";

        if (url) {

            this.dom.innerHTML = '<div class="embedding-placeholder">Loading bookmark...</div>';
            this.dom.classList.remove("url-input")

            api.get(subdomainLogic.values.subdomain, "/url-data", {
                url, 
                type: "link"
            })
                .then(response => {
                    this.renderBookmark(response);
                })

            delete this.selectNode
            delete this.deselectNode

        } else {
            const input = document.createElement("input")
            let lastValue = '';
            input.placeholder = "Paste URL here to generate a bookmark";
            input.onkeyup = e => {
                if (lastValue === "" && e.key === 'Backspace') {
                    this.removeInput();
                }
                if (e.key === 'Escape') {
                    this.removeInput();
                }

                if (e.key === 'Enter') {
                    this.view.dispatch(
                        this.view.state.tr.setNodeMarkup(
                            this.getPos(),
                            null,
                            {...node.attrs, url: input.value}
                        )
                    )
                }


                lastValue = input.value;
            }

            this.dom.classList.add("url-input");
            this.dom.appendChild(input);

            this.selectNode = () => {
                input.focus();
            }
            this.deselectNode = () => {
                input.blur();
            }

            input.focus();
        }

    }

    renderBookmark(urlData) {
        
        const thumbnail = urlData.thumbnail ? `<div class="bookmark-link-thumbnail">
            <img alt="Thumbnail" src="${urlData.thumbnail}" />
        </div>` : ''
        
        this.dom.innerHTML = `<div class="bookmark-wrap">
            <div class="bookmark-link-details">
                <div class="bookmark-link-title">${urlData.title}</div>
                <div class="bookmark-link-description">${urlData.description}</div>
                <div class="bookmark-link-domain">${urlData.domain}</div>
            </div>
            ${thumbnail}
        </div>`

    }

    stopEvent() { return true }

    removeInput() {
        const pos = this.getPos()
        const tr = this.view.state.tr.setNodeMarkup(
            pos,
            schema.nodes.paragraph
        )
        const selection = TextSelection.create(tr.doc, pos + 1);
        this.view.dispatch(
            tr.setSelection(selection)
                .scrollIntoView()
        )
        this.view.focus();
    }

}

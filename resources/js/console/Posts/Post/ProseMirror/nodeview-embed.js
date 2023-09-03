import setInnerHTMLWithScripts from "../../../../helpers/setInnerHTMLWithScripts";
import api from "../../../lib/api";
import subdomainLogic from "../../../logic/subdomainLogic";
import schema from "./schema";
import {NodeSelection, TextSelection} from "prosemirror-state";
import {toast} from "react-toastify";
import {createEmbed} from "./creators";

export default class EmbedView {

    constructor(schema, node, view, getPos) {
        this.schema = schema;
        this.node = node;
        this.view = view;
        this.getPos = getPos;

        this.dom = document.createElement("x-embed");

        const url = node.attrs.url;
        
        if (url) {

            this.dom.dataset.url = url;
            this.dom.innerHTML = '<div class="embedding-placeholder">Loading embed...</div>';
            this.dom.classList.add("loading");
            
            api.get(subdomainLogic.values.subdomain, '/url-data', {url, type: 'embed'})
                .then(response => {
                    this.dom.classList.remove("loading");
                    setInnerHTMLWithScripts(this.dom, response.html);
                })
                .catch(error => {
                    toast.error("Unable to embed this URL (Request Error)");
                })

            // select figure
            this.selectNode = () => {}
            delete this.deselectNode
            
        } else {

            /**
             * WET with nodeview-bookmark
             * When the URL is empty, embed is rendered without figure
             */

            const input = document.createElement("input")
            let lastValue = '';
            input.placeholder = "Paste URL to embed (Youtube, Twitter, and 1000+ platforms supported)";
            input.onkeydown = e => {
                if (lastValue === "" && e.key === 'Backspace') {
                    this.removeInput();
                }
                if (e.key === 'Escape') {
                    this.removeInput();
                }
                if (e.key === 'Enter') {
                    
                    if (input.value.trim() === "") {
                        return toast.error("URL cannot be empty");
                    }
                    
                    const nodeSel = NodeSelection.create(this.view.state.doc, this.getPos())
                    this.view.dispatch(
                        this.view.state.tr.replaceWith(
                            nodeSel.from,
                            nodeSel.to,
                            this.view.state.schema.nodes.embed.create({url: input.value})
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

    removeInput() {
        let pos = this.getPos();
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
        const nodeBefore = tr.doc.nodeAt(pos - 1);
        const nodeAfter = tr.doc.nodeAt(pos + nodeBefore.nodeSize + 1);

        if (nodeBefore && nodeBefore.type.name === "figure") {
            let tr2 = this.view.state.tr;
            tr2.deleteRange(pos - 1, pos + nodeBefore.nodeSize - 1);
            this.view.dispatch(tr2);
        }

        if (nodeAfter && nodeAfter.type.name === "figure") {
            let tr3 = this.view.state.tr;
            tr3.deleteRange(pos, pos + nodeAfter.nodeSize - 1);
            this.view.dispatch(tr3);
        }
       
    }


    stopEvent() { return !this.node.attrs.url }

}

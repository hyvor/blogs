import ReactDOM from 'react-dom';
import ImageUploader from './ImageUploader';
import {NodeSelection} from "prosemirror-state";

export default class Image {

    constructor(schema, node, view, getPos) {
        this.node = node;
        this.view = view;
        this.getPos = getPos;
        this.schema = schema;

        const wrap = document.createElement("div")
        wrap.className = "image-wrap";

        this.dom = wrap;

        this.createInside = this.createInside.bind(this)
        this.handleUpload = this.handleUpload.bind(this)

        this.createInside();
    }

    update(node) {
        if (node.type.name === 'image') {
            if (this.node.attrs.src !== node.attrs.src) {
                return false; // re-render
            }
            
            this.updateFromAttrs(node)
            this.node = node;
            return true;
        }
    }

    updateFromAttrs(node) {
        const { alt, width, height } = node.attrs;
        this.altInput.value = alt
        return true;
    }

    createInside() {
        const { src, alt, width, height } = this.node.attrs;

        const _self = this;
        if (src) {
            // render image
            const img = document.createElement("img");
            img.src = src;
            this.dom.appendChild(img)
            this.img = img;
            
            const altInput = document.createElement("input")
            altInput.className = "input alt-input"
            altInput.placeholder = "ALT Text..."
            this.dom.appendChild(altInput)
            altInput.value = alt

            altInput.oninput = function(e) {
                _self.view.dispatch(
                    _self.view.state.tr.setNodeMarkup(
                        _self.getPos(),
                        null,
                        {..._self.node.attrs, alt: e.target.value }
                    )
                )
            }
            this.altInput = altInput

        } else {
            // render image selector
            ReactDOM.render(<ImageUploader onUpload={this.handleUpload} />, this.dom);
        }
    }

    handleUpload(url, alt = null, unsplash = null) {
        const pos = this.getPos()
        
        const tr = this.view.state.tr.setNodeMarkup(
            pos,
            null,
            {...this.node.attrs, ...{src: url, alt}}
        )
        if (unsplash) {
            const nodeSel = NodeSelection.create(this.view.state.doc, pos + 1)
            
            const utm = "?utm_source=hyvor_blogs&utm_medium=referral"
            
            const newNode = this.schema.nodes.figcaption.create({}, [
                this.schema.text("Photo by "),
                this.schema.text(unsplash.author, [
                    this.schema.marks.link.create({
                        href: unsplash.authorUrl + utm
                    })
                ]),
                this.schema.text(" on "),
                this.schema.text("Unsplash", [
                    this.schema.marks.link.create({
                        href: "https://unsplash.com/" + utm
                    })
                ])
            ]);
            
            tr.replaceWith(
                nodeSel.from,
                nodeSel.to,
                newNode
            )
        }


        this.view.dispatch(tr)
    }

    stopEvent(e) {
        return e.target.isEqualNode(this.altInput)
    }

}

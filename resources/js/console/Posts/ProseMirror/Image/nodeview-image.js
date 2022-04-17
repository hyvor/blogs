import ReactDOM from 'react-dom';
import ImageUploader from './ImageUploader';

export default class Image {

    constructor(node, view, getPos) {
        this.node = node;
        this.view = view;
        this.getPos = getPos;

        const wrap = document.createElement("div")
        wrap.className = "image-wrap";

        this.dom = wrap;

        this.updateInside = this.updateInside.bind(this)
        this.handleUpload = this.handleUpload.bind(this)

        this.updateInside();
    }

    update(node) {
        /* if (node.type.name != "figcaption") return false
        if (node.content.size > 0) this.dom.classList.remove("empty")
        else this.dom.classList.add("empty")
        return true */
    }

    updateInside() {
        const { src, alt, title, width, height } = this.node.attrs;

        if (src) {
            // render image
            const img = document.createElement("img");
            img.src = src;
            this.dom.appendChild(img)

        } else {
            // render image selector

            ReactDOM.render(<ImageUploader onUpload={this.handleUpload} />, this.dom);
        }
    }

    handleUpload(url, alt = null) {
        this.view.dispatch(
            this.view.state.tr.setNodeMarkup(
                this.getPos(),
                null,
                {...this.node.attrs, ...{src: url, alt}}
            )
        )
    }

}

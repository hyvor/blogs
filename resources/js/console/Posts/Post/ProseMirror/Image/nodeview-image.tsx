import ReactDOM from 'react-dom';
import React from 'react';
import ImageUploader from './ImageUploader';
import {NodeSelection} from "prosemirror-state";
import {Node as ProsemirrorNode, Schema} from "prosemirror-model";
import {EditorView, NodeView} from "prosemirror-view";
import {UnsplashImage} from "../../../../types";

export type ImageUploadHandlerType = (url: string, alt?: string | null, unsplash?: UnsplashImage | null) => void;

type ImageNodeViewType = NodeView & {
    handleUpload: ImageUploadHandlerType;
}

export default class Image implements ImageNodeViewType {

    node: ProsemirrorNode;
    view: EditorView;
    getPos: () => number;
    schema: Schema;

    dom: HTMLElement;

    img: HTMLImageElement | undefined;
    altInput: HTMLInputElement | undefined;
    rangeInput: HTMLInputElement | undefined;

    constructor(schema: Schema, node: ProsemirrorNode, view: EditorView, getPos: () => number) {
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

    update(node: ProsemirrorNode) {
        if (node.type.name === 'image') {
            if (this.node.attrs.src !== node.attrs.src) {
                return false; // re-render
            }
            
            this.updateFromAttrs(node)
            this.node = node;
            return true;
        }

        return false;
    }

    updateFromAttrs(node: ProsemirrorNode) {
        const { alt, width, height } = node.attrs;

        if (!this.altInput || !this.img)
            return;

        this.altInput.value = alt

        if (width === null) {
            this.img.removeAttribute("width");
        } else {
            this.img.width = width;
        }

        if (height === null) {
            this.img.removeAttribute("height");
        } else {
            this.img.height = height;
        }

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
                    // @ts-ignore (PM Error)
                    _self.view.state.tr.setNodeMarkup(
                        _self.getPos(),
                        null,
                        {..._self.node.attrs, alt: (e.target as HTMLInputElement).value }
                    )
                )
            }
            this.altInput = altInput

            const rangeInput = document.createElement("input")
            rangeInput.type = 'range';
            rangeInput.min = "1"
            rangeInput.max = "100"
            rangeInput.value = "100"
            rangeInput.step = "1"

            rangeInput.oninput = function(e) {
                const value = parseInt((e.target as HTMLInputElement).value)
                let width, height
                if (value === 100) {
                    width = null;
                    height = null;
                } else {
                    width = (_self.img as HTMLImageElement).naturalWidth * value / 100
                    height = (_self.img as HTMLImageElement).naturalHeight * value / 100
                }
                
                _self.view.dispatch(
                    // @ts-ignore (PM Error)
                    _self.view.state.tr.setNodeMarkup(
                        _self.getPos(),
                        null,
                        {..._self.node.attrs, width, height }
                    )
                )
            }

            this.rangeInput = rangeInput

            this.dom.appendChild(rangeInput)

        } else {
            // render image selector
            ReactDOM.render(<ImageUploader onUpload={this.handleUpload} />, this.dom);
        }
    }

    handleUpload(url: string, alt: string | null = null, unsplash: UnsplashImage | null = null) {
        const pos = this.getPos()

        // @ts-ignore (PM Error)
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

    stopEvent(e: any) {
        return e.target.isEqualNode(this.altInput) || e.target.isEqualNode(this.rangeInput)
    }

}

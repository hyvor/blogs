import ReactDOM from 'react-dom';
import React from 'react';
import {NodeSelection, TextSelection} from "prosemirror-state";
import { Node, Node as ProsemirrorNode, Schema } from "prosemirror-model";
import { EditorView, NodeView } from "prosemirror-view";
import { UnsplashImage } from "../../../../types";
import mediaLogic from '../../../../logic/mediaLogic';
import getSubdomain from '../../../../logic-helpers/subdomain';
import { OnSelectProps } from '../../../../ReusableComponents/ImageUploader/ImageUploader';


export function selectImageGlobal() : Promise<OnSelectProps> {

    return new Promise((resolve, reject) => {

        const logic = mediaLogic({subdomain: getSubdomain()});
        logic.mount();
        logic.actions.setGlobalImageUploader({
            onSelect: props => {
                resolve(props);
            },
            onClose: () => {
                reject();
            }
        });

    })

}

export default class Image {

    node: ProsemirrorNode;
    view: EditorView;
    getPos: () => number | undefined;
    schema: Schema;

    dom: HTMLElement;

    img: HTMLImageElement | undefined;
    altInput: HTMLInputElement | undefined;
    rangeInput: HTMLInputElement | undefined;

    constructor(schema: Schema, node: ProsemirrorNode, view: EditorView, getPos: () => number | undefined) {

        this.node = node;
        this.view = view;
        this.getPos = getPos;
        this.schema = schema;

        const wrap = document.createElement("div")
        wrap.className = "image-wrap";

        this.dom = wrap;

        this.createInside = this.createInside.bind(this)
        this.handleChange = this.handleChange.bind(this)
        this.handleInputFocus = this.handleInputFocus.bind(this)

        this.createInside();
        this.updateFromAttrs(node);
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

        // render image
        const img = document.createElement("img");
        img.src = src;
        this.dom.appendChild(img)
        this.img = img;

        const changeButton = document.createElement("button");
        changeButton.className = "button mini change-image";
        changeButton.innerText = "Change";
        changeButton.onclick = async (e) => {

            e.stopPropagation();

            let uploadedImage;
            try {
                uploadedImage = await selectImageGlobal();
            } catch (e) {
                return;
            }

            this.handleChange(
                uploadedImage.url, 
                uploadedImage.unsplash?.alt,
                uploadedImage.unsplash
            );

        };
        this.dom.appendChild(changeButton);

        const altInput = document.createElement("input")
        altInput.className = "input alt-input"
        altInput.placeholder = "ALT Text..."
        this.dom.appendChild(altInput)
        altInput.value = alt

        altInput.oninput = function (e) {
            const pos = _self.getPos()
            if (pos === undefined)
                return;
            _self.view.dispatch(
                _self.view.state.tr.setNodeMarkup(
                    pos,
                    null,
                    { ..._self.node.attrs, alt: (e.target as HTMLInputElement).value }
                )
            )
        }
        this.altInput = altInput

        const rangeInput = document.createElement("input")
        rangeInput.type = 'range';
        rangeInput.min = "1"
        rangeInput.max = "100"
        rangeInput.value = width ? (width / this.img.naturalWidth * 100).toString() : "100";
        rangeInput.step = "1"

        rangeInput.oninput = function (e) {
            const value = parseInt((e.target as HTMLInputElement).value)
            let width, height
            if (value === 100) {
                width = null;
                height = null;
            } else {
                width = (_self.img as HTMLImageElement).naturalWidth * value / 100
                height = (_self.img as HTMLImageElement).naturalHeight * value / 100
            }

            const pos = _self.getPos()

            if (pos === undefined)
                return;

            _self.view.dispatch(
                _self.view.state.tr.setNodeMarkup(
                    pos,
                    null,
                    { ..._self.node.attrs, width, height }
                )
            )
        }

        this.rangeInput = rangeInput
        this.dom.appendChild(rangeInput)

    }

    handleInputFocus() {
        // unfocus prosemirror
        const { state, dispatch } = this.view;
        dispatch(
            state.tr.setSelection(
                TextSelection.create(state.doc, 0))
        );
    }

    handleChange(url: string, alt: string | null = null, unsplash: UnsplashImage | null = null) {
        const pos = this.getPos()

        if (pos === undefined)
            return;

        const tr = this.view.state.tr.setNodeMarkup(
            pos,
            null,
            { ...this.node.attrs, ...{ src: url, alt } }
        )
        if (unsplash) {
            const nodeSel = NodeSelection.create(this.view.state.doc, pos + 1)

            const utm = "?utm_source=hyvor_blogs&utm_medium=referral"

            const newNode = this.schema.nodes.figcaption.create({}, [
                this.schema.text("Photo by "),
                this.schema.text(unsplash.author, [
                    this.schema.marks.link.create({
                        href: unsplash.author_url + utm
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
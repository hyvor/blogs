import ReactDOM from 'react-dom';
import React, { useState } from 'react';
import {NodeSelection, TextSelection} from "prosemirror-state";
import { Node, Node as ProsemirrorNode, Schema } from "prosemirror-model";
import { EditorView, NodeView } from "prosemirror-view";
import { toast } from 'react-toastify';
import { useActions } from 'kea';
import mediaLogic from '../../../../../../resources/js/console/logic/mediaLogic';
import getSubdomain from '../../../../../../resources/js/console/logic-helpers/subdomain';
import Loader from '../../../../../../resources/js/console/ReusableComponents/Loader';
import api from '../../../../../../resources/js/console/lib/api';
import { Media } from '../../../../../../resources/js/console/types';
import { CloudUpload, Trash } from 'react-bootstrap-icons';


export default class Audio implements NodeView {
    node: ProsemirrorNode;
    view: EditorView;
    getPos: () => number | undefined;
    schema: Schema;

    dom: HTMLElement;

    constructor(schema: Schema, node: ProsemirrorNode, view: EditorView, getPos: () => number | undefined) {

        this.node = node;
        this.view = view;
        this.getPos = getPos;
        this.schema = schema;

        const wrap = document.createElement("div");
        wrap.className = "audio-wrap";

        const audio = document.createElement("audio");
        audio.setAttribute("controls", "controls");
        audio.setAttribute("src", node.attrs.src);

        const audioActions = document.createElement("div");
        audioActions.className = "audio-actions";

        const fileInputWrapper = document.createElement("label");
        fileInputWrapper.className = "button medium primary audio-upload-button";
        fileInputWrapper.innerHTML = node.attrs.src == null ? 'Upload' : 'Change';
        const fileInput = document.createElement("input");
        fileInput.className = "audio-input";
        fileInput.type = "file";
        fileInput.accept = "audio/*";
        fileInput.addEventListener("change", () => {
            this.handleFiles(fileInput.files);
        });
        const fileInputLabel = document.createElement("span");
        fileInputLabel.className = "audio-label-content";
        ReactDOM.render(<CloudUpload />, fileInputLabel);
        fileInputWrapper.appendChild(fileInputLabel);
        fileInputWrapper.appendChild(fileInput);
        audioActions.appendChild(fileInputWrapper);
        
        // Auto open file input if new node created
        if (node.attrs.src == null) {
            fileInput.click();
        }

        if (node.attrs.src != null) {
            const deleteButton = document.createElement("button");
            deleteButton.className = "button medium danger audio-delete-button";
            ReactDOM.render(<Trash />, deleteButton);
            deleteButton.addEventListener("click", () => {
                const { tr } = this.view.state;
                const pos = this.getPos();
                if (pos === undefined)
                    return;
                tr.delete(pos, pos + this.node.nodeSize);
                this.view.dispatch(tr);
            });

            audioActions.appendChild(deleteButton);
            wrap.appendChild(audioActions);
            wrap.appendChild(audio);

        }

        this.dom = wrap;
    }

    handleFiles(files: FileList | null) {
        if (!files || files.length === 0) {
            toast.error('No file selected')
            return
        } else if (files.length > 1) {
            toast.error('Select only one audio');
            return;
        }

        const file = files[0];
        this.handleFileUpload(file);
    }

    async handleFileUpload(file: File) {
        const subdomain = getSubdomain();

        if (file.size > 50 * 1000 * 1000) {
            toast.error("Max size is 50MB");
            return;
        }

        const validTypes = [
            'audio/mpeg',
            'audio/ogg',
            'audio/wav',
            'audio/webm'
        ];
        if (!validTypes.includes(file.type)) {
            toast.error('Only mp3, ogg, wav and webm files are allowed');
            return;
        }

        var formData = new FormData();
        formData.append('file', file, file.name);
        try {
            ReactDOM.render(<Loader />, this.dom);
            const media = await api.post<Media>(subdomain, '/media', formData);
            // Replace the node with the new one
            const { tr } = this.view.state;
            const pos = this.getPos();
            if (pos === undefined)
                return;
            tr.replaceWith(pos, pos + this.node.nodeSize, this.schema.nodes.audio.create({ src: media.url }));
            this.view.dispatch(tr);

        } catch (e) {
            toast.error('Error uploading file');
        }
    }

    update(node: ProsemirrorNode) {
        if (node.type.name === 'audio') {
            if (this.node.attrs.src !== node.attrs.src) {
                return false; // re-render
            }

            this.node = node;
            return true;
        }

        return false;
    }

}
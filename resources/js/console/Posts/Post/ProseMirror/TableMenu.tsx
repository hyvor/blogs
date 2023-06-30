import {Node as ProsemirrorNode, Schema} from "prosemirror-model";
import React from "react";
import { useState } from "react";

export default function TableMenu({ isFocused, selectedRow }: { isFocused: boolean, selectedRow: ProsemirrorNode }) {
    console.log('Rendering TableMenu');
    const [showMenu, setShowMenu] = useState(isFocused);

    if (!showMenu) {
        return <button
        onClick={() => setShowMenu(true)}>
            ...
        </button>
    } else {
        return <div className="table-menu">
            <button
                onClick={() => setShowMenu(true)}>
                ...
            </button>
            { showMenu && <div className="table-menu-options">
                <button
                    onClick={() => setShowMenu(false)}>
                    Close
                </button>
                <button
                    onClick={() => setShowMenu(false)}>
                    Add Row Above
                </button>
                <button
                    onClick={() => setShowMenu(false)}>
                    Add Row Below
                </button>
                <button
                    onClick={() => setShowMenu(false)}>
                    Add Column Before
                </button>
                <button
                    onClick={() => setShowMenu(false)}>
                    Add Column After
                </button>
                <button
                    onClick={() => setShowMenu(false)}>
                    Delete Row
                </button>
                <button
                    onClick={() => setShowMenu(false)}>
                    Delete Column
                </button>
            </div> }
        </div>
    }
}
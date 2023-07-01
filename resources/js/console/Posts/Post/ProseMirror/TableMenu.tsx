import {Node as ProsemirrorNode, Schema} from "prosemirror-model";
import React from "react";
import { useState } from "react";

export default function TableMenu({ row }: { row: ProsemirrorNode }) {
    console.log('Render');
    const [showMenu, setShowMenu] = useState(false);

    const toggleMenu = () => {
        setShowMenu(!showMenu);
        return false;
    }

   return !showMenu 
            ? <button
                onClick={() => setShowMenu(true)}>
                    ...
              </button>
        :
            <div className="table-menu-options">
                <button
                    onClick={toggleMenu}>
                    Close
                </button>
            </div>
}
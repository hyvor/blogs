import {Node as ProsemirrorNode, Schema} from "prosemirror-model";
import React from "react";
import { useState } from "react";

export default function TableMenu({ row }: { row: ProsemirrorNode }) {
    const [showMenu, setShowMenu] = useState(false);
    console.log('showMenu', showMenu);

    const toggleMenu = (event: React.MouseEvent<HTMLButtonElement, MouseEvent>) => {
        setShowMenu(!showMenu);
        event.stopPropagation();
        return false;
    }

   return !showMenu 
            ? <button
                onClick={(event) => toggleMenu(event)}>
                    ...
              </button>
        :
            <div className="table-menu-options">
                <button
                    onClick={(event) => toggleMenu(event)}>
                    Close
                </button>
            </div>
}
import { Node as ProsemirrorNode, Schema } from "prosemirror-model";
import { EditorState, Transaction } from "prosemirror-state";
import React from "react";
import { useState } from "react";

export default function TableMenu({ rowIdx, rowFocused, addRowBeforeWrapper }: { rowIdx: number, rowFocused: boolean,addRowBeforeWrapper: () => void }) {
  const [showMenu, setShowMenu] = useState(false);

  const toggleMenu = (event: React.MouseEvent<HTMLButtonElement, MouseEvent>) => {
    setShowMenu(!showMenu);
    event.stopPropagation();
    return false;
  };

  const handleClose = () => {
    setShowMenu(false);
  };

  console.log('rowFocused', rowFocused)

  return (
    <div className="table-menu">
      <button className="icon-button toggle-table-menu-button" 
      onClick={toggleMenu}
      disabled={!rowFocused}
      >
        ...
      </button>
      {showMenu && (
        <div className="table-menu-options">
          <button onClick={addRowBeforeWrapper}>Add row above</button>
          <button onClick={handleClose}>Close</button>
        </div>
      )}
    </div>
  );
}

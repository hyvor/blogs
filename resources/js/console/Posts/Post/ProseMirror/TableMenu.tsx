import { Node as ProsemirrorNode, Schema } from "prosemirror-model";
import { EditorState, Transaction } from "prosemirror-state";
import React from "react";
import { useState } from "react";
import { ArrowDown, ArrowUp, CardHeading, Trash } from "react-bootstrap-icons";

export default function TableMenu({ rowIdx, rowFocused, addRowBeforeWrapper, addRowAfterWrapper, makeRowHeaderWrapper, clearContentWrapper, deleteRowWrapper }: 
    { rowIdx: number, rowFocused: boolean,addRowBeforeWrapper: () => void, addRowAfterWrapper: () => void, 
        makeRowHeaderWrapper: () => void ,clearContentWrapper: () => void, deleteRowWrapper: () => void}) {
  const [showMenu, setShowMenu] = useState(false);

  console.log(showMenu);

  const toggleMenu = (event: React.MouseEvent<HTMLButtonElement, MouseEvent>) => {
    setShowMenu(!showMenu);
    event.stopPropagation();
  };

  const handleClose = () => {
    setShowMenu(false);
  };


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
          <button onClick={handleClose} className="icon-button close-table-menu">X</button>
          <button className="action-button" onClick={makeRowHeaderWrapper}>
            <CardHeading className="table-menu-icon"/>
            Header Row
          </button>
          <button className="action-button" onClick={addRowBeforeWrapper}>
            <ArrowUp className="table-menu-icon"/>
            Insert Above
          </button>
          <button className="action-button" onClick={addRowAfterWrapper}>
            <ArrowDown className="table-menu-icon"/>
            Insert Below
          </button>
          <button className="action-button" onClick={deleteRowWrapper}>
            <Trash className="table-menu-icon"/>
            Delete row
          </button>
          <button className="action-button" onClick={clearContentWrapper}>
            Clear content
          </button>
        </div>
      )}
    </div>
  );
}

import { Node as ProsemirrorNode, Schema } from "prosemirror-model";
import { EditorState, Transaction } from "prosemirror-state";
import React from "react";
import { useState } from "react";
import {
  addColumnAfter,
  addColumnBefore,
  deleteColumn,
  addRowAfter,
  addRowBefore,
  deleteRow,
  mergeCells,
  splitCell,
  setCellAttr,
  toggleHeaderRow,
  toggleHeaderColumn,
  toggleHeaderCell,
  goToNextCell,
  deleteTable,
} from "prosemirror-tables";

export default function TableMenu({ row, editorState, transaction }: { row: ProsemirrorNode, editorState: EditorState, transaction: (tr: Transaction) => void }) {
  const [showMenu, setShowMenu] = useState(false);

  const toggleMenu = (event: React.MouseEvent<HTMLButtonElement, MouseEvent>) => {
    setShowMenu(!showMenu);
    event.stopPropagation();
    return false;
  };

  const handleClose = () => {
    setShowMenu(false);
  };

  const addRowAbove = () => {
    console.log('Editor state', editorState);
    console.log('Transaction', transaction);
    addRowBefore(editorState, transaction);
  };

  return (
    <div className="table-menu">
      <button className="icon-button toggle-table-menu-button" onClick={toggleMenu}>...</button>
      {showMenu && (
        <div className="table-menu-options">
          <button onClick={addRowAbove}>Add row above</button>
          <button onClick={handleClose}>Close</button>
        </div>
      )}
    </div>
  );
}

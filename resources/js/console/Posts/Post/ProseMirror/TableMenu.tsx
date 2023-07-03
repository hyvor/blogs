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

export default function TableMenu({ row, addRowBeforeWrapper }: { row: ProsemirrorNode, addRowBeforeWrapper: () => void }) {
  const [showMenu, setShowMenu] = useState(false);

  const toggleMenu = (event: React.MouseEvent<HTMLButtonElement, MouseEvent>) => {
    setShowMenu(!showMenu);
    event.stopPropagation();
    return false;
  };

  const handleClose = () => {
    setShowMenu(false);
  };

  return (
    <div className="table-menu">
      <button className="icon-button toggle-table-menu-button" onClick={toggleMenu}>...</button>
      {showMenu && (
        <div className="table-menu-options">
          <button onClick={addRowBeforeWrapper}>Add row above</button>
          <button onClick={handleClose}>Close</button>
        </div>
      )}
    </div>
  );
}

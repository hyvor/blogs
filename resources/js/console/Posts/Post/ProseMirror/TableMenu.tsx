import { Node as ProsemirrorNode, Schema } from "prosemirror-model";
import { EditorState, Transaction } from "prosemirror-state";
import React from "react";
import { useState } from "react";
import { ArrowDown, ArrowLeft, ArrowRight, ArrowUp, Backspace, CardHeading, Trash } from "react-bootstrap-icons";

export default function TableMenu({ colunmMenu, focused, addBefore, addAfter, makeHeader, clearContent, deleteWrapper }: 
    { colunmMenu: boolean, focused: boolean, addBefore: () => void, addAfter: () => void, 
        makeHeader: () => void ,clearContent: () => void, deleteWrapper: () => void}) {

  const [showMenu, setShowMenu] = useState(false);

  const toggleMenu = (event: React.MouseEvent<HTMLButtonElement, MouseEvent>) => {
    setShowMenu(!showMenu);
    event.stopPropagation();
  };

  const handleClose = () => {
    setShowMenu(false);
  };


  return !focused ? (<div></div>) : (
    <div className="table-menu">
      <button className="toggle-table-menu-button" 
      onClick={toggleMenu}
      disabled={!focused}
      >
        ...
      </button>
      {showMenu && (
        <div className="table-menu-options">
          <button onClick={handleClose} className="icon-button close-table-menu">x</button>
          <button className="action-button" onClick={makeHeader}>
            <CardHeading className="table-menu-icon"/>
            {colunmMenu ? 'Header Column' : 'Header Row'}
          </button>
          <button className="action-button" onClick={addBefore}>
            {colunmMenu ? <ArrowLeft className="table-menu-icon"/> : <ArrowUp className="table-menu-icon"/>}
            {colunmMenu ? 'Insert Before' : 'Insert Above'}
          </button>
          <button className="action-button" onClick={addAfter}>
            {colunmMenu ? <ArrowRight className="table-menu-icon"/> : <ArrowDown className="table-menu-icon"/>}
            {colunmMenu ? 'Insert After' : 'Insert Below'}
          </button>
          <button className="action-button" onClick={deleteWrapper}>
            <Trash className="table-menu-icon"/>
            {colunmMenu ? 'Delete column' : 'Delete row'}
          </button>
          <button className="action-button" onClick={clearContent}>
            <Backspace className="table-menu-icon"/>
            Clear content
          </button>
        </div>
      )}
    </div>
  );
}

import { Node as ProsemirrorNode, Schema } from "prosemirror-model";
import { EditorState, Transaction } from "prosemirror-state";
import React, { useEffect, useRef } from "react";
import { useState } from "react";
import { ArrowDown, ArrowLeft, ArrowRight, ArrowUp, Backspace, CardHeading, Trash } from "react-bootstrap-icons";

export default function TableMenu({ colunmMenu, focused, addBefore, addAfter, makeHeader, clearContent, deleteWrapper, cssOffset}: 
    { colunmMenu: boolean, focused: boolean, addBefore: () => void, addAfter: () => void, 
        makeHeader: () => void ,clearContent: () => void, deleteWrapper: () => void, cssOffset: number}) {

  const [showMenu, setShowMenu] = useState(false);
  const menuRef = useRef<HTMLDivElement>(null);

  const toggleMenu = (event: React.MouseEvent<HTMLButtonElement, MouseEvent>) => {
    setShowMenu(!showMenu);
    event.stopPropagation();
  };

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (menuRef.current && !menuRef.current.contains(event.target as Node)) {
        setShowMenu(false);
      }
    };

    window.addEventListener("click", handleClickOutside);

    return () => {
      window.removeEventListener("click", handleClickOutside);
    };
  }, []);

  const customStyle = !colunmMenu ? {top: cssOffset + 'px'} : {left: cssOffset + 'px'};
  if (colunmMenu)
    customStyle['bottom'] = '-27px';
  else
    customStyle['left'] = '20px';
  

  return !focused ? (<div></div>) : (
    <div className={'table-menu'} style={customStyle} ref={menuRef}>
      <button className="toggle-table-menu-button" 
      onClick={toggleMenu}
      disabled={!focused}
      style={{width: colunmMenu ? "35px" : "20px", height: colunmMenu ? "20px" : "30px", flexDirection: colunmMenu ? "row" : "column"}}
      >
        <span className="table-menu-dot"/>
        <span className="table-menu-dot"/>
        <span className="table-menu-dot"/>
      </button>
      {showMenu && (
        <div className="table-menu-options">
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

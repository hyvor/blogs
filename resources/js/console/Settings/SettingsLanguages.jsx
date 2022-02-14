import { useActions, useValues } from 'kea';
import React from 'react';
import { useState } from 'react';
import { PencilFill, Plus, Trash, TrashFill } from 'react-bootstrap-icons';
import numberFormatter from '../../helpers/numberFormatter';
import languagesLogic from '../logic/languagesLogic';
import subdomainLogic from '../logic/subdomainLogic';
import Input from '../ReusableComponents/Input';
import Loader from '../ReusableComponents/Loader';
import { Popup, PopupBodyDefault, PopupConfirm, PopupFooterDoubleButton, PopupHeaderDefault } from '../ReusableComponents/Popup';

export default function SettingsLanguages() {

    const { subdomain } = useValues(subdomainLogic);
    const languageLogicInst = languagesLogic({subdomain});
    const { languages, loadAjax } = useValues(languageLogicInst);
    const { create, update, remove } = useActions(languageLogicInst);

    const [ isCreating, setIsCreating ] = useState(false);

    return <div className="setting-languages">
        <div className="title">
            Languages
        </div>

        <div className="languages-view">
            {
                loadAjax.status === 'loading' ?
                <Loader 
                    padding={100}
                /> :
                <div className="lang-table">

                    <div className="lang-table-header">      
                        <div>Name</div> 
                        <div>Code</div>
                        <div>No. of Posts</div>
                        <div></div>
                    </div>

                    <div className="lang-table-body">
                        { languages.map(lang => <Language 
                            key={lang.id} 
                            lang={lang} 
                            update={update} 
                            remove={remove} 
                        />) }
                    </div>

                    <div className="lang-add">
                        <button 
                            className="button small light" 
                            onClick={() => setIsCreating(true)}
                        >Add Language <Plus /></button>
                    </div>

                    {
                        isCreating ?
                        <CreateUpdatePopup create={create} onCancel={() => setIsCreating(false)} /> :
                        null
                    }

                </div>
            }
        </div>
    </div>

}

function Language({lang, update, remove}) {

    const [isEditing, setIsEditing] = useState(false);
    const [isDeleting, setIsDeleting] = useState(false);

    function handleDelete() {
        remove({id: lang.id});
    }

    return <div className={"lang" + (lang.is_default ? " default" : "")}>
        <div className="lang-item">
            <span>{lang.name}</span>
            {
                lang.is_default ?
                <span className="default-tag">DEFAULT</span>
                : null
            }
        </div>
        <div className="lang-item">{lang.code}</div>
        <div className="lang-item">{ numberFormatter(3253, 'comma') }</div>
        <div className="lang-actions">
            <div className="lang-edit">
                <span className="lang-button" onClick={() => setIsEditing(true)}>
                    <PencilFill size={10} />
                </span>
                {
                    isEditing ? 
                    <CreateUpdatePopup 
                        update={update}
                        lang={lang}
                        onCancel={() => setIsEditing(false)}
                    /> : null
                }
            </div>
            <div className="lang-delete">
                <span className="lang-button" onClick={() => setIsDeleting(true)}>
                    <TrashFill size={10} />
                </span>
                {
                    isDeleting ?
                    <PopupConfirm
                        title="Delete Language"
                        text="Are you sure to delete this language from this blog?"
                        name="Delete"
                        buttonClass="danger"
                        onClick={handleDelete}
                        onCancel={() => setIsDeleting(false)}
                    /> : null
                }
            </div>
        </div>
    </div>

}

function CreateUpdatePopup({ create, update, lang, onCancel }) {

    const [ name, setName ] = useState(lang ? lang.name : "");
    const [ code, setCode ] = useState(lang ? lang.code : "");

    const [isLoading, setIsLoading] = useState(false);

    function handleClick() {
        setIsLoading(true);

        const after = () => {
            setIsLoading(false);
            onCancel();
        }

        if (create) {
            create({name, code, onCreate: after});
        } else {
            update({id: lang.id, name, code, onUpdate: after});
        }
    }

    return <Popup
        header={<PopupHeaderDefault title={create ? "Add Language" : "Update Language"} />}
        body={<PopupBodyDefault>
            <div className="lang-add-popup-body">
                <Input 
                    title="Name"
                    type="text"
                    name="name"
                    autocomplete={false}
                    value={name}
                    onChange={setName}
                    maxLength={50}
                    autoFocus={true}
                    placeholder="English"
                />
                <Input 
                    title="Code"
                    type="text"
                    name="language-code"
                    autocomplete={false}
                    value={code}
                    onChange={setCode}
                    maxLength={12}
                    placeholder="en"
                />
            </div>
        </PopupBodyDefault>}
        footer={
            <PopupFooterDoubleButton
                onCancel={onCancel}
                onClick={handleClick}
                name={ create ? "Add" : "Update" }
                loadingName={ create ? "Adding" : "Updating"}
                isLoading={isLoading}
            />
        }
    />

}
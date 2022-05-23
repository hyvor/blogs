import React, {useState} from 'react';
import { useActions, useValues } from 'kea';
import { Trash, PencilFill, Plus, BoxArrowInRight, CodeSlash} from 'react-bootstrap-icons';
import {toast} from 'react-toastify'
import subdomainLogic from '../../logic/subdomainLogic';
import tagsLogic from '../../logic/tagsLogic';
import Input from '../../ReusableComponents/Input';
import { Popup, PopupBodyDefault, PopupConfirm, PopupFooterDoubleButton, PopupHeaderDefault } from '../../ReusableComponents/Popup';


export default function CreateTag(props) 
{
    const subdomain = subdomainLogic.values.subdomain;
    const tagLogicBuilt = tagsLogic({subdomain})
    const { create } = useActions(tagLogicBuilt)  

    const [createPopUpOpened, setCreatePopUpOpened] = useState(false);

    function handleCreateCancel(){
        setCreatePopUpOpened(false)
    }
    function handleCreate(e) {
        e.preventDefault();
        setCreatePopUpOpened(true);
    }

    const [ name, setName ] = useState();
    const [ slug, setSlug ] = useState();
    const [ description, setDescription ] = useState();

    function onNameChange(val) {
        setName(val);
        var slug = val.toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/-+/g, '-').replace(/(^-|-$)/g, '');
        setSlug(slug);
    }

    function onSlugChange(val){
        val = val.toLowerCase();
        setSlug(val);

        var allowedRegex = /[^a-z0-9-]/;

        if (val.substr(0, 1) === '-') {
            setSlugError('Cannot start with -');
        } else if (val.substr(val.length - 1) === '-') {
            setSlugError('Cannot end with -');
        } else if (val.match(allowedRegex)) {
            const firstLetter = val.match(allowedRegex)[0]
            setSlugError('Cannot contain ' + firstLetter);
        } else {
            setSlugError(null)
        }
    }

    function onDescriptionChange(e) {
        setDescription(e.target.value);
    }

    function submitTag (e) {
        e.preventDefault();
        create({
            name: name,
            slug: slug,
            description: description,
        });
        setName();
        setSlug();
        setDescription();
        setCreatePopUpOpened(false)
    }

    return <div>
        {
            <div className="tag-title-create-button ">
                <button type='button' className ="button small inactive tag-button-popup" onClick={handleCreate}>
                    <div className="popup-button-content">
                        <div className="popup-button-content-text">Create</div> 
                        <Plus />
                    </div>
                </button>
            </div>
        }
        {
            createPopUpOpened ?
                <Popup
                    header={<PopupHeaderDefault title='Create New Tag' />}
                    body={
                        <PopupBodyDefault>
                            <div>
                                    
                                <Input 
                                    title="Name"
                                    type="text"
                                    name="name"
                                    value={name}
                                    onChange={onNameChange}
                                    placeholder="Name"
                                />
                                <Input 
                                    title="Slug"
                                    type="text"
                                    name="url"
                                    value={slug}
                                    onChange={onSlugChange}
                                    placeholder="Slug"
                                />

                                <div className="popup-type-margin">Description</div>
                                <textarea 
                                    className="input"
                                    title="Description"
                                    type="text"
                                    name="description"
                                    value={description}
                                    onChange={onDescriptionChange}
                                    placeholder="Description"
                                ></textarea>
                            </div>
                        </PopupBodyDefault>
                    }
                    footer={
                        <PopupFooterDoubleButton
                            onCancel={handleCreateCancel}
                            onClick={(e)=> {submitTag(e)}}
                            name='Create'
                        />
                    }
                /> 
            : null
        }
    </div>
}
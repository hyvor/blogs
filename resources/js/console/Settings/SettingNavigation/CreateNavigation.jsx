import React, {useState} from 'react';
import { useActions, useValues } from 'kea';
import { Trash, PencilFill, Plus, BoxArrowInRight, CodeSlash} from 'react-bootstrap-icons';
import {toast} from 'react-toastify'
import subdomainLogic from '../../logic/subdomainLogic';
import navigationLogic from '../../logic/navigationLogic';
import Input from '../../ReusableComponents/Input';
import { Popup, PopupBodyDefault, PopupConfirm, PopupFooterDoubleButton, PopupHeaderDefault } from '../../ReusableComponents/Popup';


export default function CreatePopup({type})
{
    const subdomain = subdomainLogic.values.subdomain;
    const navigationLogicBuilt = navigationLogic({subdomain})
    const { create } = useActions(navigationLogicBuilt)

    const [createPopUpOpened, setCreatePopUpOpened] = useState(false);

    function handleCreateCancel(){
        console.log(type)
        setCreatePopUpOpened(false)
    }

    function handleCreate(e) {
        e.preventDefault();
        setCreatePopUpOpened(true);
    }

    const [ name, setName ] = useState();
    const [ url, setUrl ] = useState();

    // const [createNewNavigation, setData] = useState({type: ""});
    // const selectOptions = [
    //     { label: 'Header', value: 'header' },
    //     { label: 'Footer', value: 'footer'}
    // ];
    // function handleType({value}){
    //     console.log(value)
    //     setData({...createNewNavigation, 
    //         type: value
    //     })
    // }

    // OnClick handler for creating an new navigation
    function submitNavigationData (e) {
        console.log(type)
        e.preventDefault();
        if (!name) {
            return toast.error("Name should not be empty");
        }
        if (!url) {
            return toast.error("Url should not be empty");
        }
        create({
            name: name,
            url: url,
            type: type,
        });
        setName();
        setUrl();
        setCreatePopUpOpened(false)
    }

    return <div>
            <div className="navigation-title-create-button ">
            <button type='button' className ="button small inactive navigation-button-popup" onClick={handleCreate}>
                <div className="popup-button-content">
                    <div className="popup-button-content-text">Create</div> 
                    <Plus />
                </div>
            </button>
            </div>
            {
                createPopUpOpened ?
                    <Popup
                        header={<PopupHeaderDefault title='Create Navigation' />}
                        body={
                            <PopupBodyDefault>
                                <div>
                                    <Input 
                                        title="Name"
                                        type="text"
                                        name="name"
                                        value={name}
                                        onChange={setName}
                                    />
                                    <Input 
                                        title="Url"
                                        type="text"
                                        name="url"
                                        value={url}
                                        onChange={setUrl}
                                    />
                                </div>
                            </PopupBodyDefault>
                        }
                        footer={
                            <PopupFooterDoubleButton
                                onCancel={handleCreateCancel}
                                onClick={(e)=> {submitNavigationData(e)}}
                                name='Create'
                            />
                        }
                    /> 
                : null
            }
    </div>
}
import React, {useState, useEffect} from 'react';
import { useActions, useValues } from 'kea';
import { Trash, PencilFill, Plus, BoxArrowInRight, CodeSlash, Link} from 'react-bootstrap-icons';
import {toast} from 'react-toastify'
import navigationLogic from '../../logic/navigationLogic';
import languagesLogic from '../../logic/languagesLogic';
import blogsLogic from '../../logic/blogsLogic';
import Input from '../../ReusableComponents/Input';
import { Popup, PopupBodyDefault, PopupConfirm, PopupFooterDoubleButton, PopupHeaderDefault } from '../../ReusableComponents/Popup';
import DualSetting from '../../ReusableComponents/DualSetting';
import CodemirrorEditor, { CODEMIRROR_MODES } from '../../ReusableComponents/CodemirrorEditor';
import NavigationLanguageSelector from './NavigationLanguageSelector';


export default function Navigation ({id, name, url, type}) 
{
    const subdomain = subdomainLogic.values.subdomain;
    const navigationLogicBuilt = navigationLogic({subdomain})
    const { remove , updateData} = useActions(navigationLogicBuilt)
    const { updateDataAjax } = useValues(navigationLogicBuilt)

    // Styles
    const [styleDeleteIcon, setStyleDeleteIcon] = useState("table-button");
    const [styleUpdateIcon, setStyleUpdateIcon] = useState("table-button");

    // delete section
    const [deletePopupOpened, setDeletePopupOpened] = useState(false);

    function handleDelete(e) {
        e.preventDefault();
        setStyleDeleteIcon("table-delete-popup");
        setDeletePopupOpened(true);
    }
    function handleDoDelete() {
        console.log(id);
        toast("File deleted", {autoClose: 1500});
        remove({id});
        setStyleDeleteIcon("table-button");
        setDeletePopupOpened(false);
    }
    function handleDeleteCancel(){
        setStyleDeleteIcon("table-button");
        setDeletePopupOpened(false)
    }

    // Update Navigation Sections
    const [updateNavigationData, setUpdate] = useState({
        userId: id,
        type: type
    })
    const [updateFormOpened, setUpdateFormOpened] = useState(false);
    const [updateNavigationName, setName] = useState(name)
    const [updateNavigationUrl, setUrl] = useState(url)

    function handleUpdate(e){
        e.preventDefault();
        setStyleUpdateIcon('table-update-popup')
        setUpdateFormOpened(true);
    }
    function handleCancelUpdate(){
        setStyleUpdateIcon('table-button')
        setUpdateFormOpened(false);
    }

    // function handleNavigationName(e) {
    //     e.preventDefault();
    //     setUpdate({...updateNavigationData, 
    //         name: e.target.value
    //     })
    //     if(updateNavigationData.name != e.target.value){
    //         setStyleUpdateIcon("icon-navigation navigation-edit");
    //     }
    //     if(updateNavigationData.name === name){
    //         setStyleUpdateIcon("hide-navigation-edit");
    //     }
    // }

    // function handleNavigationUrl(e) {
    //     e.preventDefault();
    //     setUpdate({...updateNavigationData, 
    //         url: e.target.value
    //     })
    //     if(updateNavigationData.url != e.target.value){
    //         setStyleUpdateIcon("icon-navigation navigation-edit");
    //     }else{
    //         setStyleUpdateIcon("hide-navigation-edit");
    //     }
    // }

    // This is the OnClick handler for the update
    function updateNavigation(e){
        e.preventDefault();
        console.log(type);
        updateData({
            userId: updateNavigationData.userId,
            name: updateNavigationName,
            url: updateNavigationUrl,
            type:updateNavigationData.type,
        });
        setUpdate({...updateNavigationData, 
            userId: id,
            type:type
        })
        setName(name);
        setUrl(url);
        window.location.reload(false);
        setStyleUpdateIcon('table-button')
        setUpdateFormOpened(false);
    }
    

    return <div>
        <div className="global-table-body">
            <div className="table-body-three">
                {/* <span className="get-navigation-name">About</span>
                <span className="get-navigation-url">https://sipes.com/quisquam-eos-eos-nulla-vel-minima-amet.html/dddd/ffff/gggg</span>
                <input className="get-navigation-name" value={updateNavigationData.name}  onChange={(e)=> {handleNavigationName(e)}} />
                <input className="get-navigation-url" value={updateNavigationData.url} onChange={(e)=> {handleNavigationUrl(e)}}/> */}
                <div className="table-item"> 
                    {/* <div className="sort">
                        <div className="sort-icon"
                            ref={provided.innerRef} 
                            {...provided.draggableProps} 
                            {...provided.dragHandleProps}
                        >
                            <HddStackFill size={10} />
                        </div>
                        {name}
                    </div> */}
                    {name}
                </div>
                <div className="table-item"> {url} </div>
                <div className="table-actions">
                    <div className="table-edit" onClick={handleUpdate}>
                        <span className={styleUpdateIcon}>
                            <PencilFill size={10} />
                        </span>
                    </div>
                    <div className="table-delete" onClick={handleDelete}>
                        <span className={styleDeleteIcon}>
                            <Trash size={10} />
                        </span>
                    </div>
                </div>
            </div>
        </div>
        {
            updateFormOpened ? 
            <Popup
                header={<PopupHeaderDefault title='Update Navigation' />}
                body={
                    <PopupBodyDefault>
                        <div>
                            <Input 
                                title="Name"
                                type="text"
                                name="name"
                                value={updateNavigationName}
                                onChange={setName}
                                placeholder="Match Url"
                            />
                            <Input 
                                title="Url"
                                type="text"
                                name="url"
                                value={updateNavigationUrl}
                                onChange={setUrl}
                                placeholder="Redirect Url"
                            />
                        </div>
                    </PopupBodyDefault>
                }
                footer={
                    <PopupFooterDoubleButton
                        onCancel={handleCancelUpdate}
                        onClick={(e)=> {updateNavigation(e)}}
                        name='Update'
                    />
                }
            /> 
            : null
        }
        {
            deletePopupOpened ?
                <PopupConfirm
                    title="Delete Permanently"
                    text="Are you sure you won't to delete this navigation."
                    name="Delete"
                    buttonClass="danger"
                    onClick={handleDoDelete}
                    // onCancel={() => setDeletePopupOpened(false)}
                    onCancel={handleDeleteCancel}
                />
            : null
        }
    </div>
}
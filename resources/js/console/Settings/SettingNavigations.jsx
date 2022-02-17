import React, {useState, forwardRef} from 'react';
import { useActions, useValues } from 'kea';
import subdomainLogic from '../logic/subdomainLogic';
import navigationLogic from '../logic/navigationLogic';
import { Trash, PencilFill, CheckCircleFill, Plus} from 'react-bootstrap-icons';
import Loader from '../ReusableComponents/Loader';
import {toast} from 'react-toastify'
import Select from '../ReusableComponents/Select';
import Toast from '../ReusableComponents/Toast';
import NoResults from '../ReusableComponents/NoResults';
import { Popup, PopupBodyDefault, PopupConfirm, PopupFooterDoubleButton, PopupHeaderDefault } from '../ReusableComponents/Popup';
import Input from '../ReusableComponents/Input';
import { components } from 'react-select';
import { ReactSortable } from "react-sortablejs";


export default function SettingNavigations(props) {

    const subdomain = subdomainLogic.values.subdomain;
    const navigationLogicBuilt = navigationLogic({subdomain})
    const { navigation, loadAjax, createAjax } = useValues(navigationLogicBuilt)

    return <div className="navigation-view">
        <div className="navigation-title-bar">
            <div className="navigation-title">
                    Navigation
            </div>
            <CreatePopup/>
            {
                createAjax.status === 'error' ?
                    <Toast 
                        x={console.log(createAjax.error)}
                        text={createAjax.error}
                        type="error"
                    /> 
                : null
            }
        </div>
        {
            loadAjax.status === 'loading' ?
                <Loader/> :
                (
                    <div className="global-table-view">
                        {
                            // navigation.length > 0 && (
                            navigation.length > 0 ?
                                <div>
                                    <div className="navigation-sub-title">Header Navigation</div>
                                    {
                                        navigation.map(navigation => (
                                            navigation.type === 'header' ?
                                                <div>
                                                    {
                                                        <Navigation id ={navigation.id} name = {navigation.name} url = {navigation.url} type = {navigation.type}/>
                                                    }
                                                </div>
                                        : <div></div>
                                    ))}
                                </div> : 
                                <NoResults 
                                    text="There is no any redirects."
                                    padding={40}
                                    imageWidth={250}
                                />
                            // )
                        } 
                            
                        {
                            navigation.length > 0 && (
                                <div>
                                    <div className="navigation-sub-title">Footer Navigation</div>
                                    {
                                        navigation.map(navigation => (
                                            navigation.type === 'footer' ?
                                                <div>
                                                    {
                                                        <Navigation id ={navigation.id} name = {navigation.name} url = {navigation.url} type = {navigation.type}/>
                                                    }
                                                </div>
                                            : null
                                        ))
                                    }
                                </div>
                            )
                        } 
                    </div>
                )
        }
    </div>
}

function CreatePopup() {

    const subdomain = subdomainLogic.values.subdomain;
    const navigationLogicBuilt = navigationLogic({subdomain})
    const { create } = useActions(navigationLogicBuilt)

    const [createPopUpOpened, setCreatePopUpOpened] = useState(false);

    function handleCreateCancel(){
        setCreatePopUpOpened(false)
    }

    function handleCreate(e) {
        e.preventDefault();
        setCreatePopUpOpened(true);
    }

    const [ name, setName ] = useState();
    const [ url, setUrl ] = useState();
    const [createNewNavigation, setData] = useState({type: ""});

    const selectOptions = [
        { label: 'Header', value: 'header' },
        { label: 'Footer', value: 'footer'}
    ];

    function handleType({value}){
        console.log(value)
        setData({...createNewNavigation, 
            type: value
        })
    }

    // OnClick handler for creating an new navigation
    function submitNavigationData (e) {
        e.preventDefault();
        console.log(createNewNavigation.type)
        create({
            name: name,
            url: url,
            type: createNewNavigation.type,
        });
        setName();
        setUrl();
        setData({
            type:""
        });

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
                                    <div className="create-navigation-select">
                                        <div className="popup-type-margin">Type</div>
                                        <SelectType 
                                            title="Type"
                                            options={selectOptions} 
                                            onChange={handleType}
                                        />
                                    </div>
                                </div>
                            </PopupBodyDefault>
                        }
                        footer={
                            <PopupFooterDoubleButton
                                onCancel={handleCreateCancel}
                                onClick={(e)=> {submitNavigationData(e)}}
                                name='Create'
                                // loadingName='Create'
                                // isLoading={isLoading}
                            />
                        }
                    /> 
                : null
            }
    </div>
}


function Navigation ({id, name, url, type}){

    const subdomain = subdomainLogic.values.subdomain;
    const navigationLogicBuilt = navigationLogic({subdomain})
    const { remove , updateData} = useActions(navigationLogicBuilt)
    const { updateDataAjax } = useValues(navigationLogicBuilt)

    // Styles
    const [styleDeleteIcon, setStyleDeleteIcon] = useState("table-button");
    // const [styleUpdateIcon, setStyleUpdateIcon] = useState("hide-navigation-edit");

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
        setUpdateFormOpened(true);
    }
    function handleCancelUpdate(){
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
        setUpdateFormOpened(false);
    }
    

    return <div>
        <div className="global-table-body">
            <div className="table-body-three">
                {/* <span className="get-navigation-name">About</span>
                <span className="get-navigation-url">https://sipes.com/quisquam-eos-eos-nulla-vel-minima-amet.html/dddd/ffff/gggg</span>
                <input className="get-navigation-name" value={updateNavigationData.name}  onChange={(e)=> {handleNavigationName(e)}} />
                <input className="get-navigation-url" value={updateNavigationData.url} onChange={(e)=> {handleNavigationUrl(e)}}/> */}
                <div className="table-item"> {name}</div>
                <div className="table-item"> {url} </div>
                <div className="table-actions">
                    <div className="table-edit" onClick={handleUpdate}>
                        <span className="table-button">
                            <PencilFill size={15} />
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




































{/* <div className="global-table-view">


<div className="global-table-header-four">      
                        <div className="table-head-item">Name</div> 
                        <div className="table-head-item">Code</div>
                        <div>No. of Posts</div>
                        <div></div>
                    </div>


 <div className="global-table-body">
<div className="table-body-four"> 
        <div className="table-item">
            <span>About</span>
        </div>
        <div className="table-item">Hi</div>
        <div className="table-item">Test</div>
        <div className="table-actions">
            <div className="table-edit">
                <span className="table-button">
                    <Trash size={10} />
                </span>
            </div>
            <div className="table-delete">
                <span className="table-delete-popup">
                    <Plus size={10} />
                </span>
            </div>
        </div>
    </div>
    </div>

</div> */}































 </div>







    // return <div>
    //    <div>
    //         <div className="get-navigation">
    //             {/* <span className="get-navigation-name">About</span>
    //             <span className="get-navigation-url">https://sipes.com/quisquam-eos-eos-nulla-vel-minima-amet.html/dddd/ffff/gggg</span> */}
    //             <input className="get-navigation-name" value={updateNavigationData.name}  onChange={(e)=> {handleNavigationName(e)}} />
    //             <input className="get-navigation-url" value={updateNavigationData.url} onChange={(e)=> {handleNavigationUrl(e)}}/>
    //             <button className={styleDeleteIcon} onClick={handleDelete}><Trash size={15} /></button>
    //             <button className={styleUpdateIcon} onClick={updateNavigation}><CheckCircleFill size={15} /></button>
    //         </div>
    //     </div>
    //     {
    //         deletePopupOpened ?
    //             <PopupConfirm
    //                 title="Delete Permanently"
    //                 text="Are you sure you won't to delete this navigation."
    //                 name="Delete"
    //                 buttonClass="danger"
    //                 onClick={handleDoDelete}
    //                 // onCancel={() => setDeletePopupOpened(false)}
    //                 onCancel={handleDeleteCancel}
    //             />
    //             : null
    //     }
    // </div>
}

function SelectType( { options, onChange} ) {
    return <div className='inside-select'>
        <Select 
            type="small" 
            options={options} 
            onChange={onChange}
        />
    </div>
}


// function CreateNavigation() {

//     const subdomain = subdomainLogic.values.subdomain;
//     const navigationLogicBuilt = navigationLogic({subdomain})
//     const { create } = useActions(navigationLogicBuilt)


//     const [createNewNavigation, setData] = useState({
//         name: "",
//         url: "",
//         type: ""
//     });
//     function handleNavigationName(e) {
//         e.preventDefault();
//         setData({...createNewNavigation, 
//             name: e.target.value
//         })
//     }
//     function handleNavigationUrl(e) {
//         e.preventDefault();
//         setData({...createNewNavigation, 
//             url: e.target.value
//         })
//     }
//     function handleType({value}){
//         console.log(value)
//         setData({...createNewNavigation, 
//             type: value
//         })
//     }

//     // OnClick handler for creating an new navigation
//     function submitNavigationData (e) {
//         e.preventDefault();
//         console.log(createNewNavigation.type)

//         create({
//             name: createNewNavigation.name,
//             url: createNewNavigation.url,
//             type: createNewNavigation.type,
//         });
//         setData({
//             name: "",
//             url: "",
//             type: ""
//         });
//     }

//     const selectOptions = [
//         { label: 'Header', value: 'header' },
//         { label: 'Footer', value: 'footer'}
//     ];

//     return <div>
//         <div>
//             <div className="create-navigation">
//                 <input type="text" value={createNewNavigation.name} className="create-navigation-name" onChange={(e)=> {handleNavigationName(e)}} required/>
//                 <input type="text" value={createNewNavigation.url} className="create-navigation-url" onChange={(e)=> {handleNavigationUrl(e)}} required/>
//                 <div className="create-navigation-select">
//                     <SelectType options={selectOptions} onChange={handleType}/>
//                 </div>
//                 <button className="button small navigation-create-button" onClick={(e)=> {submitNavigationData(e)}}>Create</button>
//             </div>
//         </div>
//     </div>
// }



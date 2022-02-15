import React, {useState} from 'react';
import { useActions, useValues } from 'kea';
import { Trash, PencilFill ,CheckCircleFill, XCircleFill, Plus} from 'react-bootstrap-icons';
import {toast} from 'react-toastify'
// import { PopupConfirm } from '../ReusableComponents/Popup';
import subdomainLogic from '../logic/subdomainLogic';
import redirectsLogic from '../logic/redirectsLogic';
import Loader from '../ReusableComponents/Loader';
import Select from '../ReusableComponents/Select';
import { components } from 'react-select';
import Toast from '../ReusableComponents/Toast';
import NoResults from '../ReusableComponents/NoResults';
import Input from '../ReusableComponents/Input';
import { Popup, PopupBodyDefault, PopupConfirm, PopupFooterDoubleButton, PopupHeaderDefault } from '../ReusableComponents/Popup';


export default function SettingRedirects(props) {

    const subdomain = subdomainLogic.values.subdomain;
    const redirectLogicBuilt = redirectsLogic({subdomain})
    const { redirect, loadAjax, createAjax } = useValues(redirectLogicBuilt)
    // const { redirect, loadAjax, createAjax, redirectListHasMore, loadRedirectListMoreAjax } = useValues(redirectLogicBuilt)
    // const { setRedirectList } = useActions(redirectLogicBuilt)

    // load data section
    const [visible , setVisible] = useState(50);
    const loadMore = () =>{
        console.log(visible)
        setVisible(visible + visible);
    }

    // function handleScroll(e) {
    //     var et = e.target;
    //     if (
    //         loadRedirectListMoreAjax.status !== 'loading' &&  
    //         redirectListHasMore &&
    //         et.scrollTop + et.clientHeight >= et.scrollHeight
    //     ) {
    //         setRedirectList({ offset: redirect.length })
    //     } 
    // }

    return <div className="setting-redirects"> 
        <div className="redirect-view">
            {/* <div className="title">
                    Redirects
            </div> */}
            <div className="redirect-title-bar">
                <div className="redirect-title">
                    Redirects
                </div>
                {/* <div className="redirect-title-create-button ">
                    <button type='button' className ="button redirect-button-popup">
                        <div className="popup-button-content">
                            <div className="popup-button-content-text">Create</div> 
                            <Plus />
                        </div>
                    </button>
                </div> */}
                <CreatePopUp/>
            </div>

            {/* <div className="title">
            Redirect 
            <button className="button small inactive">
                <span>Upload <Plus /></span> 
            </button>
            <CreatePopUp/>
        </div> */}

            <div className="redirects">
                <div>
                    {
                        // <CreateRedirect/>
                    }
                    {
                        createAjax.status === 'error' ?
                        <Toast 
                            x={console.log(createAjax.error)}
                            text={createAjax.error}
                            type="error"
                        /> : null
                    }
                </div>
                <div className='redirect-top-bar'>
                    <div className="redirect-left">
                        <div>Match Path </div>
                    </div>
                    <div className="redirect-left redirect-top-bar-right">
                        <div>Redirecting To</div>
                    </div>
                    <div className="redirect-left redirect-top-bar-right">
                        <div>Type</div>
                    </div>
                </div> 

                {
                    loadAjax.status === 'loading' ?
                    <Loader padding={40}/> :
                    (
                        <div>
                            {
                                // redirect.length > 0 && (
                                redirect.length > 0 ?
                                    <div>
                                        <div>
                                            {
                                                redirect.slice(0, visible).map(redirect => (
                                                    <div>
                                                        <Redirect id={redirect.id} old_url={redirect.old_url} new_url={redirect.new_url} redirectType={redirect.type}/>
                                                    </div>
                                                ))
                                            }
                                        </div>
                                        <div>
                                            <button type='button' className ="loadMore-redirect" onClick={loadMore}>Load More</button>
                                        </div> 
                                        {/* <div>
                                            <button type='button' className ="loadMore-redirect" onClick={handleScroll}>Load More</button>
                                        </div>  */}
                                    </div> : 
                                    <NoResults 
                                    text="There is no any redirects."
                                    padding={40}
                                    imageWidth={250}
                                    />

                                // ) 
                            } 
                        </div>
                    )
                }
            </div>
        </div>
    </div>
}

function CreatePopUp() {

    const subdomain = subdomainLogic.values.subdomain;
    const redirectLogicBuilt = redirectsLogic({subdomain})
    const { create } = useActions(redirectLogicBuilt)  

    const [createPopUpOpened, setCreatePopUpOpened] = useState(false);

    function handleCreateCancel(){
        setCreatePopUpOpened(false)
    }
    function handleCreate(e) {
        e.preventDefault();
        setCreatePopUpOpened(true);
    }

    const [ old_url, setOldUrl ] = useState();
    const [ new_url, setNewUrl ] = useState();
    const [createNewRedirect, setData] = useState({type: ""});

    const selectOptions = [
        { label: 'Permanent', value: '301' },
        { label: 'Temporary', value: '302'}
    ];  
    function handleType({value}){
        console.log(value)
        setData({...createNewRedirect, 
            type: value
        })
    }

    function submitRedirect (e) {
        e.preventDefault();

        create({
            old_url: old_url,
            new_url: new_url,
            type: createNewRedirect.type,
        });
        setOldUrl();
        setNewUrl();
        setData({type: "" });
        setCreatePopUpOpened(false)
    }

    return <div>
        {
        <div className="redirect-title-create-button ">
            <button type='button' className ="button small inactive redirect-button-popup" onClick={handleCreate}>
                <div className="popup-button-content">
                    <div className="popup-button-content-text">Create</div> 
                    <Plus />
                </div>
            </button>
        </div>
            // <button className="button small inactive" onClick={handleCreate}>
            //     <span>Create <Plus /></span> 
            // </button>
        }
        {
                createPopUpOpened ?
                    <Popup
                        header={<PopupHeaderDefault title='Create New Redirect' />}
                        body={
                            <PopupBodyDefault>
                                <div>
                                    
                                    <Input 
                                        title="Name"
                                        type="text"
                                        name="name"
                                        value={old_url}
                                        onChange={setOldUrl}
                                        placeholder="Match Url"
                                    />
                                    <Input 
                                        title="Url"
                                        type="text"
                                        name="url"
                                        value={new_url}
                                        onChange={setNewUrl}
                                        placeholder="Redirect Url"
                                    />
                                    <div className="redirect-popup-input">
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
                                onClick={(e)=> {submitRedirect(e)}}
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

function Redirect ({id, old_url, new_url, redirectType}){

    const subdomain = subdomainLogic.values.subdomain;
    const redirectLogicBuilt = redirectsLogic({subdomain})
    const { remove , updateData} = useActions(redirectLogicBuilt)
    const { updateDataAjax } = useValues(redirectLogicBuilt)

    // delete section
    const [deletePopupOpened, setDeletePopupOpened] = useState(false);
    const [styleDeleteIcon, setStyleDeleteIcon] = useState("redirect-delete");

    function handleDelete(e) {
        e.preventDefault();
        setDeletePopupOpened(true);
        setStyleDeleteIcon("redirect-delete-after");
    }
    function handleDoDelete() {
        console.log(id);
        toast("File deleted", {autoClose: 1500});
        remove({id});
        setDeletePopupOpened(false);
        setStyleDeleteIcon("redirect-delete");
    }
    function handleDeleteCancel(){
        setStyleDeleteIcon("redirect-delete");
        setDeletePopupOpened(false)
    }

    // update section
    const [updateFormOpened, setUpdateFormOpened] = useState(false);
    const [styleEditIcon, setStyleEditIcon] = useState("redirect-edit");

    const [updateRedirectData, setUpdate] = useState({
        userId: id,
        type: redirectType
    })
    const [ oldUrl, setOldUrl ] = useState(old_url);
    const [ newUrl, setNewUrl ] = useState(new_url);

    // function handleOldUrl(e) {
    //     e.preventDefault();
    //     setUpdate({...updateRedirectData, 
    //         oldUrl: e.target.value
    //     })
    // }
    // function handleNewUrl(e) {
    //     e.preventDefault();
    //     setUpdate({...updateRedirectData, 
    //         newUrl: e.target.value
    //     })
    // }
    function handleType({value}){
        console.log(value)
        console.log(redirectType)
        setUpdate({...updateRedirectData, 
            type: value
        })
    }
    function handleUpdate(e){
        e.preventDefault();
        setUpdateFormOpened(true);
        // setStyleEditIcon("redirect-edit-after");

    }
    function handleCancelUpdate(){
        setUpdateFormOpened(false);
        setStyleEditIcon("redirect-edit");
    }

    function updateRedirect(e){
        e.preventDefault();
        console.log(updateRedirectData.userId);
        updateData({
            userId: updateRedirectData.userId,
            oldUrl: oldUrl,
            newUrl: newUrl,
            type:updateRedirectData.type,
        });
        setUpdateFormOpened(false); 
        setStyleEditIcon("redirect-edit");
        // window.location.reload(false);
    }
    
    const selectOptions = [
        { label: 'Permanent', value: '301' },
        { label: 'Temporary', value: '302'}
    ]; 

    return <div>
        {
                <div className="redirect-row">
                    <div className="redirect-display-data-one">
                        <div key={id}>{old_url}</div>
                    </div>
                    <div className="redirect-display-data-two">
                        <div key={id}>{new_url}</div>
                    </div>
                    <div className="redirect-display-data-three">
                        {/* <div key={id}>{redirectType}</div> */}
                        {
                            redirectType == 301 ? (
                            <div key={id}>Permanent</div> 
                            ) : (<div key={id}>Temporary</div>)
                        }
                    </div>
 
                    <div className="redirect-display-data-four">
 
                        <div className={styleEditIcon}>
                            <span onClick={handleUpdate}>
                                <PencilFill size={15} />
                            </span> 
                            {
                                updateFormOpened ?
                                <Popup
                                    header={<PopupHeaderDefault title='Update Redirects' />}
                                    body={
                                    <PopupBodyDefault>
                                        <div>
                                            <Input 
                                                // title="Name"
                                                type="text"
                                                name="name"
                                                value={oldUrl}
                                                onChange={setOldUrl}
                                                placeholder="Match Url"
                                             />
                                            <Input 
                                                // title="Url"
                                                type="text"
                                                name="url"
                                                value={newUrl}
                                                onChange={setNewUrl}
                                                placeholder="Redirect Url"
                                            />
                                            <div className="redirect-popup-input">
                                                <SelectType 
                                                    title="Type"
                                                    options={selectOptions} 
                                                    onChange={handleType}
                                                    defaultValue={selectOptions[0]}
                                                />
                                            </div>
                                        </div>
                                    </PopupBodyDefault>
                                    }
                                    footer={
                                        <PopupFooterDoubleButton
                                            onCancel={handleCancelUpdate}
                                            onClick={(e)=> {updateRedirect(e)}}
                                            name='Create'
                                        />
                                    }
                                /> 
                                : null
                            }
                        </div>
 
                        <div>
                            <span className={styleDeleteIcon} onClick={handleDelete}>
                                <Trash size={15} />
                            </span>
                            {
                                deletePopupOpened ?
                                    <PopupConfirm
                                        title="Delete Permanently"
                                        text="Are you sure to delete this redirect item permanently? You will not be able to access it anymore."
                                        name="Delete"
                                        buttonClass="danger"
                                        onClick={handleDoDelete}
                                        onCancel={handleDeleteCancel}
                                    />
                                : null
                            }
                        </div>
 
                    </div>
                </div>
        }
    </div>

    // return <div>
    //     {
    //         updateFormOpened ?
    //         <div>
    //             {
    //                 <div className="redirect-row">
    //                     <div className='redirect-update'>
    //                         <input className="redirect-update-input-one" type="text" id="old_url" value={updateRedirectData.oldUrl}  onChange={(e)=> {handleOldUrl(e)}} required/>
    //                         <input className="redirect-update-input-two" type="text" id="new_url" value={updateRedirectData.newUrl}  onChange={(e)=> {handleNewUrl(e)}} required/>

    //                         <div className="react-redirect-select">
    //                             {/* <SelectType  
    //                                 options={selectOptions} 
    //                                 onChange={handleType}
    //                             /> */}
    //                             <div className='inside-select'>
    //                                 <Select 
    //                                     type="small"  
    //                                     options={selectOptions} 
    //                                     onChange={handleType}
    //                                     defaultValue={selectOptions[0]}
    //                                 />
    //                             </div>
    //                         </div>

    //                         <button className="redirect-update-form-button redirect-cancel-margin" onClick={handleCancelUpdate}><XCircleFill size={15} /></button>
    //                         <button className="redirect-update-form-button redirect-update-margin" onClick={(e)=> {updateRedirect(e)}}><CheckCircleFill size={15} /></button>
    //                     </div>
    //                 </div>
    //             }
    //             {
    //                 updateDataAjax.status === 'error' ?
    //                     <Toast 
    //                         x={console.log(updateDataAjax.error)}
    //                         text={updateDataAjax.error}
    //                         type="error"
    //                     /> 
    //                 : null
    //             }
    //         </div>
    //         :
    //             <div className="redirect-row">
    //                 <div className="redirect-display-data-one">
    //                     <div key={id}>{old_url}</div>
    //                 </div>
    //                 <div className="redirect-display-data-two">
    //                     <div key={id}>{new_url}</div>
    //                 </div>
    //                 <div className="redirect-display-data-three">
    //                     {/* <div key={id}>{redirectType}</div> */}
    //                     {
    //                         redirectType == 301 ? (
    //                         <div key={id}>Permanent</div> 
    //                         ) : (<div key={id}>Temporary</div>)
    //                     }
    //                 </div>
 
    //                 <div className="redirect-display-data-four">
 
    //                     <div className="redirect-edit">
    //                         <span onClick={handleUpdate}>
    //                             <PencilFill size={15} />
    //                         </span> 
    //                     </div>
 
    //                     <div>
    //                         <span className={styleDeleteIcon} onClick={handleDelete}>
    //                             <Trash size={15} />
    //                         </span>
    //                         {
    //                             deletePopupOpened ?
    //                                 <PopupConfirm
    //                                     title="Delete Permanently"
    //                                     text="Are you sure to delete this redirect item permanently? You will not be able to access it anymore."
    //                                     name="Delete"
    //                                     buttonClass="danger"
    //                                     onClick={handleDoDelete}
    //                                     onCancel={handleDeleteCancel}
    //                                 />
    //                             : null
    //                         }
    //                     </div>
 
    //                 </div>
    //             </div>
    //     }
    // </div>
}

function SelectType( { options, onChange, defaultValue} ) {
    return <div className='inside-select'>
        <Select 
            type="small" 
            // className = {className}
            options={options} 
            onChange={onChange}
            // defaultValue={onChange}
        />
    </div>
}

// function CreateRedirect() {

//     const subdomain = subdomainLogic.values.subdomain;
//     const redirectLogicBuilt = redirectsLogic({subdomain})
//     const { create } = useActions(redirectLogicBuilt)

//     const selectOptions = [
//         { label: 'Permanent', value: '301' },
//         { label: 'Temporary', value: '302'}
//     ];    

//     const [createNewRedirect, setData] = useState({
//         old_url: "",
//         new_url: "",
//         type: ""
//     });

//     function handle(e) {
//         const newData = {...createNewRedirect}
//         newData[e.target.id] = e.target.value 
//         setData(newData)
//         console.log(newData)
//     }
//     function handleType({value}){
//         console.log(value)
//         setData({...createNewRedirect, 
//             type: value
//         })
//     }

//     function submitRedirect (e) {
//         e.preventDefault();

//         create({
//             old_url: createNewRedirect.old_url,
//             new_url: createNewRedirect.new_url,
//             type: createNewRedirect.type,
//         });
//         setData({
//             old_url: "",
//             new_url: "",
//             type: ""
//         });
//     }

//     return <div className='redirect-create'>
//         <input className="redirect-input" name="matchTo" type="text" id="old_url" value={createNewRedirect.old_url}  onChange={(e)=> {handle(e)}} placeholder='Enter Match Path' />
//         <input className="redirect-input" name="redirectTo" type="text" id="new_url" value={createNewRedirect.new_url}  onChange={(e)=> {handle(e)}} placeholder='Enter Redirecting URL' />
    
//         <div className="react-redirect-select">
//             <SelectType 
//                 options={selectOptions} 
//                 onChange={handleType} 
//             />
//         </div>
//         {
//             createNewRedirect.old_url === '' && createNewRedirect.new_url === '' && createNewRedirect.type === '' ? 
//             (
//                 <button className="button small redirect-button disabled">Create</button>
//             ) : (
//                 <button className="button small redirect-button" onClick={(e)=> {submitRedirect(e)}}>Create</button>
//             )
//         }
//     </div>
// }

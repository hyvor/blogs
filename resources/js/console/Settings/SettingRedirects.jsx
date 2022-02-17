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
    const [visible , setVisible] = useState(25);
    const loadMore = () =>{
        console.log(visible)
        setVisible(visible + visible);
    }

    return <div className="setting-redirects"> 
        {/* <div className="redirect-view"> */}
            <div className="redirect-title-bar">
                <div className="redirect-title">
                    Redirects
                </div>
                <div>
                    <CreatePopUp/>
                    {
                        createAjax.status === 'error' ?
                        <Toast 
                            x={console.log(createAjax.error)}
                            text={createAjax.error}
                            type="error"
                        /> : null
                    }
                </div>
            </div>
            {/* <div className="redirects"> */}
            <div className="global-table-view">
                {/* <div className='redirect-top-bar'>
                    <div className="redirect-left">
                        <div>Match Path </div>
                    </div>
                    <div className="redirect-left redirect-top-bar-right">
                        <div>Redirecting To</div>
                    </div>
                    <div className="redirect-left redirect-top-bar-right">
                        <div>Type</div>
                    </div>
                </div>  */}
                <div className="global-table-header-four">      
                        <div className="table-head-item">Match Path</div> 
                        <div className="table-head-item">Redirecting To</div>
                        <div className="table-head-item">Type</div>
                        <div></div>
                </div>
                {
                    loadAjax.status === 'loading' ?
                    <Loader padding={40}/> :
                    (
                        <div>
                            {
                                redirect.length > 0 ?
                                    <div>
                                        <div>
                                            {
                                                redirect.slice(0, visible).map(redirect => (
                                                    <div className="global-table-body">
                                                        <Redirect id={redirect.id} old_url={redirect.path} new_url={redirect.to} redirectType={redirect.type}/>
                                                    </div>
                                                ))
                                            }
                                        </div>
                                        {
                                            redirect.length > 25 ?
                                                <div>
                                                    <button type='button' className ="loadMore-redirect" onClick={loadMore}>Load More</button>
                                                </div>
                                            : null
                                        } 
                                    </div> : 
                                    <NoResults 
                                        text="There is no any redirects."
                                        padding={40}
                                        imageWidth={250}
                                    />
                            } 
                        </div>
                    )
                }
            </div>
        {/* </div> */}
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
        // console.log(value)
        setData({...createNewRedirect, 
            type: value
        })
    }

    function submitRedirect (e) {
        e.preventDefault();
        create({
            oldUrl: old_url,
            newUrl: new_url,
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
        }
        {
            createPopUpOpened ?
                <Popup
                    header={<PopupHeaderDefault title='Create New Redirect' />}
                    body={
                        <PopupBodyDefault>
                            <div>
                                    
                                <Input 
                                    title="Match Url"
                                    type="text"
                                    name="name"
                                    value={old_url}
                                    onChange={setOldUrl}
                                    placeholder="Match Url"
                                />
                                <Input 
                                    title="Redirect Url"
                                    type="text"
                                    name="url"
                                    value={new_url}
                                    onChange={setNewUrl}
                                    placeholder="Redirect Url"
                                />
                                <div className="redirect-popup-input">
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
                            onClick={(e)=> {submitRedirect(e)}}
                            name='Create'
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
    const [styleDeleteIcon, setStyleDeleteIcon] = useState("table-button");

    function handleDelete(e) {
        e.preventDefault();
        setDeletePopupOpened(true);
        setStyleDeleteIcon("table-delete-popup");
    }
    function handleDoDelete() {
        console.log(id);
        toast("File deleted", {autoClose: 1500});
        remove({id});
        setDeletePopupOpened(false);
        setStyleDeleteIcon("table-button");
    }
    function handleDeleteCancel(){
        setStyleDeleteIcon("table-button");
        setDeletePopupOpened(false)
    }

    // update section
    const [updateFormOpened, setUpdateFormOpened] = useState(false);
    const [updateRedirectData, setUpdate] = useState({
        userId: id,
        type: redirectType
    })
    const [ oldUrl, setOldUrl ] = useState(old_url);
    const [ newUrl, setNewUrl ] = useState(new_url);

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
    }
    function handleCancelUpdate(){
        setUpdateFormOpened(false);
    }
    function updateRedirect(e){
        e.preventDefault();
        updateData({
            userId: updateRedirectData.userId,
            oldUrl: oldUrl,
            newUrl: newUrl,
            type:updateRedirectData.type,
        });
        setUpdateFormOpened(false); 
        // window.location.reload(false);
    }
    
    const selectOptions = [
        { label: 'Permanent', value: '301' },
        { label: 'Temporary', value: '302'}
    ]; 

    return <div>
        {
            // <div className="redirect-row">
            <div className="table-body-four">
                {/* <div className="redirect-display-data-one">
                    <div key={id}>{old_url}</div>
                </div> */}
                <div className="table-item">
                    <div key={id}>{old_url}</div>
                </div>
                <div className="table-item">
                    <div key={id}>{new_url}</div>
                </div>
                <div className="table-item">
                    {/* <div key={id}>{redirectType}</div> */}
                    {
                        redirectType == 301 ? (
                            <div key={id}>Permanent</div> 
                        ) : 
                        (
                            <div key={id}>Temporary</div>
                        )
                    }
                </div>
 
                {/* <div className="redirect-display-data-four"> */}
                <div className="table-actions">
                     <div className="table-edit">
                        <span className="table-button" onClick={handleUpdate}>
                            <PencilFill size={10} />
                        </span> 
                        {
                            updateFormOpened ?
                                <Popup
                                    header={<PopupHeaderDefault title='Update Redirects' />}
                                    body={
                                    <PopupBodyDefault>
                                        <div>
                                            <Input 
                                                title="Match Url"
                                                type="text"
                                                name="name"
                                                value={oldUrl}
                                                onChange={setOldUrl}
                                                placeholder="Match Url"
                                             />
                                            <Input 
                                                title="Redirect Url"
                                                type="text"
                                                name="url"
                                                value={newUrl}
                                                onChange={setNewUrl}
                                                placeholder="Redirect Url"
                                            />
                                            <div className="redirect-popup-input">
                                                <div className="popup-type-margin">Type</div>
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
 
                        <div className="table-delete">
                            <span className={styleDeleteIcon} onClick={handleDelete}>
                                <Trash size={10} />
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
            options={options} 
            onChange={onChange}
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

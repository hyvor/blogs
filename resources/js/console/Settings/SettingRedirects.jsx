import React, {useState} from 'react';
import { useActions, useValues } from 'kea';
import { Trash, PencilFill ,CheckCircleFill, XCircleFill} from 'react-bootstrap-icons';
import {toast} from 'react-toastify'
import { PopupConfirm } from '../ReusableComponents/Popup';
import subdomainLogic from '../logic/subdomainLogic';
import redirectsLogic from '../logic/redirectsLogic';
import Loader from '../ReusableComponents/Loader';
import Select from '../ReusableComponents/Select';
import { components } from 'react-select';
import Toast from '../ReusableComponents/Toast';



export default function SettingRedirects(props) {

    const subdomain = subdomainLogic.values.subdomain;
    const redirectLogicBuilt = redirectsLogic({subdomain})
    const { redirect, loadAjax, createAjax } = useValues(redirectLogicBuilt)

    // load data section
    const [visible , setVisible] = useState(50);
    const loadMore = () =>{
        console.log(visible)
        setVisible(visible + visible);
    }

    return <div className="setting-redirects">
        <div className="redirect-view">
            <div className="title">
                Redirects
            </div>
            <div className="redirects">
                <div>
                    {
                        <CreateRedirect/>
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
                    <Loader/> :
                    (
                        <div>
                           {redirect.length > 0 && (
                                <div>
                                    {redirect.slice(0, visible).map(redirect => (
                                        <div>
                                            <GetRedirect id={redirect.id} old_url={redirect.old_url} new_url={redirect.new_url} redirectType={redirect.type}/>
                                        </div>
                                    ))}
                                </div>
                            )} 
                            <div>
                                <button type='button' className ="loadMore-redirect" onClick={loadMore}>Load More</button>
                            </div> 
                        </div>
                    )
                }
            </div>
        </div>
    </div>
}


function CreateRedirect() {

    const subdomain = subdomainLogic.values.subdomain;
    const redirectLogicBuilt = redirectsLogic({subdomain})
    const { create } = useActions(redirectLogicBuilt)

    const [createNewRedirect, setData] = useState({
        old_url: "",
        new_url: "",
        type: ""
    });

    function handle(e) {
        const newData = {...createNewRedirect}
        newData[e.target.id] = e.target.value 
        setData(newData)
        console.log(newData)
    }
    function handleType({value}){
        console.log(value)
        setData({...createNewRedirect, 
            type: value
        })
    }

    function submitRedirect (e) {
        e.preventDefault();

        create({
            old_url: createNewRedirect.old_url,
            new_url: createNewRedirect.new_url,
            type: createNewRedirect.type,
        });
        setData({
            old_url: "",
            new_url: "",
            type: ""
        });
    }

    const selectOptions = [
        { label: 'Permanent', value: '301' },
        { label: 'Temporary', value: '302'}
    ];

    return <form className='redirect-create' onSubmit={(e)=> {submitRedirect(e)}}>
        <input className="redirect-input" name="matchTo" type="text" id="old_url" value={createNewRedirect.old_url}  onChange={(e)=> {handle(e)}} placeholder='Enter Match Path' />
        <input className="redirect-input" name="redirectTo" type="text" id="new_url" value={createNewRedirect.new_url}  onChange={(e)=> {handle(e)}} placeholder='Enter Redirecting URL' />
    
        <div className="react-redirect-select">
            <SelectType options={selectOptions} onChange={handleType} />
        </div>
    
        <button className="button small redirect-button ">Create</button>
    </form>
}


function GetRedirect ({id, old_url, new_url, redirectType}){

    const subdomain = subdomainLogic.values.subdomain;
    const redirectLogicBuilt = redirectsLogic({subdomain})
    const { remove , updateData} = useActions(redirectLogicBuilt)
    const { updateDataAjax } = useValues(redirectLogicBuilt)

    // delete section
    const [deletePopupOpened, setDeletePopupOpened] = useState(false);
    function handleDelete(e) {
        e.preventDefault();
        setDeletePopupOpened(true);
    }
    function handleDoDelete() {
        console.log(id);
        toast("File deleted", {autoClose: 1500});
        remove({id});
        setDeletePopupOpened(false);
    }

    // update section
    const [updateFormOpened, setUpdateFormOpened] = useState(false);
    const [updateRedirectData, setUpdate] = useState({
        userId: id,
        oldUrl: old_url,
        newUrl: new_url,
        type: ""
    })
    function handleOldUrl(e) {
        e.preventDefault();
        setUpdate({...updateRedirectData, 
            oldUrl: e.target.value
        })
    }
    function handleNewUrl(e) {
        e.preventDefault();
        setUpdate({...updateRedirectData, 
            newUrl: e.target.value
        })
    }
    function handleType({value}){
        console.log(value)
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
        console.log(updateRedirectData.userId);
        updateData({
            userId: updateRedirectData.userId,
            oldUrl: updateRedirectData.oldUrl,
            newUrl: updateRedirectData.newUrl,
            type:updateRedirectData.type,
        });
        setUpdateFormOpened(false);
        // window.location.reload(false);
    }
    const selectOptions = [
        { value: '301', label: 'Permanent' },
        { value: '302', label: 'Temporary'}
    ];

    return <div>
        {
            updateFormOpened ?
            <div>
                {
                    <div className="redirect-row">
                        <form className='redirect-update' onSubmit={(e)=> {updateRedirect(e)}}>
                        <input className="redirect-update-input" type="text" id="old_url" value={updateRedirectData.oldUrl}  onChange={(e)=> {handleOldUrl(e)}} required/>
                        <input className="redirect-update-input" type="text" id="new_url" value={updateRedirectData.newUrl}  onChange={(e)=> {handleNewUrl(e)}} required/>

                        <div className="react-redirect-select">
                            <SelectType options={selectOptions} onChange={handleType}/>
                        </div>

                        <button className="redirect-update-form-button redirect-cancel-margin" onClick={handleCancelUpdate}><XCircleFill size={15} /></button>
                        <button className="redirect-update-form-button redirect-update-margin"><CheckCircleFill size={15} /></button>
                    </form>
                </div>
                }
                {
                        updateDataAjax.status === 'error' ?
                        <Toast 
                            x={console.log(updateDataAjax.error)}
                            text={updateDataAjax.error}
                            type="error"
                        /> : null
                    }
            </div>
            :
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
                            {redirectType} == 302 ? 
                            <div key={id}>Permanent</div> : <div key={id}>Temporary</div>
                        }
                    </div>
 
                    <div className="redirect-display-data-four">
 
                        <div className="redirect-edit">
                            <span onClick={handleUpdate}>
                                <PencilFill size={15} />
                            </span> 
                        </div>
 
                        <div className="redirect-delete">
                            <span className="media-delete" onClick={handleDelete}>
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
                                        onCancel={() => setDeletePopupOpened(false)}
                                    />
                                : null
                            }
                        </div>
 
                    </div>
                </div>
        }
    </div>
}

function SelectType( { options, onChange} ) {
    return <div className='inside-select'>
        <Select 
            type="small" 
            // className = {className}
            options={options} 
            onChange={onChange}
        />
    </div>
}
import React, {useState} from 'react';
import { useActions, useValues } from 'kea';
import { Trash, PencilFill } from 'react-bootstrap-icons';
import {toast} from 'react-toastify'
import { PopupConfirm } from '../ReusableComponents/Popup';
import subdomainLogic from '../logic/subdomainLogic';
import redirectsLogic from '../logic/redirectsLogic';
import Loader from '../ReusableComponents/Loader';
import Select from '../ReusableComponents/Select';
import { components } from 'react-select';


export default function SettingRedirects(props) {

    const subdomain = subdomainLogic.values.subdomain;
    const redirectLogicBuilt = redirectsLogic({subdomain})
    const { redirect, loadAjax } = useValues(redirectLogicBuilt)

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
                    <CreateRedirect/>
                </div>
                <div className='redirect-top-bar'>
                    <div className="redirect-left">
                        <div>Match URL </div>
                    </div>
                    <div className="redirect-left redirect-new-url-top">
                        <div>Current URL</div>
                    </div>
                </div> 

                {
                    loadAjax.status === 'loading' ?
                    <Loader/> :
                    (
                        <div>
                           {redirect.length > 0 && (
                                <div>
                                    {redirect.slice(0, visible).map(user => (
                                        <div>
                                            <GetRedirect id={user.id} old_url={user.old_url} new_url={user.new_url} />
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

    const [data, setData] = useState({
        old_url: "",
        new_url: "",
        type: ""
    })

    function handle(e) {
        const newData = {...data}
        newData[e.target.id] = e.target.value 
        setData(newData)
        console.log(newData)
    }
    function handleType({value}){
        console.log(value)
        setData({...data, 
            type: value
        })
    }
    function submitRedirect (e) {
        e.preventDefault();
        create({
            old_url: data.old_url,
            new_url: data.new_url,
            type: data.type,
        });
        window.location.reload(false);
    }
    const selectOptions = [
        { label: 'Permanent', value: '301' },
        { label: 'Temporary', value: '302'}
    ];

    return <form className='redirect-create' onSubmit={(e)=> {submitRedirect(e)}}>
        <input className="redirect-input" type="text" id="old_url" value={data.old_url}  onChange={(e)=> {handle(e)}} placeholder='Enter old URL' required/>
        <input className="redirect-input" type="text" id="new_url" value={data.new_url}  onChange={(e)=> {handle(e)}} placeholder='Enter new URL' required/>
    
        <div className="react-redirect-select">
            <SelectType options={selectOptions} onChange={handleType} />
        </div>
    
        <button value="Submit" className="button small redirect-button ">Create</button>
    </form>
}


function GetRedirect ({id, old_url, new_url}){

    const subdomain = subdomainLogic.values.subdomain;
    const redirectLogicBuilt = redirectsLogic({subdomain})
    const { remove , updateData} = useActions(redirectLogicBuilt)

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
    // function handleType(e){
    //     // e.preventDefault();
    //     setUpdate({...updateRedirectData, 
    //         type: e.target.value
    //     })
    // }
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
        window.location.reload(false);
    }
    const selectOptions = [
        { value: '301', label: 'Permanent' },
        { value: '302', label: 'Temporary'}
    ];

    return <div>
        {
            updateFormOpened ?
                <div className="redirect-row">
                    <form className='redirect-update' onSubmit={(e)=> {updateRedirect(e)}}>
                        <input className="redirect-update-input" type="text" id="old_url" value={updateRedirectData.oldUrl}  onChange={(e)=> {handleOldUrl(e)}} required/>
                        <input className="redirect-update-input" type="text" id="new_url" value={updateRedirectData.newUrl}  onChange={(e)=> {handleNewUrl(e)}} placeholder='Enter new URL' required/>
    
                        {/* <select className="redirect-select" id="type" value={updateRedirectData.type} onChange={(e)=> {handleType(e)}}>  
                            <option>Type</option>
                            <option className="redirect-type-option" value="301">Permanent</option>
                            <option className="redirect-type-option" value="302">Temporary</option>
                        </select> */}
                        <div className="react-redirect-select">
                            <SelectType options={selectOptions} onChange={handleType} />
                        </div>

                        <button value="Submit" className="button small redirect-cancel-button" onClick={handleCancelUpdate}>Cancel</button>
                        <button value="Submit" className="button small redirect-update-button ">Update</button>
                    </form>
                </div>
            :
                <div className="redirect-row">
                    <div className="redirect-left">
                        <div key={id}>{old_url}</div>
                    </div>
                    <div className="redirect-left">
                        <div key={id}>{new_url}</div>
                    </div>
 
                    <div className="redirect-right">
 
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
                                        text="Are you sure to delete this media item permanently? You will not be able to access it anymore."
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

function SelectType( { options, onChange } ) {
    return <div className='inside-select'>
        <Select 
            type="small" 
            options={options}
            onChange={onChange}
        />
    </div>
}
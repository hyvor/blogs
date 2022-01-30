import React, {useEffect, useState} from 'react';
import { useActions, useValues } from 'kea';
import { A } from 'kea-router';
import { Trash, PencilFill } from 'react-bootstrap-icons';
import axios from 'axios';
import {toast} from 'react-toastify'
import { PopupConfirm } from '../ReusableComponents/Popup';
import subdomainLogic from '../logic/subdomainLogic';
import redirectsLogic from '../logic/redirectsLogic';
import Loader from '../ReusableComponents/Loader';
// import NoResults from '../ReusableComponents/NoResults';



export default function SettingRedirects(props) {

    const subdomain = subdomainLogic.values.subdomain;
    const redirectLogicBuilt = redirectsLogic({subdomain})
    const { redirect, loadAjax } = useValues(redirectLogicBuilt)

    // load data section
    const [visible , setVisible] = useState(25);
    const loadMore = () =>{
        console.log(visible)
        setVisible(visible + visible);
    }

    // const deleteRedirect =  (id, e) => {
    //     e.preventDefault();
        // console.log(getRedirects.id)
	    // axios.delete(`http://blogs.hyvor.test/api/console/v0/blog/`+ subdomain +`/redirect/${id}`)
        // .then(res=> {
        //     console.log('Deleted !!', res)
        //     window.location.reload(false);
        // }).catch(err => console.log(err))
    //     remove({id});
    //     window.location.reload(false);
    // }

    return <div className="setting-redirects">

        <div className="redirect-view">
            <div className="title">
                Redirects
            </div>
            <div className="redirects">
                 {/* create new redirect */}
                <div>
                    <CreateRedirect/>
                </div>
                {/* <div className="redirect-row"> */}
                <div className='redirect-top-bar'>
                    <div className="redirect-left">
                        <div>Previous URL </div>
                    </div>
                    <div className="redirect-left redirect-new-url-top">
                        <div>Current URL</div>
                    </div>
                </div> 

                {/* Testing with the logic section */}
                {
                    loadAjax.status === 'loading' ?
                    <Loader style={{padding:200, textAlign: 'center'}} /> :
                    (
                    <div>
                           {redirect.length > 0 && (
                        <div>
                            {redirect.slice(0, visible).map(user => (
                                <div>
                                    <div className="redirect-row">
                                        
                                        <div className="redirect-left">
                                            <div key={user.id}>{user.old_url}</div>
                                        </div>
                                        <div className="redirect-left">
                                            <div key={user.id}>{user.new_url}</div>
                                        </div>
                                            <div>
                                                <RemoveRedirect id={user.id}/>
                                            </div>
                                    </div>
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


// Create redirect function component
function CreateRedirect() {

    const subdomain = subdomainLogic.values.subdomain;
    const redirectLogicBuilt = redirectsLogic({subdomain})
    const { create } = useActions(redirectLogicBuilt)

    const [data, setData] = useState({
        old_url: "",
        new_url: "",
        type: ""
    })

    function submitRedirect (e) {
        e.preventDefault();
        create({
            old_url: data.old_url,
            new_url: data.new_url,
            type: data.type,
        });
        window.location.reload(false);
    }

    function handle(e) {
        const newData = {...data}
        newData[e.target.id] = e.target.value 
        setData(newData)
        console.log(newData)
    }


    return <form className='redirect-create' onSubmit={(e)=> {submitRedirect(e)}}>
    <input className="redirect-input" type="text" id="old_url" value={data.old_url}  onChange={(e)=> {handle(e)}} placeholder='Enter old URL' required/>
    <input className="redirect-input" type="text" id="new_url" value={data.new_url}  onChange={(e)=> {handle(e)}} placeholder='Enter new URL' required/>
    {/* <input className="redirect-input" type="text" id="type" value={data.type}  onChange={(e)=> {handle(e)}} placeholder='Temporary or Permanent' required/> */}
    
    <select className="redirect-select" id="type" value={data.type} onChange={(e)=> {handle(e)}} >  
         
        <option disabled selected>Type</option>
        <option className="redirect-type-option" value="301">Permanent</option>
        <option className="redirect-type-option" value="302">Temporary</option>
    </select>
    
    <button value="Submit" className="button small redirect-button ">Create</button>
    </form>

}


function RemoveRedirect ({id}){

    const subdomain = subdomainLogic.values.subdomain;
    const redirectLogicBuilt = redirectsLogic({subdomain})
    const { remove , update} = useActions(redirectLogicBuilt)

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
    function updateRedirect(){
        update({id});
    }

    return <div className="redirect-right">
        <div className="redirect-edit">
            <span onClick={updateRedirect}>
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
}
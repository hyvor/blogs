import React, {useState} from 'react';
import { useActions, useValues } from 'kea';
import { Trash, PencilFill, Plus, ArrowBarRight} from 'react-bootstrap-icons';
import {toast} from 'react-toastify'
import subdomainLogic from '../logic/subdomainLogic';
import tagsLogic from '../logic/tagsLogic';
import Loader from '../ReusableComponents/Loader';
import Select from '../ReusableComponents/Select';
import Toast from '../ReusableComponents/Toast';
import NoResults from '../ReusableComponents/NoResults';
import Input from '../ReusableComponents/Input';
import { Popup, PopupBodyDefault, PopupConfirm, PopupFooterDoubleButton, PopupHeaderDefault } from '../ReusableComponents/Popup';

export default function SettingTag(props) {
    const subdomain = subdomainLogic.values.subdomain;
    const tagsLogicBuilt = tagsLogic({subdomain})
    const { 
        tag, 
        loadAjax, createAjax, tagList, loadTagListAjax, tagListHasMore, loadTagsListMoreAjax,
    } = useValues(tagsLogicBuilt)
    const { loadTagsListMore} = useActions(tagsLogicBuilt)

    function handleScroll(e) {
        var el = e.target;
        if (
            loadTagsListMoreAjax.status !== 'loading' &&  
            tagListHasMore &&
            el.scrollTop + el.clientHeight >= el.scrollHeight
        ) 
        {
            loadTagsListMore({ offset: tag.length })
        }
        // window.location.reload(false);
    }

    console.log(tag.length);
    console.log('test')
    console.log(tagListHasMore);

    return <div className="settingsTag">
        <div className="tag-title-bar">
            <div className="tag-title">
                Tags
            </div>
            <div>
                <CreateNewTag />
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

        <div>
            <div className="global-table-view">
                <div className="global-table-header-five">      
                    <div className="table-head-item">Name</div> 
                    <div className="table-head-item">Description</div>
                    <div className="table-head-item">Slug</div>
                    <div className="table-head-item">Posts</div>
                    <div></div>
                </div>

                {/* <div className="table-body-scroll"> */}
                    {
                        loadAjax.status === 'loading' ?
                            <Loader padding={40}/> :
                        (
                            <div>
                                {
                                    // tag.length > 0 ?
                                    //     <div className="table-body-scroll" onScroll={handleScroll}>
                                    //         {
                                    //             loadAjax.status === 'loading' ?
                                    //                 <Loader />
                                    //             :
                                    //             <div>
                                    //             {
                                    //                 tag.map(tag => (
                                    //                     <div className="global-table-body">
                                    //                         <Tags id={tag.id} name={tag.name} description={tag.description} slug={tag.slug}/>           
                                    //                     </div>
                                    //                 ))
                                    //             }
                                    //             </div>
                                    //         }
                                    //     </div> 
                                    // : 
                                    //     <NoResults 
                                    //         text="There is no any tags."
                                    //         padding={40}
                                    //         imageWidth={250}
                                    //     />


                                    <div className="table-body-scroll" onScroll={handleScroll}> 
                                           {
                                                loadAjax.status === 'loading' ?
                                                    <Loader /> :

                                                <div>
                                                    {
                                                        tag.length ?
                                                            <div>
                                                                {
                                                                    tag.map(tag => (
                                                                        <div className="global-table-body">
                                                                            <Tags key = {tag} id={tag.id} name={tag.name} description={tag.description} slug={tag.slug}/>           
                                                                        </div>
                                                                    ))
                                                                }
                                                            </div>
                            
                                                        :
                                                        <NoResults 
                                                            text="There is no any tags"
                                                            padding={40}
                                                            imageWidth={250}
                                                        />
                                                    }
                                                </div>
                                            }
                                    </div>





                                }  
                            </div>
                        )
                    }
                {/* </div> */}
            </div>
        </div>
    </div> 
}

function CreateNewTag(){
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
    const [ description, setDescription ] = useState();
    const [ slug, setSlug ] = useState();


    function submitTag (e) {
        e.preventDefault();
        create({
            name: name,
            description: description,
            slug: slug,
        });
        setName();
        setDescription();
        setSlug();
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
                                    title="Tag name"
                                    type="text"
                                    name="name"
                                    value={name}
                                    onChange={setName}
                                    placeholder="Tag name"
                                />
                                <Input 
                                    title="Slug"
                                    type="text"
                                    name="url"
                                    value={slug}
                                    onChange={setSlug}
                                    placeholder="SLug"
                                />
                                <Input 
                                    title="Description"
                                    type="text"
                                    name="url"
                                    value={description}
                                    onChange={setDescription}
                                    placeholder="Description"
                                />
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

function Tags ({id, name, description, slug}){
    const [styleUpdateIcon, setStyleUpdateIcon] = useState("table-button");
    const [styleDeleteIcon, setStyleDeleteIcon] = useState("table-button");
    const [updateFormOpened, setUpdateFormOpened] = useState(false);
    const [deletePopupOpened, setDeletePopupOpened] = useState(false);

    const subdomain = subdomainLogic.values.subdomain;
    const tagLogicBuilt = tagsLogic({subdomain})
    const { remove , updateData} = useActions(tagLogicBuilt)
    const { updateDataAjax } = useValues(tagLogicBuilt)

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
    const [updateTagData, setUpdate] = useState({tagId: id})
    const [ tagName, setName ] = useState(name);
    const [ tagDescription, setDescription ] = useState(description);
    const [ tagSlug, setSlug ] = useState(slug);

    function handleUpdate(e){
        e.preventDefault();
        setStyleUpdateIcon('table-update-popup')
        setUpdateFormOpened(true);
    }
    function handleCancelUpdate(){
        setStyleUpdateIcon('table-button')
        setUpdateFormOpened(false);
    }
    function updateTag(e){
        e.preventDefault();
        updateData({
            tagId: updateTagData.tagId,
            name: name,
            description: description,
            slug:slug,
        });
        setUpdateFormOpened(false); 

        setStyleUpdateIcon('table-button')
        // window.location.reload(false);
    }
    
    return <div>
        <div className="global-table-body">
            <div className="table-body-five">
                <div className="table-item"> {name}</div>
                <div className="table-item"> {slug} </div>
                <div className="table-item"> {description}</div>
                <div className="table-item"> 2000 </div>
                <div className="table-actions">
                    <div className="table-edit" onClick={handleUpdate}>
                        <span className={styleUpdateIcon}>
                            <PencilFill size={10} />
                        </span>
                        {
                            updateFormOpened ?
                                <Popup
                                    header={<PopupHeaderDefault title='Update Tag' />}
                                    body={
                                    <PopupBodyDefault>
                                        <div>
                                            <Input 
                                                title="Name"
                                                type="text"
                                                name="name"
                                                value={tagName}
                                                onChange={setName}
                                                placeholder="Name"
                                            />
                                            <Input 
                                                title="Slug"
                                                type="text"
                                                name="url"
                                                value={tagSlug}
                                                onChange={setSlug}
                                                placeholder="Slug"
                                            />
                                            <Input 
                                                title="Description"
                                                type="text"
                                                name="url"
                                                value={tagDescription}
                                                onChange={setDescription}
                                                placeholder="Description"
                                            />
                                        </div>
                                    </PopupBodyDefault>
                                    }
                                    footer={
                                        <PopupFooterDoubleButton
                                            onCancel={handleCancelUpdate}
                                            onClick={(e)=> {updateTag(e)}}
                                            name='Update'
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
                                    text="Are you sure to delete this tag permanently? You will not be able to access it anymore."
                                    name="Delete"
                                    buttonClass="danger"
                                    onClick={handleDoDelete}
                                    onCancel={handleDeleteCancel}
                                />
                            : null
                        }
                    </div>
                    <div className="table-view">
                        <span className='table-button'>
                            <ArrowBarRight size={10} />
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
}
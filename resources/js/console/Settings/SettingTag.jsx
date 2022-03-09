import React, {useState} from 'react';
import { useActions, useValues } from 'kea';
import { Trash, PencilFill, Plus, ArrowBarRight, CodeSlash} from 'react-bootstrap-icons';
import {toast} from 'react-toastify'
import subdomainLogic from '../logic/subdomainLogic';
import tagsLogic from '../logic/tagsLogic';
import Loader from '../ReusableComponents/Loader';
import Select from '../ReusableComponents/Select';
import Toast from '../ReusableComponents/Toast';
import NoResults from '../ReusableComponents/NoResults';
import Input from '../ReusableComponents/Input';
import { Popup, PopupBodyDefault, PopupConfirm, PopupFooterDoubleButton, PopupHeaderDefault } from '../ReusableComponents/Popup';
import CodemirrorEditor, { CODEMIRROR_MODES } from '../ReusableComponents/CodemirrorEditor';
import DualSetting from '../ReusableComponents/DualSetting';



export default function SettingTag(props) {
    const subdomain = subdomainLogic.values.subdomain;
    const tagsLogicBuilt = tagsLogic({subdomain})
    const { tag, loadAjax, createAjax, tagListHasMore, loadTagsListMoreAjax } = useValues(tagsLogicBuilt)
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
    }

    console.log(tag);
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
        {
            tag.length ?
            <div className="global-table-view">
                <div className="global-table-header-six">      
                    <div className="table-head-item">Name</div> 
                    <div className="table-head-item">Slug</div>
                    <div className="table-head-item">Description</div>
                    <div className="table-head-item">Code</div>
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
                                <div> 
                                    {
                                        loadAjax.status === 'loading' ?
                                            <Loader /> :
                                            <div>
                                                {/* {
                                                    tag.length ? */}
                                                        <div>
                                                            {
                                                                tag.map(tag => (
                                                                    <div className="global-table-body">
                                                                        <Tags key = {tag} id={tag.id} name={tag.name} description={tag.description} slug={tag.slug} codeHead={tag.code_head} codeFoot={tag.code_foot}/>           
                                                                    </div>
                                                                ))
                                                            }
                                                            {
                                                                tagListHasMore == true ?
                                                                    <div>
                                                                        <button type='button' className ="loadMore" onClick={handleScroll}>Load More</button>
                                                                    </div>
                                                                : null
                                                            }
                                                        </div>
                            
                                                    {/* :
                                                    <NoResults 
                                                        text="There are no tags"
                                                        padding={40}
                                                        imageWidth={250}
                                                    />
                                                } */}
                                            </div>
                                    }
                                </div>
                            }  
                        </div>
                    )
                }
            </div>
            :
            <NoResults 
                text="There are no tags"
                padding={40}
                imageWidth={250}
            />
        }
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

function Tags ({id, name, description, slug, codeHead, codeFoot}) {
    const [styleUpdateIcon, setStyleUpdateIcon] = useState("table-button");
    const [styleDeleteIcon, setStyleDeleteIcon] = useState("table-button");
    const [styleCodeIcon, setStyleCodeIcon] = useState("table-button");
    const [updatePopUpOpened, setUpdatePopUpOpened] = useState(false);

    const [deletePopupOpened, setDeletePopupOpened] = useState(false);
    const [codePopupOpened, setCodePopupOpened] = useState(false);


    const subdomain = subdomainLogic.values.subdomain;
    const tagLogicBuilt = tagsLogic({subdomain})
    const { remove , updateData} = useActions(tagLogicBuilt)
    const { updateDataAjax } = useValues(tagLogicBuilt)

    // Code Section

    const [ tagCodeHead, setCodeHead ] = useState(codeHead);
    const [ tagCodeFoot, setCodeFoot ] = useState(codeFoot);

    function handleCode(e){
        e.preventDefault();
        setStyleCodeIcon('table-code-popup')
        setCodePopupOpened(true);
    }

    function handleDoCancelCode(){
        setStyleCodeIcon("table-button");
        setCodePopupOpened(false)
        console.log('test')
        window.location.reload(false);
    }

    function updateCode(e){
        e.preventDefault();
        updateData({
            tagId: updateTagData.tagId,
            codeHead: codeHead,
            codeFoot: codeFoot,
        });
        // setUpdateFormOpened(false); 

        setStyleUpdateIcon('table-button')
        window.location.reload(false);
    }

    // Delete Section
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

    // Update section

    function handleUpdate(e){
        e.preventDefault();
        setStyleUpdateIcon('table-update-popup')
        setUpdatePopUpOpened(true);
    }
    function handleCancelUpdate(){
        setStyleUpdateIcon('table-button')
        setUpdatePopUpOpened(false);
        window.location.reload(false);
    }
    function updateTag(e){
        e.preventDefault();
        updateData({
            tagId: updateTagData.tagId,
            name: name,
            description: description,
            slug:slug,
        });
        setUpdatePopUpOpened(false); 

        setStyleUpdateIcon('table-button')
        window.location.reload(false);
    }
    
    return <div>
        <div className="global-table-body">
            <div className="table-body-six">
                <div className="table-item"> {name}</div>
                <div className="table-item"> {slug} </div>
                <div className="table-item"> {description}</div>
                <div className="table-actions">
                    <div className="table-code" onClick={handleCode}>
                        <span className={styleCodeIcon}>
                            <CodeSlash size={10} />
                        </span>
                        {
                            codePopupOpened ?
                            <div className="popup-width">
                                <Popup
                                    header={<PopupHeaderDefault title='Update Code' />}
                                    body={
                                    <PopupBodyDefault>
                                        <DualSetting 
                                            title="Head Code"
                                            description={
                                                <div className="table-code-pop-input">
                                                     <CodemirrorEditor 
                                                    mode={CODEMIRROR_MODES.twig}
                                                    value={tagCodeHead}
                                                    onChange={setCodeHead}
                                                />
                                                </div>
                                                
                                            }
                                        />
                                        <DualSetting 
                                            title="Foot Code"
                                            description={
                                                <div className="table-code-pop-input">
                                                    <CodemirrorEditor 
                                                        mode={CODEMIRROR_MODES.twig}
                                                        value={tagCodeFoot}
                                                        onChange={setCodeFoot}
                                                    />
                                                </div>
                                            }
                                        />
                                    </PopupBodyDefault>
                                    }
                                    footer={
                                        <PopupFooterDoubleButton
                                            onCancel={handleDoCancelCode}
                                            onClick={(e)=> {updateCode(e)}}
                                            name='Update'
                                        />
                                    }
                                /> 
                            </div>
                            : null
                        }
                    </div>
                </div>

                <div className="table-item"> 2000 </div>

                <div className="table-actions">
                    <div className="table-edit" onClick={handleUpdate}>
                        <span className={styleUpdateIcon}>
                            <PencilFill size={10} />
                        </span>
                       
                        {
                            updatePopUpOpened ?
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
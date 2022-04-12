import React, {useState, useEffect} from 'react';
import { useActions, useValues } from 'kea';
import { Trash, PencilFill, Plus, BoxArrowInRight, CodeSlash} from 'react-bootstrap-icons';
import {toast} from 'react-toastify'
import tagsLogic from '../../logic/tagsLogic';
import languagesLogic from '../../logic/languagesLogic';
import blogsLogic from '../../logic/blogsLogic';
import Input from '../../ReusableComponents/Input';
import { Popup, PopupBodyDefault, PopupConfirm, PopupFooterDoubleButton, PopupHeaderDefault } from '../../ReusableComponents/Popup';
import DualSetting from '../../ReusableComponents/DualSetting';
import CodemirrorEditor, { CODEMIRROR_MODES } from '../../ReusableComponents/CodemirrorEditor';
import TagLanguageSelector from './TagLanguageSelector';



export default function Tags ({tag, subdomain}) 
{
    // I should create the update section. and also I have to find the error which is occurring in the logic.

    const tagLogicBuilt = tagsLogic({subdomain})
    const { remove , updateData} = useActions(tagLogicBuilt)
    const { updateDataAjax } = useValues(tagLogicBuilt)

    // language
    const { languages, getLanguageById } = useValues(languagesLogic({subdomain}))

    const { findBlogBySubdomain } = useValues(blogsLogic)
    
    const [currentLanguageId, setCurrentLanguageId] = useState( findBlogBySubdomain(subdomain).blog.default_language.id );
    const currentLanguage = getLanguageById(currentLanguageId);

    const variants = tag.variants || [];
    const variant = variants[currentLanguageId] || {};


    const [ getName, setName ] = useState(null);
    const [ getDescription, setDescription ] = useState(null);

     // To disable editing in other languages.
     const [pointerEvent, setPointerEvent] = useState();

     // To display data in the table
    //  const [getTableName, setTableName] = useState(null);
     useEffect(() => {
         if(currentLanguage.is_primary === true){
             if(currentLanguage.id === variant.language_id){
                 if(tag.id === variant.tag_id){
                    setName(variant.name)
                    setDescription(variant.description)
                 }
             }
         }
     })
 


     // To display data in the pop-up
     const [variantName, setVariantName] = useState(null);
     const [variantDescription, setVariantDescription] = useState(null);
 
     useEffect(() => {
         if(currentLanguage.is_primary == true)
         {
             setPointerEvent()
             if(currentLanguageId == variant.language_id){
                 if(tag.id == variant.tag_id){
                     setVariantName(variant.name)
                     setVariantDescription(variant.description)
                 }
             }
         }
         else
         {
             setPointerEvent("pointerEvent")
             if(currentLanguageId == variant.language_id){
                 if(tag.id == variant.tag_id){
                    setVariantName(variant.name)
                    setVariantDescription(variant.description)
                 }
             }
         }
     }, [])


    // update section
    const [styleUpdateIcon, setStyleUpdateIcon] = useState("table-button");
    const [updatePopUpOpened, setUpdatePopUpOpened] = useState(false);

    const [updateTagData, setUpdate] = useState({tagId: tag.id})
    const [ tagSlug, setSlug ] = useState(tag.slug);
 
    function handleUpdate(e){
        e.preventDefault();
        setStyleUpdateIcon('table-update-popup')
        setUpdatePopUpOpened(true);
    }
    function handleCancelUpdate(){
        // console.log('test')
        setStyleUpdateIcon('table-button')
        setUpdatePopUpOpened(false);
        window.location.reload(false);
    }
    function updateTag(e){
        e.preventDefault();
        updateData({
            tagId: updateTagData.tagId,
            name: variantName,
            languageId : currentLanguageId,
            description: variantDescription,
            slug: tagSlug,
            codeHead: tagCodeHead,
            codeFoot: tagCodeFoot,
        });
        setUpdatePopUpOpened(false); 
 
        setStyleUpdateIcon('table-button')
        window.location.reload(false);
    }
     

    // Code Section
    const [styleCodeIcon, setStyleCodeIcon] = useState("table-button");
    const [codePopupOpened, setCodePopupOpened] = useState(false);

    const [ tagCodeHead, setCodeHead ] = useState(tag.codeHead);
    const [ tagCodeFoot, setCodeFoot ] = useState(tag.codeFoot);

    function handleCode(e){
        e.preventDefault();
        setStyleCodeIcon('table-code-popup')
        setCodePopupOpened(true);
    }

    function handleDoCancelCode(){
        setStyleCodeIcon("table-button");
        setCodePopupOpened(false)
        window.location.reload(false);
    }

    function updateCode(e){
        e.preventDefault();
        updateData({
            tagId: updateTagData.tagId,
            codeHead: codeHead,
            codeFoot: codeFoot,
        });
        setCodePopupOpened(false)
        setStyleUpdateIcon('table-button')
        window.location.reload(false);
    }

    // Delete Section
    const [styleDeleteIcon, setStyleDeleteIcon] = useState("table-button");
    const [deletePopupOpened, setDeletePopupOpened] = useState(false);

    function handleDelete(e) {
        e.preventDefault();
        setDeletePopupOpened(true);
        setStyleDeleteIcon("table-delete-popup");
    }
    function handleDoDelete() {
        toast("File deleted", {autoClose: 1500});
        remove({
            id:tag.id,
            languageId:currentLanguageId
        });
        setDeletePopupOpened(false);
        setStyleDeleteIcon("table-button");
    }
    function handleDeleteCancel(){
        setStyleDeleteIcon("table-button");
        setDeletePopupOpened(false)
    }

    return <div>
        <div className="global-table-body">
            <div className="table-body-six">
                <div className="table-item"> {getName}</div>
                <div className="table-item"> {tag.slug} </div>
                <div className="table-item"> {getDescription}</div>
                <div className="table-item"> 2000 </div>
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

                <div className="table-actions">
                    <div className="table-edit" onClick={handleUpdate}>
                        <span className={styleUpdateIcon}>
                            <PencilFill size={10} />
                        </span>
                       
                        {
                            updatePopUpOpened ?
                            <div className="popup-width">
                                <Popup
                                    header={
                                        <div>
                                            <PopupHeaderDefault title='Update Tag' />
                                            <TagLanguageSelector 
                                                id={tag.id} 
                                                subdomain={subdomain}
                                                languages={languages} 
                                                variants={variants}
                                                currentLanguageId={currentLanguageId}
                                                onChange={setCurrentLanguageId}
                                            />
                                        </div>
                                    }
                                    body={
                                    <PopupBodyDefault>
                                        <div>
                                            <Input 
                                                title="Name"
                                                type="text"
                                                name="name"
                                                value={variantName}
                                                onChange={setVariantName}
                                                placeholder="Name"
                                            />
                                            <div className={pointerEvent}>
                                            <Input 
                                                title="Slug"
                                                type="text"
                                                name="url"
                                                value={tagSlug}
                                                onChange={setSlug}
                                                placeholder="Slug"
                                            />
                                            </div>
                                            
                                            <div className="popup-type-margin">Description</div>
                                            <textarea 
                                                className="input"
                                                title="Description"
                                                type="text"
                                                name="url"
                                                value={variantDescription}
                                                onChange={setVariantDescription}
                                                placeholder="Description"
                                            ></textarea>
                                            <div className="table-delete">
                                                <span className = 'button danger' onClick={handleDelete}>
                                                    Delete
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
                            </div>
                            : null
                        }
                    </div>
                    <div className="table-view">
                        <span className='table-button'>
                            <BoxArrowInRight size={10} />
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
}

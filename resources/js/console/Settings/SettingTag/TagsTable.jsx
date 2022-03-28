import React, {useState} from 'react';
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
import LanguageSelector from '../../ReusableComponents/LanguageSelector';

import TagLanguageSelector from './TagLanguageSelector';



export default function Tags ({tag, subdomain}) 
{
    const tagLogicBuilt = tagsLogic({subdomain})
    const { remove , updateData, removeVariant, loadVariant} = useActions(tagLogicBuilt)
    const { updateDataAjax, tagVariant } = useValues(tagLogicBuilt)

    // language
    const { languages, getLanguageById } = useValues(languagesLogic({subdomain}))

    const { findBlogBySubdomain } = useValues(blogsLogic)
    
    const [currentLanguageId, setCurrentLanguageId] = useState( findBlogBySubdomain(subdomain).blog.default_language.id );
    const currentLanguage = getLanguageById(currentLanguageId);

    const variants = tag.variants || [];
    const variant = variants[currentLanguageId] || {};

    let getName;
    let getDescription;

    if(tag.id == variant.tag_id){
        getName = variant.name
        getDescription = variant.description
    }



    loadVariant({
        tagId: tag.id,
        languageId: currentLanguageId
    });

    console.log(tagVariant)
    
    // function getName(){
    //     if(tag.id == variant.tag_id){
    //         const nameData =  variant.name;
    //         setGetName(nameData)
    //         return nameData;
    //     }
    // }

    // console.log(currentLanguageId)


    // update section
    const [styleUpdateIcon, setStyleUpdateIcon] = useState("table-button");
    const [updatePopUpOpened, setUpdatePopUpOpened] = useState(false);

    const [updateTagData, setUpdate] = useState({tagId: tag.id})
    const [ tagName, setName ] = useState(getName);
    const [ tagDescription, setDescription ] = useState(getDescription);
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
            slug:tag.slug,
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
        // remove({id});
        removeVariant({
            tagId: updateTagData.tagId,
            languageId: currentLanguageId
        })
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
                                <Popup
                                    header={
                                        <div>
                                            <PopupHeaderDefault title='Update Tag' />
                                            <TagLanguageSelector 
                                                id={tag.id} 
                                                languages={languages} 
                                                variant={variant}
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

import React, {useState, useEffect} from 'react';
import { useActions, useValues } from 'kea';
import { Trash, PencilFill, Plus, BoxArrowInRight, CodeSlash, Link} from 'react-bootstrap-icons';
import {toast} from 'react-toastify'
import navigationLogic from '../../logic/navigationLogic';
import languagesLogic from '../../logic/languagesLogic';
import blogsLogic from '../../logic/blogsLogic';
import Input from '../../ReusableComponents/Input';
import { Popup, PopupBodyDefault, PopupConfirm, PopupFooterDoubleButton, PopupHeaderDefault } from '../../ReusableComponents/Popup';
import DualSetting from '../../ReusableComponents/DualSetting';
import NavigationLanguageSelector from './NavigationLanguageSelector';


export default function Navigation ({navigation, subdomain}) 
{
    // console.log(navigation);
    const navigationLogicBuilt = navigationLogic({subdomain})
    const { remove , updateData} = useActions(navigationLogicBuilt)
    const { updateDataAjax } = useValues(navigationLogicBuilt)

    // Styles
    const [styleDeleteIcon, setStyleDeleteIcon] = useState("table-button");
    const [styleUpdateIcon, setStyleUpdateIcon] = useState("table-button");

    // language
    const { languages, getLanguageById } = useValues(languagesLogic({subdomain}))

    const { findBlogBySubdomain } = useValues(blogsLogic)
     
    const [currentLanguageId, setCurrentLanguageId] = useState( findBlogBySubdomain(subdomain).blog.default_language.id );
    const currentLanguage = getLanguageById(currentLanguageId);
 
    const variants = navigation.variants || [];
    const variant = variants[currentLanguageId] || {};

    const [ getName, setName ] = useState(null);
 
    // To disable editing in other languages.
    const [pointerEvent, setPointerEvent] = useState();
 
    // To display data in the table
    useEffect(() => {
        if(currentLanguage.is_primary === true){
            if(currentLanguage.id === variant.language_id){
                if(navigation.id === variant.navigation_id){
                    setName(variant.name)
                }
            }
        }
    })
 
    console.log(variants)

    // To display data in the pop-up
    const [updateNavigationName, setVariantName] = useState(null);
    useEffect(() => {
        if(currentLanguage.is_primary === true)
        {
            setPointerEvent()
            if(currentLanguageId == variant.language_id){
                if(navigation.id == variant.navigation_id){
                    setVariantName(variant.name)
                }
            }
        }
        if(currentLanguage.is_primary === false)
        // else
        {                    
            console.log("hello")
            setPointerEvent("pointerEvent")
            if(currentLanguage.id == variant.language_id){
                if(navigation.id == variant.navigation_id){
                    console.log(variant.name)
                    setVariantName(variant.name)
                }
            }
        }
    }, [])


    // save the static values like id and type
    const [ id, setId ] = useState(navigation.id);
    const [ type, setType ] = useState(navigation.type);

    // delete section
    const [deletePopupOpened, setDeletePopupOpened] = useState(false);

    function handleDelete(e) {
        e.preventDefault();
        setStyleDeleteIcon("table-delete-popup");
        setDeletePopupOpened(true);
    }
    function handleDoDelete() {
        toast("File deleted", {autoClose: 1500});
        remove({
            id:id,
            languageId:currentLanguageId
        });
        setStyleDeleteIcon("table-button");
        setDeletePopupOpened(false);
    }
    function handleDeleteCancel(){
        setStyleDeleteIcon("table-button");
        setDeletePopupOpened(false)
    }

    // Update Navigation Sections
    const [updateNavigationData, setUpdate] = useState({
        userId: navigation.id,
        type: navigation.type
    })
    const [updateFormOpened, setUpdateFormOpened] = useState(false);
    const [updateNavigationUrl, setUrl] = useState(navigation.url)

    function handleUpdate(e){
        e.preventDefault();
        setStyleUpdateIcon('table-update-popup')
        setUpdateFormOpened(true);
    }
    function handleCancelUpdate(){
        setStyleUpdateIcon('table-button')
        setUpdateFormOpened(false);
    }

    // This is the OnClick handler for the update
    function updateNavigation(e){
        e.preventDefault();
        console.log(type);
        updateData({
            userId: updateNavigationData.userId,
            languageId : currentLanguageId,
            name: updateNavigationName,
            url: updateNavigationUrl,
            type:updateNavigationData.type,
        });
        setUpdate({...updateNavigationData, 
            userId: id,
            type:type
        })
        
        setName(updateNavigationName);
        setUrl(navigation.url);
        // window.location.reload(false);
        setStyleUpdateIcon('table-button')
        setUpdateFormOpened(false);
    }
    

    return <div>
        <div className="global-table-body">
            <div className="table-body-three">
                <div className="table-item"> {getName} </div>
                <div className="table-item"> {navigation.url} </div>
                <div className="table-actions">
                    <div className="table-edit" onClick={handleUpdate}>
                        <span className={styleUpdateIcon}>
                            <PencilFill size={10} />
                        </span>
                    </div>
                    {/* <div className="table-delete" onClick={handleDelete}>
                        <span className={styleDeleteIcon}>
                            <Trash size={10} />
                        </span>
                    </div> */}
                </div>
            </div>
        </div>
        {
            updateFormOpened ? 
            <Popup
                header={
                    <div>
                        <PopupHeaderDefault title='Update Navigation' />
                        <NavigationLanguageSelector 
                            id={navigation.id} 
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
                                value={updateNavigationName}
                                onChange={setVariantName}
                                placeholder="Match Url"
                            />
                            <div className={pointerEvent}>
                                <Input 
                                    title="Url"
                                    type="text"
                                    name="url"
                                    value={updateNavigationUrl}
                                    onChange={setUrl}
                                    placeholder="Redirect Url"
                                />
                            </div>
                        </div>
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
        {/* {
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
        } */}
    </div>
}
import React, {useState, useEffect} from 'react';
import { useActions, useValues } from 'kea';
import { Trash, PencilFill, Plus, BoxArrowInRight, CodeSlash} from 'react-bootstrap-icons';
import {toast} from 'react-toastify'
import subdomainLogic from '../logic/subdomainLogic';
import routesLogic from '../logic/routesLogic';
import Loader from '../ReusableComponents/Loader';
import Select from '../ReusableComponents/Select';
import Toast from '../ReusableComponents/Toast';
import NoResults from '../ReusableComponents/NoResults';
import Input from '../ReusableComponents/Input';
import { Popup, PopupBodyDefault, PopupConfirm, PopupFooterDoubleButton, PopupHeaderDefault } from '../ReusableComponents/Popup';
import DualSetting from '../ReusableComponents/DualSetting';



export default function SettingsRoutes() {


    const subdomain = subdomainLogic.values.subdomain;
    const routeLogicBuilt = routesLogic({subdomain})
    const { route, loadAjax, createAjax, redirectListHasMore, loadRedirectListMoreAjax } = useValues(routeLogicBuilt)
    const { loadRedirectListMore} = useActions(routeLogicBuilt)


    return <div className="settingRoute">
        <div className="route-title-bar">
            <div className="route-title">
                Routes
            </div>
            <div>
                <CreateNewRoute routeLogicBuilt = {routeLogicBuilt}/>
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
                route.length > 0 ?
                <div className="global-table-view">
                    <div className="global-table-header-four">      
                        <div className="table-head-item">Route Name</div> 
                        <div className="table-head-item">Match</div>
                        <div className="table-head-item">Template</div> 
                        <div></div>
                    </div>
                    {
                        loadAjax.status === 'loading' ?
                        <Loader padding={40}/> :
                        (
                            <div>
                                <div>
                                    {
                                        loadAjax.status === 'loading' ?
                                            <Loader />
                                        :
                                        <div>
                                            {
                                                route.map(route => (
                                                    <div className="global-table-body">
                                                        <Routes key = {route.id} routeLogicBuilt = {routeLogicBuilt} route={route}/>
                                                    </div>
                                                ))
                                            }
                                        </div>
                                    }
                                    {/* <div>
                                        <button type='button' className ="loadMore">Load More</button>
                                    </div>      */}
                                </div> 
                            </div>
                        )
                    }
                </div>
                : 
                <NoResults 
                    text="There is no any redirects."
                    padding={40}
                    imageWidth={250}
                />
            }

        </div>
    </div>

}

function CreateNewRoute({routeLogicBuilt}) {

    const { create } = useActions(routeLogicBuilt)  

    const [createPopUpOpened, setCreatePopUpOpened] = useState(false);

    const [ name, setName ] = useState();
    const [ match, setMatch ] = useState();
    const [ template, setTemplate ] = useState();


    function handleCreateCancel(){
        setCreatePopUpOpened(false)
    }
    function handleCreate(e) {
        e.preventDefault();
        setCreatePopUpOpened(true);
    }

    function saveData(e) {
        e.preventDefault();
        create({
            name: name,
            match: match,
            template:template,
        });
        setName();
        setMatch();
        setTemplate();
        setCreatePopUpOpened(false);
        window.location.reload(false);
    }

    return <div>
        {
            <div className="route-title-create-button ">
                <button type='button' className ="button small inactive route-button-popup" onClick={handleCreate}>
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
                    header={<PopupHeaderDefault title='Create New Route' />}
                    body={
                        <PopupBodyDefault>
                            <div>
                                <Input 
                                    title="Route name"
                                    type="text"
                                    name="name"
                                    value={name}
                                    onChange={setName}
                                    placeholder="Route name"
                                />
                                <Input 
                                    title="Match path"
                                    type="text"
                                    name="url"
                                    value={match}
                                    onChange={setMatch}
                                    placeholder="Match path"
                                />
                                <Input 
                                    title="Template"
                                    type="text"
                                    name="url"
                                    value={template}
                                    onChange={setTemplate}
                                    placeholder="Template"
                                />
                            </div>
                        </PopupBodyDefault>
                    }
                    footer={
                        <PopupFooterDoubleButton
                            onCancel={handleCreateCancel}
                            // onClick={(e)=> {submitTag(e)}}
                            onClick={saveData}
                            name='Create'
                        />
                    }
                /> 
            : null
        }
    </div>
}

function Routes({routeLogicBuilt, route}){

    const { remove , updateData} = useActions(routeLogicBuilt)
    const { updateDataAjax } = useValues(routeLogicBuilt)

    const [styleUpdateIcon, setStyleUpdateIcon] = useState("table-button");
    const [styleDeleteIcon, setStyleDeleteIcon] = useState("table-button");

    const [updatePopUpOpened, setUpdatePopUpOpened] = useState(false);
    const [deletePopupOpened, setDeletePopupOpened] = useState(false);

    
    const [ routeName, setRouteName ] = useState(route.name);
    const [ routeMatch, setRouteMatch ] = useState(route.match);
    const [ routeTemplate, setRouteTemplate ] = useState(route.template);
    const [ routePostsFilter, setRoutePostsFilter ] = useState(route.posts_filter);
    const [ routeContentType, setRouteContentType ] = useState(route.content_type);


    // Update section
    function handleUpdate(e) {
        e.preventDefault();
        setStyleUpdateIcon('table-update-popup')
        setUpdatePopUpOpened(true);
    }
    function handleUpdateCancel(){
        setStyleUpdateIcon('table-button')
        setUpdatePopUpOpened(false)
    }
    function submitUser (e) {
        e.preventDefault();
        setStyleUpdateIcon('table-button')
        updateData({
            id:route.id,
            name: routeName,
            match: routeMatch,
            template:routeTemplate,
            postsFilter: routePostsFilter,
            contentType:routePostsFilter,
        });
        setUpdatePopUpOpened(false)
        window.location.reload(false);
    }

     // Delete Section
    function handleDelete(e) {
        e.preventDefault();
        setDeletePopupOpened(true);
        setStyleDeleteIcon("table-delete-popup");
    }
    function handleDeleteCancel(){
        setStyleDeleteIcon("table-button");
        setDeletePopupOpened(false)
    } 
    function handleDoDelete() {
        toast("Route deleted", {autoClose: 1500});
        remove({id:route.id});

        setDeletePopupOpened(false);
        setStyleDeleteIcon("table-button");
    }

    return <div>
        <div className="global-table-body">
            <div className="table-body-four">
                <div className="table-item"> {route.name}</div>
                <div className="table-item"> {route.match} </div>
                <div className="table-item"> {route.template}</div>
                
                <div className="table-actions">
                    <div className="table-edit" >
                        <span className={styleUpdateIcon} onClick={handleUpdate}>
                            <PencilFill size={10} />
                        </span>

                        {
                            updatePopUpOpened ?
                                <div className="popup-width">
                                    <Popup
                                        header={ <PopupHeaderDefault title='Update Route' /> }
                                        body={
                                            <PopupBodyDefault>
                                                <div>
                                                    <Input 
                                                        title="Route Name"
                                                        type="text"
                                                        name="name"
                                                        value={routeName}
                                                        onChange={setRouteName}
                                                        placeholder="Route name"
                                                    />
                                                    <Input 
                                                        title="Match Path"
                                                        type="text"
                                                        name="url"
                                                        value={routeMatch}
                                                        onChange={setRouteMatch}
                                                        placeholder="Match Path"
                                                    />
                                                     <Input 
                                                        title="Template"
                                                        type="text"
                                                        name="name"
                                                        value={routeTemplate}
                                                        onChange={setRouteTemplate}
                                                        placeholder="Template"
                                                    />

                                                    <DualSetting 
                                                        left={
                                                            <Input 
                                                                title="Posts Filter"
                                                                type="text"
                                                                name="url"
                                                                value={routePostsFilter}
                                                                onChange={setRoutePostsFilter}
                                                                placeholder="Posts Filter"
                                                            />
                                                        }
                                                        right={
                                                            <Input 
                                                                title="Content Type"
                                                                type="text"
                                                                name="name"
                                                                value={routeContentType}
                                                                onChange={setRouteContentType}
                                                                placeholder="Content Type"
                                                            />
                                                        }
                                                    />
                                                </div>
                                            </PopupBodyDefault>
                                        }
                                        footer={
                                            <PopupFooterDoubleButton
                                                onCancel={handleUpdateCancel}
                                                onClick={(e)=> {submitUser(e)}}
                                                name='Update'
                                            />
                                        }
                                    /> 
                                </div>
                            : null
                        }
                    </div>

                    {
                        routeName === ('post' || 'page') ? null
                        :
                            <div className="table-delete">
                                <span className={styleDeleteIcon} onClick={handleDelete}>
                                    <Trash size={10} />
                                </span>
                                {
                                    deletePopupOpened ?
                                        <PopupConfirm
                                            title="Delete Permanently"
                                            text="Are you sure to delete this route permanently? You will not be able to access it anymore."
                                            name="Delete"
                                            buttonClass="danger"
                                            onClick={handleDoDelete}
                                            onCancel={handleDeleteCancel}
                                        />
                                    : null
                                }
                            </div>
                    }


                </div>

            </div>
        </div>
    </div>
}
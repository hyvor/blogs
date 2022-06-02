import React, {useState} from 'react';
import { useActions, useValues } from 'kea';
import subdomainLogic from '../../logic/subdomainLogic';
import navigationLogic from '../../logic/navigationLogic';
import { Trash, PencilFill, CheckCircleFill, Plus, HddStackFill} from 'react-bootstrap-icons';
import Loader from '../../ReusableComponents/Loader';
import {toast} from 'react-toastify'
import Select from '../../ReusableComponents/Select';
import Toast from '../../ReusableComponents/Toast';
import NoResults from '../../ReusableComponents/NoResults';
import { Popup, PopupBodyDefault, PopupConfirm, PopupFooterDoubleButton, PopupHeaderDefault } from '../../ReusableComponents/Popup';
import Input from '../../ReusableComponents/Input';
// import { DragDropContext, Droppable, Draggable, resetServerContext } from 'react-beautiful-dnd';
import axios from "axios";
import { components } from 'react-select';
import { ReactSortable } from "react-sortablejs";
import { list } from 'postcss';

import CreatePopup from './CreateNavigation';
import Navigation from './NavigationTable';


export default function Navigations(props)
{

    return null;
    const subdomain = subdomainLogic.values.subdomain;
    const navigationLogicBuilt = navigationLogic({subdomain})
    const { navigation, loadAjax, createAjax } = useValues(navigationLogicBuilt)
    const { updateItemNumber, updateSourceNav} = useActions(navigationLogicBuilt)

    function saveItemNumber(NavigationId,destinationId, sourceId) {
        // console.log("Navigation" + NavigationId)
        // console.log("Destination ID" + destinationId)

        NavigationId= parseInt(NavigationId)
        destinationId= parseInt(destinationId)
        updateItemNumber({ NavigationId, destinationId });
        updateSourceNav({ destinationId, sourceId });

        //  window.location.reload(false);
    }

    const onDragEnd = (params) => {
        const sourceIndex = params.source.index;
        const destinationIndex = params.destination.index;
        const { destination, source } = params;                                
        navigation.splice(destinationIndex, 0, navigation.splice(sourceIndex, 1)[0]);

        if(!destination || !source){
            return;
        }
        if (destination.droppableId === source.droppableId && destination.index === source.index) {
            return;
        }

        // console.log("droppable ID " + destination.droppableId + ' source droppable id '+source.droppableId)

        // const getDraggableId = params.draggableId;
        // const reOrderedNavigation = navigation.map((navigation) => {
        //     if (navigation.id === getDraggableId.substring(10)) {

        //         console.log("CONDITION 1", navigation);
        //         navigation.itemNumber = destinationIndex;
        //         return navigation;
        //     }
        //     else {
        //       console.log("CONDITION 3", navigation);
        //       return navigation;
        //     }
        //   });
        //   saveItemNumber(reOrderedNavigation)

        const destinationId = destinationIndex + 1;
        const sourceId = sourceIndex + 1;
        const getDraggableId = params.draggableId;
        const NavigationId = getDraggableId.substring(10);

        // updateItemNumber({NavigationId, destinationId});
        return saveItemNumber(NavigationId, destinationId, sourceId);
    }

    return <div className="navigation-view">
        <div className="title">
            Navigation 
        </div>
        {
            loadAjax.status === 'loading' ?
                <Loader/> :
                (
                    <div className="global-table-view">
                        <DragDropContext onDragEnd={onDragEnd}>
                            <div className="navigation-title-bar">
                                <div className="navigation-title">
                                    Header Navigation
                                </div>
                                <CreatePopup type="header"/>
                            </div>
                            {
                                navigation.length > 0 ?
                                <div className="global-table-view">
                                    <div className="global-table-header-three">      
                                        <div className="table-head-item table-header-margin">Name</div> 
                                        <div className="table-head-item table-header-margin">Url</div>
                                        <div></div>
                                    </div>
                                    <Droppable droppableId="droppable-1">
                                        {(provided, _ ) => (
                                            <div
                                                ref={provided.innerRef}
                                                {...provided.droppableProps}
                                            >
                                                {
                                                    navigation.map((navigation, i) => (
                                                    // navigation.map((navigation) => (
                                                        navigation.type === 'header' ?
                                                            <div>
                                                                <Draggable draggableId={"draggable-"+navigation.id} index={i} key={navigation.id}>
                                                                {/* <Draggable key = {navigation.id} draggableId={"draggable-"+navigation.id} index={navigation.itemNumber}> */}
                                                                    {(provided, snapshot) => (
                                                                        <div 
                                                                            ref={provided.innerRef} 
                                                                            {...provided.draggableProps} 
                                                                            // // style={{ }}
                                                                        >
                                                                            {
                                                                                <div className="sort">
                                                                                    <div {...provided.dragHandleProps}>
                                                                                        <HddStackFill size={10} />
                                                                                    </div>
                                                                                    {/* <div className="global-table-body"> */}
                                                                                        <Navigation key={navigation} navigation={navigation} subdomain ={subdomain}/>
                                                                                    {/* </div> */}
                                                                                </div>
                                                                                // <Navigation id ={navigation.id} name = {navigation.name} url = {navigation.url} type = {navigation.type} />
                                                                            }
                                                                        </div>
                                                                    )}
                                                                </Draggable>
                                                            </div>
                                                        : <div></div>
                                                    ))
                                                }
                                                {provided.placeholder}
                                            </div>
                                        )}
                                    </Droppable>
                                </div> : 
                                <NoResults 
                                    text="There is no any redirects."
                                    padding={40}
                                    imageWidth={250}
                                />
                            } 
                        </DragDropContext>

                        {/* ------- Footer Navigation Section -------- */}
                            
                            {/* <DragDropContext onDragEnd={(params) => { 
                                   const sourceIndex = params.source.index;
                                   const destinationIndex = params.destination.index;
                                   const { destination, source } = params;                                
                                   navigation.splice(destinationIndex, 0, navigation.splice(sourceIndex, 1)[0]);
   
                                   if(!destination || !source){
                                       return;
                                   }
                                   if (destination.droppableId === source.droppableId && destination.index === source.index) {
                                       return;
                                   }
   
                                   const destinationId = destinationIndex + 1;
                                   const sourceId = sourceIndex + 1;
                                   const getDraggableId = params.draggableId;
                                   const NavigationId = getDraggableId.substring(10);
   
                                   return saveItemNumber(NavigationId, destinationId, sourceId);

                            }}
                            > */}
                            <DragDropContext onDragEnd={onDragEnd}>

                                <div className="navigation-title-bar">
                                    <div className="navigation-title">
                                        Footer Navigation
                                    </div>
                                    <CreatePopup type="footer"/>
                                </div>
                                {
                                    navigation.length > 0 && (
                                        <div className="global-table-view">
                                            <div className="global-table-header-three">      
                                                <div className="table-head-item table-header-margin">Name</div> 
                                                <div className="table-head-item table-header-margin">Url</div>
                                                <div></div>
                                            </div>
                                            <Droppable droppableId="droppable-1">
                                                {(provided, _ ) => (
                                                    <div
                                                        ref={provided.innerRef}
                                                        {...provided.droppableProps}
                                                    >
                                                        {
                                                            navigation.map((navigation, i) => (
                                                                navigation.type === 'footer' ?
                                                                    // <div>
                                                                    //     {
                                                                    //         <Navigation id ={navigation.id} name = {navigation.name} url = {navigation.url} type = {navigation.type}/>
                                                                    //     }
                                                                    // </div>
                                                                    <div>
                                                                        <Draggable key = {navigation.id} draggableId={"droppable-"+navigation.id} index={i}>
                                                                            {(provided, snapshot) => (
                                                                                <div 
                                                                                    ref={provided.innerRef} 
                                                                                    {...provided.draggableProps} 
                                                                                    {...provided.dragHandleProps}
                                                                                >
                                                                                    {
                                                                                        <div className="sort">
                                                                                        <div {...provided.dragHandleProps}>
                                                                                            <HddStackFill size={10} />
                                                                                        </div>
                                                                                        <Navigation key={navigation} navigation={navigation} subdomain ={subdomain}/>
                                                                                    </div>
                                                                                        // <Navigation id ={navigation.id} name = {navigation.name} url = {navigation.url} type = {navigation.type}  />
                                                                                    }
                                                                                </div>
                                                                            )}
                                                                        </Draggable>
                                                                    </div>
                                                                : null
                                                            ))
                                                        }
                                                        {provided.placeholder}
                                                    </div>
                                                )}
                                            </Droppable>
                                        </div>
                                    )
                                } 
                            </DragDropContext>
                    </div>
                )
        }
    </div>
}















// function CreatePopup({type}) {

//     const subdomain = subdomainLogic.values.subdomain;
//     const navigationLogicBuilt = navigationLogic({subdomain})
//     const { create } = useActions(navigationLogicBuilt)

//     const [createPopUpOpened, setCreatePopUpOpened] = useState(false);

//     function handleCreateCancel(){
//         console.log(type)
//         setCreatePopUpOpened(false)
//     }

//     function handleCreate(e) {
//         e.preventDefault();
//         setCreatePopUpOpened(true);
//     }

//     const [ name, setName ] = useState();
//     const [ url, setUrl ] = useState();

//     // const [createNewNavigation, setData] = useState({type: ""});
//     // const selectOptions = [
//     //     { label: 'Header', value: 'header' },
//     //     { label: 'Footer', value: 'footer'}
//     // ];
//     // function handleType({value}){
//     //     console.log(value)
//     //     setData({...createNewNavigation, 
//     //         type: value
//     //     })
//     // }

//     // OnClick handler for creating an new navigation
//     function submitNavigationData (e) {
//         console.log(type)
//         e.preventDefault();
//         create({
//             name: name,
//             url: url,
//             type: type,
//         });
//         setName();
//         setUrl();
//         setCreatePopUpOpened(false)
//     }

//     return <div>
//             <div className="navigation-title-create-button ">
//             <button type='button' className ="button small inactive navigation-button-popup" onClick={handleCreate}>
//                 <div className="popup-button-content">
//                     <div className="popup-button-content-text">Create</div> 
//                     <Plus />
//                 </div>
//             </button>
//             </div>
//             {
//                 createPopUpOpened ?
//                     <Popup
//                         header={<PopupHeaderDefault title='Create Navigation' />}
//                         body={
//                             <PopupBodyDefault>
//                                 <div>
//                                     <Input 
//                                         title="Name"
//                                         type="text"
//                                         name="name"
//                                         value={name}
//                                         onChange={setName}
//                                     />
//                                     <Input 
//                                         title="Url"
//                                         type="text"
//                                         name="url"
//                                         value={url}
//                                         onChange={setUrl}
//                                     />
//                                 </div>
//                             </PopupBodyDefault>
//                         }
//                         footer={
//                             <PopupFooterDoubleButton
//                                 onCancel={handleCreateCancel}
//                                 onClick={(e)=> {submitNavigationData(e)}}
//                                 name='Create'
//                             />
//                         }
//                     /> 
//                 : null
//             }
//     </div>
// }












// function Navigation ({id, name, url, type}){

//     const subdomain = subdomainLogic.values.subdomain;
//     const navigationLogicBuilt = navigationLogic({subdomain})
//     const { remove , updateData} = useActions(navigationLogicBuilt)
//     const { updateDataAjax } = useValues(navigationLogicBuilt)

//     // Styles
//     const [styleDeleteIcon, setStyleDeleteIcon] = useState("table-button");
//     const [styleUpdateIcon, setStyleUpdateIcon] = useState("table-button");

//     // delete section
//     const [deletePopupOpened, setDeletePopupOpened] = useState(false);

//     function handleDelete(e) {
//         e.preventDefault();
//         setStyleDeleteIcon("table-delete-popup");
//         setDeletePopupOpened(true);
//     }
//     function handleDoDelete() {
//         console.log(id);
//         toast("File deleted", {autoClose: 1500});
//         remove({id});
//         setStyleDeleteIcon("table-button");
//         setDeletePopupOpened(false);
//     }
//     function handleDeleteCancel(){
//         setStyleDeleteIcon("table-button");
//         setDeletePopupOpened(false)
//     }

//     // Update Navigation Sections
//     const [updateNavigationData, setUpdate] = useState({
//         userId: id,
//         type: type
//     })
//     const [updateFormOpened, setUpdateFormOpened] = useState(false);
//     const [updateNavigationName, setName] = useState(name)
//     const [updateNavigationUrl, setUrl] = useState(url)

//     function handleUpdate(e){
//         e.preventDefault();
//         setStyleUpdateIcon('table-update-popup')
//         setUpdateFormOpened(true);
//     }
//     function handleCancelUpdate(){
//         setStyleUpdateIcon('table-button')
//         setUpdateFormOpened(false);
//     }

//     // function handleNavigationName(e) {
//     //     e.preventDefault();
//     //     setUpdate({...updateNavigationData, 
//     //         name: e.target.value
//     //     })
//     //     if(updateNavigationData.name != e.target.value){
//     //         setStyleUpdateIcon("icon-navigation navigation-edit");
//     //     }
//     //     if(updateNavigationData.name === name){
//     //         setStyleUpdateIcon("hide-navigation-edit");
//     //     }
//     // }

//     // function handleNavigationUrl(e) {
//     //     e.preventDefault();
//     //     setUpdate({...updateNavigationData, 
//     //         url: e.target.value
//     //     })
//     //     if(updateNavigationData.url != e.target.value){
//     //         setStyleUpdateIcon("icon-navigation navigation-edit");
//     //     }else{
//     //         setStyleUpdateIcon("hide-navigation-edit");
//     //     }
//     // }

//     // This is the OnClick handler for the update
//     function updateNavigation(e){
//         e.preventDefault();
//         console.log(type);
//         updateData({
//             userId: updateNavigationData.userId,
//             name: updateNavigationName,
//             url: updateNavigationUrl,
//             type:updateNavigationData.type,
//         });
//         setUpdate({...updateNavigationData, 
//             userId: id,
//             type:type
//         })
//         setName(name);
//         setUrl(url);
//         window.location.reload(false);
//         setStyleUpdateIcon('table-button')
//         setUpdateFormOpened(false);
//     }
    

//     return <div>
//         <div className="global-table-body">
//             <div className="table-body-three">
//                 {/* <span className="get-navigation-name">About</span>
//                 <span className="get-navigation-url">https://sipes.com/quisquam-eos-eos-nulla-vel-minima-amet.html/dddd/ffff/gggg</span>
//                 <input className="get-navigation-name" value={updateNavigationData.name}  onChange={(e)=> {handleNavigationName(e)}} />
//                 <input className="get-navigation-url" value={updateNavigationData.url} onChange={(e)=> {handleNavigationUrl(e)}}/> */}
//                 <div className="table-item"> 
//                     {/* <div className="sort">
//                         <div className="sort-icon"
//                             ref={provided.innerRef} 
//                             {...provided.draggableProps} 
//                             {...provided.dragHandleProps}
//                         >
//                             <HddStackFill size={10} />
//                         </div>
//                         {name}
//                     </div> */}
//                     {name}
//                 </div>
//                 <div className="table-item"> {url} </div>
//                 <div className="table-actions">
//                     <div className="table-edit" onClick={handleUpdate}>
//                         <span className={styleUpdateIcon}>
//                             <PencilFill size={10} />
//                         </span>
//                     </div>
//                     <div className="table-delete" onClick={handleDelete}>
//                         <span className={styleDeleteIcon}>
//                             <Trash size={10} />
//                         </span>
//                     </div>
//                 </div>
//             </div>
//         </div>
//         {
//             updateFormOpened ? 
//             <Popup
//                 header={<PopupHeaderDefault title='Update Navigation' />}
//                 body={
//                     <PopupBodyDefault>
//                         <div>
//                             <Input 
//                                 title="Name"
//                                 type="text"
//                                 name="name"
//                                 value={updateNavigationName}
//                                 onChange={setName}
//                                 placeholder="Match Url"
//                             />
//                             <Input 
//                                 title="Url"
//                                 type="text"
//                                 name="url"
//                                 value={updateNavigationUrl}
//                                 onChange={setUrl}
//                                 placeholder="Redirect Url"
//                             />
//                         </div>
//                     </PopupBodyDefault>
//                 }
//                 footer={
//                     <PopupFooterDoubleButton
//                         onCancel={handleCancelUpdate}
//                         onClick={(e)=> {updateNavigation(e)}}
//                         name='Update'
//                     />
//                 }
//             /> 
//             : null
//         }
//         {
//             deletePopupOpened ?
//                 <PopupConfirm
//                     title="Delete Permanently"
//                     text="Are you sure you won't to delete this navigation."
//                     name="Delete"
//                     buttonClass="danger"
//                     onClick={handleDoDelete}
//                     // onCancel={() => setDeletePopupOpened(false)}
//                     onCancel={handleDeleteCancel}
//                 />
//             : null
//         }




































// {/* <div className="global-table-view">


// <div className="global-table-header-four">      
//                         <div className="table-head-item">Name</div> 
//                         <div className="table-head-item">Code</div>
//                         <div>No. of Posts</div>
//                         <div></div>
//                     </div>


//  <div className="global-table-body">
// <div className="table-body-four"> 
//         <div className="table-item">
//             <span>About</span>
//         </div>
//         <div className="table-item">Hi</div>
//         <div className="table-item">Test</div>
//         <div className="table-actions">
//             <div className="table-edit">
//                 <span className="table-button">
//                     <Trash size={10} />
//                 </span>
//             </div>
//             <div className="table-delete">
//                 <span className="table-delete-popup">
//                     <Plus size={10} />
//                 </span>
//             </div>
//         </div>
//     </div>
//     </div>

// </div> */}































//  </div>







//     // return <div>
//     //    <div>
//     //         <div className="get-navigation">
//     //             {/* <span className="get-navigation-name">About</span>
//     //             <span className="get-navigation-url">https://sipes.com/quisquam-eos-eos-nulla-vel-minima-amet.html/dddd/ffff/gggg</span> */}
//     //             <input className="get-navigation-name" value={updateNavigationData.name}  onChange={(e)=> {handleNavigationName(e)}} />
//     //             <input className="get-navigation-url" value={updateNavigationData.url} onChange={(e)=> {handleNavigationUrl(e)}}/>
//     //             <button className={styleDeleteIcon} onClick={handleDelete}><Trash size={15} /></button>
//     //             <button className={styleUpdateIcon} onClick={updateNavigation}><CheckCircleFill size={15} /></button>
//     //         </div>
//     //     </div>
//     //     {
//     //         deletePopupOpened ?
//     //             <PopupConfirm
//     //                 title="Delete Permanently"
//     //                 text="Are you sure you won't to delete this navigation."
//     //                 name="Delete"
//     //                 buttonClass="danger"
//     //                 onClick={handleDoDelete}
//     //                 // onCancel={() => setDeletePopupOpened(false)}
//     //                 onCancel={handleDeleteCancel}
//     //             />
//     //             : null
//     //     }
//     // </div>
// }

// function SelectType( { options, onChange} ) {
//     return <div className='inside-select'>
//         <Select 
//             type="small" 
//             options={options} 
//             onChange={onChange}
//         />
//     </div>
// }


// // function CreateNavigation() {

// //     const subdomain = subdomainLogic.values.subdomain;
// //     const navigationLogicBuilt = navigationLogic({subdomain})
// //     const { create } = useActions(navigationLogicBuilt)


// //     const [createNewNavigation, setData] = useState({
// //         name: "",
// //         url: "",
// //         type: ""
// //     });
// //     function handleNavigationName(e) {
// //         e.preventDefault();
// //         setData({...createNewNavigation, 
// //             name: e.target.value
// //         })
// //     }
// //     function handleNavigationUrl(e) {
// //         e.preventDefault();
// //         setData({...createNewNavigation, 
// //             url: e.target.value
// //         })
// //     }
// //     function handleType({value}){
// //         console.log(value)
// //         setData({...createNewNavigation, 
// //             type: value
// //         })
// //     }

// //     // OnClick handler for creating an new navigation
// //     function submitNavigationData (e) {
// //         e.preventDefault();
// //         console.log(createNewNavigation.type)

// //         create({
// //             name: createNewNavigation.name,
// //             url: createNewNavigation.url,
// //             type: createNewNavigation.type,
// //         });
// //         setData({
// //             name: "",
// //             url: "",
// //             type: ""
// //         });
// //     }

// //     const selectOptions = [
// //         { label: 'Header', value: 'header' },
// //         { label: 'Footer', value: 'footer'}
// //     ];

// //     return <div>
// //         <div>
// //             <div className="create-navigation">
// //                 <input type="text" value={createNewNavigation.name} className="create-navigation-name" onChange={(e)=> {handleNavigationName(e)}} required/>
// //                 <input type="text" value={createNewNavigation.url} className="create-navigation-url" onChange={(e)=> {handleNavigationUrl(e)}} required/>
// //                 <div className="create-navigation-select">
// //                     <SelectType options={selectOptions} onChange={handleType}/>
// //                 </div>
// //                 <button className="button small navigation-create-button" onClick={(e)=> {submitNavigationData(e)}}>Create</button>
// //             </div>
// //         </div>
// //     </div>
// // }



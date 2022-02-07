import React, {useState} from 'react';
import { useActions, useValues } from 'kea';
import subdomainLogic from '../logic/subdomainLogic';
import navigationLogic from '../logic/navigationLogic';
import { Trash, PencilFill ,BackspaceReverseFill ,CheckCircleFill} from 'react-bootstrap-icons';
import { PopupConfirm } from '../ReusableComponents/Popup';
import Loader from '../ReusableComponents/Loader';
import {toast} from 'react-toastify'
import Toast from '../ReusableComponents/Toast';



export default function SettingNavigations(props) {

    const subdomain = subdomainLogic.values.subdomain;
    const navigationLogicBuilt = navigationLogic({subdomain})
    const { navigation, loadAjax, createAjax } = useValues(navigationLogicBuilt)
    const { remove, updateData } = useActions(navigationLogicBuilt)

    
    console.log(navigation)

    return <div>
        {
                    loadAjax.status === 'loading' ?
                    <Loader/> :
                    (
                        <div>
                           {navigation.length > 0 && (
                                <div>
                                    {navigation.map(navigation => (
                                       <div>
                                           {navigation.navigation_name}<br></br>
                                           {navigation.navigation_url}<br></br>
                                           {navigation.type}
                                        </div>
                                    
                                    ))}
                                </div>
                            )} 
                        </div>
                    )
        }
    </div>
}


function CreateNavigation() {

}


function GetNavigation (){
 
}


import React, {useState} from 'react';
import { useActions, useValues } from 'kea';
import navigationLogic from '../../logic/navigationLogic';
import { Plus} from 'react-bootstrap-icons';
import Loader from '../../ReusableComponents/Loader';
import getSubdomain from "../../logic-helpers/subdomain";
import NavigationTable from './NavigationTable';
import CreateUpdateNavigationPopup from "./CreateUpdateNavigationPopup";

export default function Navigations() {

    const navigationLogicInst = navigationLogic({subdomain: getSubdomain()});
    const { loadAjax, headerNavigations, footerNavigations } = useValues(navigationLogicInst)

    const [isCreating, setIsCreating] = useState(false);

    return <div className="settings-navigations">
        <div className="title">
            Navigation <button
                className="button small inactive"
                onClick={() => setIsCreating(true)}
            >Create <Plus/></button>
        </div>

        {
            loadAjax.status === 'loading' ?
                <Loader/> :
                <div>
                    <NavigationTable type="header" navigations={headerNavigations} />
                    <NavigationTable type="footer" navigations={footerNavigations} />
                </div>
        }

        {
            isCreating &&
            <CreateUpdateNavigationPopup onClose={() => setIsCreating(false)} />
        }
    </div>

}

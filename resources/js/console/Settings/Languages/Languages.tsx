import { useActions, useValues } from 'kea';
import React, {Fragment} from 'react';
import { useState } from 'react';
import { Plus } from 'react-bootstrap-icons';
import languagesLogic from '../../logic/languagesLogic';
import Loader from '../../ReusableComponents/Loader';
import Language from "./Language";
import {Table, TableHead, TableHeadItem} from "../../ReusableComponents/Table";
import CreateUpdateLanguagePopup from "./CreateUpdateLanguagePopup";
import getSubdomain from "../../logic-helpers/subdomain";

export default function Languages() {

    const subdomain = getSubdomain()
    const languageLogicInst = languagesLogic({subdomain});
    const { languages, loadAjax } = useValues(languageLogicInst);
    const { remove } = useActions(languageLogicInst);

    const [ isCreating, setIsCreating ] = useState(false);

    return <div className="setting-languages">
        <div className="title">
            Languages <button
                className="button small inactive"
                onClick={() => setIsCreating(true)}
            >New <Plus /></button>
        </div>

        <div className="languages-view">
            {
                loadAjax.status === 'loading' ?
                <Loader 
                    padding={100}
                /> :
                <Table>

                    <TableHead>
                        <TableHeadItem>Name</TableHeadItem>
                        <TableHeadItem>Code</TableHeadItem>
                        <div />
                    </TableHead>

                    <Fragment>
                        {
                            languages.map(lang => <Language
                                key={lang.id}
                                language={lang}
                                remove={remove}
                            />)
                        }
                    </Fragment>

                    {
                        isCreating ?
                        <CreateUpdateLanguagePopup onCancel={() => setIsCreating(false)} /> :
                        null
                    }

                </Table>
            }
        </div>
    </div>

}
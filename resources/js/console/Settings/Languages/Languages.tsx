import { useActions, useValues } from 'kea';
import React, {Fragment} from 'react';
import { useState } from 'react';
import { ArrowUpCircleFill, Plus } from 'react-bootstrap-icons';
import NavLink from '../../ReusableComponents/NavLink'
import { isBlogInTeamPlan } from '../../lib/blog-helpers';
import languagesLogic from '../../logic/languagesLogic';
import subdomainLogic from '../../logic/subdomainLogic';
import Callout from '../../ReusableComponents/Callout';
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

    const isInTeamPlan = isBlogInTeamPlan(subdomain)

    const [ isCreating, setIsCreating ] = useState(false);

    return <div className="setting-languages">
        <div className="title">
            Languages { isInTeamPlan ? <button
                className="button small inactive"
                onClick={() => setIsCreating(true)}
            >New <Plus /></button> : null }
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
                        !isInTeamPlan ?
                        <div className="upgrade-view">
                            <Callout 
                                icon={<ArrowUpCircleFill />}
                                color="orange"
                                title="Upgrade to add more languages"
                                text={<div>Upgrade to the <b>Team</b> or <b>Enterprise</b> plan to add more languages and enable multi-language features.
                                <div style={{marginTop: 10}}>
                                    <NavLink
                                        className="button small orange"
                                        href={`/console/${subdomain}/billing`}
                                    >Upgrade Now</NavLink>
                                </div>
                                </div>}
                            />
                        </div> : null
                    }

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
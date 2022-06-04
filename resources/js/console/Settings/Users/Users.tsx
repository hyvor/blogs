import React, {Fragment, useEffect, useState} from 'react';
import { useActions, useValues } from 'kea';
import subdomainLogic from '../../logic/subdomainLogic';
import usersLogic from '../../logic/usersLogic';
import Loader from '../../ReusableComponents/Loader';
import Toast from '../../ReusableComponents/Toast';
import NoResults from '../../ReusableComponents/NoResults';
import CreateNewUser from './CreateNewUser';
import User from "./User";
import getSubdomain from "../../logic-helpers/subdomain";
import {Table, TableHead, TableHeadItem, TableRow} from "../../ReusableComponents/Table";
import {Plus} from "react-bootstrap-icons";

export default function Users() {

    const usersLogicBuilt = usersLogic({subdomain: getSubdomain()})
    const { usersList, users, loadAjax, createAjax, loadMoreAjax } = useValues(usersLogicBuilt)
    const { load, loadMore } = useActions(usersLogicBuilt)

    useEffect(() => {
        load();
    }, [])


    return <div className="settings-users">

        <div className="title">
            Users <button
                className="button small inactive"
                onClick={() => setIsCreating(true)}
            >Add <Plus/></button>
        </div>

        <div>
            {
                loadAjax.status === 'loading' ?
                    <Loader padding={40}/> 
                :
                (
                    usersList.length ?
                        <Table>
                            <TableHead>
                                <TableHeadItem>Name</TableHeadItem>
                                <TableHeadItem>Slug</TableHeadItem>
                                <TableHeadItem>Posts</TableHeadItem>
                                <TableHeadItem>Role</TableHeadItem>
                                <div />
                            </TableHead>
                            <Fragment>
                                {
                                    usersList.map(userId => <User user={users[userId]} />)
                                }
                            </Fragment>
                        </Table>

                    :
                    <NoResults
                        text="There are no users"
                        padding={40}
                        imageWidth={250}
                    />
                )
            }
        </div>
    </div> 
}
import React, {Fragment} from 'react';
import {Navigation, NavigationType} from "../../types";
import {Table, TableHead, TableHeadItem} from "../../ReusableComponents/Table";
import NoResults from "../../ReusableComponents/NoResults";
import NavigationComponent from './Navigation';

interface NavigationTableProps {
    navigations: Navigation[],
    type: NavigationType
}

export default function NavigationTable({navigations, type} : NavigationTableProps) {

    return <div className="nav-table-wrap">
        <div className="nav-title">{ type === 'header' ? "Header" : "Footer" } Navigation</div>
        <div className="nav-table">

            {

                navigations.length ?

                    <Table>

                        <TableHead>
                            <TableHeadItem>Name</TableHeadItem>
                            <TableHeadItem>URL</TableHeadItem>
                            <div/>
                        </TableHead>

                        <Fragment>
                            {
                                navigations.map(nav => <NavigationComponent navigation={nav} />)
                            }
                        </Fragment>

                    </Table>

                    :

                    <NoResults text="No navigations" />

            }

        </div>
    </div>

}
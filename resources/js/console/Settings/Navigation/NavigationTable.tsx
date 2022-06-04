import React, {Fragment} from 'react';
import {Navigation, NavigationType} from "../../types";
import {Table, TableHead, TableHeadItem} from "../../ReusableComponents/Table";
import NoResults from "../../ReusableComponents/NoResults";
import NavigationComponent from './Navigation';
import {ReactSortable} from "react-sortablejs";
import {useNavigationActions} from "./useNavigation";

interface NavigationTableProps {
    navigations: Navigation[],
    type: NavigationType
}

export default function NavigationTable({navigations, type} : NavigationTableProps) {

    const { updateSortValue, saveSort } = useNavigationActions();

    function setSort(navigations: Navigation[]) {
        navigations.forEach((nav, i) => updateSortValue(nav.id, i + 1));
    }

    function handleSort() {
        saveSort({type})
    }

    return <div className="nav-table-wrap">
        <div className="nav-title">{ type === 'header' ? "Header" : "Footer" } Navigation</div>
        <div className="nav-table">

            {

                navigations.length ?

                    <Table>

                        <TableHead>
                            <div />
                            <TableHeadItem>Name</TableHeadItem>
                            <TableHeadItem>URL</TableHeadItem>
                            <div/>
                        </TableHead>

                        <Fragment>
                            <ReactSortable
                                list={navigations}
                                setList={setSort}
                                animation={200}
                                onEnd={handleSort}
                            >
                                {
                                    navigations.map(nav => <NavigationComponent key={nav.id} navigation={nav} />)
                                }
                            </ReactSortable>
                        </Fragment>

                    </Table>

                    :

                    <NoResults text="No navigations" padding={30} imageWidth={150} />

            }

        </div>
    </div>

}
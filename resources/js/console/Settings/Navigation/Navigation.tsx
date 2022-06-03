import React from "react";
import {TableRow, TableRowItem} from "../../ReusableComponents/Table";
import {Navigation} from "../../types";
import {useLanguagesValues} from "../Languages/helpers";

export default function Navigation({ navigation } : { navigation: Navigation }) {

    const { primaryLanguage } = useLanguagesValues();

    return <TableRow>
        <TableRowItem>{ navigation.variants[primaryLanguage.id].name }</TableRowItem>
        <TableRowItem>{ navigation.url }</TableRowItem>
    </TableRow>

}
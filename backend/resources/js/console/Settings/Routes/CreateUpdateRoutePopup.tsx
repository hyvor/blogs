import {useActions, useValues} from "kea";
import React, {useState} from "react";
import {Popup, PopupBodyDefault, PopupFooterDoubleButton, PopupHeaderDefault} from "../../ReusableComponents/Popup";
import Input from "../../ReusableComponents/Input";
import {Route} from "../../types";
import routesLogic from "../../logic/routesLogic";
import subdomainLogic from "../../logic/subdomainLogic";
import getSubdomain from "../../logic-helpers/subdomain";

export default function CreateUpdateRoutePopup({ route, onClose } : {route?: Route, onClose: Function}) {

    const isCreate = !route;

    const routesLogicInst = routesLogic({subdomain: getSubdomain()})
    const { create, update } = useActions(routesLogicInst)
    const { createAjax, updateAjax } = useValues(routesLogicInst)

    const [name, setName] = useState<string>(isCreate ? '' : route.name);
    const [match, setMatch] = useState<string>(isCreate ? '' : route.match);
    const [template, setTemplate] = useState(isCreate ? '' : route.template);
    const [postsFilter, setPostsFilter] = useState<string | null>(isCreate ? null : route.posts_filter);
    const [contentType, setContentType] = useState<string | null>(isCreate ? null : route.content_type);

    function handleClick() {
        if (isCreate) {
            create({
                name,
                match,
                template,
                posts_filter: postsFilter,
                content_type: contentType
            })
        } else {
            update({
                id: route.id,
                match,
                template,
                posts_filter: postsFilter,
                content_type: contentType
            })
        }
        onClose();
    }

    return <Popup
        header={<PopupHeaderDefault title={ isCreate ? "Create Route" : "Update Route" } />}
        body={
            <PopupBodyDefault>
                <Input
                    title="Name"
                    type="text"
                    name="name"
                    value={name}
                    onChange={setName}
                    autoFocus={true}
                    placeholder="New Route"
                    readOnly={!isCreate}
                />
                <Input
                    title="Match"
                    type="text"
                    name="match"
                    value={match}
                    onChange={setMatch}
                    placeholder="/path"
                />
                <Input
                    title="Template"
                    type="text"
                    name="template"
                    value={template}
                    onChange={setTemplate}
                    placeholder="template"
                />
                <Input
                    title="Posts Filter"
                    type="text"
                    name="posts-filter"
                    value={postsFilter}
                    onChange={setPostsFilter}
                    placeholder="A FilterQ expression..."
                />
                <Input
                    title="Content Type"
                    type="text"
                    name="content-type"
                    value={contentType}
                    onChange={setContentType}
                    placeholder="text/html"
                />
            </PopupBodyDefault>
        }
        footer={
            <PopupFooterDoubleButton
                onCancel={onClose}
                onClick={handleClick}
                name={isCreate ? "Create" : "Update"}
                isLoading={createAjax.status === 'loading' || updateAjax.status === 'loading'}
            />
        }
    />
}

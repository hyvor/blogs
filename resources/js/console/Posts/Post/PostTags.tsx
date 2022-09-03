import React from 'react';
import {useActions, useValues} from "kea";
import AsyncSelect from "react-select/async";
import api from "../../lib/api";
import {Post, Tag, TagVariant, User} from "../../types";
import {getPrimaryLanguage} from "../../lib/blog-helpers";
import tagsLogic, {IDKeyedTags} from "../../logic/tagsLogic";
import getSubdomain from "../../logic-helpers/subdomain";
import {OnChangeValue} from "react-select";

interface SelectOption {
    value: number;
    label: string | null;
}

export default function PostTags({ post, updatePostValue } : { post: Post, updatePostValue: Function }) {

    const subdomain = getSubdomain()

    const tagsLogicInst = tagsLogic({subdomain})
    const { tags } = useValues(tagsLogicInst) as { tags: IDKeyedTags }
    const { addTags } = useActions(tagsLogicInst)
    const languageId = getPrimaryLanguage(subdomain).id

    const options: Array<SelectOption> = [];

    for (let id in tags) {
        options.push({
            value: parseInt(id),
            label: (tags[id].variants.find(v => v.language_id === languageId) as TagVariant).name
        })
    }

    const defaultValue = post.tags.map(tag => (
        {value: tag.id , label: (tag.variants.find(v => v.language_id === languageId) as TagVariant).name }
    ))

    async function loader(input: string) : Promise<Array<SelectOption>> {

        const tags : Array<Tag> = await api.get(subdomain, '/tags/search', {
            search: input
        });

        addTags(tags)

        return tags.map(tag => ({
            value: tag.id,
            label: (tag.variants.find(v => v.language_id === languageId) as TagVariant).name
        }))

    }

    function handleChange(options: OnChangeValue<SelectOption, true>) {
        const postTags : Array<Tag> = [];
        options.forEach(({value}) => postTags.push(tags[value]))
        updatePostValue('tags', postTags);
    }

    return <AsyncSelect
        loadOptions={loader}
        cacheOptions
        defaultOptions={options}
        // defaultValue={defaultValue}
        value={defaultValue}
        isMulti
        classNamePrefix="react-select"
        className="react-select react-select-normal react-select-multi"
        onChange={handleChange}
    />

}
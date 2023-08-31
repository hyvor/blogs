/* import React from 'react';
import {useActions, useValues} from "kea";
import AsyncSelect from "react-select/async";
import api from "../../../lib/api";
import {Post, Tag, TagVariant, User} from "../../../types";
import {getPrimaryLanguage} from "../../../lib/blog-helpers";
import tagsLogic, {IDKeyedTags} from "../../../logic/tagsLogic";
import getSubdomain from "../../../logic-helpers/subdomain";
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

} */


import React, { useEffect, useRef, useState } from 'react';
import api from "../../../lib/api";
import {Post, Tag, User} from "../../../types";
import {getPrimaryLanguage} from "../../../lib/blog-helpers";
import getSubdomain from "../../../logic-helpers/subdomain";
import ProfilePicture from '../../../ReusableComponents/ProfilePicture';
import { OutsideClick } from '../../../ReusableComponents/OutsideClick';
import Loader from '../../../ReusableComponents/Loader';
import { UpdatePostValueType } from './PostAuthors';
import { useTagsActions, useTagsValues } from '../../../Settings/Tags/useTags';

export default function PostTags({ post, updatePostValue } : { post: Post, updatePostValue: UpdatePostValueType }) {

    const subdomain = getSubdomain()
    const [isAdding, setIsAdding] = useState(false);
    const languageId = getPrimaryLanguage(subdomain).id

    function handleRemove(id: number) {
        updatePostValue('tags', post.tags.filter(tag => tag.id !== id));
    }

    return <div className="post-authors post-tags">

        <div className="left">

                {
                    post.tags.length ?
                    post.tags.map(tag => {
                        const variant = tag.variants.find(v => v.language_id === languageId);

                        return <span className="post-author" key={tag.id}>
                            <span className="name">
                                { variant?.name || '(Tag)' }
                            </span>
                            <span className="remove" onClick={() => handleRemove(tag.id)}>
                                &times;
                            </span>
                        </span>

                    }) :
                    <span className="no-authors">No Tags</span>
                }

        </div>

        <div className="right">

            <span className="author-adder">
                <span className="plus" onClick={e => {
                    e.stopPropagation();
                    setIsAdding(!isAdding)
                }}>+</span>
                { isAdding &&
                    <Adder post={post} updatePostValue={updatePostValue} onClose={() => setIsAdding(false)} />
                }
            </span>

        </div>

    </div>

}

function Adder({post, updatePostValue, onClose} : { post: Post, updatePostValue: UpdatePostValueType, onClose: Function}) {

    const subdomain = getSubdomain()

    const { create } = useTagsActions()

    const [isLoading, setIsLoading] = useState(true);
    const [tags, setTags] = useState<Tag[]>([]);
    const [searchedTags, setSearchedTags] = useState<Tag[]>([]);
    const [search, setSearch] = useState('');
    const [isCreating, setIsCreating] = useState(false);

    const searchTimeout = useRef<null | ReturnType<typeof setTimeout>>(null);

    const languageId = getPrimaryLanguage(subdomain).id

    const availableTags = Object.values(search.trim() !== '' ? searchedTags : tags);

    function handleAdd(tag: Tag) {
        if (!post.tags.find(t => t.id === tag.id))
            updatePostValue('tags', [...post.tags, tag]);
        onClose();
    }

    useEffect(() => {
        api.get<Tag[]>(subdomain, '/tags').then(tags => {
            setTags(tags);
            setIsLoading(false);
        });
    }, []);

    function handleSearchChange(val: string) {  
        
        setSearch(val);
        setIsLoading(true);

        if (val.trim() === '') {
            setSearchedTags([]);
            setIsLoading(false);
            return;
        }

        if (searchTimeout.current)
            clearTimeout(searchTimeout.current);

        searchTimeout.current = setTimeout(() => {

            api.get<Tag[]>(subdomain, '/tags/search', {
                search: val
            }).then(tags => {
                setSearchedTags(tags);
                setIsLoading(false);
            });

        }, 250);
    }

    function handleCreateNew() {
        setIsCreating(true);
        create({
            name: search, 
            onCreate: (tag) => {
                handleAdd(tag);
                setIsCreating(false);
            }
        });
    }


    return <OutsideClick onClick={onClose}>
        <div className="adder-popup g-box">

            {
                isCreating ?
                    <Loader padding={40} size="small" /> :

                    <div>
                        <div className="search-wrap">
                            <input 
                                type="text"
                                placeholder="Search..."
                                className="input medium"
                                autoFocus={true} 
                                value={search}
                                onChange={e => handleSearchChange(e.target.value)}
                            />
                        </div>

                        <div className="users">

                            {

                                isLoading ?
                                    <Loader padding={40} size="small" /> :
                                    availableTags.length ?
                                    availableTags.map((tag) => {

                                            const variant = tag.variants.find(v => v.language_id === languageId);
                                            const alreadyAuthor = post.tags.find(t => t.id === tag.id);

                                            return <div 
                                                className={"user" + (alreadyAuthor ? ' already-author' : '')}
                                                key={tag.id}
                                                onClick={() => handleAdd(tag)}
                                            >
                                                <span className="name">
                                                    { variant?.name || '(Tag)' }
                                                </span>
                                            </div>

                                        }) :
                                        <div className="no-users">No Tags</div>

                            }


                            {
                                !isLoading &&
                                search.trim() !== '' &&
                                availableTags
                                    .findIndex(
                                        t => t.variants.find(v => v.language_id === languageId)?.name === search
                                    ) === -1 &&
                                <div className='create-tag'>
                                    <button className="button secondary small" onClick={e => {
                                        e.stopPropagation();
                                        handleCreateNew();
                                    }}>
                                        + Create tag <span className="name">{search}</span>
                                    </button>
                                </div>
                            }

                        </div>

                    </div>
            }

            

        </div>
    </OutsideClick>

}
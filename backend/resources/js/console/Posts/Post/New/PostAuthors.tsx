import React, { useEffect, useRef, useState } from 'react';
import api from "../../../lib/api";
import {Post, User} from "../../../types";
import {getPrimaryLanguage} from "../../../lib/blog-helpers";
import getSubdomain from "../../../logic-helpers/subdomain";
import ProfilePicture from '../../../ReusableComponents/ProfilePicture';
import { OutsideClick } from '../../../ReusableComponents/OutsideClick';
import Loader from '../../../ReusableComponents/Loader';

export type UpdatePostValueType<T extends keyof Post = keyof Post> = (key: T, value: Post[T]) => void;

export default function PostAuthors({ post, updatePostValue } : { post: Post, updatePostValue: UpdatePostValueType }) {

    const subdomain = getSubdomain()
    const [isAdding, setIsAdding] = useState(false);
    const languageId = getPrimaryLanguage(subdomain).id

    function handleRemove(id: number) {
        const authors = post.authors.filter(author => author.id !== id);
        updatePostValue('authors', authors);
    }

    return <div className="post-authors">

        <div className="left">

                {
                    post.authors.length ?
                    post.authors.map(author => {

                            const variant = author.variants.find(v => v.language_id === languageId);

                            return <span className="post-author" key={author.id}>
                                <ProfilePicture user={author} size={16} /> 
                                <span className="name">
                                    { variant?.name || 'Anonymous' }
                                </span>
                                <span className="remove" onClick={() => handleRemove(author.id)}>
                                    &times;
                                </span>
                            </span>

                        }) :
                        <span className="no-authors">No Authors</span>
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

    const [isLoading, setIsLoading] = useState(true);
    const [users, setUsers] = useState<User[]>([]);
    const [searchedUsers, setSearchedUsers] = useState<User[]>([]);
    const [search, setSearch] = useState('');

    const searchTimeout = useRef<null | ReturnType<typeof setTimeout>>(null);

    const languageId = getPrimaryLanguage(subdomain).id

    const availableUsers = Object.values(search.trim() !== '' ? searchedUsers : users);

    function handleAdd(user: User) {
        if (!post.authors.find(author => author.id === user.id))
            updatePostValue('authors', [...post.authors, user]);
        onClose();
    }

    useEffect(() => {
        api.get<User[]>(subdomain, '/users').then(users => {
            setUsers(users);
            setIsLoading(false);
        });
    }, []);

    function handleSearchChange(val: string) {  
        
        setSearch(val);
        setIsLoading(true);

        if (val.trim() === '') {
            setSearchedUsers([]);
            setIsLoading(false);
            return;
        }

        if (searchTimeout.current)
            clearTimeout(searchTimeout.current);

        searchTimeout.current = setTimeout(() => {

            api.get<User[]>(subdomain, '/users/search', {
                search: val
            }).then(users => {
                setSearchedUsers(users);
                setIsLoading(false);
            });

        }, 250);
    }

    return <OutsideClick onClick={onClose}>
        <div className="adder-popup g-box">

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
                        availableUsers.length ?
                            availableUsers.map((user: User) => {

                                const variant = user.variants.find(v => v.language_id === languageId);
                                const alreadyAuthor = post.authors.find(author => author.id === user.id);

                                return <div 
                                    className={"user" + (alreadyAuthor ? ' already-author' : '')}
                                    key={user.id}
                                    onClick={() => handleAdd(user)}
                                >
                                    <ProfilePicture user={user} size={16} /> 
                                    <span className="name">
                                        { variant?.name || 'Anonymous' }
                                    </span>
                                </div>

                            }) :
                            <div className="no-users">No users</div>

                }

            </div>

        </div>
    </OutsideClick>

}
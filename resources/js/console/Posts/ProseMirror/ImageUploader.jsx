import axios from 'axios';
import React, { useEffect, useRef, useState } from 'react';
import api, { getEndpoint } from '../../lib/api';
import Loader from '../../ReusableComponents/Loader';
import NoResults from '../../ReusableComponents/NoResults';

export default function ImageUploader({onUpload}) {

    const [search, setSearch] = useState('');
    const [ajaxStatus, setAjaxStatus] = useState(null);
    const abortControllerRef = useRef(null)
    const [images, setImages] = useState([]);

    useEffect(() => {
        if (!search.trim()) return

        // abort old request
        abortControllerRef.current && abortControllerRef.current.abort();

        setAjaxStatus('loading')
        setImages([]);

        load().catch(() => {
            setAjaxStatus('error')
        })

        return () => abortControllerRef.current.abort()

    }, [search]);

    function load(page = 1) {
        abortControllerRef.current = new AbortController();
        return axios.get(
            getEndpoint(window.currentSubdomain, '/media/unsplash/search'),
            {
                signal: abortControllerRef.current.signal,
                params: {
                    search: search,
                    page
                }
            }
        ).then(({data: newImages}) => {
            setAjaxStatus('success')
            setImages([...images, ...newImages])
        })
    }

    return <div className={"image-uploader" + ( search.trim() ? " searching" : "")}>
        <div className="uploader-content">
            <div className="search">
                <input 
                    type="text" 
                    className="input" 
                    placeholder="Search on Unsplash"
                    value={search}
                    onChange={e => setSearch(e.target.value)}
                    onFocus={e => { e.preventDefault()}}

                    // otherwise call Backspace events in Prosemirror
                    onKeyDown={e => e.stopPropagation()}
                />
            </div>
            <div className="non-search">
                <div className="or">OR</div>
                <div className="upload">
                    <button className="button small">
                        Upload
                    </button>
                </div>
            </div>

            {
                search.trim() ?
                (
                    ajaxStatus === 'loading' ?
                    <Loader padding={60} /> :
                    (
                        ajaxStatus === 'success' && images.length ?
                        <Images
                            images={images}
                            onUpload={onUpload}
                        /> :
                        <NoResults imageWidth={150} />
                    )
                ) : null
            }

        </div>
    </div>

}


function Images({images, onUpload}) {

    const left = [];
    const right = [];

    images.forEach((img, i) => (i % 2 === 0 ? right : left).push(img));

    return <div className="search-results">
        <ImageColumn onUpload={onUpload} images={left} />
        <ImageColumn onUpload={onUpload} images={right} />
    </div>

}

function ImageColumn({images, onUpload}) {
    return <div className="images-column">
        {
            images.map(img => {
                return <img
                    key={img.url}
                    onClick={() => onUpload(img.url, img.alt, img.title)}
                    src={img.url} title={img.title} alt={img.alt} />
            })
        }
    </div>
}
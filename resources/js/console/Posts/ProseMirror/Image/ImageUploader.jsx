import axios from 'axios';
import React, { useEffect, useRef, useState } from 'react';
import api, { getEndpoint } from '../../../lib/api';
import Loader from '../../../ReusableComponents/Loader';
import NoResults from '../../../ReusableComponents/NoResults';
import {toast} from "react-toastify";

export default function ImageUploader({onUpload}) {

    const [search, setSearch] = useState('');
    const [ajaxStatus, setAjaxStatus] = useState(null);
    const abortControllerRef = useRef(null)
    const [images, setImages] = useState([]);
    const [isUploading, setIsUploading] = useState(false);
    
    const fileUploadInputRef = useRef(null);

    useEffect(() => {
        if (!search.trim()) return

        // abort old request
        console.log(abortControllerRef.current)
        abortControllerRef.current && abortControllerRef.current.abort();

        setAjaxStatus('loading')
        setImages([]);

        load().catch(() => {
            setAjaxStatus('error')
        })

        return () => abortControllerRef.current.abort()

    }, [search]);

    function load(page = 1) {
        if (!abortControllerRef.current)
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
    
    function openUploader() {
        fileUploadInputRef.current.click()
    }
    function handleFileUploadChange(e) {
        const files = e.target.files;
        if (files.length === 0) {
            toast.error('No file selected')
        } else if (files.length > 1) {
            toast.error('Select only one image');
        }
        
        const file = files[0];
        if (file.size > 50 * 1000 * 1000) {
            toast.error("Max size is 50MB");
        }
    
        // https://developer.mozilla.org/en-US/docs/Web/Media/Formats/Image_types#common_image_file_types
        const validTypes = [
            'image/gif', 'image/jpeg', 'image/png',
            'image/svg+xml', 'image/webp',
            'image/apng', 'image/avif'
        ];
        if (!validTypes.includes(file.type)) {
            toast.error('Only PNG, JPEG, GIF, APNG, WEBP, AVIF, and SVG images are allowed');
        }
        
        uploadFile(file)
    }
    
    function uploadFile(file) {
        setIsUploading(true)
        
        var formData = new FormData();
        formData.append('file', file, file.name);
        axios.post(
            getEndpoint(window.currentSubdomain, '/media'),
            formData
        ).then(({data}) => {
            setIsUploading(false)
            onUpload(data.url)
        }).catch(() => {
            setIsUploading(false)
            toast.error("Uploading failed");
        })
        
    }

    return <div className={"image-uploader" + ( search.trim() ? " searching" : "")}>
        <div className="uploader-content">

            {
                isUploading ?
                    <Loader padding={60}/>
                    :
                    <div>
                        <div className="search">
                            <input
                                type="text"
                                className="input"
                                placeholder="Search on Unsplash"
                                value={search}
                                onChange={e => setSearch(e.target.value)}
                                onFocus={e => {
                                    e.preventDefault()
                                }}

                                // otherwise call Backspace events in Prosemirror
                                onKeyDown={e => e.stopPropagation()}
                            />
                        </div>
                        <div className="non-search">
                            <div className="or">OR</div>
                            <div className="upload">
                                <button
                                    className="button small"
                                    onClick={openUploader}
                                >
                                    Upload
                                </button>
                                <input
                                    type="file"
                                    accept="image/*"
                                    style={{display: "none"}}
                                    ref={fileUploadInputRef}
                                    onChange={handleFileUploadChange}
                                />
                            </div>
                        </div>

                        {
                            search.trim() ?
                                (
                                    ajaxStatus === 'loading' ?
                                        <Loader padding={60}/> :
                                        (
                                            ajaxStatus === 'success' && images.length ?
                                                <Images
                                                    images={images}
                                                    onUpload={onUpload}
                                                /> :
                                                <NoResults imageWidth={150}/>
                                        )
                                ) : null
                        }

                    </div>

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

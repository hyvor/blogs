import axios, { AxiosResponse } from 'axios';
import React, { ChangeEvent, useEffect, useRef, useState } from 'react';
import { getEndpoint } from '../../../../lib/api';
import Loader from '../../../../ReusableComponents/Loader';
import NoResults from '../../../../ReusableComponents/NoResults';
import { toast } from "react-toastify";
import { ConsoleWindow, Media, UnsplashImage } from "../../../../types";
import Image, { ImageUploadHandlerType } from "./nodeview-image";

export default function ImageUploader({ onUpload, onUrlLoad }: { onUpload: ImageUploadHandlerType, onUrlLoad: (url: string) => void },) {

    const [search, setSearch] = useState('');
    const [ajaxStatus, setAjaxStatus] = useState<null | 'loading' | 'success'>(null);
    const abortControllerRef = useRef(new AbortController())
    const [images, setImages] = useState<UnsplashImage[]>([]);
    const [isUploading, setIsUploading] = useState(false);
    const [hasMore, setHasMore] = useState(false)
    const [imageUrl, setImageUrl] = useState('');
    const [isValidImageUrl, setIsValidImageUrl] = useState(true);
    const [isImageLoading, setIsImageLoading] = useState(false);

    const fileUploadInputRef = useRef<null | HTMLInputElement>(null);

    useEffect(() => {
        if (!search.trim() && !imageUrl.trim()) return

        // abort old request
        abortControllerRef.current && abortControllerRef.current.abort();

        setAjaxStatus('loading')
        setImages([]);
        if (search.trim().length > 0) {
            load();
            return;
        }
        if (imageUrl.trim().length > 0) {
            return;
        }


    }, [search, imageUrl]);

    function load(page = 1) {
        abortControllerRef.current = new AbortController()

        axios.get<UnsplashImage[]>(
            getEndpoint((window as ConsoleWindow).currentSubdomain as string, '/media/unsplash/search'),
            {
                signal: abortControllerRef.current.signal,
                params: {
                    search: search,
                    page
                }
            }
        ).then(({ data: newImages }) => {
            setAjaxStatus('success')
            setImages(page === 1 ? newImages : [...images, ...newImages])
            setHasMore(newImages.length === 30)
        }).catch(() => { })
    }

    function openUploader() {
        fileUploadInputRef.current && fileUploadInputRef.current.click()
    }
    function handleFileUploadChange(e: ChangeEvent) {
        const files = (e.target as HTMLInputElement).files;
        if (!files || files.length === 0) {
            toast.error('No file selected')
            return
        } else if (files.length > 1) {
            toast.error('Select only one image');
            return;
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

    function uploadFile(file: File) {
        setIsUploading(true)

        var formData = new FormData();
        formData.append('file', file, file.name);
        axios.post<any, AxiosResponse<Media>>(
            getEndpoint((window as ConsoleWindow).currentSubdomain as string, '/media'),
            formData
        ).then(({ data }) => {
            setIsUploading(false)
            onUpload(data.url)
        }).catch(() => {
            setIsUploading(false)
            toast.error("Uploading failed");
        })

    }

    function handleUrlInputChange(input: string) {
        setImageUrl(input)
        setIsValidImageUrl(true);
    }

    return <div className={"image-uploader" + (search.trim() || imageUrl.trim() ? " searching" : "")}>
        <div className="uploader-content">
            {
                isUploading ?
                    <Loader padding={60} />
                    :
                    // If no preview image is selected
                    imageUrl.trim().length == 0 && <div>
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
                        <div className='url-search'>
                            <div className="or">OR</div>
                            <input
                                className="input"
                                placeholder="Import from URL"
                                value={imageUrl}
                                onChange={e => handleUrlInputChange(e.target.value)}
                                onPaste={(e) => handleUrlInputChange(e.clipboardData.getData('text'))}
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
                                    style={{ display: "none" }}
                                    ref={fileUploadInputRef}
                                    onChange={handleFileUploadChange}
                                />
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
                                                    load={load}
                                                    hasMore={hasMore}
                                                    setHasMore={setHasMore}
                                                /> :
                                                <NoResults
                                                    text="No images found"
                                                    imageWidth={150}
                                                />
                                        )
                                ) : null
                        }

                    </div>

            }
            {
                // If preview image is selected
                imageUrl.trim() ? (
                    isImageLoading ? <Loader padding={60} /> :
                        <div className='image-preview'>
                            <input
                                className="input image-confirm"
                                placeholder="Import from URL"
                                value={imageUrl}
                                onChange={e => handleUrlInputChange(e.target.value)}
                                onPaste={(e) => handleUrlInputChange(e.clipboardData.getData('text'))}
                                onFocus={e => {
                                    e.preventDefault()
                                }}

                                // otherwise call Backspace events in Prosemirror
                                onKeyDown={e => e.stopPropagation()}
                            />
                            {isValidImageUrl ?
                                <img src={imageUrl}
                                    onLoadStart={() => setIsImageLoading(true)}
                                    onLoad={() => {
                                        setIsImageLoading(false);
                                        setIsValidImageUrl(true);
                                    }}
                                    onError={() => setIsValidImageUrl(false)}></img>
                                : <p>Invalid image URL</p>}
                            <div className='action-area'>
                                <button className='button small confirm-button' onClick={() => onUrlLoad(imageUrl)}>Confirm </button>
                                <button className='button small' onClick={() => handleUrlInputChange('')}>Change</button>
                            </div>
                        </div>
                ) : null
            }
        </div>
    </div >

}


interface ImagesProps {
    images: UnsplashImage[],
    onUpload: ImageUploadHandlerType,
    load: Function,
    hasMore: boolean,
    setHasMore: Function
}

function Images({ images, onUpload, load, hasMore, setHasMore }: ImagesProps) {

    const left: UnsplashImage[] = [];
    const right: UnsplashImage[] = [];

    images.forEach((img, i) => (i % 2 === 0 ? right : left).push(img));

    function handleScroll(e: any) {
        const el = e.target;
        if (
            hasMore &&
            el.scrollTop + el.clientHeight >= el.scrollHeight
        ) {
            setHasMore(false)
            load((images.length / 30) + 1)
        }
    }

    return <div
        className="search-results"
        onScroll={handleScroll}
    >
        <ImageColumn onUpload={onUpload} images={left} />
        <ImageColumn onUpload={onUpload} images={right} />
    </div>

}

function ImageColumn({ images, onUpload }: { images: UnsplashImage[], onUpload: ImageUploadHandlerType }) {
    return <div className="images-column">
        {
            images.map(img => {
                return <img
                    key={img.url}
                    onClick={() => onUpload(img.url, img.alt, img)}
                    src={img.url} title={img.title || ''} alt={img.alt || ''} />
            })
        }
    </div>
}
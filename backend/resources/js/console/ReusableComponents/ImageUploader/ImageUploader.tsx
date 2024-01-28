import React, { ReactNode, useEffect, useState } from 'react';
import { OutsideClick } from '../OutsideClick';
import Button from '../Button';
import { Media, UnsplashImage } from '../../types';
import { toast } from 'react-toastify';
import getSubdomain, { useSubdomain } from '../../logic-helpers/subdomain';
import { useActions, useValues } from 'kea';
import mediaLogic from '../../logic/mediaLogic';
import Loader from '../Loader';
import { CardImage, CloudUpload } from 'react-bootstrap-icons';
import NoResults from '../NoResults';

type TabType = 'upload' | 'media' | 'unsplash';

export type OnSelectProps = {
    url: string,
    from: TabType,
    unsplash?: UnsplashImage,
};

export type OnSelect = (props: OnSelectProps) => void;

interface ImageUploaderProps {
    placeholder?: ReactNode,
    onSelect: OnSelect,
    onClose?: () => void,
}


// to open the image uploader globally
export function GlobalImageUploader() {
    const subdomain = useSubdomain();
    return subdomain ?
        <GlobalImageUploaderInner subdomain={subdomain} /> :
        null;
}

function GlobalImageUploaderInner({subdomain} : {subdomain: string}) {
    const logic  = mediaLogic({subdomain});
    const { globalImageUploader } = useValues(logic);
    const { setGlobalImageUploader } = useActions(logic);

    if (!globalImageUploader) return null;

    return <UploaderPopup 
        onSelect={props => {
            globalImageUploader.onSelect(props);
            setGlobalImageUploader(null);
        }}
        onClose={() => {
            globalImageUploader.onClose?.();
            setGlobalImageUploader(null)
        }}
    />
}

export default function ImageUploader(props : ImageUploaderProps) {

    const [isUploading, setIsUploading] = React.useState(false);

    return isUploading ? 
        <UploaderPopup {...props} onClose={() => setIsUploading(false)} /> : 
        <div className="placeholder-wrap" onClick={e => {
            e.stopPropagation();
            setIsUploading(true)
        }}>
            {props.placeholder}
        </div>

}

function UploaderPopup(props : ImageUploaderProps & {onClose: () => void}) {

    const [selectedTab, setSelectedTab] = React.useState<TabType>('upload');

    const [selectedImage, setSelectedImage] = React.useState<OnSelectProps | null>(null);

    function getButtonType(tab: TabType) {
        return tab === selectedTab ? 'primary' : 'light';
    }

    function handleSelect(props: OnSelectProps) {
        setSelectedImage(props);
    }

    useEffect(() => {
        function handleKeyUp(e: KeyboardEvent) {
            if (e.key === 'Escape') {
                props.onClose();
            }
        }

        window.addEventListener('keyup', handleKeyUp);

        return () => {
            window.removeEventListener('keyup', handleKeyUp);
        }
    }, []);

    return <div className="global-image-uploader">

        <OutsideClick onClick={props.onClose}>

            <div className="inner">

                <div className="image-toolbar">
               
                    <div className="button-group">

                        <Button 
                            onClick={() => setSelectedTab('upload')} 
                            type={getButtonType('upload')}
                        >
                            <CloudUpload />
                            Upload
                        </Button>
                        <Button 
                            onClick={() => setSelectedTab('media')} 
                            type={getButtonType('media')}
                        >
                            <CardImage />
                            Blog Media
                        </Button>
                        <Button 
                            onClick={() => setSelectedTab('unsplash')} 
                            type={getButtonType('unsplash')}
                        >
                            <svg role="img" width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><title/><path d="M7.5 6.75V0h9v6.75h-9zm9 3.75H24V24H0V10.5h7.5v6.75h9V10.5z"/></svg>
                            Unsplash
                        </Button>

                    </div>

                </div>


                {
                    selectedImage ?

                    <div className="selected-image">
                        <div className="image-preview">
                            <img src={selectedImage.url} alt="Selected" />
                        </div>
                        <div className="confirm-wrap">
                            <Button 
                                type="text-only" 
                                size="medium" 
                                onClick={e => {
                                    e.stopPropagation();
                                    setSelectedImage(null)
                                }}
                            >Discard</Button>
                            <Button 
                                type="primary" 
                                size="medium"
                                onClick={e => {
                                    e.stopPropagation();
                                    props.onSelect(selectedImage);
                                    props.onClose();
                                }}
                            >Confirm</Button>
                        </div>
                    </div> :

                    <div className="inner-tabs">

                        {selectedTab === 'upload' && <TabUpload onSelect={handleSelect} />}
                        {selectedTab === 'media' && <TabMedia onSelect={handleSelect} />}
                        {selectedTab === 'unsplash' && <TabUnsplash onSelect={handleSelect} />}

                    </div>

                }

            </div>

        </OutsideClick>

    </div>

}

function TabUpload({onSelect} : {onSelect: OnSelect}) {

    const subdomain = getSubdomain();
    const inputRef = React.useRef<HTMLInputElement>(null);
    const [isDragging, setIsDragging] = React.useState(false);
    const [isUploading, setIsUploading] = React.useState(false);

    const uploadAreaRef = React.useRef<HTMLDivElement>(null);

    const { uploadImage } = useActions(mediaLogic({subdomain}));

    function handleFiles(files: FileList | null) {
        if (!files || files.length === 0) {
            toast.error('No file selected')
            return
        } else if (files.length > 1) {
            toast.error('Select only one image');
            return;
        }

        const file = files[0];
        handleFileUpload(file);
    }

    function handleFileUpload(file: File) {
        if (file.size > 50 * 1000 * 1000) {
            toast.error("Max size is 50MB");
            return;
        }

        // https://developer.mozilla.org/en-US/docs/Web/Media/Formats/Image_types#common_image_file_types
        const validTypes = [
            'image/gif', 'image/jpeg', 'image/png',
            'image/svg+xml', 'image/webp',
            'image/apng', 'image/avif'
        ];
        if (!validTypes.includes(file.type)) {
            toast.error('Only PNG, JPEG, GIF, APNG, WEBP, AVIF, and SVG images are allowed');
            return;
        }

        setIsUploading(true);

        uploadImage({
            file,
            onUpload: (media) => {
                setIsUploading(false);
                onSelect({
                    url: media.url,
                    from: 'upload'
                });
            },
            onError: () => {
                setIsUploading(false);
            }
        });
    }



    useEffect(() => {

        async function handlePaste(e: ClipboardEvent) {

            if (!inputRef.current) return;

            console.log(e.clipboardData?.getData('text/html'), e.clipboardData?.items[0]);
            const items = e.clipboardData?.items;
            if (!items) return;

            for (let i = 0; i < items.length; i++) {
                const item = items[i];
                if (item.type.indexOf('image') === 0) {
                    const blob = item.getAsFile();
                    if (!blob) continue;
                    const file = new File([blob], 'pasted-image.png', { type: blob.type });
                    handleFileUpload(file);
                    break;
                }
            }

        }

        function handleDragEnter(e: DragEvent) {
            e.preventDefault();e.stopPropagation();
            setIsDragging(true);
        }
        function handleDragLeave(e: DragEvent) {
            e.preventDefault();e.stopPropagation();
            setIsDragging(false);
        }

        function handleDragDrop(e: DragEvent) {
            console.log("DROPPED");
            e.preventDefault();e.stopPropagation();
            setIsDragging(false);

            if (!e.dataTransfer) return;
            const files = e.dataTransfer.files;
            handleFiles(files);
        }

        window.addEventListener('paste', handlePaste);
        window.addEventListener('dragenter', handleDragEnter);
        window.addEventListener('dragover', handleDragEnter);
        window.addEventListener('dragleave', handleDragLeave);
        uploadAreaRef.current?.addEventListener('drop', handleDragDrop, false);

        return () => {
            window.removeEventListener('paste', handlePaste);
            window.removeEventListener('dragenter', handleDragEnter);
            window.removeEventListener('dragover', handleDragEnter);
            window.removeEventListener('dragleave', handleDragLeave);
            uploadAreaRef.current?.removeEventListener('drop', handleDragDrop, false);
        }

    }, []);

    return <div className="tab-upload">

        <input
            type="file"
            accept="image/*"
            style={{ display: "none" }}
            ref={inputRef}
            onChange={() => handleFiles(inputRef.current!.files)}
        />

        {

            isUploading ?

            <Loader /> :

            <div 
                className="upload-area global-input-shadow" 
                onClick={() => inputRef.current && inputRef.current.click()}
                ref={uploadAreaRef}
            >
                {
                    isDragging ?
                    "Drop here!" :
                    "Drag and drop, paste, or click to upload"
                }
            </div>

        }

    </div>
}

function TabMedia({onSelect} : {onSelect: OnSelect}) {

    const [images, setImages] = useState<Media[]>([]);
    const { loadImages } = useActions(mediaLogic({subdomain: getSubdomain()}));
    const [isLoading, setIsLoading] = useState(false);
    const [isLoadingMore, setIsLoadingMore] = useState(false);
    const [hasMore, setHasMore] = useState(true);
    const [search, setSearch] = useState('');

    const limit = 30;

    function handleKeyUp(e: React.KeyboardEvent<HTMLInputElement>) {
        if (e.key === 'Enter') {
            performLoad();
        }
    }

    function performLoad(more: boolean = false) {
        more ? setIsLoadingMore(true) : setIsLoading(true);
        loadImages({
            offset: more ? images.length : 0,
            limit,
            search: search.trim() === '' ? null : search.trim(),
            onLoad: newImages => {
                setIsLoading(false);
                setIsLoadingMore(false);
                setImages(more ? [...images, ...newImages] : newImages);
                setHasMore(newImages.length === limit);
            }
        });
    }

    function handleLoadMore(e: React.MouseEvent<HTMLButtonElement, MouseEvent>) {
        e.stopPropagation();
        if (!hasMore) return;
        setIsLoadingMore(true);
        performLoad(true);
    }

    useEffect(() => {
        setIsLoading(true);
        performLoad();
    }, []);

    return <div className="tab-media">

        <div className="search-wrap">
            <input
                value={search}
                onChange={e => setSearch(e.target.value)}
                onKeyUp={handleKeyUp}
                placeholder="Search images"
                autoFocus={true}
                autoComplete="off"
                className="input"
            />
            <Button type="primary" size="medium" onClick={() => performLoad()}>
                Search &#9166;
            </Button>
        </div>

        {

            isLoading ?

            <Loader padding={40} /> :

            <div className="media-display">

                <div className="display-inner">

                    {
                        images.length ?
                        images.map(image => <div 
                            className="media-item" 
                            key={image.id}
                            onClick={e => {
                                e.stopPropagation();
                                onSelect({
                                    url: image.url,
                                    from: 'media'
                                });
                            }}
                        >
                            <div className="media-inner">
                                <div className="image-wrap" style={{
                                    backgroundImage: `url(${image.url})`
                                }}></div>
                                <div className="media-data">
                                    <div className="media-title">{image.original_name}</div>
                                    <div className="media-at">{ 
                                        image.uploaded_at && 
                                        new Date(image.uploaded_at * 1000).toDateString() 
                                    }</div>
                                </div>
                            </div>
                        </div>) :
                        <NoResults 
                            text="No images found in blog media."
                            imageWidth={140}
                            padding={40}
                        />
                    }

                </div>



                {           
                    hasMore &&
                    <div className="has-more">
                        {
                            isLoadingMore ?
                            <Loader padding={20} /> :
                            <Button 
                                type="light" 
                                size="medium"
                                onClick={handleLoadMore}
                            >Load More</Button>
                        }
                    </div>
                }



            </div>

        }

    </div>
}

function TabUnsplash({onSelect} : {onSelect: OnSelect}) {

    const [search, setSearch] = useState('');
    const { searchUnsplash }  = useActions(mediaLogic({subdomain: getSubdomain()}));

    const [images, setImages] = useState<UnsplashImage[]>([]);
    const [hasMore, setHasMore] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const [isLoadingMore, setIsLoadingMore] = useState(false);

    const [hasLoaded, setHasLoaded] = useState(false);

    function performSearch(page: number | undefined = 1) {
        if (search.trim() === '') {
            return toast.error('Enter a search term');
        }

        page === 1 ? setIsLoading(true) : setIsLoadingMore(true);

        searchUnsplash({
            query: search,
            page,
            onLoad: results => {
                setImages(page === 1 ? results : [...images, ...results]);
                setHasMore(results.length === 30);

                setIsLoading(false);
                setIsLoadingMore(false);

                setHasLoaded(true);
            }
        })
    }

    function handleLoadMore(e: React.MouseEvent<HTMLButtonElement, MouseEvent>) {
        e.stopPropagation();
        if (!hasMore) return;
        setIsLoadingMore(true);
        performSearch((images.length / 30) + 1);
    }

    function handleKeyUp(e: React.KeyboardEvent<HTMLInputElement>) {
        if (e.key === 'Enter') {
            performSearch();
        }
    }

    return <div className="tab-unsplash">

        <div className="search-wrap">
            <input
                value={search}
                onChange={e => setSearch(e.target.value)}
                onKeyUp={handleKeyUp}
                placeholder="Search Unsplash"
                autoFocus={true}
                autoComplete="off"
                className="input"
            />
            <Button type="primary" size="medium" onClick={() => performSearch()}>
                Search &#9166;
            </Button>
        </div>

        {

            isLoading ?
            <Loader padding={60} /> :

            <div className="unsplash-display">

                <div className="display-cols">

                    {
                        images.length ?
                        [0,1].map(x => {
                            return <div className="display-col" key={x}>
                                {
                                    images.map((img, i) => {

                                        if (i % 2 !== x) return null;

                                        return <div 
                                            className="unsplash-image-wrap" 
                                            key={img.url}
                                            onClick={e => {
                                                e.stopPropagation();
                                                onSelect({
                                                    url: img.url,
                                                    from: 'unsplash',
                                                    unsplash: img
                                                })
                                            }}
                                        >
                                            <img src={img.url} />
                                        </div>

                                    })
                                }
                            </div>
                        }) : 
                        (
                            hasLoaded &&
                            <NoResults
                                text="No images found for your search."
                                imageWidth={140}
                                padding={40}
                            />
                        )
                    }

                </div>

                {
                    hasMore &&
                    <div className="has-more">
                        {
                            isLoadingMore ?
                            <Loader padding={20} /> :
                            <Button 
                                type="light" 
                                size="medium"
                                onClick={handleLoadMore}
                            >Load More</Button>
                        }
                    </div>
                }

            </div>

        }

    </div>
}
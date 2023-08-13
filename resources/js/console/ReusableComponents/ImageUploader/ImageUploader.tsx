import React, { ReactNode } from 'react';
import { OutsideClick } from '../OutsideClick';
import Button from '../Button';

interface ImageUploaderProps {
    placeholder?: ReactNode
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

    return <div className="global-image-uploader">

        <OutsideClick onClick={props.onClose}>

            <div className="inner">

                <div className="image-toolbar">
               
                    <div className="button-group">

                        <Button onClick={() => {}} type="text-only">Upload</Button>
                        <Button onClick={() => {}} type="text-only">Blog Media</Button>
                        <Button onClick={() => {}} type="text-only">Unsplash</Button>

                    </div>

                </div>

            </div>

        </OutsideClick>

    </div>

}
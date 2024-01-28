import React, {useState} from 'react';
import {Popup, PopupBodyDefault, PopupFooterDoubleButton, PopupHeaderDefault} from "../../ReusableComponents/Popup";
import Input from "../../ReusableComponents/Input";
import getSubdomain from "../../logic-helpers/subdomain";
import {useBlogActions, useBlogValues} from "../../logic-helpers/blog";
import {toast} from "react-toastify";

export default function DeleteBlogPopup({onClose, onDelete}: {onClose: Function, onDelete: Function}) {

    const subdomain = getSubdomain();
    const { deleteBlog } = useBlogActions()
    const { deleteBlogAjax } = useBlogValues()

    const  [typedSubdomain, setTypedSubdomain] = useState('');

    function handleDelete() {
        if (typedSubdomain !== subdomain) {
            return toast.error("Subdomain is different");
        }

        deleteBlog({onDelete});
    }

    return <Popup
        header={<PopupHeaderDefault title="Delete Blog" />}
        body={
            <PopupBodyDefault>
                <div>
                    <p>
                        Type the subdomain of the blog (<b>{subdomain}</b>)  to confirm that you have exported data of your blog and you understand that this action will permanently delete all data of this blog.
                    </p>
                    <Input
                        type="text"
                        name="subdomain"
                        value={typedSubdomain}
                        onChange={value => setTypedSubdomain(value)}
                        placeholder={subdomain}
                        autoFocus={true}
                    />
                </div>
            </PopupBodyDefault>
        }
        footer={
            <PopupFooterDoubleButton
                onCancel={onClose}
                onClick={handleDelete}
                name='Delete'
                isLoading={deleteBlogAjax.status === 'loading'}
                loadingName="Deleting"
            />
        }
    />

}
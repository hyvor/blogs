import {Popup, PopupBodyDefault, PopupFooterDoubleButton, PopupHeaderDefault} from "../../ReusableComponents/Popup";
import React, {useState} from "react";
import DualSetting from "../../ReusableComponents/DualSetting";
import Radio from "../../ReusableComponents/Radio";
import {useBlogActions, useBlogValues} from "../../logic-helpers/blog";
import {toast} from "react-toastify";

export default function ClearCachePopup({onClose} : {onClose: Function}) {

    const { clearBlogCacheAjax } = useBlogValues();
    const { clearBlogCache } = useBlogActions();

    const [type, setType] = useState<'all' | 'template' | 'paths'>('template');
    const [paths, setPaths] = useState('');

    function handleClear() {

        clearBlogCache({
            onClear: () => {
                onClose();
                toast.success('Cache cleared');
            },
            data: {
                type,
                paths: paths.split('\n').filter(path => path.trim() !== '')
            }
        })

    }

    return <Popup
        className="clear-cache-popup"
        header={<PopupHeaderDefault title="Clear Cache" />}
        body={
            <PopupBodyDefault>
                <div>
                    <DualSetting
                        title="Type"
                        description="Which cache to clear"
                        right={

                            <div>

                                <Radio
                                    name="type"
                                    value="template"
                                    onChange={() => setType('template')}
                                    checkFor={type}
                                    placeholder="Template"
                                />

                                <Radio
                                    name="type"
                                    value="paths"
                                    onChange={() => setType('paths')}
                                    checkFor={type}
                                    placeholder="Paths"
                                />

                                <Radio
                                    name="type"
                                    value="all"
                                    onChange={() => setType('all')}
                                    checkFor={type}
                                    placeholder="All"
                                />

                            </div>

                        }
                    />

                    {
                        type === 'paths' &&
                        <DualSetting
                            title="Paths"
                            description="Paths to clear. One per line."
                            right={
                                <textarea
                                    className="input"
                                    value={paths}
                                    onChange={e => setPaths(e.target.value)}
                                />
                            }
                        />

                    }

                    <div className="cache-notice">
                        <div className="notice-title">What will be cleared:</div>
                        <div className="notice-description">
                            {type === 'template' ?
                                'Cache of template pages (index, posts, pages), but not media and assets' :
                                (
                                    type === 'all' ?
                                        'All cache, including media and assets' :
                                        'Cache of specified paths'
                                )
                            }
                        </div>
                    </div>

                </div>
            </PopupBodyDefault>
        }
        footer={
            <PopupFooterDoubleButton
                onCancel={onClose}
                onClick={handleClear}
                name='Clear Cache'
                isLoading={clearBlogCacheAjax.status === 'loading'}
                loadingName="Clearing"
            />
        }
    />

}
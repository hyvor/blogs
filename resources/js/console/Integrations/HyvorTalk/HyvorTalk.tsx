import React, {useState} from "react";
import DualSetting from "../../ReusableComponents/DualSetting";
import NavLink from "../../ReusableComponents/NavLink";
import getSubdomain from "../../logic-helpers/subdomain";
import UserPermissions from "../../services/UserPermissions";
import Callout, {CalloutColors} from "../../ReusableComponents/Callout";
import hyvorTalkLogic from "./hyvorTalkLogic";
import {useActions, useValues} from "kea";
import Loader from "../../ReusableComponents/Loader";
import {PopupConfirm} from "../../ReusableComponents/Popup";
import blogLogic from "../../logic/blogLogic";
import {toast} from "react-toastify";

export default function HyvorTalk() {

    const subdomain = getSubdomain();
    const isOwner = UserPermissions.isOwner();

    const logic = hyvorTalkLogic({subdomain});
    const { loadAjax, deleteIntegrationAjax, data } = useValues(logic);
    const { createIntegration, deleteIntegration } = useActions(logic);

    const { updateBlogSave } = useActions(blogLogic({subdomain}));

    const [isConnecting, setIsConnecting] = useState(false);
    const [isConnectConfirming, setIsConnectConfirming] = useState(false);
    const [isDisconnecting, setIsDisconnecting] = useState(false);

    const embedCode = data.connected ?
`<script async src="https://talk.hyvor.com/embed/embed.js" type="module"></script>
<hyvor-talk-comments 
    website-id="${data.data.website_id}" 
    page-id="{{ _post.id }}"
></hyvor-talk-comments>` : '';

    function handleAddingCode() {

        const promise = new Promise((resolve, reject) => {
            try {
                updateBlogSave({
                    data: {'comments_code': embedCode},
                    onUpdate: resolve
                })
            } catch (e) {
                reject();
            }
        })

        toast.promise(promise, {
            pending: 'Updating code...',
            success: 'Code updated successfully!',
            error: 'Failed to update code.'
        })

    }

    if (!isOwner) {
        return <Callout
            color={CalloutColors.RED}
            text="Only the owner of the blog can manage the Hyvor Talk integration."
        />;
    }

    return <div className="integration hyvor-talk">

        {
            loadAjax.status === 'success' ?

            <div>

                <DualSetting
                    title="Introduction"
                    right={
                        <div>
                            <a href="https://talk.hyvor.com" target="_blank" className="link">Hyvor Talk</a> is our own commenting platform. You can use it on your blog for free.
                            <ul>
                                <li>
                                    Connect your blog to a website in Hyvor Talk
                                </li>
                                <li>
                                    Use the same HYVOR account (for the owner)
                                </li>
                                <li>
                                    Completely free
                                </li>
                                <li>
                                    Fast, secure, and privacy-focused
                                </li>
                            </ul>
                        </div>
                    }
                />

                <DualSetting
                    title="Connect Hyvor Talk"
                    right={
                        <div>
                            {
                                data.connected ?
                                <>
                                    <div className="connection-status">
                                        This blog is connected to website ID <strong>{data.data.website_id}</strong> in Hyvor Talk. You can manage comments from the Hyvor Talk Console.
                                    </div>
                                    <a
                                        className="button small"
                                        href={`https://talk.hyvor.com/consolev3/${data.website_id}/comments`}
                                        target="_blank"
                                    >Go to Hyvor Talk Console</a>
                                    <button
                                        className="button small danger disconnect-button"
                                        onClick={() => setIsDisconnecting(true)}
                                    >Disconnect</button>
                                </> :
                                <>
                                    <div className="connection-status">
                                        This blog is not connected to a website in Hyvor Talk.
                                    </div>
                                    <button
                                        className="button"
                                        onClick={() => setIsConnecting(true)}
                                    >Connect Now</button>
                                </>
                            }
                        </div>
                    }
                />

                {
                    data.connected &&
                    <DualSetting
                        title="Embed Code"
                        right={
                            <div>
                                <div className="connection-status">
                                    Add the following code to <NavLink href={`/console/${subdomain}/settings/comments`} className="global-jump-link"><strong>Settings &rarr; Comments & Newsletter &rarr; Comments Embed Code</strong></NavLink> by copying and pasting or clicking the button below. You may customize the code if you want.
                                </div>
                                <pre><code>{embedCode}</code></pre>
                                <button className="button small" onClick={handleAddingCode}>Update "Comments Embed Code"</button>
                            </div>
                        }
                    />
                }

            </div> :
            <Loader padding={100} />
        }

        {
            isConnecting &&

            <PopupConfirm
                title="Connect Hyvor Talk"
                text={
                    <div>
                        <p>
                            Please confirm that you want to create a website ID in Hyvor Talk for this blog.
                        </p>
                        <ul>
                            <li>A new Hyvor Talk website ID will be created under your HYVOR account.</li>
                            <li>It is free of charge.</li>
                            <li>It can only be used on this blog.</li>
                            <li>If you have any other websites on Hyvor Talk, you will need a separate subscription.</li>
                        </ul>
                    </div>
                }
                name="Connect Now"
                onClick={() => {
                    setIsConnectConfirming(true);
                    createIntegration({
                        onSuccess: () => {
                            setIsConnectConfirming(false);
                            setIsConnecting(false);
                        }
                    });
                }}
                onCancel={() => setIsConnecting(false)}
                isLoading={isConnectConfirming}
            />
        }

        {
            isDisconnecting &&
            <PopupConfirm
                title="Disconnect Hyvor Talk"
                text={
                    <div>
                        <Callout
                            color={CalloutColors.RED}
                            title="Caution"
                            text="You cannot connect this blog to the same website ID again."
                        />
                        Are you sure you want to disconnect this blog from Hyvor Talk? This will not delete your Hyvor Talk Website ID. You will have to delete it manually from the Hyvor Talk Console.
                    </div>
                }
                name="Disconnect"
                onClick={() => {
                    deleteIntegration({
                        onSuccess: () => {
                            setIsDisconnecting(false);
                        }
                    });
                }}
                onCancel={() => setIsDisconnecting(false)}
                isLoading={deleteIntegrationAjax.status === 'loading'}
                buttonClass="danger"
            />
        }

    </div>;
}
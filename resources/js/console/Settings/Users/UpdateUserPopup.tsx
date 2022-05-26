import {useValues} from "kea";
import languagesLogic from "../../logic/languagesLogic";
import React, {useState} from "react";
import {User, UserVariant} from "../../types";
import subdomainLogic from "../../logic/subdomainLogic";
import {Popup, PopupBodyDefault, PopupFooterDoubleButton, PopupHeaderDefault} from "../../ReusableComponents/Popup";
import UserLanguageSelector from "./UserLanguageSelector";
import Input from "../../ReusableComponents/Input";
import LanguageSelector from "../../ReusableComponents/LanguageSelector";

export default function UpdateUserPopup({user, onClose}: {user: User, onClose: Function}) {

    const subdomain = subdomainLogic.values.subdomain;

    const { languages, primaryLanguage } = useValues(languagesLogic({subdomain}))
    const [currentLanguageId, setCurrentLanguageId] = useState( primaryLanguage.id );

    const [newUser, setNewUser] = useState<User>(user)

    const variants = newUser.variants || [];
    const variant: UserVariant = variants[currentLanguageId] || {} as UserVariant;

    const [ isUpdating, setIsUpdating ] = useState<boolean>(false)

    function updateUser() {
        setIsUpdating(true);
    }

    return <Popup
        header={
            <div>
                <PopupHeaderDefault title="Update User" />
                <LanguageSelector
                    languageId={currentLanguageId}
                    variantsLanguageIds={Object.keys(variants).map(id => parseInt(id))}
                    onChange={setCurrentLanguageId}
                    variantCreator={() => {}}
                />
            </div>
        }
        body={
            <PopupBodyDefault>
                <Input
                    title="Name"
                    type="text"
                    name="name"
                    value={variant.name}
                    onChange={value => {}}
                    placeholder="Name"
                />
                <Input
                    title="Slug"
                    type="text"
                    name="slug"
                    value={newUser.slug}
                    onChange={value => {}}
                    placeholder="Slug"
                />
                <Input
                    title="Email"
                    type="text"
                    name="email"
                    value={newUser.email}
                    onChange={value => {}}
                    placeholder="Email"
                />
                <Input
                    title="Website URL"
                    type="text"
                    name="url"
                    value={newUser.website_url}
                    onChange={value => {}}
                    placeholder="URL"
                />
            </PopupBodyDefault>

            /*<PopupBodyDefault>
                <div>
                    <div className="global-avatar">
                    <div className="avatar">
                        <img src={user.picture_url} alt="Avatar" className="avatar-center"/>
                    </div>
                    <input type="file" id="actual-btn" hidden/>

                        <div className="upload-lable"
                        onClick={uploadAjax.status === 'loading' ? null: handleUpload}>
                    {uploadAjax.status === 'loading' ? "Uploading..." : <span> Choose File</span>}
                        </div>
                        </div>

                        <Input
                            title="Name"
                            type="text"
                            name="name"
                            value={variantName}
                            onChange={setVariantName}
                            placeholder="Name"
                        />
                        <div className={pointerEvent}>
                        <Input
                            title="Slug"
                            type="text"
                            name="slug"
                            value={userSlug}
                            onChange={setSlug}
                            placeholder="Slug"
                        />
                        </div>
                        <div className={pointerEvent}>
                        <DualSetting
                        left={
                        <Input
                        title="Email"
                        type="text"
                        name="email"
                        value={userEmail}
                        onChange={setEmail}
                        placeholder="Email"
                        />
                    }
                        right={
                        <Input
                        title="Url"
                        type="text"
                        name="url"
                        value={userUrl}
                        onChange={setUrl}
                        placeholder="Url"
                        />
                    }
                        />
                        </div>
                        <div className={pointerEvent}>
                        <DualSetting
                        left={
                        <div>
                        <div className="popup-type-margin">Role</div>
                        <SelectUserRole  options = {selectRole} onChange = {handleRole}/>
                        </div>
                    }
                        right={
                        <div>
                        <div className="popup-type-margin">Status</div>
                        <SelectUserRole  options = {selectStatus} onChange = {handleStatus}/>
                        </div>
                    }
                        />
                        </div>

                        <DualSetting
                        left={
                        <Input
                        title="Location"
                        type="text"
                        name="location"
                        value={variantLocation}
                        onChange={setVariantLocation}
                        placeholder="Location"
                        />
                    }
                        right={
                        <div className={pointerEvent}>
                        <Input
                        title="Facebook"
                        type="text"
                        name="facebook"
                        value={facebook}
                        onChange={setFacebook}
                        placeholder="Facebook"
                        />
                        </div>
                    }
                        />
                        <div className={pointerEvent}>
                        <DualSetting
                        left={
                        <Input
                        title="Twitter"
                        type="text"
                        name="twitter"
                        value={twitter}
                        onChange={setTwitter}
                        placeholder="Twitter"
                        />
                    }
                        right={
                        <Input
                        title="LinkedIn"
                        type="text"
                        name="LinkedIn"
                        value={linkedIn}
                        onChange={setLinkedIn}
                        placeholder="linkedIn"
                        />
                    }
                        />
                        <DualSetting
                        left={
                        <Input
                        title="Youtube"
                        type="text"
                        name="youtube"
                        value={youtube}
                        onChange={setYoutube}
                        placeholder="Youtube"
                        />
                    }
                        right={
                        <Input
                        title="Instagram"
                        type="text"
                        name="instagram"
                        value={instagram}
                        onChange={setInstagram}
                        placeholder="Instagram"
                        />
                    }
                        />
                        </div>

                        <div className="popup-type-margin">Bio</div>
                        <textarea
                        className="input"
                        placeholder="Write a bio..."
                        value={variantBio}
                        onChange={setVariantBio}
                        maxLength={350}
                        ></textarea>

                        <div className="table-delete">
                        <span className = 'button danger' onClick={handleDelete}>
                        Delete
                        </span>
                    {
                        deletePopupOpened ?
                        <PopupConfirm
                        title="Delete Permanently"
                        text="Are you sure to delete this user permanently? You will not be able to access it anymore."
                        name="Delete"
                        buttonClass="danger"
                        onClick={handleDoDelete}
                        onCancel={handleDeleteCancel}
                        />
                        : null
                    }
                        </div>
                        </div>
            </PopupBodyDefault>*/
        }
        footer={
            <PopupFooterDoubleButton
                onCancel={onClose}
                onClick={updateUser}
                name="Update"
                isLoading={isUpdating}
            />
        }
    />


}

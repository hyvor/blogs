import React, {useState} from "react";
import { User, UserVariant} from "../../types";
import {Popup, PopupBodyDefault, PopupFooterDoubleButton, PopupHeaderDefault} from "../../ReusableComponents/Popup";
import Input, {InputView} from "../../ReusableComponents/Input";
import LanguageSelector from "../../ReusableComponents/LanguageSelector";
import {useLanguagesValues} from "../Languages/helpers";
import {useUsersActions} from "../../logic-helpers/users";
import ImageSelector from "../../ReusableComponents/ImageSelector";
import {CaretDownFill, CaretRightFill} from "react-bootstrap-icons";

export default function UpdateUserPopup({user, onClose}: {user: User, onClose: Function}) {

    const { createVariant, update } = useUsersActions()

    const { primaryLanguage } = useLanguagesValues();
    const [currentLanguageId, setCurrentLanguageId] = useState(primaryLanguage.id);

    const [newUser, setNewUser] = useState(user)
    const [showSocial, setShowSocial] = useState(false);

    const variants = newUser.variants || [];
    const variant: UserVariant = variants.find(v => v.language_id === currentLanguageId) as UserVariant;

    const [ isUpdating, setIsUpdating ] = useState<boolean>(false)

    function updateUser() {
        setIsUpdating(true);
        update({
            user: newUser,
            onUpdate: onClose,
            onError: () => setIsUpdating(false)
        })
    }

    function createVariantExtended({id, languageId, onCreate}: {id: number, languageId: number, onCreate: Function}) {
        createVariant({
            id,
            languageId,
            onCreate: (variant: UserVariant) => {
                const copy = {...newUser}
                copy.variants.push(variant)
                setNewUser(copy)
                onCreate(variant);
            }
        })
    }

    function changeVariantValue<T extends keyof UserVariant>(key: T, val: UserVariant[T]) {
        const variantCopy  = {...variant}
        variantCopy[key] = val;
        const copy = {...newUser}
        copy.variants = copy.variants.map(
            v => v.language_id === currentLanguageId ? variantCopy : v
        );
        setNewUser(copy)
    }

    function changeValue<T extends keyof User>(key: T, val: User[T]) {
        const copy = {...newUser}
        copy[key] = val;
        setNewUser(copy)
    }

    return <Popup
        header={
            <div>
                <PopupHeaderDefault title="Update User" />
                <LanguageSelector
                    id={user.id}
                    languageId={currentLanguageId}
                    variantsLanguageIds={variants.map(v => v.language_id)}
                    onChange={setCurrentLanguageId}
                    variantCreator={createVariantExtended}
                />
            </div>
        }
        body={
            <PopupBodyDefault>
                <div className="user-image-view">
                    <ImageSelector
                        src={newUser.picture_url}
                        onChange={url => changeValue('picture_url', url)}
                    />
                </div>
                <Input
                    title="Name"
                    type="text"
                    name="name"
                    value={variant.name}
                    onChange={value => changeVariantValue('name', value)}
                />
                <Input
                    title="Slug"
                    type="text"
                    name="slug"
                    value={newUser.slug}
                    onChange={value => changeValue('slug', value)}
                />
                <Input
                    title="Email"
                    type="text"
                    name="email"
                    value={newUser.email}
                    onChange={value => changeValue('email', value)}
                />
                <InputView
                    title="Bio"
                    content={
                        <textarea
                            className="input"
                            value={variant.bio || ''}
                            onChange={e => changeVariantValue('bio', e.target.value)}
                        />
                    }
                />
                <Input
                    title="Location"
                    type="text"
                    name="location"
                    value={variant.location}
                    onChange={value => changeVariantValue('location', value)}
                />
                <Input
                    title="Website URL"
                    type="text"
                    name="url"
                    value={newUser.website_url}
                    onChange={value => changeValue('website_url', value)}
                />
                <div className="show-social">
                    <a
                        className="link"
                        onClick={() => setShowSocial(!showSocial)}
                    >Social Links { showSocial ? <CaretDownFill /> : <CaretRightFill /> }</a>
                </div>
                {
                    showSocial &&
                    <div>
                        <Input
                            title="Facebook"
                            type="text"
                            name="facebook_url"
                            value={newUser.social_facebook}
                            onChange={value => changeValue('social_facebook', value)}
                        />
                        <Input
                            title="Twitter"
                            type="text"
                            name="twitter_url"
                            value={newUser.social_twitter}
                            onChange={value => changeValue('social_twitter', value)}
                        />
                        <Input
                            title="Linkedin"
                            type="text"
                            name="linkedin_url"
                            value={newUser.social_linkedin}
                            onChange={value => changeValue('social_linkedin', value)}
                        />
                        <Input
                            title="Youtube"
                            type="text"
                            name="youtube_url"
                            value={newUser.social_youtube}
                            onChange={value => changeValue('social_youtube', value)}
                        />
                        <Input
                            title="Tiktok"
                            type="text"
                            name="tiktok_url"
                            value={newUser.social_tiktok}
                            onChange={value => changeValue('social_tiktok', value)}
                        />
                        <Input
                            title="Instagram"
                            type="text"
                            name="instagram_url"
                            value={newUser.social_instagram}
                            onChange={value => changeValue('social_instagram', value)}
                        />
                        <Input
                            title="Github"
                            type="text"
                            name="github_url"
                            value={newUser.social_github}
                            onChange={value => changeValue('social_github', value)}
                        />
                    </div>
                }
            </PopupBodyDefault>
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

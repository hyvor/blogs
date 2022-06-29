import React, {useState} from 'react'
import {Popup, PopupBodyDefault, PopupFooterDoubleButton, PopupHeaderDefault} from "../../ReusableComponents/Popup";
import Input, {InputView} from "../../ReusableComponents/Input";
import {useTagsActions, useTagsValues} from "./useTags";
import LanguageSelector from "../../ReusableComponents/LanguageSelector";
import { Tag, TagVariant} from "../../types";
import {useLanguagesValues} from "../Languages/helpers";
import CodemirrorEditor, {CODEMIRROR_MODES} from "../../ReusableComponents/CodemirrorEditor";
import {CaretDownFill, CaretRightFill} from "react-bootstrap-icons";

export default function UpdateTagPopup({ tag, onClose } : { tag: Tag, onClose: Function }) {

    const { updateAjax } = useTagsValues()
    const { update, createVariant } = useTagsActions()

    const { primaryLanguage } = useLanguagesValues()
    const [currentLanguageId, setCurrentLanguageId] = useState(primaryLanguage.id)

    const [ showCode, setShowCode ] = useState(false)

    const [tagState, setTagState] = useState(tag);

    const variants = tagState.variants;
    const variant = variants.find(v => v.language_id === currentLanguageId) as TagVariant

    function handleClick() {
        update({tag: tagState, onUpdate: onClose})
    }

    function createVariantExtended({id, languageId, onCreate}: {id: number, languageId: number, onCreate: Function}) {
        createVariant({
            id,
            languageId,
            onCreate: (variant: TagVariant) => {
                const copy = {...tagState}
                copy.variants.push(variant)
                setTagState(copy)
                onCreate(variant);
            }
        })
    }

    function changeVariantValue<T extends keyof TagVariant>(key: T, val: TagVariant[T]) {
        const variantCopy  = {...variant}
        variantCopy[key] = val;
        const copy = {...tagState}
        copy.variants = copy.variants.map(
            v => v.language_id === currentLanguageId ? variantCopy : v
        );
        setTagState(copy)
    }

    function changeValue<T extends keyof Tag>(key: T, val: Tag[T]) {
        const copy = {...tagState}
        copy[key] = val;
        setTagState(copy)
    }

    return <Popup
        header={
            <div>
                <PopupHeaderDefault title="Update Tag" />
                <LanguageSelector
                    id={tag.id}
                    languageId={currentLanguageId}
                    variantsLanguageIds={variants.map(v => v.language_id)}
                    onChange={setCurrentLanguageId}
                    variantCreator={createVariantExtended}
                />
            </div>
        }
        body={
            <PopupBodyDefault>
                <Input
                    title="Name"
                    type="text"
                    name="name"
                    value={variant.name || ''}
                    onChange={val => changeVariantValue('name', val)}
                    autoFocus={true}
                />
                <Input
                    title="Description"
                    type="text"
                    name="description"
                    value={variant.description || ''}
                    onChange={val => changeVariantValue('description', val)}
                />
                <div className={currentLanguageId !== primaryLanguage.id ? "global-non-primary-language-hidden" : ""}>
                    <Input
                        title="Slug"
                        type="text"
                        name="slug"
                        value={tagState.slug}
                        onChange={val => changeValue('slug', val)}
                    />
                    <div className="show-code">
                        <a
                            className="link"
                            onClick={() => setShowCode(!showCode)}
                        >Custom Code { showCode ? <CaretDownFill /> : <CaretRightFill /> }</a>
                    </div>
                    {
                        showCode &&
                        <div>
                            <InputView
                                title="Code Head"
                                content={
                                    <CodemirrorEditor
                                        value={tagState.code_head || ''}
                                        onChange={(v : string) => changeValue('code_head', v)}
                                        mode={CODEMIRROR_MODES.twig}
                                    />
                                }
                            />
                            <InputView
                                title="Code Foot"
                                content={
                                    <CodemirrorEditor
                                        value={tagState.code_foot || ''}
                                        onChange={(v : string) => changeValue('code_foot', v)}
                                        mode={CODEMIRROR_MODES.twig}
                                    />
                                }
                            />
                        </div>
                    }
                </div>
            </PopupBodyDefault>
        }
        footer={
            <PopupFooterDoubleButton
                onCancel={onClose}
                onClick={handleClick}
                name="Update"
                isLoading={updateAjax.status === 'loading'}
                loadingName="Updating"
            />
        }
    />;
}
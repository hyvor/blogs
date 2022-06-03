import React, {useState} from 'react';
import {Navigation, NavigationType, NavigationVariant, UserVariant} from "../../types";
import navigationLogic from "../../logic/navigationLogic";
import getSubdomain from "../../logic-helpers/subdomain";
import {useActions, useValues} from "kea";
import {Popup, PopupBodyDefault, PopupFooterDoubleButton, PopupHeaderDefault} from "../../ReusableComponents/Popup";
import Input, {InputView} from "../../ReusableComponents/Input";
import Select, {SelectOption} from "../../ReusableComponents/Select";
import LanguageSelector from "../../ReusableComponents/LanguageSelector";
import languagesLogic from "../../logic/languagesLogic";
import merge from "deepmerge";

export default function CreateUpdateNavigationPopup(
    { navigation = {} as Navigation, onClose } :
    { navigation?: Navigation, onClose: Function }
) {

    const isCreate = !navigation.id;

    const subdomain = getSubdomain()
    const navigationLogicInst = navigationLogic({subdomain})
    const { createAjax, updateAjax, updateVariantAjax } = useValues(navigationLogicInst)
    const { create, update, updateVariant, createVariant } = useActions(navigationLogicInst)


    function handleClick() {
        if (isCreate) {
            create({
                name,
                url,
                type,
                onCreate: onClose
            })
        } else {
            let langId : string | number
            for (langId in variantsState) {
                langId = parseInt(langId)
                if (variantsState[langId].name !== variants[langId].name) {
                     updateVariant({
                        id: navigation.id,
                        languageId: langId,
                        name: variantsState[langId].name,
                    })
                }
            }

            if (url !== navigation.url) {
                update({
                    id: navigation.id,
                    url
                });
            }

            onClose();
        }
    }

    const typeOptions : SelectOption[] = [
        { label: 'Header', value: 'header' },
        { label: 'Footer', value: 'footer'}
    ];

    const { primaryLanguage } = useValues(languagesLogic({subdomain}))
    const [currentLanguageId, setCurrentLanguageId] = useState( primaryLanguage.id );

    const variants = navigation.variants || [];
    const variant: NavigationVariant = variants[currentLanguageId] || {} as NavigationVariant;

    const [variantsState, setVariantsState] = useState(navigation.variants);

    const [name, setName] = useState(variant.name || '');
    const [url, setUrl] = useState(navigation.url || '');

    const [type, setType] = useState<NavigationType>('header');

    function updateName(value: any) {
        setVariantsState(merge(variantsState, {[currentLanguageId]: {name: value}}));
    }

    return <Popup
        header={
            <div>
                <PopupHeaderDefault title={isCreate ? "Create Navigation" : "Update Navigation"} />
                {
                    !isCreate &&
                    <LanguageSelector
                        id={navigation.id}
                        languageId={currentLanguageId}
                        variantsLanguageIds={Object.keys(variants).map(id => parseInt(id))}
                        onChange={setCurrentLanguageId}
                        variantCreator={createVariant}
                    />
                }
            </div>
        }
        body={
            <PopupBodyDefault>
                <div>
                    <Input
                        title="Name"
                        type="text"
                        name="name"
                        value={isCreate ? name : variantsState[currentLanguageId].name}
                        onChange={value => isCreate ? setName(value) : updateName(value)}
                        placeholder="About us"
                        autoFocus={true}
                    />
                    <div className={currentLanguageId !== primaryLanguage.id ? "global-non-primary-language-hidden" : ""}>
                        <Input
                            title="URL"
                            type="text"
                            name="url"
                            value={url}
                            onChange={value => setUrl(value)}
                            placeholder="/about"
                        />
                    </div>
                    {
                        isCreate &&
                        <InputView
                            title="Type"
                            content={
                                <Select
                                    options={typeOptions}
                                    defaultValue={typeOptions.find(o => o.value === type)}
                                    onChange={(v: any) => setType(v.value)}
                                />
                            }
                        />
                    }
                </div>
            </PopupBodyDefault>
        }
        footer={
            <PopupFooterDoubleButton
                onCancel={onClose}
                onClick={handleClick}
                name={isCreate ? "Create" : "Update"}
                isLoading={
                    createAjax.status === 'loading' || updateAjax.status === 'loading' ||
                    updateVariantAjax.status === 'loading'
                }
                loadingName={isCreate ? "Creating" : "Updating"}
            />
        }
    />;

}
import React, {useState} from "react";
import {Popup, PopupBodyDefault, PopupFooterDoubleButton, PopupHeaderDefault} from "../../ReusableComponents/Popup";
import Input, {InputView} from "../../ReusableComponents/Input";
import {ApiKeyType} from "../../types";
import Select, {SelectOption} from "../../ReusableComponents/Select";
import {useApiKeysActions, useApiKeysValues} from "../../logic-helpers/api-keys";

export default function CreateApiKeyPopup( {onClose } : { onClose: Function}) {

    const { create } = useApiKeysActions()
    const { createAjax } = useApiKeysValues()

    const [name, setName] = useState('');
    const [type, setType] = useState<ApiKeyType>('console');

    const typeOptions : SelectOption[] = [
        { label: 'Console API', value: 'console' },
        { label: 'Delivery API', value: 'delivery'}
    ];

    function handleClick() {
        create({name, type, onCreate: onClose});
    }

    return <Popup
        header={<PopupHeaderDefault title="Create API Key" />}
        body={
            <PopupBodyDefault>
                <div>
                    <Input
                        title="Name"
                        type="text"
                        name="name"
                        value={name}
                        onChange={value => setName(value)}
                        placeholder="My API Key"
                        autoFocus={true}
                    />
                    <InputView title="API" content={
                        <Select
                            options={typeOptions}
                            defaultValue={typeOptions.find(o => o.value === type)}
                            onChange={(v: any) => setType(v.value)}
                        />
                    } />
                </div>
            </PopupBodyDefault>
        }
        footer={
            <PopupFooterDoubleButton
                onCancel={onClose}
                onClick={handleClick}
                name='Create'
                isLoading={createAjax.status === 'loading'}
                loadingName="Creating"
            />
        }
    />;

}
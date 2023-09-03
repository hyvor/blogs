import yaml from 'js-yaml';
import Callout, { CalloutColors } from '../console/ReusableComponents/Callout';
import React, { useEffect, useState } from 'react';
import DualSetting from '../console/ReusableComponents/DualSetting';
import Input from '../console/ReusableComponents/Input';
import Radio from '../console/ReusableComponents/Radio';
import Switch from '../console/ReusableComponents/Switch';
import { ColorPicker } from '../console/ReusableComponents/ColorPicker';
import deepmerge from 'deepmerge';
import DOMPurify from "dompurify";

interface ConfigDefProps {
    configYaml: string,
    configDefYaml: string,
    onConfigChange?: (configYaml: string) => void
}


function createUpdatingObject(parentKeys: string[], key: string, value: any) {

    let updatingObject : any = {};

    if (parentKeys.length === 0) {
        updatingObject[key] = value;
    } else {
        updatingObject = {
            [parentKeys[0]]: createUpdatingObject(parentKeys.slice(1), key, value)
        }
    }

    return updatingObject;

}

function getNewConfigState(configState: object, parentKeys: string[], key: string, value: any) : object {
    const updatingObject = createUpdatingObject(parentKeys, key, value);
    return deepmerge(configState, updatingObject);
}

export default function ConfigDef({ configYaml, configDefYaml, onConfigChange } : ConfigDefProps) {

    const [configState, setConfigState] = useState({});

    let error;
    let configDef;

    useEffect(() => {
        try { 
            setConfigState(yaml.load(configYaml) as any); 
        } catch (e: any) { 
            error = 'Unable to parse config.yaml: ' + e.message
        }
    }, [configYaml]);

    
    try { 
        configDef = yaml.load(configDefYaml) 
    } catch (e: any) { 
        error = 'Unable to parse config.def.yaml: ' + e.message
    }

    if (!error && (!configState || typeof configState !== 'object')) {
        error = 'Invalid data type in config.yaml. Object required.';
    }

    if (error) {
        return <Callout 
            color={CalloutColors.RED}
            title="YAML Parsing Error"
            text={error}
        />
    }

    function handleChange(parentKeys: string[], key: string, value: any) {
        const newState = getNewConfigState(configState, parentKeys, key, value);
        setConfigState(newState)
        onConfigChange && onConfigChange(yaml.dump(newState));
    }

    configDef = configDef || {};

    // add default values
    configDef = {...configDef, ...{
        THEME_NAME: {
            $name: 'Theme Name',
            $type: 'none'
        },
        THEME_VERSION: {
            $name: 'Theme Version',
            $type: 'none'
        },
        THEME_FONTS: {
            $name: 'Theme Fonts',
            $type: 'text',
            $description: 'Load Google fonts locally. See <a class="link" href="https://blogs.hyvor.com/docs/fonts" target="_blank">docs</a> for more info.'
        },
        POSTS_PER_PAGINATION: {
            $name: 'Posts per Pagination',
            $description: 'Number of posts to show per page on index pages',
            $type: 'number',
        }
    }}

    return <ObjectConfig 
        config={configState as object} 
        configDef={configDef || {} as object}
        onChange={handleChange}
    />;

}

interface ObjectConfigProps {
    config: object,
    configDef: object,
    parentKeys?: string[],
    onChange: (parentKeys: string[], key: string, value: any) => void
}

function ObjectConfig({ config, configDef, onChange, parentKeys = [] } : ObjectConfigProps) {


    return <div>

        {
            Object.entries(config).map(([key, value]) => {

                const def : any = configDef?.[key as keyof typeof configDef] || {};
                const name = def.$name || key;
                const description = def.$description || null;

                const allParentKeys = [...parentKeys, key];

                const hasChildren = typeof value === 'object' && value !== null;

                return <div key={allParentKeys.join('.')}>


                    <DualSetting
                        title={name}
                        description={
                            <div dangerouslySetInnerHTML={{
                                __html: DOMPurify.sanitize(description || '', {
                                    ADD_ATTR: ['target']
                                })
                            }}></div>
                        }
                        right={
                            <div>
                                {
                                    hasChildren ?
                                        <ObjectConfig
                                            config={value}
                                            configDef={def}
                                            parentKeys={allParentKeys}
                                            onChange={onChange}
                                        />
                                    : 
                                    <ConfigInput 
                                        value={value} 
                                        def={def}
                                        onChange={value => onChange(parentKeys, key, value)}
                                    />
                                }
                            </div>
                        }
                        props={{
                            "data-testid": "config-" + allParentKeys.join('.')
                        }}
                        subsection={hasChildren === true}
                    ></DualSetting>

                </div>

            })
        }

    </div>


}

function ConfigInput({value, def, onChange} : {value: any, def: any, onChange: (value: any) => void}) {

    const type = getValidType(def?.$type || 'text');

    if (type === 'none') {
        return value;
    } else if (type === 'text') {

        return <Input
            type="text"
            maxLength={def?.$maxlength}
            minLength={def?.$minlength}
            value={value}
            onChange={v => onChange(v)}
        ></Input>

    } else if (type === 'textarea') {

        return <textarea
            maxLength={def?.$maxlength}
            minLength={def?.$minlength}
            readOnly={def?.$disabled}
            value={value}
            onChange={e => onChange(e.target.value)}
        ></textarea>

    } else if (type === 'number') {

        return <Input
            type="number"
            min={def?.$min}
            max={def?.$max}
            value={value}
            onChange={v => onChange(v)}
        ></Input>

    } else if (type === 'checkbox') {

        return <Switch
            checked={value}
            onChange={v => onChange(v)}
        />

    } else if (type === 'radio') {

        const options : Record<string, string> = def?.$options || {};

        return <div>

            {
                Object.entries(options).map(([key, label]) => {

                    return <div>
                        <Radio 
                            checkFor={value}
                            placeholder={label}
                            name={key}
                            value={key}
                            onChange={v => onChange(v)}
                        />
                    </div>

                })
            }

        </div>

    } else if (type === 'color') {

        return <ColorPicker
            color={value}
            onChange={v => onChange(v)}
            onClose={() => {}}
            preset={[]}
        />

    }

    return <div>{value}</div>

}

function getValidType(type: any) {

    const types = [
        'none',
        'text',
        'textarea',
        'number',
        'checkbox',
        'radio',
        'select',
        'color'
    ];

    if (types.includes(type)) {
        return type;
    }

    return 'text';

}
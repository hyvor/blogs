import yaml from 'js-yaml';
import Callout, { CalloutColors } from '../console/ReusableComponents/Callout';
import React from 'react';
import DualSetting from '../console/ReusableComponents/DualSetting';

interface ConfigDefProps {
    configYaml: string,
    configDefYaml: string
}

export default function ConfigDef({ configYaml, configDefYaml } : ConfigDefProps) {

    let error;

    let config, configDef;

    try { 
        config = yaml.load(configYaml) 
    } catch (e: any) { 
        error = 'Unable to parse config.yaml: ' + e.message
    }

    try { 
        configDef = yaml.load(configDefYaml) 
    } catch (e: any) { 
        error = 'Unable to parse config.def.yaml: ' + e.message
    }

    if (!error && (!config || typeof config !== 'object')) {
        error = 'Invalid data type in config.yaml. Object required.';
    }

    if (error) {
        return <Callout 
            color={CalloutColors.RED}
            title="YAML Parsing Error"
            text={error}
        />
    }

    return <ObjectConfig 
        config={config as object} 
        configDef={configDef || {} as object}
    />;

}

function ObjectConfig({ config, configDef } : { config: object, configDef: object }) {


    return <div>

        {
            Object.entries(config).map(([key, value]) => {


                

                return <div>

                    <DualSetting
                        title={key}
                        right={
                            <div>
                                {
                                    typeof value === 'object' && value !== null ?
                                        <ObjectConfig
                                            config={value}
                                            configDef={configDef[key as keyof typeof configDef] as object}
                                        />
                                    : value
                                }
                            </div>
                        }
                    ></DualSetting>

                </div>

            })
        }

    </div>


}
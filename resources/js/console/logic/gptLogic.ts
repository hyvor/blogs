import { actions, kea, key, path, props, reducers } from "kea";
import { gptLogicType } from "./gptLogicType";
import { GptPrompt } from "../types";

const gptLogic = kea<gptLogicType>([
    
    props({} as {id: number}),
    key((props) => props.id),
    path(key => ['gpt', key]),

    actions(({values}) => ({

        setPrompts: (prompts: GptPrompt[]) => ({prompts}),

        setPendingPrompt: (prompt: string) => ({prompt, error: null}),
        setPendingPromptError: (error: string) => ({error}),

    })),


    reducers({

        prompts: [
            [] as GptPrompt[],
            {
                setPrompts: (_, {prompts}) => prompts,
            }
        ],

        pendingPrompt: [
            null as {
                prompt: string,
                error: null | string
            } | null,
            {
                setPendingPrompt: (_, {prompt, error}) => ({prompt, error}),
                setPendingPromptError: (state, {error}) => ({prompt: state?.prompt || '', error}),
            }
        ],



    })

]);

export default gptLogic;
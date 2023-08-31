import { actions, kea, key, path, props, reducers } from "kea";
import type { gptLogicType } from "./gptLogicType";
import { GptPrompt } from "../types";
import { ajax } from "kea-ajax";
import api from "../lib/api";
import getSubdomain from "../logic-helpers/subdomain";

const gptLogic = kea<gptLogicType>([
    
    props({} as {id: number}),
    key((props) => props.id),
    path(key => ['gpt', key]),

    actions(({values}) => ({

        setPrompts: (prompts: GptPrompt[]) => ({prompts}),

        unsetPendingPrompt: () => ({}),
        setPendingPrompt: (prompt: string) => ({prompt, error: null}),
        setPendingPromptError: (error: string) => ({error}),

    })),

    ajax(({actions, values, props}) => ({
      
        loadPrompts: async () => {
            const res = await api.get<GptPrompt[]>(getSubdomain(), '/gpt/post-history', {
                post_id: props.id
            });
            actions.setPrompts(res);
        },


        createPrompt: async ({prompt}: {prompt: string}) => {

            actions.setPendingPrompt(prompt);

            try {

                const res = await api.post<GptPrompt>(getSubdomain(), '/gpt/prompt', {
                    post_id: props.id,
                    prompt
                })

                actions.setPrompts([...values.prompts, res]);
                actions.unsetPendingPrompt();

            } catch (e) {
                actions.setPendingPromptError('Error creating prompt');
            }

        },

        resetChat: async () => {
            actions.setPrompts([]);
            await api.delete<GptPrompt[]>(getSubdomain(), '/gpt/post-history', {
                post_id: props.id
            });
        }

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
                unsetPendingPrompt: () => null,
                setPendingPrompt: (_, {prompt, error}) => ({prompt, error}),
                setPendingPromptError: (state, {error}) => ({prompt: state?.prompt || '', error}),
            }
        ],



    })

]);

export default gptLogic;
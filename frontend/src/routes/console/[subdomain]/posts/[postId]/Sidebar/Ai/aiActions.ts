import { get } from "svelte/store";
import type { GptPrompt, Post, PostVariant } from "../../../../../lib/types";
import consoleApi from "../../../../../lib/consoleApi";

export function sendPrompt(prompt: string, post_id: number) {
    return consoleApi.post({
        endpoint: '/gpt/prompt',
        data: {
            post_id: post_id,
            prompt: prompt
        }
    });
}

export function getPrompts(post_id: number) {
    return consoleApi.get<GptPrompt[]>({
        endpoint: '/gpt/post-history',
        data: {
            post_id
        }
    });
}
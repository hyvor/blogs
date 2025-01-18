<script lang="ts">
	import { Button, Divider, IconMessage, Link, Loader, TextInput, Textarea, Tooltip, toast } from "@hyvor/design/components";
	import type { GptPrompt } from "../../../../../../lib/types";
	import { postStore, postVariantStore } from "../../../postStore";
	import IconArrowClockwise from '@hyvor/icons/IconArrowClockwise';
import IconMagic from '@hyvor/icons/IconMagic';
import IconRobot from '@hyvor/icons/IconRobot';

	import { getPrompts, resetChat, sendPrompt } from "./aiActions";
	import { onMount, tick } from "svelte";
	import { tab } from "../sidebar";
	import PromptResponse from "./PromptResponse.svelte";

    interface AutomaticPromptOptions {
        title: string | null,
        primaryKeyword: string | null,
        secondaryKeywords: string[] | null,
    }

    interface AutomaticPrompt {
        name: string,
        description: string,
        prompt: (options: AutomaticPromptOptions) => string,
        tip?: false
    }

    function addKeywordPrompt(p:string, options: AutomaticPromptOptions) {
        if (options.primaryKeyword) {
            p += `\n\nMy primary keyword is ${options.primaryKeyword}.`;
            if (options.secondaryKeywords?.length) {
                p += ` Secondary keywords are ${options.secondaryKeywords.join(', ')}.`;
            }
        }
        return p;
    }

    const automaticPrompts : AutomaticPrompt[]  = [
        {
            name: 'Blog Outline',
            description: 'Generate an outline for a blog post',
            prompt: (options) => addKeywordPrompt(`Write a blog outline on ${options.title}.`, options)
        },

        {
            name: 'Blog Post',
            description: 'Generate a blog post',
            prompt: (options) => addKeywordPrompt(`Write a blog post about ${options.title}.`, options)
        },

        {
            name: 'Article',
            description: 'Generate an article, which is more formal than a blog post',
            prompt: (options) => {
                let p = `Write an article about ${options.title}.`;
                p = addKeywordPrompt(p, options);
                p += `\n\nInclude real-life examples and case studies to support my points.`;
                return p;
            }
        },

        {
            name: 'FAQ generator',
            description: 'Generate a list of questions and answers',
            prompt: (options) => addKeywordPrompt(`Write a list of frequently asked questions about "${options.title}" and provide answers to them considering SERP and rich result guidelines.`, options)
        },

        {
            name: 'SEO Brief',
            description: 'Generate an SEO content brief',
            prompt: (options) => addKeywordPrompt(`Write an SEO content brief for ${options.title}.`, options)
        },

        {
            name: 'SEO Keyword Ideas',
            description: 'Generate a list of SEO keyword ideas',
            prompt: (options) => `Write a list of SEO keyword ideas for ${options.title}.`,
            tip: false
        }
    ];

    let isLoading = $state(true);

    let prompts : GptPrompt[] = $state([]);
    let prompt = $state('');
    
    let pendingPrompt : string | null = $state(null);
    let pendingPromptError : string | null = $state(null);

    let title = $derived($postVariantStore.title);
    let primaryKeyword = $derived($postVariantStore.seo_primary_keyword);
    let secondaryKeywords = $derived($postVariantStore.seo_secondary_keywords);

    function loadPrompts() {
        getPrompts($postStore.id)
            .then(res => {
                prompts = res;
            })
            .catch(err => {
                toast.error(err.message);
            })
            .finally(() => {
                isLoading = false;
            })
        }

    function scrollToBottom() {
        const chatZone = document.querySelector('.chat-zone');
        if (chatZone) {
            chatZone.scrollTop = chatZone.scrollHeight;
        }
    }

    async function handleGenerate() {

        pendingPrompt = prompt;
        pendingPromptError = null;

        await tick();
        
        scrollToBottom();

        sendPrompt(prompt, $postStore.id)
            .then(res => {
                prompts = [...prompts, res];
                scrollToBottom();
                prompt = '';
                pendingPrompt = null;
            })
            .catch(err => {
                pendingPromptError = err.message;
            });

    }

    function resetPrompts() {
        isLoading = true;
        resetChat($postStore.id).then(res => {
            prompts = [];
        }).catch(err => {
            toast.error(err.message);
        })
        .finally(() => {
            isLoading = false;
        })
        pendingPrompt = '';
    }

    onMount(loadPrompts);

</script>

<div class="ai-chat">


    {#if isLoading}
        <Loader full />
    {:else}
        <div class="chat-zone">
            {#if prompts.length == 0}
                <IconMessage 
                    icon={IconRobot}
                    message="Start a conversation with the AI to generate content."
                />
            {:else}
                {#each prompts as prompt, i}
                    <PromptResponse 
                        prompt={prompt.prompt} 
                        response={prompt.gpt_response}
                    />
                {/each}

                {#if pendingPrompt}
                    <PromptResponse 
                        prompt={pendingPrompt} 
                        response={null}
                        error={pendingPromptError}
                    />
                {/if}

                <div class="reset-button">
                    <Button size="small" color="input" on:click={() => resetPrompts()}>
                        {#snippet start()}
                                                <IconArrowClockwise  />
                                            {/snippet}
                        Reset chat
                    </Button>
                </div>
            {/if}
        </div>

        <Divider color="var(--border)" />

        <div class="input-zone">

            <div class="automatic-prompts-buttons">
                {#each automaticPrompts as p}
                    <div class="automatic-prompt-button">
                        <Tooltip text={p.description}>
                            <Button
                                color="input"
                                on:click={() => prompt = p.prompt({
                                    title,
                                    primaryKeyword,
                                    secondaryKeywords })}
                                outline
                                size="small"
                            >
                                <div class="prompt-button-title">{p.name}</div>
                            </Button>
                        </Tooltip>
                    </div>
                {/each}
            </div>

            {#if !primaryKeyword}
                <div class="keyword-tip">
                    💡 Tip: <Link
                        href="javascript:void(0)"
                        on:click={() => tab.set('seo')}
                    >Add SEO keywords</Link> for better prompts.
                </div>
            {/if}

            <div class="input-row">
                <div class="prompt-input">
                    <TextInput
                        block={true}
                        placeholder="Type your prompt here..."
                        rows={1}
                        bind:value={prompt}
                    />
                </div>
                <Button
                    disabled={prompt.trim() === ''}
                    on:click={handleGenerate}>
                    <div class="generate-button-content">
                        Generate
                        <div class="generate-icon"><IconMagic /></div>
                    </div>
                </Button>
            </div>
            <div class="disclaimer">
                This chat is powered by OpenAI's GPT-4o-mini model. It may produce inaccurate results.
            </div>
        </div>

    {/if}

</div>


<style>

    .ai-chat {
        height: 100%;
        min-height: 0;
        margin:0 -25px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .chat-zone {
        overflow: auto;
        display: flex;
        flex-direction: column;
        flex: 1;
    }

    .input-zone {
        padding: 15px 25px 0;
    }
    
    .disclaimer {
        font-size: 12px;
        color: var(--text-light);
    }

    .keyword-tip {
        padding: 5px;
        font-size: 12px;
        color: var(--text-light);
        margin-bottom:3px;
    }

    .input-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .automatic-prompts-buttons {
        display: flex;
        flex-wrap: wrap;
    }

    .prompt-button-title {
        font-weight: 600;
        font-size: 12px;
    }
    
    .automatic-prompt-button {
        margin-right: 6px;
        margin-bottom: 6px;
    }

    .generate-button-content {
        display: flex;
        font-size: 12px;
        align-items: center;
    }

    .generate-icon {
        margin-left: 5px;
    }
    
    .prompt-input {
        width: 100%;
        margin-right: 5px;
    }

    .reset-button {
        align-self: flex-end;
        display: block;
        padding: 15px 25px;
    }

</style>
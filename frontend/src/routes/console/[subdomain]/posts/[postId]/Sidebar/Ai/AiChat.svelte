<script lang="ts">
	import { Button, Divider, Loader, TextInput, Textarea, Tooltip, toast } from "@hyvor/design/components";
	import type { GptPrompt } from "../../../../../lib/types";
	import { postStore, postVariantStore } from "../../../postStore";
	import { Icon2Square, IconArrowClockwise, IconMagic, IconRewind, IconSearch } from "@hyvor/icons";
	import { getPrompts, resetChat, sendPrompt } from "./aiActions";
	import { onMount } from "svelte";
    import AiResponse from "./AiResponse.svelte";

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

    export let isLoading = false;

    let prompts : GptPrompt[] = [];
    let pendingPrompt: string = '';

    let title = $postVariantStore.title;
    let primaryKeyword = $postVariantStore.seo_primary_keyword;
    let secondaryKeywords = $postVariantStore.seo_secondary_keywords;

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


        function getPromptResult() {
            sendPrompt(pendingPrompt, $postStore.id)
                .then(res => {
                    prompts = [res as GptPrompt, ...prompts];
                    pendingPrompt = '';
                })
                .catch(err => {
                    toast.error(err.message);
                })
        }

        function resetPrompts() {
            resetChat($postStore.id).then(res => {
                prompts = [];
            }).catch(err => {
                toast.error(err.message);
            })
            pendingPrompt = '';
        }

    onMount(loadPrompts);

</script>

<div class="ai-chat">

    <div class="chat-display">

        {#if isLoading}

            <div class="loader-wrap">
                <Loader padding={100} />
            </div>

        {:else}
            <div class="chat-zone">
                {#if prompts.length == 0}
                    <div class="empty-chat">
                        <IconSearch size={80}/>
                        <div class="empty-chat-text">No chat history on this post yet.</div>
                    </div>
                {:else}
                    {#each prompts as prompt}
                        <AiResponse gptPrompt={prompt} />
                    {/each}
                    <div class="reset-button">
                        <Button size="small" color="gray" on:click={() => resetPrompts()}>
                            <div class="reset-button-content">
                                <IconArrowClockwise />
                                <span class="reset-button-text">Reset chat</span>
                            </div>
                        </Button>
                    </div>
                {/if}
            </div>
            <Divider />
            <div class="input-zone">

                <div class="automatic-prompts-buttons">
                    {#each automaticPrompts as prompt}
                        <div class="automatic-prompt-button">
                            <Tooltip text={prompt.description}>
                                <Button
                                    color="gray"
                                    on:click={() => pendingPrompt = prompt.prompt({
                                        title,
                                        primaryKeyword,
                                        secondaryKeywords })}
                                    outline
                                    size="small">
                                <div class="prompt-button-title">{prompt.name}</div>
                                </Button>
                            </Tooltip>
                        </div>
                    {/each}
                </div>

                {#if !primaryKeyword}
                    <div class="keyword-tip">
                        Tip: Add SEO keywords for better prompts.
                    </div>
                {/if}

                <div class="input-row">
                    <div class="prompt-input">
                        <TextInput
                            block={true}
                            placeholder="Type your prompt here..."
                            rows={1}
                            bind:value={pendingPrompt}
                        />
                    </div>
                    <Button
                        on:click={() => getPromptResult()}>
                        <div class="generate-button-content">
                            Generate
                            <div class="generate-icon"><IconMagic /></div>
                        </div>
                    </Button>
                </div>
                <div class="disclaimer">
                    This chat is powered by OpenAI's GPT-3.5 model. It may produce inaccurate results.
                </div>
            </div>

        {/if}

    </div>

</div>


<style>

    .ai-chat {
        height: 100%;
        display: flex;
        flex-direction: column;
        min-height: 0;
    }

    .chat-display {
        flex: 1;
        overflow: hidden;
    }

    .loader-wrap {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
    }

    .chat-zone {
        padding: 20px 15px 15px;
        height: 75%;
        overflow: auto;
        display: flex;
        flex-direction: column;
    }

    .input-zone {
        padding: 20px 15px 15px;
        height: 25%;
    }
    
    .disclaimer {
        padding: 0 15px 15px;
        font-size: 12px;
    }

    .keyword-tip {
        padding: 5px;
        font-size: 12px;
    }

    .input-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 15px;
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

    .empty-chat-text {
        margin-top: 10px;
    }

    .empty-chat {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
    }

    .reset-button {
        align-self: flex-end;
        display: block;
    }

    .reset-button-content {
        display: flex;
        align-items: center;
        font-size: 12px;
    }

    .reset-button-text {
        margin-left: 5px;
    }

</style>
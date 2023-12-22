import React, { useEffect, useRef, useState } from 'react';
import { ArrowClockwise, Clipboard, FileEarmarkText, Magic } from 'react-bootstrap-icons';
import TextareaAutosize from "react-textarea-autosize";
import Prompts from './Prompts';
import Loader from "../../../../ReusableComponents/Loader";
import NoResults from "../../../../ReusableComponents/NoResults";
import { useBlogValues } from "../../../../logic-helpers/blog";
import { useActions, useValues } from "kea";
import gptLogic from "../../../../logic/gptLogic";
import { marked } from "marked";
import DOMPurify from 'dompurify';
import Button from "../../../../ReusableComponents/Button";
import { toast } from "react-toastify";
import { usePostValues } from "../../helpers";
import { appendHtml } from "../../ProseMirror/helpers";
import UpgradeRequired from "../../../../ReusableComponents/UpgradeRequired";

export default function Ai({id} : {id: number}) {

    const inputRef = useRef<HTMLTextAreaElement>(null);
    const [prompt, setPrompt] = useState('');

    function handleSelect(prompt: string) {
        inputRef.current?.focus();
        setPrompt(prompt);
    }

    const gptLogicInst = gptLogic({id});
    const { loadPromptsAjax, prompts, pendingPrompt } = useValues(gptLogicInst);
    const { 
        loadPrompts, 
        setPrompts, 
        setPendingPrompt , 
        resetChat,
        createPrompt 
    } = useActions(gptLogicInst);

    const chatDisplayRef = useRef<HTMLDivElement>(null);


    function handleGenerate() {
        const promptCopy = prompt;
        setPrompt('');
        createPrompt({
            prompt: promptCopy
        })

        setTimeout(() => {
            chatDisplayRef.current?.scrollTo({
                top: chatDisplayRef.current.scrollHeight,
            });
        }, 0);
    }

    function handleKeyDown(e: React.KeyboardEvent<HTMLTextAreaElement>) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            handleGenerate();
        }
    }

    function handleResetChat() {
        resetChat();
    }

    useEffect(() => {

        if (loadPromptsAjax.status === null) {
            loadPrompts();
        }

    }, []);

    return  <UpgradeRequired
        minPlan="growth" 
        trialAllowed={true}
        text={
            <div>
                AI chat is only available on the <b>Growth plan</b> and above. Upgrade now to use GPT to help you write amazing posts!
            </div>
        }
    >
    
        <div className="ai-chat">

            <div className="chat-display" ref={chatDisplayRef}>

                {
                    loadPromptsAjax.status === 'loading' ?
                        <div className="loader-wrap"><Loader /></div> :
                        !prompts.length && !pendingPrompt ?
                        <div className="loader-wrap"><NoResults 
                            text="No chat history on this post yet."
                            imageWidth={100}
                        /></div> :
                        <div className="chat">
                            {
                                prompts.map((prompt, i) => {

                                    return <PromptResponse
                                        key={prompt.id}
                                        id={id}
                                        prompt={prompt.prompt}
                                        response={prompt.gpt_response}
                                    />

                                })
                            }
                            {
                                pendingPrompt &&
                                <PromptResponse
                                    id={id}
                                    prompt={pendingPrompt.prompt}
                                    response={null}
                                    hasError={Boolean(pendingPrompt.error)}
                                />
                            }
                        </div>
                }

                {
                    prompts && prompts.length > 0 &&
                    <div className="clear-chat-wrap">
                        <button
                            className="button small light"
                            onClick={handleResetChat}
                        >
                            <ArrowClockwise /> Reset Chat
                        </button>
                    </div>
                }

            </div>
            <div className="chat-box">

                <Prompts
                    id={id}
                    onSelect={handleSelect}
                />

                <div className="inner">
                    <div className="textbox-wrap">
                        <TextareaAutosize
                            ref={inputRef}
                            placeholder="Type your prompt here..."
                            className="input"
                            autoFocus={true}
                            rows={1}
                            value={prompt}
                            onChange={e => setPrompt(e.target.value)}
                            maxLength={1000}
                            onKeyDown={handleKeyDown}
                        />
                    </div>
                    <div className="send-wrap">
                        <button 
                            className="button medium"
                            onClick={handleGenerate}
                            disabled={Boolean(pendingPrompt && !pendingPrompt.error)}
                        >
                            Generate <Magic />
                        </button>
                    </div>
                </div>
            </div>
            <div className="disclaimer">
                This chat is powered by OpenAI's GPT-3.5 model. It may produce inaccurate results.
            </div>
        </div>


    </UpgradeRequired>

}

interface PromptResponseProps {
    id: number,
    prompt: string,
    response: string | null,
    hasError?: boolean
}

function PromptResponse({id, prompt, response, hasError = false} : PromptResponseProps) {

    const { blog } = useBlogValues();
    const { editorState } = usePostValues(id);

    blog.icon_url || blog.logo_url;

    function MessageUser({url} : {url: string | null}) {

        return <div className={"message-user"}>
            {
                url ?
                    <img src={url} /> : 
                    <span className="placeholder">
                        { blog.subdomain[0].toUpperCase() }
                    </span>
            }
        </div>
    }

    const responseHtml = getResponseHtml(response);

    const messageHtmlRef = useRef<HTMLDivElement>(null);

    function handleCopy() {
        copyHtmlToClipboard(messageHtmlRef.current!);
        toast.success('Copied to clipboard!');
        return;
    }

    function handleAddToEditor() {

        if (editorState.editorView) {
            appendHtml(editorState.editorView, responseHtml);
        }

    }

    return <div className="single-prompt">

        <div className="message-wrap">
            <MessageUser url={blog.icon_url || blog.logo_url} />
            <div className="message message-input">{prompt}</div>
        </div>

        <div className="message-wrap ai">
            <MessageUser url={"/img/logo.png"} />
            <div className="message">
                {
                    response === null ?
                        (
                            hasError ?
                                <div className="error">
                                    Something went wrong. Please try again.
                                </div> :
                                <Loader inline={true} size="small" />
                        ) :
                        <div>
                            <div
                                className="message-html"
                                dangerouslySetInnerHTML={{__html: responseHtml}}
                                ref={messageHtmlRef}
                            />
                            <div className="buttons-wrap">
                                <Button size="small" type="light" onClick={handleCopy}>
                                    <Clipboard /><span>Copy</span>
                                </Button>
                                <Button size="small" type="light" onClick={handleAddToEditor}>
                                    <FileEarmarkText /><span>Add to Editor</span>
                                </Button>
                            </div>
                        </div>
                }
            </div>
        </div>

    </div>

}


function getResponseHtml(response: string | null) {
    const renderer = new marked.Renderer();
    renderer.link = function(href, title, text) {
        return `<a href="${href}" target="_blank" rel="noopener noreferrer">${text}</a>`;
    }
    return DOMPurify.sanitize(marked(response || '', {renderer}));
}

function copyHtmlToClipboard(el: HTMLElement) {

    
    window.getSelection()?.removeAllRanges();
    let range = document.createRange();
    range.selectNode(el);
    window.getSelection()?.addRange(range);
    document.execCommand('copy');
    window.getSelection()?.removeAllRanges();

}

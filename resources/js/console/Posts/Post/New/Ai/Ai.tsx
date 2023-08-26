import React, { useRef, useState } from 'react';
import { Magic } from 'react-bootstrap-icons';
import TextareaAutosize from "react-textarea-autosize";
import Prompts from './Prompts';

export default function Ai({id} : {id: number}) {

    const inputRef = useRef<HTMLTextAreaElement>(null);
    const [prompt, setPrompt] = useState('');

    function handleSelect(prompt: string) {
        inputRef.current?.focus();
        setPrompt(prompt);
    }

    return <div className="ai-chat">

        <div className="chat-display">
            Your Chat
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
                        maxLength={1500}
                    />
                </div>
                <div className="send-wrap">
                    <button 
                        className="button medium"
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

}
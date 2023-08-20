import React from 'react';
import { SendFill } from 'react-bootstrap-icons';
import TextareaAutosize from "react-textarea-autosize";
import Prompts from './Prompts';

export default function Ai({id} : {id: number}) {

    return <div className="ai-chat">

        <Prompts />

        <div className="chat-display">
            Your Chat
        </div>
        <div className="chat-box">
            <div className="textbox-wrap">
                <TextareaAutosize
                    placeholder="Type your prompt here..."
                    className="input"
                    autoFocus={true}
                    rows={1}
                />
            </div>
            <div className="send-wrap">
                <button 
                    className="button medium"
                >
                    Send <SendFill />
                </button>
            </div>
        </div>
        <div className="disclaimer">
            This chat is powered by OpenAI's GPT-3.5 model. It may produce inaccurate results.
        </div>
    </div>

}
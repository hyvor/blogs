import React, { useState } from "react";
import Button from "../../../../ReusableComponents/Button";
import { CaretLeft } from "react-bootstrap-icons";
import { usePostActions, usePostValues } from "../../helpers";
import Tooltip from "../../../../ReusableComponents/Tooltip";


interface Options {
    title: string,
    primaryKeyword: string | null,
    secondaryKeywords: string[] | null,
}


interface Prompt {
    name: string,
    description: string,
    prompt: (options: Options) => string,
    tip?: false
}

function addKeywordPrompt(p:string, options: Options) {
    if (options.primaryKeyword) {
        p += `\n\nMy primary keyword is ${options.primaryKeyword}.`;
        if (options.secondaryKeywords?.length) {
            p += ` Secondary keywords are ${options.secondaryKeywords.join(', ')}.`;
        }
    }
    return p;
}

const prompts : Prompt[]  = [

    {
        name: 'Blog Outline',
        description: 'Generate an outline for a blog post',
        prompt: (options) => addKeywordPrompt(`Write a blog outline on ${options.title}`, options)
    },

    {
        name: 'Blog Post',
        description: 'Generate a blog post',
        prompt: (options) => addKeywordPrompt(`Write a blog post about ${options.title}`, options)
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
        prompt: (options) => addKeywordPrompt(`Write an SEO content brief for ${options.title}`, options)
    },

    {
        name: 'SEO Keyword Ideas',
        description: 'Generate a list of SEO keyword ideas',
        prompt: (options) => `Write a list of SEO keyword ideas for ${options.title}`,
        tip: false
    }
    

];


export default function Prompts({ onSelect, id } : { id: number, onSelect: (prompt: string) => void }) {

    const [selectedPromptName, setSelectedPromptName] = useState<string | null>(null);

    const selectedPrompt = selectedPromptName ? 
        prompts.find(prompt => prompt.name === selectedPromptName) : 
        null;

    const { currentVariant } = usePostValues(id);
    const { changeEditorState } = usePostActions(id);

    const options = {
        title: currentVariant?.title || '[title]',
        primaryKeyword: currentVariant?.seo_primary_keyword || null,
        secondaryKeywords: currentVariant?.seo_secondary_keywords || null,
    }

    return <div className="chat-prompts">

        <div className="all-prompts">

            <div className="prompt-buttons">
                
                {
                    prompts.map(prompt => {

                        return <Tooltip
                            key={prompt.name}
                            tooltip={prompt.description}
                        >
                            <Button
                                size="small"
                                type="light"
                                onClick={() => onSelect(prompt.prompt(options))}
                            >
                                { prompt.name }
                            </Button>
                        </Tooltip>

                    })
                }

            </div>

            {
                !currentVariant?.seo_primary_keyword &&
                <div className="tip">
                    <b>💡 Tip</b>: <a 
                        className="link"
                        onClick={() => changeEditorState('settingsSection', 'seo')}
                    >Add SEO keywords</a> for better prompts.
                </div>
            }

        </div>

    </div>

}

function Prompt(
    {id, prompt, onSelect, onBack} : 
    {id: number, prompt: Prompt, onSelect: (prompt: string) => void, onBack: Function}
) {

    const { currentVariant } = usePostValues(id);
    const { changeEditorState } = usePostActions(id);


    const options = {
        title: currentVariant?.title || '[title]',
        primaryKeyword: currentVariant?.seo_primary_keyword || null,
        secondaryKeywords: currentVariant?.seo_secondary_keywords || null,
    }

    const p = prompt.prompt(options);

    return <div className="single-prompt">

        <div className="back">
            <Button type="light" size="small" onClick={() => onBack()}>
                <CaretLeft /> Back to Prompts
            </Button>
        </div>

        <div className="title">
            { prompt.name }
        </div>

        <div className="description">
            { prompt.description }
        </div>

        <div className="prompt-display">
            <div className="prompt">
                <div>
                    <b>Prompt</b>:
                </div>
                { p }
            </div>
        </div>


        {
            !currentVariant?.seo_primary_keyword && prompt.tip === undefined &&
            <div className="tip">
                <b>💡 Tip</b>: <a 
                    className="link"
                    onClick={() => changeEditorState('settingsSection', 'seo')}
                >Add SEO keywords</a> to improve the prompt.
            </div>
        }

        <div className="select">
            <Button 
                onClick={() => {
                    onSelect(p)
                    onBack();
                }}
            >Use Prompt</Button>
        </div>

    </div>;

}
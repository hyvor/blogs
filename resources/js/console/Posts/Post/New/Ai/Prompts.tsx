import React from "react";


interface Prompt {
    name: string,
    description?: string,
    options?: Record<string, 'text' | 'boolean'>,
    prompt: (options: Record<string, string | boolean>) => string,
}

const prompts : Prompt[]  = [

    {
        name: 'Blog Post',
        description: 'This prompt will generate a blog post.',
        prompt: () => 'Write a blog post about [topic]'
    },

    {
        name: 'Article',
        description: 'This prompt will generate an article, which is more formal than a blog post.',
        options: {

        },
        prompt: (options) => `Write a blog post about [post_title].

        Optimize it for SEO. My primary keyword is [primary keyword]. Secondary keywords are []...
        
        Include real-life examples and case studies to support my points. `
    },

    {
        name: 'Blog Outline',
        description: 'This prompt will generate an outline for a blog post.',
        prompt: () => 'Write a blog outline on [topic]'
    },

    {
        name: 'FAQ generator',
        description: 'This prompt will generate a list of questions and answers.',
        prompt: () => 'Write a list of questions and answers about [topic]'
    },

    {
        name: 'SEO Brief',
        description: 'This prompt will generate an SEO brief.',
        prompt: () => 'Write an SEO brief for [topic]'
    },

    {
        name: 'SEO Keyword Ideas',
        description: 'This prompt will generate a list of SEO keyword ideas.',
        prompt: () => 'Write a list of SEO keyword ideas for [topic]'
    }
    

]

export default function Prompts() {

    const Tip = () => null;

    return <div className="chat-prompts">

        <div className="title">
            Prompts
        </div>

    </div>

}
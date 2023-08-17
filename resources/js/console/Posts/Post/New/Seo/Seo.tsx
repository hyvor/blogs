import React, { useState } from 'react';
import { usePostValues } from '../../helpers';
import { SeoAnalyzer, TestResult } from './seo-analyzer';
import { useValues } from 'kea';
import userBlogsLogic from '../../../../logic/userBlogsLogic';
import getSubdomain from '../../../../logic-helpers/subdomain';

export default function Seo({id}: {id: number}) {

    const [settingsType, setSettingsType] = useState<'seo' | 'links'>('seo');

    return <div className="toolbar-content">

        <div className="post-settings-wrap post-analysis" data-testid="post-seo">

            {/* <div className="setting-select">
                <button 
                    onClick={() => setSettingsType('seo')} 
                    className={settingsType === 'seo' ? 'active' : ''}
                >SEO</button>
                <button 
                    onClick={() => setSettingsType('links')} 
                    className={settingsType === 'links' ? 'active' : ''}
                >Links</button>
            </div> */}

            {settingsType === 'seo' && <SeoAnalysis id={id} />}

        </div>

    </div>

}


function SeoAnalysis({id}: {id: number}) {

    const { findBlogBySubdomain } = useValues(userBlogsLogic());
    const { post, postOriginal, currentLanguage, currentVariant, diff } = usePostValues(id);

    const userBlog = findBlogBySubdomain(getSubdomain())
    const blogUrl = userBlog.blog.base_url;

    const [primaryKeyword, setPrimaryKeyword] = useState('blogging');
    const [secondaryKeywords, setSecondaryKeywords] = useState<string[]>([]);

    const analyzer = new SeoAnalyzer({
        primaryKeyword,
        secondaryKeywords,
        title: currentVariant.title || '',
        slug: currentVariant.slug || '',
        description: currentVariant.description || '',
        content: currentVariant.content_unsaved || currentVariant.content,
        blogUrl,
    });
    const results = analyzer.analyze();

    const svg = (widthPerc: number, gradient: boolean = false) => {
        const radius = 65;
        const dashArray = Math.PI * radius * widthPerc / 100;

        return <svg width="200" height="120">
            <circle 
                cx="100" 
                cy="100" 
                r={radius} 
                strokeLinecap="round"
                strokeDashoffset={-1 * Math.PI * radius}
                strokeDasharray={`${dashArray} 100000`}
                stroke={gradient ? 'url(#score-gradient)' : '#e5e5e5'}
            ></circle>
            {
                gradient &&
                <defs>
                    <linearGradient id="score-gradient">
                        <stop offset="0%" stopColor="#e13a3a" />
                        <stop offset="25%" stopColor="#ffb93a" />
                        <stop offset="100%" stopColor="#429e46" />
                    </linearGradient>
                </defs>
            }
        </svg>;
    }

    return <div className="seo-analysis">

        <div className="seo-top">

            <div className="score-wrap">
                <div className="score">
                    <div className="score-bar">
                        <div className="placeholder">{svg(100)}</div>
                        <div className="score-circle">{svg(results.average, true)}</div>
                    </div>
                    <div className="score-value">
                        <div className="score-name">Score</div>
                        <div className="score-number">
                            {Math.round(results.average)}%
                        </div>
                    </div>
                </div>
            </div>

            <div className="keywords">

                <div className="keyword-input">
                    <div className="keyword-title">Primary Keyword</div>
                    <input
                        type="text"
                        className="input medium"
                        value={primaryKeyword}
                        onChange={e => setPrimaryKeyword(e.target.value)}
                    />
                </div>

                <div className="keyword-input">
                    <div className="keyword-title">Secondary Keywords</div>
                    {
                        secondaryKeywords.map((keyword, i) => {

                            function setKeyword(keyword: string) {
                                const newKeywords = [...secondaryKeywords];
                                newKeywords[i] = keyword;
                                setSecondaryKeywords(newKeywords);
                            }

                            return <input
                                type="text"
                                className="input medium"
                                value={secondaryKeywords[i]}
                                onChange={e => setKeyword(e.target.value)}
                            />
                        })
                    }
                    
                </div>

            </div>

        </div>

        <div className="results">
            {
                results.tests.map(test => <SingleTest key={test.name} result={test} />)
            }
        </div>

    </div>

}

function ScoreTag({score, ignore = false}: {score: number, ignore: boolean}) {

    const color = score < 50 ? 'red' : score < 80 ? 'orange' : 'green';

    return <span 
        className={"score-tag " + (ignore ? 'ignore' : color)}
    >{ignore ? "?" : score}</span>
}

function SingleTest({result}: {result: TestResult}) {

    return <div className="single-test">
        <div className="score-tag-wrap">
            <ScoreTag score={result.score} ignore={result.ignore} />
        </div>
        <div className="score-message">
            {result.message}
        </div>
    </div>

}

/* function MultipleTest({test}: {test: MultiTestResult}) {

    const average = test.results.reduce((acc, result) => acc + result.result.score, 0) / test.results.length;

    return <div className="multi-test">
        <div className="multi-test-top">
            <ScoreTag score={average} />
        </div>
        <div className="multi-test-inner">
            {
                test.results.map(result => 
                    <div>
                        <SingleTest result={result.result} />
                    </div>
                )
            }
        </div>
    </div>

} */
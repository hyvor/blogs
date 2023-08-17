import React, { useState } from 'react';
import { usePostValues } from '../../helpers';
import { SeoAnalyzer } from './seo-analyzer';

export default function Seo({id}: {id: number}) {

    const [settingsType, setSettingsType] = useState<'analysis' | 'links'>('analysis');

    return <div className="toolbar-content">

        <div className="post-settings-wrap" data-testid="post-seo">

            <div className="setting-select">
                <button 
                    onClick={() => setSettingsType('analysis')} 
                    className={settingsType === 'analysis' ? 'active' : ''}
                >Analysis</button>
                <button 
                    onClick={() => setSettingsType('links')} 
                    className={settingsType === 'links' ? 'active' : ''}
                >Links</button>
            </div>

            {settingsType === 'analysis' && <Analysis id={id} />}

        </div>

    </div>

}


function Analysis({id}: {id: number}) {

    const { post, postOriginal, currentLanguage, currentVariant, diff } = usePostValues(id);

    const analyzer = new SeoAnalyzer({
        primaryKeyword: 'wordpress alternatives',
        secondaryKeywords: [],
        title: currentVariant.title || '',
        slug: currentVariant.slug || '',
        description: '',
        content: currentVariant.content_unsaved || currentVariant.content,
        blogUrl: '',
    });
    const results = analyzer.analyze();

    return <div>

        Score: {results.average}

        {
            results.tests.map(test => {

                return <div key={test.testName}>
                    <div>{test.score}</div>
                    <div>{test.message}</div>
                </div>

            })
        }

    </div>

}
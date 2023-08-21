import React, { useEffect, useRef, useState } from 'react';
import { usePostActions, usePostValues } from '../../helpers';
import { Output, TestResult } from './seo-analyzer';
import { Check, X } from 'react-bootstrap-icons';
import { toast } from 'react-toastify';

export default function Seo({id}: {id: number}) {

    return <div className="toolbar-content">
        <div className="post-settings-wrap" data-testid="post-seo">
            <SeoAnalysis id={id} />
        </div>
    </div>

}


function SeoAnalysis({id}: {id: number}) {

    const { currentVariant, currentVariantSeoResults } = usePostValues(id);
    const { saveCurrentVariantDiff, updateCurrentPostVariantValue }  = usePostActions(id);
   
    const primaryKeyword = currentVariant.seo_primary_keyword;
    const secondaryKeywords = currentVariant.seo_secondary_keywords;
    const results : Output = currentVariantSeoResults;

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
                        <stop offset="0%" stopColor="#db7474" />
                        <stop offset="25%" stopColor="#f1c40f" />
                        <stop offset="100%" stopColor="#5d995d" />
                    </linearGradient>
                </defs>
            }
        </svg>;
    }

    function updateSecondaryKeywords(secondaryKeywords: string[]) {
        updateCurrentPostVariantValue('seo_secondary_keywords', secondaryKeywords);
        saveCurrentVariantDiff({
            diff: {
                seo_secondary_keywords: secondaryKeywords
            }
        });
    }
    function updatePrimaryKeyword(primaryKeyword: string|null) {
        updateCurrentPostVariantValue('seo_primary_keyword', primaryKeyword);
        saveCurrentVariantDiff({
            diff: {
                seo_primary_keyword: primaryKeyword,
            }
        });
    }

    function handleAddSecondaryKeyword(keyword: string) {

        if (keyword.toLowerCase() === primaryKeyword?.toLowerCase()) {
            toast.error("Keyword already added as primary keyword");
            return false;
        }

        if (secondaryKeywords.length >= 10) {
            toast.error("Maximum 10 keywords allowed");
            return false;
        }

        let isDuplicate = false;
        secondaryKeywords.forEach(secondaryKeyword => {
            if (secondaryKeyword.toLowerCase() === keyword.toLowerCase()) {
                toast.error("Keyword already added");
                isDuplicate = true;
            }
        })
        if (isDuplicate) {
            return false;
        }

        updateSecondaryKeywords([...secondaryKeywords, keyword])

        return true;
    }

    function handleUpdateSecondaryKeyword(oldKeyword: string, newKeyword: string) {

        if (newKeyword.toLowerCase() === primaryKeyword?.toLowerCase()) {
            toast.error("Keyword already added as primary keyword");
            return false;
        }

        let isDuplicate = false;
        secondaryKeywords.forEach(secondaryKeyword => {
            if (secondaryKeyword === oldKeyword) {
                return;
            }
            if (secondaryKeyword.toLowerCase() === newKeyword.toLowerCase()) {
                toast.error("Keyword already added");
                isDuplicate = true;
            }
        })
        if (isDuplicate) {
            return false;
        }

        const newKeywords = [...secondaryKeywords];
        newKeywords[newKeywords.indexOf(oldKeyword)] = newKeyword;
        updateSecondaryKeywords(newKeywords);

        return true;

    }

    function handleAddPrimaryKeyword(keyword: string) {

        let isDuplicate = false;
        secondaryKeywords.forEach(secondaryKeyword => {
            if (secondaryKeyword.toLowerCase() === keyword.toLowerCase()) {
                toast.error("Keyword already added");
                isDuplicate = true;
            }
        })
        if (isDuplicate) {
            return false;
        }

        updatePrimaryKeyword(keyword);

        return true;
    }

    function handlePrimaryKeywordUpdate(keyword: string) {

        let isDuplicate = false;
        secondaryKeywords.forEach(secondaryKeyword => {
            if (secondaryKeyword.toLowerCase() === keyword.toLowerCase()) {
                toast.error("Keyword already added");
                isDuplicate = true;
            }
        })
        if (isDuplicate) {
            return false;
        }

        updatePrimaryKeyword(keyword);

        return true;
    
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

                    {
                        primaryKeyword === null ?
                        <KeywordAdder 
                            onAdd={handleAddPrimaryKeyword}
                        /> :
                        <KeywordDisplay 
                            keyword={primaryKeyword}
                            onUpdate={handlePrimaryKeywordUpdate}
                            onRemove={() => updatePrimaryKeyword(null)}
                        />
                    }
                </div>

                <div className="keyword-input">
                    <div className="keyword-title">Secondary Keywords</div>
                    
                    {
                        secondaryKeywords.map((keyword, i) => {
                            return <KeywordDisplay 
                                key={keyword}
                                keyword={keyword}
                                onUpdate={newKeyword => {
                                    return handleUpdateSecondaryKeyword(keyword, newKeyword);
                                }}
                                onRemove={() => {
                                    const newKeywords = [...secondaryKeywords];
                                    newKeywords.splice(i, 1);
                                    updateSecondaryKeywords(newKeywords);
                                }
                            } />
                        })
                    }

                    <div className="secondary-keyword-adder-wrap">
                        <KeywordAdder 
                            onAdd={handleAddSecondaryKeyword}
                        />
                    </div>
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

function KeywordAdder(
    { onAdd, startKeyword, onClose } :
    {
        startKeyword?: string,
        onAdd: (keyword: string) => boolean,
        onClose?: () => void
    }
) {
    
    const [isAdding, setIsAdding] = useState(startKeyword ? true : false);
    
    const [keyword, setKeyword] = useState(startKeyword || '');
    const keywordRef = useRef<string>(keyword);

    function handleConfirm() {
        const currentKeyword = keywordRef.current.trim(); 
        if (currentKeyword === '') {
            toast.error("Keyword can't be empty");
            return;
        }

        const success = onAdd(currentKeyword);

        if (success) {
            setIsAdding(false);
            setKeyword('');
        }
    }

    function handleClose() {
        setIsAdding(false);
        setKeyword('');
        onClose?.();
    }

    useEffect(() => {

        function handleKeyDown(e: KeyboardEvent) {
            if (e.key === 'Enter') {
                handleConfirm();
            }
            if (e.key === 'Escape') {
                handleClose();
            }
        }

        if (isAdding) {
            document.addEventListener('keydown', handleKeyDown);
        } else {
            document.removeEventListener('keydown', handleKeyDown);
        }

        return () => {
            document.removeEventListener('keydown', handleKeyDown);
        }

    }, [isAdding]);

    useEffect(() => {
        keywordRef.current = keyword;
    }, [keyword])

    return <div className="keyword-adder">
        {
            isAdding ?
                <div className="adding">
                    <input 
                        type="text" 
                        className="input medium" 
                        autoFocus={true}
                        value={keyword}
                        onChange={e => setKeyword(e.target.value)}
                    />
                    <div className="confirm-buttons">
                        <a onClick={handleConfirm} className={keyword.trim() === '' ? 'inactive' : ''}><Check /></a>
                        <a onClick={handleClose}><X /></a>
                    </div>
                </div> :
                <button
                    className="add-button button mini light" 
                    onClick={() => setIsAdding(true)}
                >+ Add</button>
        }
    </div>   

}

function KeywordDisplay(
    {keyword, onUpdate, onRemove}: 
    {
        keyword: string, 
        onUpdate: (keyword: string) => boolean,
        onRemove: () => void
    }) {

    const [isEditing, setIsEditing] = useState(false);

    return isEditing ? 
        <KeywordAdder
            startKeyword={keyword}
            onAdd={keyword => {
                const success = onUpdate(keyword);
                if (success) {
                    setIsEditing(false);
                }
                return success;
            }}
            onClose={() => setIsEditing(false)}
        /> :
        <div className="keyword-display">
            <span className="keyword" onClick={() => setIsEditing(true)}>{keyword}</span>
            <a className="remove-button" onClick={onRemove}><X /></a>
        </div>

}

export function SeoScoreTag(
    {score, ignore = false, percentage = false}: 
    {score: number, ignore?: boolean, percentage?: boolean}
) {
    score = Math.round(score);
    const color = score < 50 ? 'red' : score < 80 ? 'orange' : 'green';

    return <span 
        className={"global-seo-score-tag " + (ignore ? 'ignore' : color)}
    >{ignore ? "?" : score}{percentage && "%"}</span>
}

function SingleTest({result}: {result: TestResult}) {

    return <div className="single-test">
        <div className="score-tag-wrap">
            <SeoScoreTag score={result.score} ignore={result.ignore} />
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
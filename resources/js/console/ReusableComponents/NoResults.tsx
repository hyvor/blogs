import React from 'react';

/**
 * There are lot of instances where you want to show no-results section
 */
interface NoResultsProps {
    text: string,
    padding: number,
    imageWidth: number
}

export default function NoResults({ text, padding = 100, imageWidth = 150 }: NoResultsProps) {

    return <div className="global-no-results" style={{padding}}>
        <img className="no-results-img" style={{width: imageWidth}} src="/img/console/noresults.svg" />
        <div>{ text || "No results found" }</div>
    </div>

}
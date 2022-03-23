
/**
 * There are lot of instances where you want to show no-results section
 */

export default function NoResults({ text, padding = 100, imageWidth = null }) {

    return <div className="global-no-results" style={{padding}}>
        <img className="no-results-img" style={{width: imageWidth}} src="/img/console/noresults.svg" />
        <div>{ text || "No results found" }</div>
    </div>

}
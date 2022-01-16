
/**
 * There are lot of instances where you want to show no-results section
 */

export default function NoResults({ text, style }) {

    return <div className="global-no-results" style={style}>
        <img src="/img/console/noresults.svg" />
        <div>{ text || "No results found" }</div>
        
    </div>

}
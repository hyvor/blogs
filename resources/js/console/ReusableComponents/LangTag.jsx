
export default function LangTag( {isActive, onClick, code, icon} ) {

    return <span
        className={"global-lang-tag" + (isActive ? " active" : "")}
        onClick={onClick}
    >
        <span className="code">{code}</span>
        <span className="status-icon">
            { icon }
        </span>
    </span>

}
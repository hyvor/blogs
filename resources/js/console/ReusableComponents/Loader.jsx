

export default function Loader({width, height, padding = 0}) {

    const logo = <svg xmlns="http://www.w3.org/2000/svg" 
        width={width || 50} 
        height= {height || 50}
        viewBox="0 0 132.292 132.292"><g strokeWidth="7.158" stroke="#000" fillRule="evenodd"><rect width="77.87" height="123.577" x="26.535" y="5.026" ry="38.935" transform="rotate(359.418)" fill="#f1e8e8"/><path d="M105.079 65.75l.232 22.852c.219 21.569-16.969 39.11-38.538 39.329s-39.11-16.969-39.329-38.538l-.232-22.852z" fill="#896c6b"/></g><path d="M59.3 31.89c0-2.555-3.289-4.625-7.346-4.625s-7.346 2.071-7.346 4.625m43.076 0c0-2.555-3.289-4.625-7.346-4.625s-7.346 2.071-7.346 4.625m.271 15.859c0 2.555-3.289 4.625-7.346 4.625s-7.346-2.071-7.346-4.625" stroke="#000" paintOrder="stroke fill markers" fill="none" strokeLinecap="round" strokeLinejoin="round" strokeWidth="6.65"/></svg>;


    return <div 
        className="global-loader"
        style={{ padding }}>
        { logo }
    </div>;

}

export function FullPageLoader({text}) {

    return <div className="global-full-page-loader">
        <Loader />
        <div className="text">{text}</div>
    </div>

}
import React from "react";
import NoResults from "../../ReusableComponents/NoResults";

export default function ImportHistory() {

    return <div>
        <NoResults 
            text="No previous imports"
            imageWidth={80}
            padding={0}
        />
    </div>

}
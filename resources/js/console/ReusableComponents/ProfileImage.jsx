import React from 'react'

export default function ProfileImage() {

    return <div className="global-avatar">
        <div class="avatar">
            {/* <input type="file" id="actual-btn" hidden/> */}
            <img src="https://picsum.photos/200/200" alt="Avatar" className="avatar-center"/>
        </div>

        <input type="file" id="actual-btn" hidden/>
        <label for="actual-btn" className="upload-lable">Choose File</label>

    </div>

}
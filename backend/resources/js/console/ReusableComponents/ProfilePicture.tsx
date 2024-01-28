import React from 'react'
import { User } from '../types'

export default function ProfilePicture({ user, size = 30 } : { user: User, size?: number }) {

    function getInitials() {
        const name = user.variants[0]?.name || 'Anonymous';
        return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
    }

    return <span className="global-profile-picture" style={{width: size, height: size}}>
        { 
            user.picture_url ? 
                <img src={user.picture_url} /> :
                <span className="initials">{getInitials()}</span>
        }
    </span>
}
import consoleApi from "../../../lib/consoleApi";
import type { User, UserRole } from "../../../lib/types";

interface GetUsersData {
    limit?: number,
    offset?: number
}

export function getUsers(data: GetUsersData = {}) {
    return consoleApi.get<User[]>({
        endpoint: '/users',
        data
    });
}

interface SearchUsersData {
    search: string;
}

export function searchUsers(data: SearchUsersData) {
    return consoleApi.get<User[]>({
        endpoint: '/users/search',
        data
    });
}

export function deleteUser(id: number) {
    return consoleApi.delete({
        endpoint: `/user/${id}`
    });
}

export function createHyvorUser(usernameOrEmail: string, role: UserRole) {
    return consoleApi.post<User>({
        endpoint: '/user',
        data: { 
            username_or_email: usernameOrEmail,
            role: role
        }
    });
}

export function createGuestUser(name: string) {
    return consoleApi.post<User>({
        endpoint: '/user/guest',
        data: { name }
    });
}


export function checkSlugAvailability(userId: number, slug: string, signal: AbortSignal) {
    return consoleApi.get<{available: boolean}>({
        endpoint: `/user/${userId}/slug-available`,
        data: {
            slug
        },
        signal
    })
}
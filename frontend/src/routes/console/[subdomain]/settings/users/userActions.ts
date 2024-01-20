import consoleApi from "../../../lib/consoleApi";
import type { User } from "../../../lib/types";

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
        endpoint: `/users/${id}`
    });
}
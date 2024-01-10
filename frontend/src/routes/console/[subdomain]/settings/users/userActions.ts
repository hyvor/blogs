import consoleApi from "../../../lib/consoleApi";
import type { User } from "../../../lib/types";

interface GetUsersData {
    offset?: number;
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
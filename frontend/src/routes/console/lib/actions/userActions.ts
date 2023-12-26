import consoleApi, { type ConsoleApiOptions } from "../consoleApi";
import type { User } from "../types";



export function getUsers() {
    return consoleApi.get<User[]>({
        endpoint: 'users',
    });
}

export function searchUsers(search: string, opt: Partial<ConsoleApiOptions> = {}) {
    return consoleApi.get<User[]>({
        endpoint: 'users/search',
        data: {
            search,
        },
        ...opt,
    });
}
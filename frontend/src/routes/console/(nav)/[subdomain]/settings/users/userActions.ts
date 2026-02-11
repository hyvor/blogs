import consoleApi from "../../../../lib/consoleApi";
import type { User, UserRole, UserVariant } from "../../../../lib/types";

interface GetUsersData {
  limit?: number;
  offset?: number;
}

export function getUsers(data: GetUsersData = {}) {
  return consoleApi.get<User[]>({
    endpoint: "/users",
    data,
  });
}

interface SearchUsersData {
  search: string;
}

export function searchUsers(data: SearchUsersData) {
  return consoleApi.get<User[]>({
    endpoint: "/users/search",
    data,
  });
}

export function deleteUser(id: number) {
  return consoleApi.delete({
    endpoint: `/user/${id}`,
  });
}

export function createHyvorUser(hyvorUserId: number, role: UserRole) {
  return consoleApi.post<User>({
    endpoint: "/user",
    data: {
      hyvor_user_id: hyvorUserId,
      role: role,
    },
  });
}

export function createGuestUser(name: string) {
  return consoleApi.post<User>({
    endpoint: "/user/guest",
    data: { name },
  });
}

export function checkSlugAvailability(
  userId: number,
  slug: string,
  signal: AbortSignal,
) {
  return consoleApi.get<{ available: boolean }>({
    endpoint: `/user/${userId}/slug-available`,
    data: {
      slug,
    },
    signal,
  });
}

export function updateUserVariant(
  userId: number,
  languageId: number,
  variant: Partial<UserVariant>,
) {
  return consoleApi.patch<User>({
    endpoint: `/user/${userId}/variant`,
    data: {
      language_id: languageId,
      ...variant,
    },
  });
}

export function updateUser(id: number, user: Partial<User>) {
  return consoleApi.patch<User>({
    endpoint: `/user/${id}`,
    data: user,
  });
}

export function createUserVariant(userId: number, languageId: number) {
  return consoleApi.post<User>({
    endpoint: `/user/${userId}/variant`,
    data: {
      language_id: languageId,
    },
  });
}

export function resendInvitation(userId: number) {
  return consoleApi.post<User>({
    endpoint: `/user/${userId}/resend-invite`,
  });
}

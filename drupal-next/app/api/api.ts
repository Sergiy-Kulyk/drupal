import { getCsrfToken } from "../../lib/csrf"

// lib/api.ts
export async function authenticatedFetch(
  url: string,
  options: RequestInit = {}
) {
  const csrfToken = await getCsrfToken()

  const headers = {
    "Content-Type": "application/json",
    "X-CSRF-Token": csrfToken,
    ...options.headers,
  }

  return fetch(url, {
    ...options,
    credentials: "include",
    headers,
  })
}

import { NextDrupal } from "next-drupal"
let csrfTokenCache: { token: string; timestamp: number } | null = null
const TOKEN_CACHE_DURATION = 1000 * 60 * 30 // 30 minutes

export async function getCsrfToken() {
  // Check if we have a valid cached token
  if (
    csrfTokenCache &&
    Date.now() - csrfTokenCache.timestamp < TOKEN_CACHE_DURATION
  ) {
    return csrfTokenCache.token
  }

  // Fetch new token
  const response = await fetch(
    `${process.env.NEXT_PUBLIC_DRUPAL_BASE_URL}/session/token`,
    {
      credentials: "include",
    }
  )
  const token = await response.text()

  // Cache the token
  csrfTokenCache = {
    token,
    timestamp: Date.now(),
  }

  return token
}

// Create a wrapper for drupal client that includes CSRF token
export async function getAuthenticatedDrupal() {
  const csrfToken = await getCsrfToken()

  return new NextDrupal(process.env.NEXT_PUBLIC_DRUPAL_BASE_URL as string, {
    withAuth: true,
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-Token": csrfToken,
    },
    fetchOptions: {
      credentials: "include",
    },
  })
}

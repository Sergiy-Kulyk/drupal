import { NextDrupal } from "next-drupal"

const baseUrl = process.env.NEXT_PUBLIC_DRUPAL_BASE_URL as string
const clientId = process.env.DRUPAL_CLIENT_ID as string
const clientSecret = process.env.DRUPAL_CLIENT_SECRET as string

export const drupal = new NextDrupal(baseUrl, {
  // Enable CSRF token authentication
  withAuth: true,
  // Add custom headers for CSRF token
  headers: {
    "Content-Type": "application/json",
  },
  // Add custom fetch options
  fetchOptions: {
    credentials: "include", // This is important for CSRF token to work
  },
})

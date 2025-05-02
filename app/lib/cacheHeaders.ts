import type { NextResponse } from "next/server"

/**
 * Sets appropriate cache headers for optimal performance
 * @param response The NextResponse object
 * @param maxAge Cache duration in seconds
 * @returns The response with cache headers
 */
export function setCacheHeaders(response: NextResponse, maxAge = 31536000): NextResponse {
  // Cache-Control header
  response.headers.set("Cache-Control", `public, max-age=${maxAge}, s-maxage=${maxAge}, stale-while-revalidate=86400`)
  return response
}

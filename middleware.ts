import { NextResponse } from "next/server"
import type { NextRequest } from "next/server"

// Simple in-memory rate limiting
// Note: This is per-instance and will reset on deployment
const RATE_LIMIT_WINDOW = 60 * 1000 // 1 minute
const MAX_REQUESTS_PER_WINDOW = 100 // 100 requests per minute per IP

// Use a Map instead of a plain object for better performance with many keys
const ipRequestCounts = new Map<string, { count: number; resetTime: number }>()

export function middleware(request: NextRequest) {
  const response = NextResponse.next()

  // Add security headers
  response.headers.set("X-Content-Type-Options", "nosniff")
  response.headers.set("X-Frame-Options", "DENY")
  response.headers.set("X-XSS-Protection", "1; mode=block")

  // Basic rate limiting
  const ip = request.ip || "unknown"
  const now = Date.now()

  // Initialize or reset counter if window has passed
  if (!ipRequestCounts.has(ip) || now > (ipRequestCounts.get(ip)?.resetTime || 0)) {
    ipRequestCounts.set(ip, {
      count: 1,
      resetTime: now + RATE_LIMIT_WINDOW,
    })
  } else {
    // Increment counter
    const current = ipRequestCounts.get(ip)!
    current.count++
    ipRequestCounts.set(ip, current)

    // Check if rate limit exceeded
    if (current.count > MAX_REQUESTS_PER_WINDOW) {
      return new NextResponse(
        JSON.stringify({
          error: "Too many requests",
          message: "Rate limit exceeded",
        }),
        {
          status: 429,
          headers: {
            "Content-Type": "application/json",
            "Retry-After": "60",
          },
        },
      )
    }
  }

  return response
}

export const config = {
  matcher: "/api/:path*",
}

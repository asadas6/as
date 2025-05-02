import type { NextResponse } from "next/server"
import { setCacheHeaders } from "./cacheHeaders"

export function setCorsHeaders(response: NextResponse, maxAge = 31536000): NextResponse {
  // Set CORS headers
  response.headers.set("Access-Control-Allow-Origin", "*")
  response.headers.set("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE, OPTIONS")
  response.headers.set("Access-Control-Allow-Headers", "Content-Type, Authorization")

  // Set cache headers
  return setCacheHeaders(response, maxAge)
}

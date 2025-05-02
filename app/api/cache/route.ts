import { NextResponse } from "next/server"
import { setCorsHeaders } from "@/app/lib/cors"

// In-memory cache for API responses
const apiCache: Record<string, any> = {}

export async function POST(request: Request) {
  try {
    const { action, key } = await request.json()

    if (action === "clear_all") {
      // Clear all cache entries
      Object.keys(apiCache).forEach((k) => {
        delete apiCache[k]
      })

      const response = NextResponse.json({
        status: 200,
        message: "All cache entries cleared successfully",
      })
      return setCorsHeaders(response)
    } else if (action === "clear_entry" && key) {
      // Clear a specific cache entry
      if (apiCache[key]) {
        delete apiCache[key]
        const response = NextResponse.json({
          status: 200,
          message: `Cache entry for ${key} cleared successfully`,
        })
        return setCorsHeaders(response)
      } else {
        const response = NextResponse.json(
          {
            status: 404,
            message: `Cache entry for ${key} not found`,
          },
          { status: 404 },
        )
        return setCorsHeaders(response)
      }
    }

    const errorResponse = NextResponse.json(
      {
        status: 400,
        error: "Invalid action. Use 'clear_all' or 'clear_entry' with a key parameter",
      },
      { status: 400 },
    )
    return setCorsHeaders(errorResponse)
  } catch (error) {
    const errorResponse = NextResponse.json(
      {
        status: 500,
        error: "Failed to process cache control request",
      },
      { status: 500 },
    )
    return setCorsHeaders(errorResponse)
  }
}

export async function OPTIONS() {
  return setCorsHeaders(new NextResponse(null, { status: 200 }))
}

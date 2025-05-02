import { NextResponse } from "next/server"
import { fetchDataWithCache, getCachedData } from "@/app/lib/fetchData"
import { setCorsHeaders } from "@/app/lib/cors"

// Disable Next.js revalidation
export const revalidate = false

export async function GET() {
  try {
    // Ensure data is fetched and cached
    await fetchDataWithCache()

    // Get the cached data
    const data = getCachedData()

    if (data) {
      const response = NextResponse.json(data)
      return setCorsHeaders(response)
    } else {
      // Fallback to fetching again if somehow the cache is empty
      const freshData = await fetchDataWithCache()
      const response = NextResponse.json(freshData)
      return setCorsHeaders(response)
    }
  } catch (error) {
    const errorResponse = NextResponse.json({ error: "Failed to fetch data" }, { status: 500 })
    return setCorsHeaders(errorResponse)
  }
}

export async function OPTIONS() {
  return setCorsHeaders(new NextResponse(null, { status: 200 }))
}

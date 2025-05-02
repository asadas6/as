import { NextResponse } from "next/server"
import { fetchDataWithCache } from "@/app/lib/fetchData"
import { setCorsHeaders } from "@/app/lib/cors"
import { processTitle } from "@/app/lib/titleProcessor"
import { getCachedData, generateCacheKey } from "@/app/lib/apiCache"

// Disable Next.js revalidation
export const revalidate = false

export async function GET(request: Request) {
  try {
    const { searchParams } = new URL(request.url)
    const fileCode = searchParams.get("file_code")

    // If no file_code is provided, return a helpful error message
    if (!fileCode) {
      const errorResponse = NextResponse.json(
        {
          error: "file_code is required",
          message: "Please provide a file_code parameter in your request",
          example: "/api/info?file_code=example_code",
        },
        { status: 400 },
      )
      return setCorsHeaders(errorResponse)
    }

    // Generate cache key for the raw data
    const cacheKey = generateCacheKey(request)

    // Get the raw data (either from cache or fresh)
    const data = await getCachedData(cacheKey, async () => {
      // Fetch all data
      const allData = await fetchDataWithCache()

      if (!Array.isArray(allData)) {
        throw new Error("Invalid data format received from source")
      }

      // Find the specific file info
      const fileInfo = allData.find((file: any) => file && typeof file === "object" && file.file_code === fileCode)

      if (!fileInfo) {
        throw new Error(`No file found with file_code: ${fileCode}`)
      }

      // Return the raw file info
      return fileInfo
    })

    // If we got here, we have the file info (either from cache or fresh)
    // Now process the title dynamically for every request
    const result = {
      filecode: data.file_code || "",
      size: String(data.size || 0),
      status: 200,
      protected_embed: data.protected_embed || "",
      uploaded: data.uploaded || "",
      last_view: new Date().toISOString().replace("T", " ").substr(0, 19),
      canplay: data.canplay ? 1 : 0,
      protected_dl: data.protected_dl || "",
      single_img: data.single_img || "",
      // Process the title dynamically for every request
      title: processTitle(data.title || ""),
      views: String(data.views || 0),
      length: String(data.length || 0),
      splash_img: data.splash_img || "",
    }

    const response = NextResponse.json({
      status: 200,
      result: [result],
      server_time: new Date().toISOString().replace("T", " ").substr(0, 19),
      msg: "OK",
    })

    return setCorsHeaders(response)
  } catch (error) {
    console.error("Error in info endpoint:", error)

    // Check if it's a "not found" error
    if (error instanceof Error && error.message.includes("No file found")) {
      const notFoundResponse = NextResponse.json(
        {
          error: "File not found",
          message: error.message,
        },
        { status: 404 },
      )
      return setCorsHeaders(notFoundResponse)
    }

    // Generic error
    const errorResponse = NextResponse.json(
      {
        error: "Server error",
        message: "An unexpected error occurred",
      },
      { status: 500 },
    )
    return setCorsHeaders(errorResponse)
  }
}

export async function OPTIONS() {
  return setCorsHeaders(new NextResponse(null, { status: 200 }))
}

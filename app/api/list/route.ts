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
    const page = Number.parseInt(searchParams.get("page") || "1")
    const perPage = Number.parseInt(searchParams.get("per_page") || "50")

    // Generate cache key for the raw data
    const cacheKey = generateCacheKey(request)

    // Get the raw data (either from cache or fresh)
    const rawData = await getCachedData(cacheKey, async () => {
      // Fetch all data
      const allData = await fetchDataWithCache()

      if (!Array.isArray(allData)) {
        throw new Error("Invalid data format received from source")
      }

      // Calculate pagination
      const startIndex = (page - 1) * perPage
      const endIndex = startIndex + perPage

      // Return the paginated raw data
      return {
        allData,
        paginatedData: allData.slice(startIndex, endIndex),
      }
    })

    // Process the data with dynamic title processing
    const processedFiles = rawData.paginatedData.map((file) => ({
      public: "1",
      single_img: file.single_img,
      canplay: file.canplay ? 1 : 0,
      uploaded: file.uploaded,
      views: file.views.toString(),
      length: file.length.toString(),
      download_url: file.download_url,
      file_code: file.file_code,
      // Process the title dynamically for every request
      title: processTitle(file.title),
      fld_id: "0",
      splash_img: file.splash_img,
    }))

    const response = NextResponse.json({
      result: {
        total_pages: Math.ceil(rawData.allData.length / perPage),
        results_total: rawData.allData.length.toString(),
        results: processedFiles.length,
        files: processedFiles,
        per_page_limit: perPage.toString(),
      },
      status: 200,
      msg: "OK",
      server_time: new Date().toISOString().replace("T", " ").substr(0, 19),
    })

    return setCorsHeaders(response)
  } catch (error) {
    console.error("Error in list endpoint:", error)
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

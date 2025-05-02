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
    const query = searchParams.get("q")
    const page = Number.parseInt(searchParams.get("page") || "1")
    const perPage = Number.parseInt(searchParams.get("per_page") || "200")

    if (!query) {
      const errorResponse = NextResponse.json(
        {
          error: "Search query is required",
          message: "Please provide a 'q' parameter with your search query",
        },
        { status: 400 },
      )
      return setCorsHeaders(errorResponse)
    }

    // Generate cache key for the raw search results
    const cacheKey = generateCacheKey(request)

    // Get the raw search results (either from cache or fresh)
    const rawSearchResults = await getCachedData(cacheKey, async () => {
      // Fetch all data
      const allData = await fetchDataWithCache()

      if (!Array.isArray(allData)) {
        throw new Error("Invalid data format received from source")
      }

      // Memisahkan query menjadi array kata-kata
      const keywords = query.toLowerCase().split(/\s+/).filter(Boolean)

      // Membuat Set untuk menyimpan file_code yang sudah ditemukan agar tidak ada duplikat
      const seenFileCodes = new Set<string>()

      // Mencari hasil untuk setiap kata kunci
      const searchResults = allData
        .filter((file: any) => {
          if (!file || !file.title) return false
          const titleLower = file.title.toLowerCase()
          // Hanya menyertakan file yang belum dilihat dan cocok dengan salah satu kata kunci
          return !seenFileCodes.has(file.file_code) && keywords.some((keyword) => titleLower.includes(keyword))
        })
        .map((file: any) => {
          // Menambahkan file_code ke Set setelah diproses
          seenFileCodes.add(file.file_code)
          return file
        })

      const startIndex = (page - 1) * perPage
      const endIndex = startIndex + perPage

      // Return the raw search results
      return searchResults.slice(startIndex, endIndex)
    })

    // Process the search results with dynamic title processing
    const processedResults = rawSearchResults.map((file) => ({
      single_img: file.single_img,
      length: file.length.toString(),
      views: file.views.toString(),
      // Process the title dynamically for every request
      title: processTitle(file.title),
      file_code: file.file_code,
      uploaded: file.uploaded,
      splash_img: file.splash_img,
      canplay: file.canplay ? 1 : 0,
    }))

    const response = NextResponse.json({
      server_time: new Date().toISOString().replace("T", " ").substr(0, 19),
      status: 200,
      msg: "OK",
      result: processedResults,
    })

    return setCorsHeaders(response)
  } catch (error) {
    console.error("Error in search endpoint:", error)
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

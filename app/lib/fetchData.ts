// Global variable to store the cached data
let cachedData: any = null
let isDataFetching = false
let fetchPromise: Promise<any> | null = null

/**
 * Fetches data from the external source only once and caches it permanently
 * @returns The cached data
 */
export async function fetchDataWithCache() {
  // If we already have cached data, return it immediately
  if (cachedData !== null) {
    return cachedData
  }

  // If a fetch is already in progress, wait for it to complete
  if (isDataFetching && fetchPromise) {
    return fetchPromise
  }

  // First time fetch - get the data and store it permanently
  isDataFetching = true
  fetchPromise = (async () => {
    try {
      console.log("Fetching data from external source (one-time only)")

      // Try multiple data sources in case one fails
      const sources = ["https://v0-webapi7-ky1jd5.vercel.app/api/compressed", "https://a.cewe.pro/data.json"]

      let response = null
      let error = null

      // Try each source until one succeeds
      for (const source of sources) {
        try {
          response = await fetch(source, {
            headers: {
              "Accept-Encoding": "gzip, deflate, br",
            },
            next: { revalidate: false },
          })

          if (response.ok) {
            break
          }
        } catch (e) {
          error = e
          console.error(`Failed to fetch from ${source}:`, e)
        }
      }

      if (!response || !response.ok) {
        throw error || new Error("All data sources failed")
      }

      const data = await response.json()

      // Validate that the data is an array
      if (!Array.isArray(data)) {
        throw new Error("Invalid data format: expected an array")
      }

      // Filter out any invalid entries
      const validData = data.filter((item) => item && typeof item === "object" && item.file_code)

      cachedData = validData
      return cachedData
    } catch (error) {
      console.error("Failed to fetch data:", error)
      // Return an empty array instead of throwing to prevent cascading failures
      cachedData = []
      return cachedData
    } finally {
      isDataFetching = false
      fetchPromise = null
    }
  })()

  return fetchPromise
}

/**
 * Gets the cached data
 * @returns The cached data or empty array if not yet fetched
 */
export function getCachedData(): any[] {
  return cachedData || []
}

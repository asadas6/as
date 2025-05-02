import { LRUCache } from "lru-cache"
import { NextResponse } from "next/server"

// Configure the LRU cache with a maximum of 1000 items
// Using a very large integer for TTL instead of infinity
// 100 years in milliseconds should be effectively "forever" for our purposes
const ONE_HUNDRED_YEARS_MS = 100 * 365 * 24 * 60 * 60 * 1000

// Cache for raw data only, not processed responses
const dataCache = new LRUCache<string, any>({
  max: 1000,
  ttl: ONE_HUNDRED_YEARS_MS, // Very long expiration instead of infinity
})

// Cache for API responses
const responseCache = new LRUCache<string, NextResponse>({
  max: 1000,
  ttl: ONE_HUNDRED_YEARS_MS, // Very long expiration instead of infinity
})

/**
 * Get cached raw data or fetch fresh data
 * @param cacheKey Unique key for the cache entry
 * @param dataFetcher Function that returns the raw data
 * @returns The cached or fresh raw data
 */
export async function getCachedData<T>(cacheKey: string, dataFetcher: () => Promise<T>): Promise<T> {
  try {
    // Check if we have cached data
    if (dataCache.has(cacheKey)) {
      console.log(`Data cache hit for: ${cacheKey}`)
      return dataCache.get(cacheKey) as T
    }

    // No cache, fetch fresh data
    console.log(`Data cache miss for: ${cacheKey}, fetching fresh data`)
    const data = await dataFetcher()

    // Store raw data in cache
    dataCache.set(cacheKey, data)

    return data
  } catch (error) {
    console.error(`Error in getCachedData for key ${cacheKey}:`, error)
    // Re-throw the error to be handled by the caller
    throw error
  }
}

/**
 * Get cached API response or generate a fresh response
 * @param cacheKey Unique key for the cache entry
 * @param responseGenerator Function that generates the NextResponse
 * @returns The cached or fresh NextResponse
 */
export async function getCachedResponse(
  cacheKey: string,
  responseGenerator: () => Promise<NextResponse>,
): Promise<NextResponse> {
  try {
    // Check if we have a cached response
    if (responseCache.has(cacheKey)) {
      console.log(`Response cache hit for: ${cacheKey}`)
      return responseCache.get(cacheKey) as NextResponse
    }

    // No cache, generate a fresh response
    console.log(`Response cache miss for: ${cacheKey}, generating fresh response`)
    const response = await responseGenerator()

    // Store the response in the cache
    responseCache.set(cacheKey, response)

    return response
  } catch (error) {
    console.error(`Error in getCachedResponse for key ${cacheKey}:`, error)
    // Create an error response
    return new NextResponse(
      JSON.stringify({
        error: "Server error",
        message: "An unexpected error occurred",
      }),
      {
        status: 500,
        headers: {
          "Content-Type": "application/json",
        },
      },
    )
  }
}

/**
 * Generate a cache key from request URL and search params
 * @param request Request object
 * @returns Cache key string
 */
export function generateCacheKey(request: Request): string {
  try {
    const url = new URL(request.url)
    const params = new URLSearchParams(url.search)
    const sortedParams = Array.from(params.entries())
      .sort(([keyA], [keyB]) => keyA.localeCompare(keyB))
      .map(([key, value]) => `${key}=${value}`)
      .join("&")

    return `${url.pathname}?${sortedParams}`
  } catch (error) {
    console.error("Error generating cache key:", error)
    // Return a fallback key based on the raw URL
    return `fallback-${request.url}`
  }
}

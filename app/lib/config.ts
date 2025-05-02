export const config = {
  // Cache durations in seconds
  cache: {
    defaultMaxAge: 31536000, // 1 year
    searchMaxAge: 31536000, // 1 year
    infoMaxAge: 157680000, // 5 years
    listMaxAge: 86400, // 1 day
    randMaxAge: 3600, // 1 hour
  },

  // Rate limiting
  rateLimit: {
    windowMs: 60 * 1000, // 1 minute
    maxRequests: 100, // 100 requests per minute
  },

  // Data source
  dataSource: {
    url: "https://v0-webapi-i6uuym.vercel.app/api/compressed",
    refreshInterval: null, // null means never refresh
  },

  // Performance
  performance: {
    useCompression: true,
    maxCacheItems: 1000,
  },
}

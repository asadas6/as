import Link from "next/link"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { ArrowRight, Database, Search, Shuffle, List, Info } from "lucide-react"

export default function Home() {
  return (
    <div className="container mx-auto px-2 sm:px-4 py-4 sm:py-8">
      <div className="flex flex-col items-center text-center mb-8 sm:mb-12">
        <h1 className="text-3xl sm:text-4xl font-bold mb-2 sm:mb-4">High-Performance API Dashboard</h1>
        <p className="text-lg sm:text-xl text-muted-foreground max-w-2xl mb-4 sm:mb-8">
          Browse, search, and explore content with our optimized API interface
        </p>
        <Link href="/dashboard">
          <Button className="gap-2">
            Go to Dashboard
            <ArrowRight className="w-4 h-4" />
          </Button>
        </Link>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 mb-8 sm:mb-12">
        <Card className="transition-all duration-200 hover:shadow-md hover:translate-y-[-2px] border-border">
          <CardHeader className="pb-2">
            <CardTitle className="flex items-center gap-2 text-base sm:text-lg">
              <List className="w-4 h-4 sm:w-5 sm:h-5 text-primary" />
              List View
            </CardTitle>
          </CardHeader>
          <CardContent>
            <CardDescription className="text-xs sm:text-sm">
              Browse through paginated content with optimized loading
            </CardDescription>
          </CardContent>
        </Card>

        <Card className="transition-all duration-200 hover:shadow-md hover:translate-y-[-2px] border-border">
          <CardHeader className="pb-2">
            <CardTitle className="flex items-center gap-2 text-base sm:text-lg">
              <Search className="w-4 h-4 sm:w-5 sm:h-5 text-primary" />
              Search
            </CardTitle>
          </CardHeader>
          <CardContent>
            <CardDescription className="text-xs sm:text-sm">
              Find specific content with our powerful search functionality
            </CardDescription>
          </CardContent>
        </Card>

        <Card className="transition-all duration-200 hover:shadow-md hover:translate-y-[-2px] border-border">
          <CardHeader className="pb-2">
            <CardTitle className="flex items-center gap-2 text-base sm:text-lg">
              <Shuffle className="w-4 h-4 sm:w-5 sm:h-5 text-primary" />
              Random
            </CardTitle>
          </CardHeader>
          <CardContent>
            <CardDescription className="text-xs sm:text-sm">
              Discover new content with our random content generator
            </CardDescription>
          </CardContent>
        </Card>

        <Card className="transition-all duration-200 hover:shadow-md hover:translate-y-[-2px] border-border">
          <CardHeader className="pb-2">
            <CardTitle className="flex items-center gap-2 text-base sm:text-lg">
              <Info className="w-4 h-4 sm:w-5 sm:h-5 text-primary" />
              Details
            </CardTitle>
          </CardHeader>
          <CardContent>
            <CardDescription className="text-xs sm:text-sm">
              View comprehensive details about each content item
            </CardDescription>
          </CardContent>
        </Card>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-6">
        <div className="p-4 sm:p-6 bg-muted rounded-lg">
          <h2 className="text-xl font-semibold mb-4 flex items-center gap-2">
            <Database className="w-5 h-5" />
            API Features
          </h2>
          <ul className="space-y-2">
            <li className="flex items-start gap-2">
              <ArrowRight className="w-4 h-4 mt-1 text-primary" />
              <span>Raw data caching with dynamic title processing</span>
            </li>
            <li className="flex items-start gap-2">
              <ArrowRight className="w-4 h-4 mt-1 text-primary" />
              <span>HTTP cache headers for CDN caching</span>
            </li>
            <li className="flex items-start gap-2">
              <ArrowRight className="w-4 h-4 mt-1 text-primary" />
              <span>Robust error handling</span>
            </li>
            <li className="flex items-start gap-2">
              <ArrowRight className="w-4 h-4 mt-1 text-primary" />
              <span>LRU cache for memory management</span>
            </li>
            <li className="flex items-start gap-2">
              <ArrowRight className="w-4 h-4 mt-1 text-primary" />
              <span>Multiple data source fallbacks</span>
            </li>
          </ul>
        </div>

        <div className="p-4 sm:p-6 bg-muted rounded-lg">
          <h2 className="text-xl font-semibold mb-4">API Endpoints</h2>
          <ul className="space-y-2">
            <li>
              <Link
                href="/api/list?page=1&per_page=10"
                className="text-primary hover:underline flex items-center gap-2"
              >
                <ArrowRight className="w-4 h-4" />
                <span>/api/list</span>
              </Link>
            </li>
            <li>
              <Link
                href="/api/rand?page=1&per_page=10"
                className="text-primary hover:underline flex items-center gap-2"
              >
                <ArrowRight className="w-4 h-4" />
                <span>/api/rand</span>
              </Link>
            </li>
            <li>
              <Link
                href="/api/info?file_code=0vq5urkvffjl"
                className="text-primary hover:underline flex items-center gap-2"
              >
                <ArrowRight className="w-4 h-4" />
                <span>/api/info</span>
              </Link>
            </li>
            <li>
              <Link
                href="/api/search?q=video&page=1&per_page=10"
                className="text-primary hover:underline flex items-center gap-2"
              >
                <ArrowRight className="w-4 h-4" />
                <span>/api/search</span>
              </Link>
            </li>
            <li>
              <Link href="/api/compressed" className="text-primary hover:underline flex items-center gap-2">
                <ArrowRight className="w-4 h-4" />
                <span>/api/compressed</span>
              </Link>
            </li>
          </ul>
        </div>
      </div>
    </div>
  )
}

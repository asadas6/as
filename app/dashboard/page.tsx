"use client"

import type React from "react"

import { useState, useEffect } from "react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Card, CardContent } from "@/components/ui/card"
import { Tabs, TabsList, TabsTrigger } from "@/components/ui/tabs"
import {
  Pagination,
  PaginationContent,
  PaginationItem,
  PaginationLink,
  PaginationNext,
  PaginationPrevious,
} from "@/components/ui/pagination"
import { Search, RefreshCw, Info, List, Shuffle } from "lucide-react"
import { useRouter } from "next/navigation"

interface File {
  file_code: string
  title: string
  single_img: string
  views: string
  uploaded: string
  length: string
  canplay: number
  splash_img: string
  protected_embed?: string
}

interface ListResponse {
  result: {
    files: File[]
    total_pages: number
    results_total: string
    results: number
  }
  status: number
  msg: string
  server_time: string
}

interface SearchResponse {
  result: File[]
  status: number
  msg: string
  server_time: string
}

export default function Dashboard() {
  const [activeTab, setActiveTab] = useState("list")
  const [files, setFiles] = useState<File[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [page, setPage] = useState(1)
  const [totalPages, setTotalPages] = useState(1)
  const [searchQuery, setSearchQuery] = useState("")
  const [perPage, setPerPage] = useState(12)
  const router = useRouter()

  const fetchList = async () => {
    try {
      setLoading(true)
      setError(null)
      const response = await fetch(`/api/list?page=${page}&per_page=${perPage}`)
      if (!response.ok) throw new Error("Failed to fetch list data")

      const data: ListResponse = await response.json()
      setFiles(data.result.files)
      setTotalPages(data.result.total_pages)
      setLoading(false)
    } catch (err) {
      setError(err instanceof Error ? err.message : "An unknown error occurred")
      setLoading(false)
    }
  }

  const fetchRandom = async () => {
    try {
      setLoading(true)
      setError(null)
      const response = await fetch(`/api/rand?page=${page}&per_page=${perPage}`)
      if (!response.ok) throw new Error("Failed to fetch random data")

      const data: ListResponse = await response.json()
      setFiles(data.result.files)
      setTotalPages(data.result.total_pages)
      setLoading(false)
    } catch (err) {
      setError(err instanceof Error ? err.message : "An unknown error occurred")
      setLoading(false)
    }
  }

  useEffect(() => {
    if (activeTab === "list") {
      fetchList()
    } else if (activeTab === "random") {
      fetchRandom()
    }
  }, [activeTab, page, perPage])

  const handleTabChange = (value: string) => {
    setActiveTab(value)
    setPage(1)
  }

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault()
    if (searchQuery.trim()) {
      // Navigate to the search page with the query
      router.push(`/f/${encodeURIComponent(searchQuery.trim())}`)
    } else {
      setError("Please enter a search query")
    }
  }

  const handlePageChange = (newPage: number) => {
    setPage(newPage)
    window.scrollTo(0, 0)
  }

  const handleFileClick = (file: File) => {
    // Navigate to the file detail page
    router.push(`/e/${file.file_code}`)
  }

  return (
    <div className="container mx-auto px-2 sm:px-4 py-4 sm:py-8">
      <h1 className="text-2xl sm:text-3xl font-bold mb-4 sm:mb-6">Content Dashboard</h1>

      <Tabs value={activeTab} onValueChange={handleTabChange} className="mb-8">
        <TabsList className="grid w-full grid-cols-3 mb-4">
          <TabsTrigger value="list" className="flex items-center gap-2">
            <List className="w-4 h-4" />
            List View
          </TabsTrigger>
          <TabsTrigger value="random" className="flex items-center gap-2">
            <Shuffle className="w-4 h-4" />
            Random
          </TabsTrigger>
          <TabsTrigger value="search" className="flex items-center gap-2">
            <Search className="w-4 h-4" />
            Search
          </TabsTrigger>
        </TabsList>

        {activeTab === "search" && (
          <form onSubmit={handleSearch} className="flex flex-col sm:flex-row gap-2 mb-4">
            <Input
              type="text"
              placeholder="Enter search query..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="flex-1"
            />
            <Button type="submit" disabled={loading} className="w-full sm:w-auto">
              {loading ? <RefreshCw className="w-4 h-4 animate-spin" /> : <Search className="w-4 h-4 mr-2" />}
              Search
            </Button>
          </form>
        )}

        {error && <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{error}</div>}

        {loading ? (
          <div className="flex justify-center items-center py-12">
            <RefreshCw className="w-8 h-8 animate-spin text-primary" />
          </div>
        ) : files.length === 0 ? (
          <div className="text-center py-12">
            <p className="text-muted-foreground">No files found</p>
          </div>
        ) : (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
            {files.map((file) => (
              <Card
                key={file.file_code}
                className="overflow-hidden cursor-pointer transition-all duration-200 hover:shadow-md hover:translate-y-[-2px] border-border"
                onClick={() => handleFileClick(file)}
              >
                <div className="aspect-video relative">
                  <img
                    src={file.single_img || "/placeholder.svg?height=200&width=300"}
                    alt={file.title}
                    className="w-full h-full object-cover"
                    onError={(e) => {
                      const target = e.target as HTMLImageElement
                      target.src = "/placeholder.svg?height=200&width=300"
                    }}
                  />
                </div>
                <CardContent className="p-3 sm:p-4">
                  <h3 className="font-medium text-sm sm:text-base line-clamp-2 h-8 sm:h-12">{file.title}</h3>
                  <div className="flex justify-between text-xs sm:text-sm text-muted-foreground mt-2">
                    <span>Views: {file.views}</span>
                    <span>
                      <Info className="w-3 h-3 sm:w-4 sm:h-4 inline mr-1" />
                      {file.file_code.substring(0, 6)}...
                    </span>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        )}

        {!loading && files.length > 0 && (
          <Pagination className="mt-8">
            <PaginationContent>
              <PaginationItem>
                <PaginationPrevious
                  onClick={() => handlePageChange(Math.max(1, page - 1))}
                  className={page <= 1 ? "pointer-events-none opacity-50" : ""}
                />
              </PaginationItem>

              {Array.from({ length: Math.min(5, totalPages) }, (_, i) => {
                const pageNumber = page <= 3 ? i + 1 : page >= totalPages - 2 ? totalPages - 4 + i : page - 2 + i

                if (pageNumber <= 0 || pageNumber > totalPages) return null

                return (
                  <PaginationItem key={pageNumber}>
                    <PaginationLink isActive={page === pageNumber} onClick={() => handlePageChange(pageNumber)}>
                      {pageNumber}
                    </PaginationLink>
                  </PaginationItem>
                )
              })}

              <PaginationItem>
                <PaginationNext
                  onClick={() => handlePageChange(Math.min(totalPages, page + 1))}
                  className={page >= totalPages ? "pointer-events-none opacity-50" : ""}
                />
              </PaginationItem>
            </PaginationContent>
          </Pagination>
        )}
      </Tabs>
    </div>
  )
}

"use client"

import type React from "react"

import { useState, useEffect } from "react"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import {
  RefreshCw,
  ArrowLeft,
  Search,
  Download,
  ExternalLink,
  Clock,
  Eye,
  Calendar,
  FileText,
  Tag,
  Play,
} from "lucide-react"
import { useRouter } from "next/navigation"
import { Tabs, TabsList, TabsTrigger } from "@/components/ui/tabs"

interface File {
  filecode: string
  title: string
  single_img: string
  views: string
  uploaded: string
  length: string
  canplay: number
  splash_img: string
  protected_embed?: string
  protected_dl?: string
  size?: string
  file_code?: string // Some APIs return file_code instead of filecode
}

export default function FileDetailPage({ params }: { params: { file_code: string } }) {
  const [file, setFile] = useState<File | null>(null)
  const [relatedVideos, setRelatedVideos] = useState<File[]>([])
  const [randomVideos, setRandomVideos] = useState<File[]>([])
  const [loading, setLoading] = useState(true)
  const [relatedLoading, setRelatedLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [searchQuery, setSearchQuery] = useState("")
  const [activeTab, setActiveTab] = useState("related")
  const [isVideoPlaying, setIsVideoPlaying] = useState(false)
  const [selectedSidebarVideo, setSelectedSidebarVideo] = useState<File | null>(null)
  const router = useRouter()

  // Extract keywords from title
  const extractKeywords = (title: string): string[] => {
    if (!title) return []

    // Remove common stop words and split by spaces
    const stopWords = ["a", "an", "the", "and", "or", "but", "in", "on", "at", "to", "for", "with", "by", "about", "as"]
    const words = title.toLowerCase().split(/\s+/)

    // Filter out stop words and short words (less than 3 characters)
    const filteredWords = words.filter(
      (word) => !stopWords.includes(word) && word.length >= 3 && !word.match(/^\d+$/), // Filter out numbers
    )

    // Get unique words and limit to 4 most relevant (longer words tend to be more specific)
    const uniqueWords = Array.from(new Set(filteredWords))
      .sort((a, b) => b.length - a.length)
      .slice(0, 4)

    return uniqueWords
  }

  // Generate a brief review of the video
  const generateVideoReview = (file: File): string => {
    if (!file) return ""

    const lengthInMinutes = Number.parseInt(file.length) / 60
    const formattedLength =
      lengthInMinutes < 1 ? `${Number.parseInt(file.length)} seconds` : `${lengthInMinutes.toFixed(1)} minutes`

    const viewCount = Number.parseInt(file.views)
    let popularityDesc = "new"
    if (viewCount > 10000) popularityDesc = "extremely popular"
    else if (viewCount > 5000) popularityDesc = "very popular"
    else if (viewCount > 1000) popularityDesc = "popular"
    else if (viewCount > 100) popularityDesc = "moderately viewed"

    // Calculate approximate upload date
    let uploadTimeDesc = "recently"
    if (file.uploaded) {
      try {
        const uploadDate = new Date(file.uploaded)
        const now = new Date()
        const diffDays = Math.floor((now.getTime() - uploadDate.getTime()) / (1000 * 60 * 60 * 24))

        if (diffDays > 365) uploadTimeDesc = `${Math.floor(diffDays / 365)} years ago`
        else if (diffDays > 30) uploadTimeDesc = `${Math.floor(diffDays / 30)} months ago`
        else if (diffDays > 0) uploadTimeDesc = `${diffDays} days ago`
        else uploadTimeDesc = "today"
      } catch (e) {
        // If date parsing fails, use the default "recently"
      }
    }

    return `This ${formattedLength} video was uploaded ${uploadTimeDesc} and has been viewed ${viewCount} times, making it ${popularityDesc}. ${file.canplay ? "It's available for playback." : ""}`
  }

  // Fetch file details
  useEffect(() => {
    const fetchFileInfo = async () => {
      try {
        setLoading(true)
        setError(null)
        setIsVideoPlaying(false) // Reset video playing state when loading a new video
        setSelectedSidebarVideo(null) // Reset selected sidebar video
        const response = await fetch(`/api/info?file_code=${params.file_code}`)

        if (!response.ok) {
          throw new Error(`Failed to fetch file info: ${response.statusText}`)
        }

        const data = await response.json()
        if (data.result && data.result.length > 0) {
          setFile(data.result[0])
        } else {
          throw new Error("File not found")
        }
      } catch (err) {
        setError(err instanceof Error ? err.message : "An unknown error occurred")
      } finally {
        setLoading(false)
      }
    }

    fetchFileInfo()
  }, [params.file_code])

  // Fetch related and random videos
  useEffect(() => {
    if (!file || !file.title) return

    const fetchRelatedVideos = async () => {
      try {
        setRelatedLoading(true)

        // Extract up to 4 keywords from the title for search
        const keywords = extractKeywords(file.title)
        const searchQuery = keywords.join(" ")
        console.log("Search keywords:", keywords)

        // Fetch related videos based on title keywords
        const relatedResponse = await fetch(`/api/search?q=${encodeURIComponent(searchQuery)}&per_page=12`)

        if (!relatedResponse.ok) {
          throw new Error("Failed to fetch related videos")
        }

        const relatedData = await relatedResponse.json()

        // Filter out the current video
        const filteredRelated = relatedData.result
          .filter((video: File) => (video.file_code || video.filecode) !== params.file_code)
          .slice(0, 6)

        setRelatedVideos(filteredRelated)

        // Fetch random videos
        const randomResponse = await fetch(`/api/rand?per_page=6`)

        if (!randomResponse.ok) {
          throw new Error("Failed to fetch random videos")
        }

        const randomData = await randomResponse.json()

        // Filter out the current video and any videos that are already in related
        const relatedIds = new Set(filteredRelated.map((v: File) => v.file_code || v.filecode))
        const filteredRandom = randomData.result.files
          .filter(
            (video: File) =>
              (video.file_code || video.filecode) !== params.file_code &&
              !relatedIds.has(video.file_code || video.filecode),
          )
          .slice(0, 6)

        setRandomVideos(filteredRandom)
      } catch (err) {
        console.error("Error fetching related videos:", err)
      } finally {
        setRelatedLoading(false)
      }
    }

    fetchRelatedVideos()
  }, [file, params.file_code])

  const handleBackClick = () => {
    router.back()
  }

  const handleVideoClick = (video: File) => {
    // Set the selected video instead of navigating
    setSelectedSidebarVideo(video)
  }

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault()
    if (searchQuery.trim()) {
      router.push(`/f/${encodeURIComponent(searchQuery.trim())}`)
    }
  }

  const handlePlayVideo = () => {
    setIsVideoPlaying(true)
  }

  if (loading) {
    return (
      <div className="container mx-auto px-4 py-8 flex justify-center items-center min-h-[50vh]">
        <RefreshCw className="w-8 h-8 animate-spin text-primary" />
      </div>
    )
  }

  if (error || !file) {
    return (
      <div className="container mx-auto px-4 py-8">
        <Button variant="outline" onClick={handleBackClick} className="mb-4">
          <ArrowLeft className="mr-2 h-4 w-4" /> Back
        </Button>
        <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
          {error || "File not found"}
        </div>
      </div>
    )
  }

  const videoReview = generateVideoReview(file)
  const keywords = extractKeywords(file.title)

  return (
    <div className="container mx-auto px-4 py-4 md:py-6">
      <Button variant="outline" onClick={handleBackClick} className="mb-4">
        <ArrowLeft className="mr-2 h-4 w-4" /> Back
      </Button>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">
        {/* Main content - Video player and details */}
        <div className="lg:col-span-2">
          {/* Main video title */}
          <h1 className="text-xl md:text-2xl font-bold mb-3">{file.title}</h1>

          {/* Custom Video Player */}
          {file.protected_embed && (
            <div className="aspect-video w-full rounded-md overflow-hidden border border-border mb-4 bg-black relative">
              {isVideoPlaying ? (
                <iframe
                  className="w-full h-full"
                  src={file.protected_embed}
                  frameBorder="0"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                  allowFullScreen
                ></iframe>
              ) : (
                <div
                  className="absolute inset-0 flex flex-col items-center justify-center cursor-pointer"
                  onClick={handlePlayVideo}
                  style={{
                    backgroundImage: `url(${file.splash_img || file.single_img || "/placeholder.svg?height=400&width=600"})`,
                    backgroundSize: "cover",
                    backgroundPosition: "center",
                  }}
                >
                  <div className="absolute inset-0 bg-black bg-opacity-40"></div>
                  <div className="relative z-10 flex flex-col items-center">
                    <div className="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-primary bg-opacity-80 flex items-center justify-center mb-2 hover:bg-opacity-100 transition-all">
                      <Play className="w-8 h-8 sm:w-10 sm:h-10 text-primary-foreground ml-1" />
                    </div>
                    <p className="text-white font-medium text-sm sm:text-base">Click to play video</p>
                  </div>
                </div>
              )}
            </div>
          )}

          {/* Brief review */}
          <div className="bg-muted p-3 rounded-md mb-4 text-sm">
            <div className="flex items-start gap-2">
              <FileText className="w-4 h-4 mt-0.5 text-muted-foreground flex-shrink-0" />
              <p>{videoReview}</p>
            </div>

            {keywords.length > 0 && (
              <div className="flex flex-wrap gap-2 mt-2">
                <span className="flex items-center text-xs text-muted-foreground">
                  <Tag className="w-3.5 h-3.5 mr-1" />
                  Keywords:
                </span>
                {keywords.map((keyword, index) => (
                  <span key={index} className="px-2 py-0.5 bg-secondary text-secondary-foreground rounded-full text-xs">
                    {keyword}
                  </span>
                ))}
              </div>
            )}
          </div>

          {/* Action buttons */}
          <div className="flex flex-wrap gap-2 mb-4">
            {file.protected_dl && (
              <Button asChild className="gap-2">
                <a href={file.protected_dl} target="_blank" rel="noopener noreferrer">
                  <Download className="w-4 h-4" />
                  Download
                </a>
              </Button>
            )}
            {file.protected_embed && (
              <Button variant="outline" asChild className="gap-2">
                <a href={file.protected_embed} target="_blank" rel="noopener noreferrer">
                  <ExternalLink className="w-4 h-4" />
                  Open in New Tab
                </a>
              </Button>
            )}
          </div>

          {/* Video details */}
          {selectedSidebarVideo ? (
            <div className="mt-4 border-t pt-4">
              <h2 className="text-lg font-bold mb-3">Selected Video Details</h2>

              <div className="flex flex-col md:flex-row gap-4 mb-4">
                <div className="md:w-1/3">
                  <img
                    src={selectedSidebarVideo.single_img || "/placeholder.svg?height=200&width=300"}
                    alt={selectedSidebarVideo.title}
                    className="w-full aspect-video object-cover rounded-md"
                    onError={(e) => {
                      const target = e.target as HTMLImageElement
                      target.src = "/placeholder.svg?height=200&width=300"
                    }}
                  />
                </div>

                <div className="md:w-2/3">
                  <h3 className="text-base font-semibold mb-2">{selectedSidebarVideo.title}</h3>

                  <div className="grid grid-cols-2 gap-2 text-sm">
                    <div className="flex items-center gap-1">
                      <Eye className="w-3.5 h-3.5 text-muted-foreground" />
                      <span>Views: {selectedSidebarVideo.views}</span>
                    </div>
                    <div className="flex items-center gap-1">
                      <Clock className="w-3.5 h-3.5 text-muted-foreground" />
                      <span>Length: {selectedSidebarVideo.length} sec</span>
                    </div>
                    <div className="flex items-center gap-1">
                      <Calendar className="w-3.5 h-3.5 text-muted-foreground" />
                      <span>Uploaded: {selectedSidebarVideo.uploaded}</span>
                    </div>
                  </div>

                  <div className="mt-3 flex gap-2">
                    <Button
                      size="sm"
                      onClick={() =>
                        router.push(`/e/${selectedSidebarVideo.file_code || selectedSidebarVideo.filecode}`)
                      }
                    >
                      View Full Details
                    </Button>
                    {selectedSidebarVideo.protected_dl && (
                      <Button size="sm" variant="outline" asChild>
                        <a href={selectedSidebarVideo.protected_dl} target="_blank" rel="noopener noreferrer">
                          <Download className="w-3.5 h-3.5 mr-1" />
                          Download
                        </a>
                      </Button>
                    )}
                  </div>
                </div>
              </div>
            </div>
          ) : (
            <div className="grid grid-cols-2 md:grid-cols-3 gap-3 mb-4">
              <div className="bg-card rounded-md p-3">
                <h3 className="text-xs font-medium text-muted-foreground mb-1">Views</h3>
                <p className="text-base flex items-center gap-1">
                  <Eye className="w-3.5 h-3.5" />
                  {file.views}
                </p>
              </div>
              <div className="bg-card rounded-md p-3">
                <h3 className="text-xs font-medium text-muted-foreground mb-1">Uploaded</h3>
                <p className="text-base flex items-center gap-1">
                  <Calendar className="w-3.5 h-3.5" />
                  {file.uploaded}
                </p>
              </div>
              <div className="bg-card rounded-md p-3">
                <h3 className="text-xs font-medium text-muted-foreground mb-1">Length</h3>
                <p className="text-base flex items-center gap-1">
                  <Clock className="w-3.5 h-3.5" />
                  {file.length} sec
                </p>
              </div>
              <div className="bg-card rounded-md p-3">
                <h3 className="text-xs font-medium text-muted-foreground mb-1">File Code</h3>
                <p className="text-base">{file.filecode || file.file_code}</p>
              </div>
              <div className="bg-card rounded-md p-3">
                <h3 className="text-xs font-medium text-muted-foreground mb-1">Size</h3>
                <p className="text-base">{file.size || "Unknown"}</p>
              </div>
              <div className="bg-card rounded-md p-3">
                <h3 className="text-xs font-medium text-muted-foreground mb-1">Can Play</h3>
                <p className="text-base">{file.canplay ? "Yes" : "No"}</p>
              </div>
            </div>
          )}
        </div>

        {/* Sidebar - Content Dashboard */}
        <div className="lg:col-span-1">
          {/* Search form */}
          <form onSubmit={handleSearch} className="flex flex-col sm:flex-row gap-2 mb-4">
            <Input
              type="text"
              placeholder="Search for content..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="flex-1"
            />
            <Button type="submit" className="w-full sm:w-auto">
              <Search className="w-4 h-4 mr-2" />
              Search
            </Button>
          </form>

          {/* Tabs for Related and Random */}
          <Tabs value={activeTab} onValueChange={setActiveTab} className="mb-3">
            <TabsList className="grid w-full grid-cols-2 mb-3">
              <TabsTrigger value="related">Related</TabsTrigger>
              <TabsTrigger value="random">Random</TabsTrigger>
            </TabsList>
          </Tabs>

          {relatedLoading ? (
            <div className="flex justify-center items-center py-6">
              <RefreshCw className="w-5 h-5 animate-spin text-primary" />
            </div>
          ) : (
            <div className="space-y-3">
              {activeTab === "related" ? (
                relatedVideos.length > 0 ? (
                  relatedVideos.map((video) => (
                    <Card
                      key={video.file_code || video.filecode}
                      className={`cursor-pointer hover:shadow-md transition-shadow ${
                        selectedSidebarVideo &&
                        (
                          selectedSidebarVideo.file_code === video.file_code ||
                            selectedSidebarVideo.filecode === video.filecode
                        )
                          ? "ring-2 ring-primary"
                          : ""
                      }`}
                      onClick={() => handleVideoClick(video)}
                    >
                      <div className="flex flex-row h-20">
                        <div className="w-1/3 h-full relative">
                          <img
                            src={video.single_img || "/placeholder.svg?height=100&width=100"}
                            alt={video.title}
                            className="w-full h-full object-cover"
                            onError={(e) => {
                              const target = e.target as HTMLImageElement
                              target.src = "/placeholder.svg?height=100&width=100"
                            }}
                          />
                          <div className="absolute inset-0 flex items-center justify-center bg-black bg-opacity-30 opacity-0 hover:opacity-100 transition-opacity">
                            <Play className="w-6 h-6 text-white" />
                          </div>
                        </div>
                        <CardContent className="w-2/3 p-2">
                          <p className="text-xs font-medium line-clamp-2">{video.title}</p>
                          <p className="text-xs text-muted-foreground mt-1 flex items-center">
                            <Eye className="w-3 h-3 mr-1" />
                            {video.views}
                          </p>
                        </CardContent>
                      </div>
                    </Card>
                  ))
                ) : (
                  <p className="text-sm text-muted-foreground">No related videos found</p>
                )
              ) : randomVideos.length > 0 ? (
                randomVideos.map((video) => (
                  <Card
                    key={video.file_code || video.filecode}
                    className={`cursor-pointer hover:shadow-md transition-shadow ${
                      selectedSidebarVideo &&
                      (
                        selectedSidebarVideo.file_code === video.file_code ||
                          selectedSidebarVideo.filecode === video.filecode
                      )
                        ? "ring-2 ring-primary"
                        : ""
                    }`}
                    onClick={() => handleVideoClick(video)}
                  >
                    <div className="flex flex-row h-20">
                      <div className="w-1/3 h-full relative">
                        <img
                          src={video.single_img || "/placeholder.svg?height=100&width=100"}
                          alt={video.title}
                          className="w-full h-full object-cover"
                          onError={(e) => {
                            const target = e.target as HTMLImageElement
                            target.src = "/placeholder.svg?height=100&width=100"
                          }}
                        />
                        <div className="absolute inset-0 flex items-center justify-center bg-black bg-opacity-30 opacity-0 hover:opacity-100 transition-opacity">
                          <Play className="w-6 h-6 text-white" />
                        </div>
                      </div>
                      <CardContent className="w-2/3 p-2">
                        <p className="text-xs font-medium line-clamp-2">{video.title}</p>
                        <p className="text-xs text-muted-foreground mt-1 flex items-center">
                          <Eye className="w-3 h-3 mr-1" />
                          {video.views}
                        </p>
                      </CardContent>
                    </div>
                  </Card>
                ))
              ) : (
                <p className="text-sm text-muted-foreground">No random videos found</p>
              )}
            </div>
          )}
        </div>
      </div>
    </div>
  )
}

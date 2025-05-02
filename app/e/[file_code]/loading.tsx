import { RefreshCw } from "lucide-react"

export default function Loading() {
  return (
    <div className="container mx-auto px-4 py-8 flex justify-center items-center min-h-[70vh]">
      <div className="flex flex-col items-center">
        <RefreshCw className="w-12 h-12 animate-spin text-primary mb-4" />
        <p className="text-lg text-muted-foreground">Loading video...</p>
      </div>
    </div>
  )
}

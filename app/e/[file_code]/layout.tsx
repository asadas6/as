import type React from "react"
import type { Metadata } from "next"

export const metadata: Metadata = {
  title: "File Details - Content API",
  description: "View detailed information about content",
}

export default function FileDetailLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return (
    <div className="min-h-screen bg-background">
      <header className="sticky top-0 z-10 border-b bg-background">
        <div className="container flex h-12 sm:h-16 items-center px-2 sm:px-4">
          <h1 className="text-base sm:text-lg font-semibold">Content API Dashboard</h1>
          <nav className="ml-auto flex gap-2 sm:gap-4">
            <a href="/" className="text-xs sm:text-sm font-medium hover:underline">
              Home
            </a>
            <a href="/dashboard" className="text-xs sm:text-sm font-medium hover:underline">
              Dashboard
            </a>
          </nav>
        </div>
      </header>
      <main>{children}</main>
    </div>
  )
}

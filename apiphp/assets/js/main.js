/**
 * Main JavaScript file for the application
 */

document.addEventListener("DOMContentLoaded", () => {
  // Initialize any global functionality here

  // Handle image errors
  document.querySelectorAll("img").forEach((img) => {
    img.addEventListener("error", function () {
      this.src = "/assets/images/placeholder.jpg"
    })
  })

  // Add smooth scrolling to page navigation
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      const href = this.getAttribute("href")

      if (href !== "#") {
        e.preventDefault()

        const target = document.querySelector(href)
        if (target) {
          target.scrollIntoView({
            behavior: "smooth",
          })
        }
      }
    })
  })

  // Ensure keyword tags are properly styled on hover
  document.querySelectorAll(".keyword-tag").forEach((tag) => {
    tag.addEventListener("mouseenter", function () {
      this.style.transform = "translateY(-1px)"
    })

    tag.addEventListener("mouseleave", function () {
      this.style.transform = "translateY(0)"
    })
  })

  // Ensure video thumbnails show play button on hover
  document.querySelectorAll(".video-thumbnail").forEach((thumbnail) => {
    thumbnail.addEventListener("mouseenter", function () {
      const overlay = this.querySelector(".overlay")
      if (overlay) {
        overlay.style.backgroundColor = "rgba(0, 0, 0, 0.6)"
      }
    })

    thumbnail.addEventListener("mouseleave", function () {
      const overlay = this.querySelector(".overlay")
      if (overlay) {
        overlay.style.backgroundColor = "rgba(0, 0, 0, 0.4)"
      }
    })
  })
})

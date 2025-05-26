// ===== FUNCIONALIDAD MÓVIL =====

// Mobile menu functionality
const mobileMenuBtn = document.getElementById("mobileMenuBtn")
const navLinks = document.getElementById("navLinks")
const body = document.body

if (mobileMenuBtn && navLinks) {
  mobileMenuBtn.addEventListener("click", () => {
    // Toggle active classes
    mobileMenuBtn.classList.toggle("active")
    navLinks.classList.toggle("active")
    body.classList.toggle("menu-open")
  })

  // Close menu when clicking on a nav link
  const navLinkElements = navLinks.querySelectorAll(".nav-link, .mobile-nav-link")
  navLinkElements.forEach((link) => {
    link.addEventListener("click", () => {
      mobileMenuBtn.classList.remove("active")
      navLinks.classList.remove("active")
      body.classList.remove("menu-open")
    })
  })

  // Close menu when clicking outside
  document.addEventListener("click", (event) => {
    if (!mobileMenuBtn.contains(event.target) && !navLinks.contains(event.target)) {
      mobileMenuBtn.classList.remove("active")
      navLinks.classList.remove("active")
      body.classList.remove("menu-open")
    }
  })

  // Close menu on escape key
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      mobileMenuBtn.classList.remove("active")
      navLinks.classList.remove("active")
      body.classList.remove("menu-open")
    }
  })

  // Handle window resize
  window.addEventListener("resize", () => {
    if (window.innerWidth > 768) {
      mobileMenuBtn.classList.remove("active")
      navLinks.classList.remove("active")
      body.classList.remove("menu-open")
    }
  })
}

// Enhanced scroll effect for mobile
let lastScrollTop = 0
const header = document.querySelector(".app-header")

window.addEventListener("scroll", () => {
  const scrollTop = window.pageYOffset || document.documentElement.scrollTop

  // Only hide header on mobile when scrolling down
  if (window.innerWidth <= 768) {
    if (scrollTop > lastScrollTop && scrollTop > 100) {
      // Scrolling down
      header.style.transform = "translateY(-100%)"
    } else {
      // Scrolling up
      header.style.transform = "translateY(0)"
    }
  }

  // Original scroll effect
  if (scrollTop > 10) {
    header.classList.add("scrolled")
  } else {
    header.classList.remove("scrolled")
  }

  lastScrollTop = scrollTop
})

// Touch gestures for mobile menu
let touchStartX = 0
let touchEndX = 0

document.addEventListener("touchstart", (event) => {
  touchStartX = event.changedTouches[0].screenX
})

document.addEventListener("touchend", (event) => {
  touchEndX = event.changedTouches[0].screenX
  handleSwipe()
})

function handleSwipe() {
  const swipeThreshold = 50
  const swipeDistance = touchEndX - touchStartX

  // Swipe right to open menu (only if menu is closed and swipe starts from left edge)
  if (swipeDistance > swipeThreshold && touchStartX < 50 && navLinks && !navLinks.classList.contains("active")) {
    if (mobileMenuBtn) {
      mobileMenuBtn.classList.add("active")
      navLinks.classList.add("active")
      body.classList.add("menu-open")
    }
  }

  // Swipe left to close menu (only if menu is open)
  if (swipeDistance < -swipeThreshold && navLinks && navLinks.classList.contains("active")) {
    if (mobileMenuBtn) {
      mobileMenuBtn.classList.remove("active")
      navLinks.classList.remove("active")
      body.classList.remove("menu-open")
    }
  }
}

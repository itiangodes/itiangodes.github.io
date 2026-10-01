document.addEventListener("DOMContentLoaded", function () {
    
    // 1. Typed.js Initialization
    if (document.querySelector(".typing")) {
        new Typed(".typing", {
            strings: [
                "IT Specialist",
                "Web Developer",
                "Hardware Technician",
                "Software Developer"
            ],
            typeSpeed: 60,
            backSpeed: 40,
            loop: true
        });
    }

    // 2. Mobile Sidebar Toggle
    const navToggler = document.getElementById("nav-toggler");
    const sidebar = document.getElementById("sidebar");

    if (navToggler && sidebar) {
        navToggler.addEventListener("click", function () {
            sidebar.classList.toggle("open");
        });
    }

    // Close menu when clicking a link on mobile
    const navLinks = document.querySelectorAll(".nav-link");
    navLinks.forEach(link => {
        link.addEventListener("click", () => {
            if (window.innerWidth <= 991 && sidebar) {
                sidebar.classList.remove("open");
            }
        });
    });

    // 3. Project Filter Logic
    const filterButtons = document.querySelectorAll(".filter-btn");
    const projectItems = document.querySelectorAll(".projects-item");

    filterButtons.forEach(button => {
        button.addEventListener("click", function () {
            filterButtons.forEach(btn => btn.classList.remove("active"));
            this.classList.add("active");

            const filterValue = this.getAttribute("data-filter");

            projectItems.forEach(item => {
                const category = item.getAttribute("data-category");
                if (filterValue === "all" || category === filterValue) {
                    item.style.display = "block";
                } else {
                    item.style.display = "none";
                }
            });
        });
    });

    // 4. Scroll Reveal Animations
    if (typeof ScrollReveal !== "undefined") {
        const sr = ScrollReveal({
            origin: "bottom",
            distance: "30px",
            duration: 800,
            delay: 150,
            reset: false
        });

        sr.reveal(".section-title", { origin: "left" });
        sr.reveal(".home-info", { delay: 200 });
        sr.reveal(".home-img", { delay: 300 });
        sr.reveal(".glass-card", { interval: 100 });
    }
});

// 5. Light/Dark Theme Switcher Toggle Function
function toggleTheme() {
    const body = document.body;
    body.classList.toggle("light-theme");
}

// 6. Active Nav Link on Scroll (ScrollSpy)
const sections = document.querySelectorAll("section[id]");
const sidebarLinks = document.querySelectorAll(".aside .nav a");

const observerOptions = {
    root: null,
    rootMargin: "-20% 0px -70% 0px", // Triggers when the section is near the upper-middle portion of the viewport
    threshold: 0
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
        if (entry.isIntersecting) {
            const currentId = entry.target.getAttribute("id");

            sidebarLinks.forEach((link) => {
                link.classList.remove("active");
                if (link.getAttribute("href") === `#${currentId}`) {
                    link.classList.add("active");
                }
            });
        }
    });
}, observerOptions);

sections.forEach((section) => observer.observe(section));
(function () {
  const body = document.body;

  function showToast(message, type = "success") {
    const old = document.querySelector(".toast");
    if (old) old.remove();

    const toast = document.createElement("div");
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 2400);
  }

  window.SkyMayToast = { show: showToast };

  document.querySelectorAll(".year").forEach((node) => {
    node.textContent = String(new Date().getFullYear());
  });

  const themeBtn = document.getElementById("themeToggle");
  const savedTheme = localStorage.getItem("skymay_theme");
  if (savedTheme === "dark") body.classList.add("dark");
  if (themeBtn) {
    themeBtn.addEventListener("click", function () {
      body.classList.toggle("dark");
      localStorage.setItem("skymay_theme", body.classList.contains("dark") ? "dark" : "light");
    });
  }

  const navLinks = document.querySelectorAll(".nav-main a[data-nav]");
  const currentNav = body.getAttribute("data-nav");
  navLinks.forEach((link) => {
    if (link.getAttribute("data-nav") === currentNav) link.classList.add("active");
  });

  const navToggle = document.getElementById("navToggle");
  const mainNav = document.getElementById("mainNav");
  if (navToggle && mainNav) {
    navToggle.addEventListener("click", function () {
      mainNav.classList.toggle("open");
    });
  }

  document.querySelectorAll(".dropbtn").forEach((btn) => {
    btn.addEventListener("click", function () {
      const parent = btn.closest(".dropdown");
      if (parent) parent.classList.toggle("open");
    });
  });

  const reveals = document.querySelectorAll(".reveal");
  if ("IntersectionObserver" in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) entry.target.classList.add("show");
      });
    }, { threshold: 0.1 });
    reveals.forEach((el) => observer.observe(el));
  } else {
    reveals.forEach((el) => el.classList.add("show"));
  }

  const searchInput = document.getElementById("searchInput");
  const categoryFilter = document.getElementById("categoryFilter");
  const filterResult = document.getElementById("filterResultMsg");
  function filterEvents() {
    const query = (searchInput?.value || "").trim().toLowerCase();
    const category = (categoryFilter?.value || "all").toLowerCase();
    let visibleCount = 0;

    document.querySelectorAll(".event-item").forEach((item) => {
      const title = (item.getAttribute("data-title") || "").toLowerCase();
      const itemCategory = (item.getAttribute("data-category") || "").toLowerCase();
      const isVisible = title.includes(query) && (category === "all" || itemCategory === category);
      item.hidden = !isVisible;
      if (isVisible) visibleCount += 1;
    });

    if (filterResult) {
      filterResult.textContent = visibleCount === 0
        ? "No events found. Try a different search or category."
        : `Showing ${visibleCount} event${visibleCount === 1 ? "" : "s"}.`;
    }
  }
  if (searchInput) searchInput.addEventListener("input", filterEvents);
  if (categoryFilter) categoryFilter.addEventListener("change", filterEvents);
  if (searchInput || categoryFilter) filterEvents();

  const titleInput = document.getElementById("eventTitle");
  const dateInput = document.getElementById("eventDate");
  const venueInput = document.getElementById("eventVenue");
  const descInput = document.getElementById("eventDesc");
  const pTitle = document.getElementById("previewTitle");
  const pDate = document.getElementById("previewDate");
  const pVenue = document.getElementById("previewVenue");
  const pDesc = document.getElementById("previewDesc");
  function syncPreview() {
    if (!pTitle) return;
    pTitle.textContent = titleInput?.value.trim() || "Your event title";
    pDate.textContent = dateInput?.value || "Event date";
    pVenue.textContent = venueInput?.value.trim() || "Event venue";
    pDesc.textContent = descInput?.value.trim() || "Event description preview will appear here.";
  }
  [titleInput, dateInput, venueInput, descInput].forEach((el) => {
    if (el) el.addEventListener("input", syncPreview);
  });
  syncPreview();

  document.querySelectorAll(".toggle-password").forEach((toggle) => {
    toggle.addEventListener("click", function () {
      const targetId = toggle.getAttribute("data-target");
      const input = document.getElementById(targetId);
      if (!input) return;
      input.type = input.type === "password" ? "text" : "password";
      toggle.textContent = input.type === "password" ? "Show" : "Hide";
    });
  });

})();

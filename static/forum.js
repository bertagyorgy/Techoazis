document.addEventListener("DOMContentLoaded", () => {
    console.log("Forum.js betöltve, inicializálás...");

    // ===========================
    // 1. KOMMENTEK MEGNYITÁSA
    // ===========================
    document.querySelectorAll(".show-comments-btn").forEach(btn => {
        btn.addEventListener("click", async () => {
            const postId = btn.dataset.post;
            const container = document.getElementById("comments-" + postId);

            // Ha már nyitva van -> bezárjuk
            if (container.classList.contains("open")) {
                container.classList.remove("open");
                container.innerHTML = "";
                btn.textContent = "Kommentek megnyitása";
                return;
            }

            // Betöltés
            try {
                const response = await fetch("/Techoazis/app/get_comments.php?post_id=" + postId);
                // Ellenőrizzük, hogy tényleg JSON jött-e
                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    throw new Error("A szerver nem JSON-t küldött! (PHP hiba lehet)");
                }

                const data = await response.json();

                if (data.success) {
                    container.innerHTML = generateCommentsHTML(data.comments);
                    container.classList.add("open");
                    btn.textContent = "Kommentek elrejtése";
                } else {
                    console.error("Szerver hiba:", data.message);
                }
            } catch (error) {
                console.error("Hiba a kommentek betöltésekor:", error);
                alert("Hiba történt a kommentek betöltésekor. Részletek a konzolon (F12).");
            }
        });
    });

    // ===========================
    // 2. KOMMENT KÜLDÉSE
    // ===========================
    document.querySelectorAll(".comment-form").forEach(form => {
        form.addEventListener("submit", async (e) => {
            e.preventDefault();

            const textarea = form.querySelector("textarea");
            const content = textarea.value.trim();
            const postId = form.dataset.post;
            const container = document.getElementById("comments-" + postId);

            if (!content) return;

            try {
                const response = await fetch("/Techoazis/app/add_comment.php", {
                    method: "POST",
                    body: new URLSearchParams({
                        post_id: postId,
                        content: content
                    }),
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                });

                const result = await response.json();

                if (result.success) {
                    textarea.value = ""; 

                    // Újratöltjük a kommenteket
                    const load = await fetch("/Techoazis/app/get_comments.php?post_id=" + postId);
                    const data = await load.json();

                    if (data.success) {
                        container.innerHTML = generateCommentsHTML(data.comments);
                        if (!container.classList.contains("open")) container.classList.add("open");
                        
                        // Gomb szöveg frissítése
                        const btn = document.querySelector(`.show-comments-btn[data-post="${postId}"]`);
                        if(btn) btn.textContent = "Kommentek elrejtése";

                        // Számláló frissítése
                        updateCommentCountInDOM(postId, data.comments.length);
                    }
                } else {
                    alert("Hiba: " + (result.message || "Ismeretlen hiba"));
                }
            } catch (error) {
                console.error("Küldési hiba:", error);
            }
        });
    });

    // ===========================
    // 3. KOMMENT SZÁMLÁLÓK BETÖLTÉSE
    // ===========================
    const counters = document.querySelectorAll(".comment-count");
    if (counters.length > 0) {
        counters.forEach(async counter => {
            const postId = counter.id.replace("comment-count-", "");
            try {
                // Útvonal: .php kiterjesztéssel!
                const response = await fetch("/Techoazis/app/get_comment_count.php?post_id=" + postId);
                const data = await response.json();
                
                // Biztonsági ellenőrzés: Ha nincs count, akkor 0 legyen
                const countNum = (data.count !== undefined) ? data.count : 0;
                
                counter.textContent = countNum + " komment";

            } catch (error) {
                console.error(`Hiba a számlálónál (Post: ${postId}):`, error);
                // Hiba esetén maradjon az eredeti szöveg (pl. "0 komment") vagy írjunk ki hibaüzenetet konzolra
            }
        });
    }

    // ===========================
    // 4. KÉP PREVIEW
    // ===========================
    const fileInput = document.getElementById("postImages");
    const preview = document.getElementById("imagePreview");

    if (fileInput && preview) {
        let selectedFiles = [];

        fileInput.addEventListener("change", (e) => {
            const files = Array.from(e.target.files);
            if (selectedFiles.length + files.length > 3) {
                alert("Maximum 3 képet tölthetsz fel!");
                return;
            }
            files.forEach(file => selectedFiles.push(file));
            showPreview();
            updateInputFiles();
        });

        function showPreview() {
            preview.innerHTML = "";
            selectedFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const div = document.createElement("div");
                    div.classList.add("preview-item");
                    div.innerHTML = `
                        <img src="${e.target.result}" class="preview-thumb">
                        <button type="button" class="remove-image" data-index="${index}">
                            <i style="color:#fff;" class="fa-solid fa-x"></i>
                        </button>
                    `;
                    preview.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        }

        function updateInputFiles() {
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => dataTransfer.items.add(file));
            fileInput.files = dataTransfer.files;
        }

        preview.addEventListener("click", (e) => {
            const btn = e.target.closest(".remove-image");
            if (!btn) return;
            selectedFiles.splice(btn.dataset.index, 1);
            showPreview();
            updateInputFiles();
        });
    }

}); // --- DOMContentLoaded VÉGE ---

// Helper funkciók (ezek lehetnek kívül)

// ===========================
// HELPER: KOMMENT HTML GENERÁLÓ (DEBUG VERZIÓ)
// ===========================
function generateCommentsHTML(comments) {
    console.log("Beérkezett kommentek:", comments); // EZT NÉZD MEG A KONZOLON!

    if (!comments || comments.length === 0) {
        return "<p class='no-comments'>Nincs még komment...</p>";
    }

    return comments.map(c => {
        // Ellenőrizzük, hogy léteznek-e az adatok, ha nem, helyettesítjük
        const safeUsername = c.username || "Ismeretlen";
        const safeContent = c.content || "<i>Hiba: Nincs tartalom</i>";
        const safeDate = c.created_at || "";
        const safeImage = c.profile_image || "./images/default_avatar.png";

        return `
        <div class="comment-item">
            <img class="comment-avatar" src="${safeImage}" alt="avatar">
            <div class="comment-body">
                <span class="comment-meta">
                    <strong>${safeUsername}</strong> • <span>${safeDate}</span>
                </span>
                <p>${safeContent}</p>
            </div>
        </div>
        `;
    }).join("");
}

function updateCommentCountInDOM(postId, count) { 
    const counter = document.getElementById("comment-count-" + postId); 
    if (counter) { 
        counter.textContent = count + " komment"; 
    } else {
        console.warn("Nem található a számláló elem ezzel az ID-val: comment-count-" + postId);
    }
}
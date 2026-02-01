document.addEventListener("DOMContentLoaded", () => {

    // ===========================
    // 1. KOMMENTEK MEGNYITÁSA / BEZÁRÁSA
    // ===========================
    document.querySelectorAll(".show-comments-btn").forEach(btn => {
        btn.addEventListener("click", async () => {
            const postId = btn.dataset.post;
            const container = document.getElementById("comments-" + postId);
            const icon = btn.querySelector(".comment-caret");

            // BEZÁRÁS
            if (container.classList.contains("open")) {
                container.classList.remove("open");
                container.innerHTML = "";

                icon.classList.remove("fa-caret-up");
                icon.classList.add("fa-caret-down");
                return;
            }

            // MEGNYITÁS
            try {
                const response = await fetch(
                    "/Techoazis/app/get_comments.php?post_id=" + postId
                );

                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    throw new Error("Nem JSON válasz érkezett");
                }

                const data = await response.json();

                if (data.success) {
                    container.innerHTML = generateCommentsHTML(data.comments);
                    container.classList.add("open");

                    icon.classList.remove("fa-caret-down");
                    icon.classList.add("fa-caret-up");

                    // Biztonsági számláló frissítés
                    updateCommentCountInDOM(postId, data.comments.length);
                }
            } catch (error) {
                console.error("Komment betöltési hiba:", error);
                alert("Hiba történt a kommentek betöltésekor.");
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
                const response = await fetch(
                    "/Techoazis/app/add_comment.php",
                    {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: new URLSearchParams({
                            post_id: postId,
                            content: content
                        })
                    }
                );

                const result = await response.json();

                if (result.success) {
                    textarea.value = "";

                    const reload = await fetch(
                        "/Techoazis/app/get_comments.php?post_id=" + postId
                    );
                    const data = await reload.json();

                    if (data.success) {
                        container.innerHTML = generateCommentsHTML(data.comments);
                        container.classList.add("open");

                        const btn = document.querySelector(
                            `.show-comments-btn[data-post="${postId}"]`
                        );
                        const icon = btn.querySelector(".comment-caret");

                        icon.classList.remove("fa-caret-down");
                        icon.classList.add("fa-caret-up");

                        updateCommentCountInDOM(postId, data.comments.length);
                    }
                }
            } catch (error) {
                console.error("Komment küldési hiba:", error);
            }
        });
    });

    // ===========================
    // 3. KOMMENT SZÁMLÁLÓK BETÖLTÉSE
    // ===========================
    document.querySelectorAll(".comment-count").forEach(async counter => {
        const postId = counter.id.replace("comment-count-", "");

        try {
            const response = await fetch(
                "/Techoazis/app/get_comment_count.php?post_id=" + postId
            );
            const data = await response.json();

            counter.textContent = data.count ?? 0;
        } catch (error) {
            console.error(`Számláló hiba (post ${postId}):`, error);
        }
    });


    // ===========================
    // SEGÉDFÜGGVÉNY
    // ===========================
    function updateCommentCountInDOM(postId, count) {
        const counter = document.getElementById("comment-count-" + postId);
        if (counter) {
            counter.textContent = count;
        }
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
                    <strong>${safeUsername}</strong> <span>${safeDate}</span>
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
// create post toogle
document.querySelector('.display-btn').addEventListener('click', () => {
    document.querySelector('.create-post-bar').classList.toggle('active');
});
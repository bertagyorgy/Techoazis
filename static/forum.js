document.addEventListener("DOMContentLoaded", () => {

    // ===========================
    // KOMMENTEK MEGNYITÁSA
    // ===========================
    document.querySelectorAll(".show-comments-btn").forEach(btn => {
        btn.addEventListener("click", async () => {
            const postId = btn.dataset.post;
            const container = document.getElementById("comments-" + postId);

            // már látható → elrejt
            if (container.classList.contains("open")) {
                container.classList.remove("open");
                container.innerHTML = "";
                btn.textContent = "Kommentek megnyitása";
                return;
            }

            // betöltés AJAX-szal
            const response = await fetch("./app/get_comments.php?post_id=" + postId);
            const data = await response.json();

            if (data.success) {
                container.innerHTML = generateCommentsHTML(data.comments);
                container.classList.add("open");
                btn.textContent = "Kommentek elrejtése";
            }
        });
    });

    // ===========================
    // KOMMENT KÜLDÉSE (AJAX)
    // ===========================
    document.querySelectorAll(".comment-form").forEach(form => {

        form.addEventListener("submit", async (e) => {
            e.preventDefault();

            const textarea = form.querySelector("textarea");
            const content = textarea.value.trim();
            const postId = form.dataset.post;
            const container = document.getElementById("comments-" + postId);

            if (!content) return;

            const response = await fetch("./app/add_comment.php", {
                method: "POST",
                body: new URLSearchParams({
                    post_id: postId,
                    content: content
                })
            });

            const result = await response.json();

            if (result.success) {
                textarea.value = "";

                // Újratöltjük a kommenteket
                const load = await fetch("./app/get_comments.php?post_id=" + postId);
                const data = await load.json();

                if (data.success) {
                    container.innerHTML = generateCommentsHTML(data.comments);
                    container.classList.add("open");
                    //kommentek számának frissítése
                    updateCommentCount(postId, data.comments.length);
                }
            }
        });
    });
    // ===========================
    // KÉP PREVIEW (AJAX)
    // ===========================
    const fileInput = document.getElementById("postImages");
    const preview = document.getElementById("imagePreview");

    let selectedFiles = [];

    fileInput.addEventListener("change", (e) => {
        const files = Array.from(e.target.files);

        if (selectedFiles.length + files.length > 3) {
            alert("Maximum 3 képet tölthetsz fel!");
            return;
        }

        files.forEach(file => {
            selectedFiles.push(file);
            showPreview();
        });

        updateInputFiles();
    });

    // Preview megjelenítés
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
                        <i style="color:#fff;"class="fa-solid fa-x"></i>
                    </button>
                `;

                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }

    // Input fájlainak frissítése
    function updateInputFiles() {
        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => dataTransfer.items.add(file));
        fileInput.files = dataTransfer.files;
    }

    // Törlés egy képnél
    preview.addEventListener("click", (e) => {
        if (!e.target.classList.contains("remove-image")) return;

        const index = e.target.dataset.index;
        selectedFiles.splice(index, 1);

        showPreview();
        updateInputFiles();
    });
});





// ===========================
// KOMMENT HTML GENERÁLÓ
// ===========================
function generateCommentsHTML(comments) {
    if (comments.length === 0) {
        return "<p class='no-comments'>Nincs még komment...</p>";
    }

    return comments.map(c => `
        <div class="comment-item">
            <img class="comment-avatar" src="${c.profile_image}" alt="">
            <div class="comment-body">
                <span class="comment-meta">${c.username} • <span>${c.created_at}</span></span>
                <p>${c.content}</p>
            </div>
        </div>
    `).join("");
}

// ===========================
// KOMMENT SZÁMOK BETÖLTÉSE OLDALBETÖLTÉSKOR
// ===========================
document.querySelectorAll(".comment-count").forEach(async counter => {
    const postId = counter.id.replace("comment-count-", "");

    const response = await fetch("./app/get_comment_count.php?post_id=" + postId);
    const data = await response.json();

    if (data.success) {
        counter.textContent = data.count + " komment";
    }
});


// =========================== 
// KOMMENT SZÁMLÁLÓ FRISSÍTŐ
//  =========================== 
function updateCommentCount(postId, count) { 
    const counter = document.getElementById("comment-count-" + postId); 
    if (counter) { 
        counter.textContent = count + " komment"; 
    } 
}
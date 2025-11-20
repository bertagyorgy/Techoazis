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
                }
            }
        });
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

document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".comment-form").forEach(form => {
        
        form.addEventListener("submit", async (e) => {
            e.preventDefault();

            const textarea = form.querySelector("textarea");
            const content = textarea.value.trim();
            const postId = form.dataset.post;

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
                alert("Komment elküldve! (Később betöltjük AJAX-live frissítéssel)");
            } else {
                alert("Hiba történt: " + result.message);
            }
        });
    });
});

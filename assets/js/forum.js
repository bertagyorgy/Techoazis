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
                const response = await fetch(APP_BASE_URL + "/app/get_comments.php?post_id=" + postId);
                const rawText = await response.text();
                
                // Megkeressük a JSON kezdetét (az első '{' karaktert)
                const jsonStart = rawText.indexOf('{');
                if (jsonStart === -1) {
                    console.error("Nem érkezett JSON adat a szervertől. Válasz:", rawText);
                    return;
                }

                // Csak a JSON részt vágjuk ki és alakítjuk objektummá
                const cleanJson = rawText.substring(jsonStart);
                const data = JSON.parse(cleanJson);

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
                const response = await fetch(APP_BASE_URL + "/app/add_comment.php",
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

                    const reload = await fetch(APP_BASE_URL + "/app/get_comments.php?post_id=" + postId
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
            const response = await fetch(APP_BASE_URL + "/app/get_comment_count.php?post_id=" + postId
            );
            const data = await response.json();

            counter.textContent = data.count ?? 0;
        } catch (error) {
            console.error(`Számláló hiba (post ${postId}):`, error);
        }
    });

    // ===========================
    // 4. KOMMENT TEXTAREA VÁLTOZTATÓ (MINDEN MEZŐRE)
    // ===========================
    const commentInputs = document.querySelectorAll('.comment-input');

    commentInputs.forEach(textarea => {
        textarea.addEventListener('input', function () {
            // Alaphelyzetbe állítás a törléskori visszaugráshoz
            this.style.height = '44px'; 
            
            // Új magasság kiszámítása (de max 140px)
            const newHeight = Math.min(this.scrollHeight, 140);
            this.style.height = newHeight + 'px';

            // Görgetősáv kezelése
            if (this.scrollHeight > 140) {
                this.style.overflowY = 'auto';
            } else {
                this.style.overflowY = 'hidden';
            }
        });
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
    // 5. KÉP PREVIEW
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

// képek kivetítése (overlay / dialog)
(() => {
  const dialog = document.getElementById('imgModal');
  const modalImg = document.getElementById('imgModalImage');
  const closeBtn = dialog?.querySelector('.img-modal-close');

  if (!dialog || !modalImg || !closeBtn) return;

  // 1x1 átlátszó pixel: sose legyen “törött kép” állapot
  const TRANSPARENT_PIXEL =
    'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==';

  function openImage(src, alt = '') {
    modalImg.style.visibility = 'visible';
    modalImg.src = src;
    modalImg.alt = alt || 'Nagy kép';
    if (!dialog.open) dialog.showModal();
  }

  function closeImage() {
    if (!dialog.open) return;

    // azonnal eltüntetjük, hogy ne villanjon se broken icon, se alt
    modalImg.style.visibility = 'hidden';
    dialog.close();
  }

  // amikor ténylegesen bezárult: biztonságos reset
  dialog.addEventListener('close', () => {
    modalImg.src = TRANSPARENT_PIXEL;
    modalImg.alt = '';

    // következő tickben vissza, hogy a következő open-nél rendben legyen
    setTimeout(() => {
      modalImg.style.visibility = 'visible';
    }, 0);
  });

  // Kattintás képre -> nyit
  document.addEventListener('click', (e) => {
    const img = e.target.closest('img.js-zoomable');
    if (!img) return;

    e.preventDefault();
    openImage(img.src, img.alt);
  });

  // X gomb
  closeBtn.addEventListener('click', closeImage);

  // háttérre katt -> bezár
  dialog.addEventListener('click', (e) => {
    if (e.target === dialog) closeImage();
  });

  // ESC (biztosra megyünk)
  dialog.addEventListener('cancel', (e) => {
    e.preventDefault();
    closeImage();
  });
})();



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
        const safeUsername = c.username || "Ismeretlen";
        // Ha van az adatbázisban DiceBear URL, azt fogja használni, 
        // ha üres, akkor jön a régi alapértelmezett kép.
        const safeImage = c.profile_image || "./images/default_avatar.png"; 
        const safeDate = c.created_at || "";

        return `
        <div class="comment-item">
            <img class="comment-avatar" src="${safeImage}" alt="avatar">
            <div class="comment-body">
                <span class="comment-meta">
                    <strong>${safeUsername}</strong> <span>${safeDate}</span>
                </span>
                <p>${c.content}</p>
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
const displayBtn = document.querySelector('.display-btn');

if (displayBtn) {
    displayBtn.addEventListener('click', () => {
        const postBar = document.querySelector('.create-post-bar');
        if (postBar) {
            postBar.classList.toggle('active');
        }
    });
}

// 1. Segédfüggvény az URL-ek normalizálásához (pl. www. kezdetnél http hozzáadása)
function normalizeUrl(url) {
    if (!/^https?:\/\//i.test(url)) {
        return 'https://' + url;
    }
    return url;
}

function linkify(container) {
    // 1. Megkeressük az összes olyan elemet a konténeren belül, amiben szöveg lehet
    // Ide tartozik maga a konténer és a .more-content span is.
    const targets = [container];
    const moreContent = container.querySelector('.more-content');
    if (moreContent) targets.push(moreContent);

    const urlRegex = /(https?:\/\/[^\s]+|www\.[^\s]+\.[a-z]{2,})/gi;

    targets.forEach(target => {
        // Csak a közvetlen szöveges gyerekeket nézzük, hogy ne linkesítsünk duplán
        const nodes = Array.from(target.childNodes);
        
        nodes.forEach(node => {
            if (node.nodeType === Node.TEXT_NODE) {
                const text = node.nodeValue;
                
                if (urlRegex.test(text)) {
                    const span = document.createElement('span');
                    const newHTML = text.replace(urlRegex, (url) => {
                        let href = url.startsWith('http') ? url : 'https://' + url;
                        // Itt adjuk hozzá a tördelést segítő stílust és az új ablakot
                        return `<a href="${href}" target="_blank" rel="noopener noreferrer" class="post-link">${url}</a>`;
                    });
                    
                    span.innerHTML = newHTML;
                    // Kicseréljük a nyers szöveget a linkesített HTML-re
                    node.parentNode.replaceChild(span, node);
                    
                    // Kicsomagoljuk a span-ból, hogy ne rontsa a designt (opcionális)
                    while (span.firstChild) {
                        span.parentNode.insertBefore(span.firstChild, span);
                    }
                    span.remove();
                }
            }
        });
    });
}

function toggleReadMore(event, postId) {
    event.preventDefault();
    const container = document.getElementById('postText-' + postId);
    const moreContent = container.querySelector('.more-content');
    const link = event.target;

    if (container.classList.contains('expanded')) {
        moreContent.style.display = "none";
        link.textContent = " ...Több";
        container.classList.remove('expanded');
    } else {
        // Fontos: inline-t használunk, hogy ne törje meg a szövegfolyamot
        moreContent.style.display = "inline"; 
        link.textContent = " ...Kevesebb";
        container.classList.add('expanded');
    }
}

// Inicializálás
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".text-container").forEach(linkify);
});
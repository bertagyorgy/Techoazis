<?php
// app/user_stats.php
// mysqli + prepared; IDs never 0 used.

function refreshUserStats(mysqli $conn, int $user_id): void
{
    if ($user_id < 1) return;

    // Posztok
    $stmt = $conn->prepare("SELECT COUNT(*) FROM posts WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($total_posts);
    $stmt->fetch();
    $stmt->close();

    // Kommentek
    $stmt = $conn->prepare("SELECT COUNT(*) FROM comments WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($total_comments);
    $stmt->fetch();
    $stmt->close();

    // Eladott termékek: deals alapján (legpontosabb)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM deals WHERE seller_user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($sold_items);
    $stmt->fetch();
    $stmt->close();

    // Vásárolt termékek: deals alapján
    $stmt = $conn->prepare("SELECT COUNT(*) FROM deals WHERE buyer_user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($bought_items);
    $stmt->fetch();
    $stmt->close();

    // Mentés users-be
    $stmt = $conn->prepare("
        UPDATE users
        SET total_posts = ?, total_comments = ?, sold_items = ?, bought_items = ?
        WHERE user_id = ?
    ");
    $stmt->bind_param("iiiii", $total_posts, $total_comments, $sold_items, $bought_items, $user_id);
    $stmt->execute();
    $stmt->close();
}

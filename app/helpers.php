<?php
function make_slug(string $username): string {
    $s = mb_strtolower(trim($username), 'UTF-8');

    // magyar ékezetek
    $map = [
        'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ö'=>'o','ő'=>'o','ú'=>'u','ü'=>'u','ű'=>'u',
        'Á'=>'a','É'=>'e','Í'=>'i','Ó'=>'o','Ö'=>'o','Ő'=>'o','Ú'=>'u','Ü'=>'u','Ű'=>'u'
    ];
    $s = strtr($s, $map);

    // szóközök -> underscore
    $s = preg_replace('/\s+/', '_', $s);

    // csak [a-z0-9_]
    $s = preg_replace('/[^a-z0-9_]/', '', $s);

    // több underscore összevonása
    $s = preg_replace('/_+/', '_', $s);

    return trim($s, '_');
}
function unique_slug(mysqli $conn, string $base, int $user_id): string {
    $slug = $base;
    $i = 2;

    while (true) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username_slug = ? AND user_id != ? LIMIT 1");
        $stmt->bind_param("si", $slug, $user_id);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$exists) return $slug;
        $slug = $base . "_" . $i;
        $i++;
    }
}

?>

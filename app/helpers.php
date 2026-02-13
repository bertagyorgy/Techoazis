<?php
function make_slug(string $username): string {
    // Kisbetűsítés és szélekről whitespace eltávolítás
    $s = mb_strtolower(trim($username), 'UTF-8');

    // Magyar ékezetek és egyéb speciális karakterek átalakítása
    $map = [
        'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ö'=>'o','ő'=>'o','ú'=>'u','ü'=>'u','ű'=>'u',
        'Á'=>'a','É'=>'e','Í'=>'i','Ó'=>'o','Ö'=>'o','Ő'=>'o','Ú'=>'u','Ü'=>'u','Ű'=>'u',
        // Opcionálisan bővíthető más karakterekkel is
    ];
    $s = strtr($s, $map);

    // Szóközök és speciális karakterek cseréje kötőjelre
    // Minden, ami nem betű vagy szám, kötőjel lesz
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);

    // Több egymást követő kötőjel összevonása egyre
    $s = preg_replace('/-+/', '-', $s);

    // Kötőjelek levágása a szöveg elejéről és végéről
    return trim($s, '-');
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
        
        // Itt is kötőjellel fűzzük hozzá a sorszámot
        $slug = $base . "-" . $i;
        $i++;
    }
}
?>
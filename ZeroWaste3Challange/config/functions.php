<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isUser() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'user';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function getUserById($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getUserPoints($userId) {
    $user = getUserById($userId);
    return $user ? $user['points'] : 0;
}

function updateUserPoints($userId, $points) {
    $db = getDB();
    $stmt = $db->prepare("UPDATE users SET points = points + ? WHERE id = ?");
    return $stmt->execute([$points, $userId]);
}

function getBadge($points) {
    if ($points >= 1000) return ['name' => 'Eco Champion', 'color' => 'gold', 'icon' => 'trophy'];
    if ($points >= 500) return ['name' => 'Green Warrior', 'color' => 'silver', 'icon' => 'shield'];
    if ($points >= 200) return ['name' => 'Eco Enthusiast', 'color' => 'bronze', 'icon' => 'star'];
    if ($points >= 50) return ['name' => 'Eco Starter', 'color' => 'green', 'icon' => 'leaf'];
    return ['name' => 'Newcomer', 'color' => 'gray', 'icon' => 'seedling'];
}

function getMotivationalMessage($name, $todayWaste = 0) {
    $messages = [
        "Wow, $name, keren! Kamu sudah berkontribusi untuk bumi yang lebih hijau!",
        "Hebat, $name! Setiap langkah kecilmu sangat berarti!",
        "Luar biasa, $name! Terus jaga semangat eco-friendly-mu!",
        "$name, kamu adalah pahlawan lingkungan!",
        "Terima kasih $name, bumi tersenyum karena aksimu!"
    ];
    
    if ($todayWaste == 0) {
        return "Wow, $name, keren! Kamu hari ini tidak menghasilkan sampah sama sekali!";
    }
    
    return $messages[array_rand($messages)];
}

function uploadFile($file, $folder = 'sampah') {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $maxSize = 5 * 1024 * 1024;
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error'];
    }
    
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'File type not allowed'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File too large (max 5MB)'];
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowedExtensions)) {
        return ['success' => false, 'message' => 'File extension not allowed'];
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $realMime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($realMime, $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file content'];
    }
    
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $uploadPath = UPLOAD_PATH . $folder . '/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['success' => true, 'filename' => $filename];
    }
    
    return ['success' => false, 'message' => 'Failed to move file'];
}

function formatDate($date) {
    return date('d M Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('d M Y H:i', strtotime($datetime));
}

function getWasteCategories() {
    return [
        'plastik' => 'Plastik',
        'kertas' => 'Kertas',
        'logam' => 'Logam',
        'kaca' => 'Kaca',
        'organik' => 'Organik',
        'elektronik' => 'Elektronik',
        'tekstil' => 'Tekstil',
        'lainnya' => 'Lainnya'
    ];
}

function alert($message, $type = 'success') {
    $_SESSION['alert'] = ['message' => $message, 'type' => $type];
}

function showAlert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        unset($_SESSION['alert']);
        return '<div class="alert alert-' . $alert['type'] . ' alert-dismissible fade show" role="alert">
            ' . $alert['message'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
    }
    return '';
}
?>

<?php
$resortId = $_SESSION['user_reg'];

$resort = $conn->prepare("SELECT * FROM tb_resort WHERE resortid = ?");
$resort->execute([$resortId]);
$resortData = $resort->fetch();

if (!$resortData) {
    echo '<script>window.location.href = "index.php";</script>';
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_image'])) {
    $imageId = $_POST['image_id'];
    $del = $conn->prepare("SELECT file_name FROM images WHERE id = ? AND resortid = ? AND resort_room_id IS NULL");
    $del->execute([$imageId, $resortId]);
    $img = $del->fetch();
    if($img) {
        $filePath = 'uploads_flow/' . $img['file_name'];
        if(file_exists($filePath)) unlink($filePath);
        $conn->prepare("DELETE FROM images WHERE id = ?")->execute([$imageId]);
    }
}

$images = $conn->prepare("SELECT * FROM images WHERE resortid = ? AND resort_room_id IS NULL ORDER BY id DESC");
$images->execute([$resortId]);
$allImages = $images->fetchAll();
?>

<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Resort Gallery</h1>
            <p class="text-gray-500">Manage images for <?php echo htmlspecialchars($resortData['resortname']); ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Upload Images</h2>
            <form action="api/upload.php" method="POST" enctype="multipart/form-data" id="uploadForm">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Choose Images</label>
                    <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center hover:border-teal-500 transition cursor-pointer" onclick="document.getElementById('fileInput').click()">
                        <i class="bi bi-cloud-arrow-up text-4xl text-gray-300 mb-2"></i>
                        <p class="text-gray-500 text-sm" id="fileLabel">Click to select files</p>
                        <input type="file" name="files[]" id="fileInput" class="hidden" multiple accept="image/*" onchange="updateFileLabel(this)">
                    </div>
                </div>
                <button type="submit" name="submit" class="w-full py-3 bg-gradient-to-r from-teal-500 to-teal-600 text-white rounded-xl font-semibold hover:shadow-lg transition">
                    <i class="bi bi-upload me-2"></i> Upload Images
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-800">
                    Gallery Images 
                    <span class="text-sm font-normal text-gray-500">(<?php echo count($allImages); ?>)</span>
                </h2>
            </div>
            <?php if(count($allImages) > 0): ?>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <?php foreach($allImages as $img): ?>
                <div class="relative group rounded-xl overflow-hidden bg-gray-100 aspect-[4/3]">
                    <img src="uploads_flow/<?php echo htmlspecialchars($img['file_name']); ?>" 
                         class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                         onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center text-gray-400\'><i class=\'bi bi-image\' style=\'font-size:2rem\'></i></div>'">
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition flex items-center justify-center opacity-0 group-hover:opacity-100">
                        <form method="POST" onsubmit="return confirm('Delete this image?')">
                            <input type="hidden" name="delete_image" value="1">
                            <input type="hidden" name="image_id" value="<?php echo $img['id']; ?>">
                            <button type="submit" class="w-10 h-10 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition shadow-lg">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-12">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="bi bi-images text-gray-400 text-3xl"></i>
                </div>
                <p class="text-gray-500 font-medium">No images yet</p>
                <p class="text-gray-400 text-sm">Upload images to showcase your resort</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function updateFileLabel(input) {
    const label = document.getElementById('fileLabel');
    if(input.files.length > 0) {
        label.textContent = input.files.length + ' file(s) selected';
    } else {
        label.textContent = 'Click to select files';
    }
}
</script>

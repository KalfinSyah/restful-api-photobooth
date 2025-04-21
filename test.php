<?php 
require_once 'private/photobooth_repository.php';
$photos = PhotoboothRepository::getPhotos("kalfin");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create API Key</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .photo-container {
            max-width: 600px;
            margin: 20px auto;
        }
        .photo-thumbnail {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin: 10px 0;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container photo-container">
        <?php if (!empty($photos)): ?>
            <div class="row">
                <?php foreach ($photos as $photo): ?>
                    <div class="col-12 col-md-6 col-lg-4 mb-4">
                        <?php if (isset($photo['image_data'])): ?>
                            <!-- Add width and height attributes for aspect ratio -->
                            <img src="<?= $photo['image_data'] ?>" 
                                 alt="User photo" 
                                 class="photo-thumbnail"
                                 style="width: 100%; height: 300px; object-fit: cover;">
                        <?php else: ?>
                            <div class="text-danger">Invalid image data</div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center text-muted mt-5">No photos found</p>
        <?php endif; ?>
    </div>
</body>
</html>
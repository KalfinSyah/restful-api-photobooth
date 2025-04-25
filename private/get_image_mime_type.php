<?php
function getImageMimeType($filePath) {
    $imageInfo = getimagesize($filePath);
    return $imageInfo['mime'];
}
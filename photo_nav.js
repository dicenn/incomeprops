function nextPhoto() {
    if (currentPhotoIndex < photos.length - 1) {  
        currentPhotoIndex++;
        document.getElementById('currentPhoto').src = photos[currentPhotoIndex];
    }
}

function prevPhoto() {
    if (currentPhotoIndex > 0) { 
        currentPhotoIndex--;
        document.getElementById('currentPhoto').src = photos[currentPhotoIndex];
    }
}
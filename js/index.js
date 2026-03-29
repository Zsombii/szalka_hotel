function changeMainImage(roomId, imageSrc) {
    document.getElementById('mainImage' + roomId).src = imageSrc;
    
    var thumbnails = document.querySelectorAll('#roomModal' + roomId + ' .modal-gallery img');
    thumbnails.forEach(function(img) {
        img.classList.remove('active');
    });
    
    event.target.classList.add('active');
}
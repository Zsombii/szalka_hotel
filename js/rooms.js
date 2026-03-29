    function openRoomModal(roomId) {
        document.getElementById('roomModal' + roomId).style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeRoomModal(roomId) {
        document.getElementById('roomModal' + roomId).style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    function changeMainImage(roomId, imageSrc) {
        document.getElementById('mainImage' + roomId).src = imageSrc;
        
        var thumbnails = document.querySelectorAll('#roomModal' + roomId + ' .modal-gallery img');
        thumbnails.forEach(function(img) {
            img.classList.remove('active');
        });
        
        event.target.classList.add('active');
    }

    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            var modals = document.getElementsByClassName('modal');
            for (var i = 0; i < modals.length; i++) {
                if (event.target == modals[i]) {
                    modals[i].style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            }
        }
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            var modals = document.getElementsByClassName('modal');
            for (var i = 0; i < modals.length; i++) {
                if (modals[i].style.display === 'block') {
                    modals[i].style.display = 'none';
                    document.body.style.overflow = 'auto';
                    break;
                }
            }
        }
    });
        let slideIndices = {
            'slideshow1': 1,
            'slideshow2': 1,
            'slideshow3': 1,
            'slideshow4': 1
        };
        
        let autoSlideTimers = {};
        
        function initSlideshows() {
            for (let slideshowId in slideIndices) {
                showSlide(slideIndices[slideshowId], slideshowId);
                startAutoSlide(slideshowId);
            }
        }
        
        function startAutoSlide(slideshowId) {
            if (autoSlideTimers[slideshowId]) {
                clearInterval(autoSlideTimers[slideshowId]);
            }
            autoSlideTimers[slideshowId] = setInterval(function() {
                changeSlide(1, slideshowId);
            }, 4000);
        }
        
        function stopAutoSlide(slideshowId) {
            if (autoSlideTimers[slideshowId]) {
                clearInterval(autoSlideTimers[slideshowId]);
            }
        }
        
        function changeSlide(n, slideshowId) {
            stopAutoSlide(slideshowId);
            
            slideIndices[slideshowId] += n;
            showSlide(slideIndices[slideshowId], slideshowId);
            
            startAutoSlide(slideshowId);
        }
        
        function currentSlide(n, slideshowId) {
            stopAutoSlide(slideshowId);
            
            slideIndices[slideshowId] = n;
            showSlide(slideIndices[slideshowId], slideshowId);
            
            startAutoSlide(slideshowId);
        }
        
        function showSlide(n, slideshowId) {
            const slideshow = document.getElementById(slideshowId);
            const slides = slideshow.getElementsByClassName('slide');
            const dots = document.getElementById('nav-' + slideshowId).getElementsByClassName('slideshow-dot');
            
            if (n > slides.length) {
                slideIndices[slideshowId] = 1;
            }
            if (n < 1) {
                slideIndices[slideshowId] = slides.length;
            }
            
            for (let i = 0; i < slides.length; i++) {
                slides[i].classList.remove('active');
            }
            
            for (let i = 0; i < dots.length; i++) {
                dots[i].classList.remove('active');
            }
            
            slides[slideIndices[slideshowId] - 1].classList.add('active');
            dots[slideIndices[slideshowId] - 1].classList.add('active');
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            initSlideshows();
        });
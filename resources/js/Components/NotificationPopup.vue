<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import { router } from '@inertiajs/vue3';
import lottie from 'lottie-web';

const shownNotifications = ref(new Set());

// Load animation data once
const checkAnimation = {
    "nm":"animation","ddd":0,"h":1300,"w":1300,"meta":{"g":"LottieFiles AE 0.1.20"},"layers":[{"ty":4,"nm":"Tick","sr":1,"st":0,"op":120,"ip":0,"hd":false,"ddd":0,"bm":0,"hasMask":false,"ao":0,"ks":{"a":{"a":0,"k":[278.033,227.218,0],"ix":1},"s":{"a":1,"k":[{"o":{"x":0.333,"y":0},"i":{"x":0.667,"y":1},"s":[20,20,100],"t":19.533},{"s":[120.056,120.056,100],"t":38}],"ix":6},"sk":{"a":0,"k":0},"p":{"a":0,"k":[346.916,346.917,0],"ix":2},"r":{"a":1,"k":[{"o":{"x":0.333,"y":0},"i":{"x":0.667,"y":1},"s":[-165],"t":12},{"s":[0],"t":39}],"ix":10},"sa":{"a":0,"k":0},"o":{"a":1,"k":[{"o":{"x":0.167,"y":0.167},"i":{"x":0.833,"y":0.833},"s":[0],"t":0},{"o":{"x":0.167,"y":0.167},"i":{"x":0.833,"y":0.833},"s":[100],"t":3.115},{"s":[100],"t":20.796875}],"ix":11}},"ef":[],"shapes":[{"ty":"gr","bm":0,"hd":false,"mn":"ADBE Vector Group","nm":"Group 1","ix":1,"cix":2,"np":2,"it":[{"ty":"sh","bm":0,"hd":false,"mn":"ADBE Vector Shape - Group","nm":"Path 1","ix":1,"d":1,"ks":{"a":0,"k":{"c":false,"i":[[0,0],[0,0],[0,0]],"o":[[0,0],[0,0],[0,0]],"v":[[-153.033,0.588],[-51.404,102.218],[153.032,-102.218]]},"ix":2}},{"ty":"st","bm":0,"hd":false,"mn":"ADBE Vector Graphic - Stroke","nm":"Stroke 1","lc":2,"lj":2,"ml":1,"o":{"a":0,"k":100,"ix":4},"w":{"a":0,"k":55,"ix":5},"c":{"a":0,"k":[1,1,1],"ix":3}},{"ty":"tr","a":{"a":0,"k":[0,0],"ix":1},"s":{"a":0,"k":[100,100],"ix":3},"sk":{"a":0,"k":0,"ix":4},"p":{"a":0,"k":[278.033,227.218],"ix":2},"r":{"a":0,"k":0,"ix":6},"sa":{"a":0,"k":0,"ix":5},"o":{"a":0,"k":100,"ix":7}}]},{"ty":"tm","bm":0,"hd":false,"mn":"ADBE Vector Filter - Trim","nm":"Trim Paths 1","ix":2,"e":{"a":1,"k":[{"o":{"x":0.67,"y":0},"i":{"x":0.33,"y":1},"s":[0],"t":20.164},{"s":[100],"t":38.48046875}],"ix":2},"o":{"a":0,"k":0,"ix":3},"s":{"a":0,"k":0,"ix":1},"m":1}],"ind":1,"parent":4},{"ty":4,"nm":"Background Circle (Blue)","sr":1,"st":0,"op":120,"ip":0,"hd":false,"ddd":0,"bm":0,"hasMask":false,"ao":0,"ks":{"a":{"a":0,"k":[346.917,346.917,0],"ix":1},"s":{"a":1,"k":[{"o":{"x":0.333,"y":0},"i":{"x":0.667,"y":1},"s":[20,20,100],"t":19.533},{"o":{"x":0.333,"y":0},"i":{"x":0.833,"y":1},"s":[110,110,100],"t":39}],"ix":6},"sk":{"a":0,"k":0},"p":{"a":0,"k":[650,650,0],"ix":2},"r":{"a":0,"k":0,"ix":10},"sa":{"a":0,"k":0},"o":{"a":1,"k":[{"o":{"x":0.167,"y":0.167},"i":{"x":0.833,"y":0.833},"s":[0],"t":0},{"o":{"x":0.167,"y":0.167},"i":{"x":0.833,"y":0.833},"s":[100],"t":8},{"s":[100],"t":20.796875}],"ix":11}},"ef":[],"shapes":[{"ty":"gr","bm":0,"hd":false,"mn":"ADBE Vector Group","nm":"Group 1","ix":1,"cix":2,"np":2,"it":[{"ty":"sh","bm":0,"hd":false,"mn":"ADBE Vector Shape - Group","nm":"Path 1","ix":1,"d":1,"ks":{"a":0,"k":{"c":true,"i":[[0,-191.459],[191.459,0],[0,191.458],[-191.458,0]],"o":[[0,191.458],[-191.458,0],[0,-191.459],[191.459,0]],"v":[[346.667,0],[0,346.667],[-346.667,0],[0,-346.666]]},"ix":2}},{"ty":"fl","bm":0,"hd":false,"mn":"ADBE Vector Graphic - Fill","nm":"Fill 1","c":{"a":0,"k":[0.1804,0.8,0.4431],"ix":4},"r":1,"o":{"a":0,"k":100,"ix":5}},{"ty":"tr","a":{"a":0,"k":[0,0],"ix":1},"s":{"a":0,"k":[100,100],"ix":3},"sk":{"a":0,"k":0,"ix":4},"p":{"a":0,"k":[346.916,346.917],"ix":2},"r":{"a":0,"k":0,"ix":6},"sa":{"a":0,"k":0,"ix":5},"o":{"a":0,"k":100,"ix":7}}]}],"ind":4}],"v":"5.5.7","fr":60,"op":120,"ip":0,"assets":[]
};

const checkNotifications = async () => {
    try {
        const response = await axios.get('/notifications/unread');
        const notifications = response.data;

        // Find appointment completed notifications that haven't been shown yet
        const completedAppointments = notifications.filter(
            notif => notif.type === 'appointment_completed' && 
                     !notif.is_read && 
                     !shownNotifications.value.has(notif.id)
        );

        // Show popup only once for each notification
        if (completedAppointments.length > 0) {
            const notif = completedAppointments[0]; // Get first notification only
            shownNotifications.value.add(notif.id);

            const { value: formValues } = await Swal.fire({
                title: '🎉 Janji Temu Selesai',
                html: `
                    <div class="text-center">
                        <div id="lottie-animation" style="width: 150px; height: 150px; margin: 0 auto 20px;"></div>
                        
                        <p class="text-gray-700 mb-3">Selamat! Anda mendapatkan <span class="font-bold text-yellow-600">+${notif.data.points} Poin</span> loyalitas</p>
                        <div class="bg-green-50 rounded-lg p-3 mb-4">
                            <p class="text-sm text-gray-600">Kode Booking</p>
                            <p class="font-bold text-lg">${notif.data.booking_code}</p>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Beri Rating</label>
                            <div id="star-rating" class="flex justify-center space-x-1 mb-2">
                                ${[1, 2, 3, 4, 5].map(i => `
                                    <svg class="star-icon w-10 h-10 cursor-pointer text-gray-300 hover:text-yellow-400 transition-colors" data-rating="${i}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                `).join('')}
                            </div>
                            <p id="rating-label" class="text-sm text-gray-500 h-5"></p>
                            <input type="hidden" id="rating-value" value="0">
                        </div>
                        
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-2 text-left">Ulasan (opsional)</label>
                            <textarea 
                                id="review-comment" 
                                rows="3" 
                                placeholder="Tulis ulasan Anda tentang pelayanan kami..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent resize-none text-sm"
                            ></textarea>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Kirim',
                cancelButtonText: 'Lewati',
                confirmButtonColor: '#EAB308',
                cancelButtonColor: '#6B7280',
                allowOutsideClick: false,
                customClass: {
                    popup: 'rounded-2xl',
                    confirmButton: 'px-6 py-2 rounded-lg',
                    cancelButton: 'px-6 py-2 rounded-lg'
                },
                didOpen: () => {
                    // Load Lottie animation (imported directly for better performance)
                    lottie.loadAnimation({
                        container: document.getElementById('lottie-animation'),
                        renderer: 'svg',
                        loop: false,
                        autoplay: true,
                        animationData: checkAnimation
                    });

                    const stars = document.querySelectorAll('.star-icon');
                    const ratingInput = document.getElementById('rating-value');
                    const ratingLabel = document.getElementById('rating-label');
                    
                    const ratingLabels = {
                        1: 'Sangat Buruk',
                        2: 'Buruk',
                        3: 'Cukup',
                        4: 'Baik',
                        5: 'Sangat Baik'
                    };

                    const updateStars = (rating) => {
                        stars.forEach((star, index) => {
                            if (index < rating) {
                                star.classList.remove('text-gray-300');
                                star.classList.add('text-yellow-400');
                            } else {
                                star.classList.remove('text-yellow-400');
                                star.classList.add('text-gray-300');
                            }
                        });
                        ratingInput.value = rating;
                        ratingLabel.textContent = rating > 0 ? ratingLabels[rating] : '';
                    };

                    stars.forEach((star) => {
                        star.addEventListener('click', (e) => {
                            const rating = parseInt(e.currentTarget.getAttribute('data-rating'));
                            updateStars(rating);
                        });
                        
                        star.addEventListener('mouseenter', (e) => {
                            const rating = parseInt(e.currentTarget.getAttribute('data-rating'));
                            stars.forEach((s, idx) => {
                                if (idx < rating) {
                                    s.classList.remove('text-gray-300');
                                    s.classList.add('text-yellow-400');
                                } else {
                                    s.classList.remove('text-yellow-400');
                                    s.classList.add('text-gray-300');
                                }
                            });
                        });
                    });

                    document.getElementById('star-rating').addEventListener('mouseleave', () => {
                        const currentRating = parseInt(ratingInput.value);
                        updateStars(currentRating);
                    });
                },
                preConfirm: () => {
                    const rating = parseInt(document.getElementById('rating-value').value);
                    const comment = document.getElementById('review-comment').value;

                    if (rating === 0) {
                        Swal.showValidationMessage('Silakan pilih rating bintang');
                        return false;
                    }

                    return { rating, comment };
                }
            });

            // Mark notification as read first
            await axios.post(`/notifications/${notif.id}/read`);

            // If user submitted review
            if (formValues && formValues.rating > 0) {
                try {
                    const response = await axios.post(`/booking/${notif.data.appointment_id}/review`, {
                        rating: formValues.rating,
                        comment: formValues.comment || ''
                    });

                    console.log('Review submitted successfully:', response.data);

                    await Swal.fire({
                        icon: 'success',
                        title: 'Terima Kasih!',
                        text: 'Ulasan Anda berhasil disimpan',
                        confirmButtonColor: '#10B981',
                        timer: 2000
                    });
                } catch (error) {
                    console.error('Error submitting review:', error);
                    console.error('Error response:', error.response?.data);
                    
                    await Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: error.response?.data?.message || 'Terjadi kesalahan saat menyimpan ulasan',
                        confirmButtonColor: '#EF4444'
                    });
                }
            }
        }
    } catch (error) {
        console.error('Error checking notifications:', error);
    }
};

onMounted(() => {
    // Check notifications when component mounted
    checkNotifications();

    // Check periodically every 2 minutes (reduced from 30s for better performance)
    setInterval(checkNotifications, 120000);
});
</script>

<template>
    <!-- This component doesn't render anything, it just handles notifications -->
    <div></div>
</template>

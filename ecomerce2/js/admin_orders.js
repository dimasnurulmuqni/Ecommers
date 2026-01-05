document.addEventListener('DOMContentLoaded', function() {
    const proofModal = document.getElementById('proof-modal');
    const shippingModal = document.getElementById('shipping-modal');
    const rejectionModal = document.getElementById('rejection-modal');
    const modals = [proofModal, shippingModal, rejectionModal];
    const closeButtons = document.querySelectorAll('.close-btn');

    const shippingForm = document.getElementById('shipping-form');
    const rejectionForm = document.getElementById('rejection-form');

    let originalStatusValue = null;
    let currentSelectElement = null;

    function openModal(modal) {
        if (modal) modal.style.display = 'block';
    }

    function closeModal(modal) {
        if (modal) {
            modal.style.display = 'none';
            if (originalStatusValue && currentSelectElement) {
                currentSelectElement.value = originalStatusValue;
            }
        }
    }

    closeButtons.forEach(btn => {
        btn.onclick = function() {
            closeModal(btn.closest('.modal'));
        }
    });

    window.onclick = function(event) {
        modals.forEach(modal => {
            if (event.target == modal) {
                closeModal(modal);
            }
        });
    }

   async function updateOrderStatus(orderId, status, data = {}) {
        const payload = {
            order_id: orderId,
            status: status,
            ...data
        };

        try {
            const response = await fetch('../api/update_order_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            if (result.success) {
                alert(result.message);

                if (currentSelectElement) {
                    currentSelectElement.value = status;
                }

                const statusBadge = document.getElementById(`status-badge-${orderId}`);
                if (statusBadge) {
                    statusBadge.textContent = status;
                    statusBadge.className = `badge badge-${status}`;
                }

                closeModal(shippingModal);
                closeModal(rejectionModal);

            } else {
                alert(`Error: ${result.message}`);
                if (originalStatusValue && currentSelectElement) {
                    currentSelectElement.value = originalStatusValue;
                }
            }
        } catch (error) {
            console.error('Fetch error:', error);
            alert('Terjadi kesalahan koneksi.');
            if (originalStatusValue && currentSelectElement) {
                currentSelectElement.value = originalStatusValue;
            }
        }
    }

    const orderTable = document.querySelector('.order-table');
    if (orderTable) {
        orderTable.addEventListener('click', function(event) {
            if (event.target.classList.contains('view-proof-btn')) {
                const imageUrl = event.target.dataset.imageUrl;
                const proofImageEl = document.getElementById('proof-image');
                if (imageUrl && proofImageEl) {
                    proofImageEl.src = imageUrl;
                    openModal(proofModal);
                }
            }
        });

        orderTable.addEventListener('change', function(event) {
            if (event.target.classList.contains('status-select')) {
                const select = event.target;
                const orderId = select.dataset.orderId;
                const newStatus = select.value;

                originalStatusValue = select.value;
                currentSelectElement = select;

                if (newStatus === 'shipped') {
                    document.getElementById('shipping-order-id').value = orderId;
                    openModal(shippingModal);
                } else if (newStatus === 'cancelled') {
                    document.getElementById('rejection-order-id').value = orderId;
                    openModal(rejectionModal);
                } else {
                    if (confirm(`Anda yakin ingin mengubah status pesanan #${orderId} menjadi "${newStatus}"?`)) {
                        updateOrderStatus(orderId, newStatus);
                    } else {
                        select.value = originalStatusValue;
                    }
                }
            }
        });
    }

    if (shippingForm) {
        shippingForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const orderId = document.getElementById('shipping-order-id').value;
            const trackingNumber = document.getElementById('tracking_number').value;
            updateOrderStatus(orderId, 'shipped', { tracking_number: trackingNumber });
        });
    }

    if (rejectionForm) {
        rejectionForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const orderId = document.getElementById('rejection-order-id').value;
            const rejectionReason = document.getElementById('rejection_reason').value;
            updateOrderStatus(orderId, 'cancelled', { rejection_reason: rejectionReason });
        });
    }
});
console.log('Rejection modal script loaded');

function showRejectionModal(type, id, name, email) {
    console.log('Showing rejection modal for:', { type, id, name, email });
    
    Swal.fire({
        title: 'Reject ' + type.charAt(0).toUpperCase() + type.slice(1),
        html: `
            <div class="rejection-form">
                <p class="mb-2">Please provide a reason for rejecting ${name}.</p>
                <p class="mb-3 text-muted small">This message will be sent to ${email}</p>
                <textarea id="rejectionReason" class="swal2-textarea" placeholder="Enter rejection reason..." rows="4"></textarea>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Reject',
        confirmButtonColor: '#dc3545',
        cancelButtonText: 'Cancel',
        preConfirm: () => {
            const reason = document.getElementById('rejectionReason').value;
            if (!reason) {
                Swal.showValidationMessage('Please enter a rejection reason');
                return false;
            }
            return reason;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            console.log('Rejection confirmed with reason:', result.value);
            updateStatus(type, id, type === 'recipient' ? 'Rejected' : 'Rejected', result.value);
        }
    });
}

function updateHospitalStatus(hospitalId, status) {
    if (status === 'Rejected') {
        const hospital = document.querySelector(`[data-hospital-id="${hospitalId}"]`);
        const name = hospital.dataset.name;
        const email = hospital.dataset.email;
        showRejectionModal('hospital', hospitalId, name, email);
        return;
    }
    updateStatus('hospital', hospitalId, status);
}

function updateDonorStatus(donorId, status) {
    if (status === 'Rejected') {
        const donor = document.querySelector(`[data-donor-id="${donorId}"]`);
        const name = donor.dataset.name;
        const email = donor.dataset.email;
        showRejectionModal('donor', donorId, name, email);
        return;
    }
    updateStatus('donor', donorId, status);
}

function updateRecipientStatus(recipientId, status) {
    if (status === 'Rejected') {
        const recipient = document.querySelector(`[data-recipient-id="${recipientId}"]`);
        const name = recipient.dataset.name;
        const email = recipient.dataset.email;
        showRejectionModal('recipient', recipientId, name, email);
        return;
    }
    updateStatus('recipient', recipientId, status);
}

function updateStatus(type, id, status, reason = '') {
    console.log('Updating status:', { type, id, status, reason });
    
    // Convert status for recipient
    if (type === 'recipient') {
        if (status === 'Approved') status = 'Accepted';
    }
    
    const url = `../../backend/php/update_${type}_status.php`;
    const data = {
        [`${type}_id`]: id,
        status: status,
        reason: reason
    };

    console.log('Sending request to:', url, 'with data:', data);

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        console.log('Response received:', data);
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message,
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred. Please try again.'
        });
    });
}

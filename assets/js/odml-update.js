// Function to show ODML update confirmation modal
function showODMLUpdateModal(type, id, name, email) {
    const modalHtml = `
        <div class="odml-modal" id="odmlModal">
            <div class="odml-modal-content">
                <div class="modal-header">
                    <h4>Update ODML ID</h4>
                    <button type="button" class="close-btn" onclick="closeODMLModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="info-section">
                        <div class="icon-container">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <p class="modal-message">
                            You are about to update the ODML ID for:<br>
                            <strong>${name}</strong>
                        </p>
                    </div>
                    <div class="odml-input-container">
                        <label for="odmlId">Enter ODML ID:</label>
                        <input type="text" id="odmlId" class="odml-input" placeholder="Enter ODML ID" required>
                    </div>
                    <div class="confirmation-text">
                        <i class="fas fa-envelope"></i>
                        An email notification will be sent to <strong>${email}</strong> with the ODML ID details.
                    </div>
                    <div class="status-update-text">
                        <i class="fas fa-check-circle"></i>
                        This action will approve the ${type}'s registration.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="cancel-btn" onclick="closeODMLModal()">Cancel</button>
                    <button type="button" class="approve-btn" onclick="updateODMLId('${type}', ${id})">
                        <i class="fas fa-check"></i> Approve & Update
                    </button>
                </div>
            </div>
        </div>
    `;

    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);

    // Add modal styles if not already added
    if (!document.getElementById('odmlModalStyles')) {
        const styles = `
            <style id="odmlModalStyles">
                .odml-modal {
                    display: block;
                    position: fixed;
                    z-index: 1000;
                    left: 0;
                    top: 0;
                    width: 100%;
                    height: 100%;
                    background-color: rgba(0,0,0,0.5);
                    animation: fadeIn 0.3s ease-out;
                }

                @keyframes fadeIn {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }

                .odml-modal-content {
                    background: white;
                    margin: 10% auto;
                    max-width: 500px;
                    border-radius: 8px;
                    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
                    animation: slideIn 0.3s ease-out;
                }

                @keyframes slideIn {
                    from { transform: translateY(-100px); opacity: 0; }
                    to { transform: translateY(0); opacity: 1; }
                }

                .modal-header {
                    padding: 20px;
                    border-bottom: 1px solid #eee;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }

                .modal-header h4 {
                    margin: 0;
                    color: #2c3e50;
                    font-size: 1.4rem;
                }

                .close-btn {
                    background: none;
                    border: none;
                    font-size: 1.5rem;
                    cursor: pointer;
                    color: #666;
                }

                .modal-body {
                    padding: 20px;
                }

                .info-section {
                    display: flex;
                    align-items: center;
                    margin-bottom: 20px;
                    background: #f8f9fa;
                    padding: 15px;
                    border-radius: 6px;
                }

                .icon-container {
                    margin-right: 15px;
                    font-size: 2rem;
                    color: #3498db;
                }

                .modal-message {
                    margin: 0;
                    color: #2c3e50;
                }

                .odml-input-container {
                    margin-bottom: 20px;
                }

                .odml-input-container label {
                    display: block;
                    margin-bottom: 8px;
                    color: #2c3e50;
                }

                .odml-input {
                    width: 100%;
                    padding: 10px;
                    border: 2px solid #ddd;
                    border-radius: 4px;
                    font-size: 1rem;
                    transition: border-color 0.3s;
                }

                .odml-input:focus {
                    border-color: #3498db;
                    outline: none;
                }

                .confirmation-text, .status-update-text {
                    margin: 15px 0;
                    padding: 10px;
                    border-radius: 4px;
                    color: #2c3e50;
                }

                .confirmation-text {
                    background: #e8f4fd;
                }

                .status-update-text {
                    background: #e8f6ef;
                }

                .confirmation-text i, .status-update-text i {
                    margin-right: 8px;
                    color: #3498db;
                }

                .status-update-text i {
                    color: #2ecc71;
                }

                .modal-footer {
                    padding: 20px;
                    border-top: 1px solid #eee;
                    display: flex;
                    justify-content: flex-end;
                    gap: 10px;
                }

                .cancel-btn, .approve-btn {
                    padding: 10px 20px;
                    border-radius: 4px;
                    cursor: pointer;
                    font-size: 1rem;
                    transition: all 0.3s;
                }

                .cancel-btn {
                    background: #f8f9fa;
                    border: 1px solid #ddd;
                    color: #666;
                }

                .approve-btn {
                    background: #2ecc71;
                    border: none;
                    color: white;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }

                .cancel-btn:hover {
                    background: #e9ecef;
                }

                .approve-btn:hover {
                    background: #27ae60;
                }

                .approve-btn i {
                    font-size: 0.9rem;
                }
            </style>
        `;
        document.head.insertAdjacentHTML('beforeend', styles);
    }
}

// Function to close the modal
function closeODMLModal() {
    const modal = document.getElementById('odmlModal');
    if (modal) {
        modal.remove();
    }
}

// Function to update ODML ID
function updateODMLId(type, id) {
    const odmlId = document.getElementById('odmlId').value;
    if (!odmlId) {
        showError('Please enter an ODML ID');
        return;
    }

    const approveBtn = document.querySelector('.approve-btn');
    const originalBtnHtml = approveBtn.innerHTML;
    approveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    approveBtn.disabled = true;

    console.log('Updating ODML ID:', { type, id, odmlId });

    // Make API call to update ODML ID
    let endpoint = '../../backend/php/update_recipient_odml.php';
    let data = {
        recipient_id: id,
        odml_id: odmlId,
        action: 'approve'
    };

    if (type !== 'recipient') {
        endpoint = '../../backend/php/update_odml.php';
        data = {
            type: type,
            id: id,
            odmlId: odmlId
        };
    }

    console.log('Request details:', {
        endpoint,
        data,
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    });

    fetch(endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Server response:', data);
        if (data.success) {
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'ODML ID updated successfully.',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                // Reload the page or update UI
                window.location.reload();
            });
        } else {
            throw new Error(data.message || 'Failed to update ODML ID');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError(error.message || 'An error occurred while updating ODML ID');
        approveBtn.innerHTML = originalBtnHtml;
        approveBtn.disabled = false;
    });
}

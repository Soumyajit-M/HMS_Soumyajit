// Insurance Management JavaScript

// Sanitize HTML to prevent XSS
function sanitizeHTML(str) {
    const temp = document.createElement('div');
    temp.textContent = str;
    return temp.innerHTML;
}

document.addEventListener('DOMContentLoaded', function() {
    // Add Provider
    document.getElementById('saveProviderBtn').addEventListener('click', function() {
        const form = document.getElementById('addProviderForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        data.action = 'provider';

        fetch('api/insurance.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Provider added successfully!');
                form.reset();
                bootstrap.Modal.getInstance(document.getElementById('addProviderModal')).hide();
                location.reload();
            } else {
                alert('Error: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while adding provider');
        });
    });

    // Edit Provider
    document.querySelectorAll('.edit-provider').forEach(button => {
        button.addEventListener('click', function() {
            const providerId = this.getAttribute('data-id');
            
            fetch(`api/insurance.php?action=providers&id=${providerId}`)
            .then(response => response.json())
            .then(result => {
                if (result.success && result.provider) {
                    // You would need to create an edit modal similar to add modal
                    alert('Edit functionality - implement edit provider modal');
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });

    // Delete Provider
    document.querySelectorAll('.delete-provider').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this provider?')) {
                const providerId = this.getAttribute('data-id');
                
                fetch(`api/insurance.php?action=providers&id=${providerId}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert('Provider deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + result.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting provider');
                });
            }
        });
    });

    // Add Patient Insurance
    document.getElementById('savePolicyBtn').addEventListener('click', function() {
        const form = document.getElementById('addPolicyForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        data.action = 'patient_insurance';

        fetch('api/insurance.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Patient insurance added successfully!');
                form.reset();
                bootstrap.Modal.getInstance(document.getElementById('addPolicyModal')).hide();
                location.reload();
            } else {
                alert('Error: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while adding patient insurance');
        });
    });

    // Delete Policy
    document.querySelectorAll('.delete-policy').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this policy?')) {
                const policyId = this.getAttribute('data-id');
                
                fetch(`api/insurance.php?action=patient_insurance&id=${policyId}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert('Policy deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + result.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting policy');
                });
            }
        });
    });

    // Create Claim
    document.querySelectorAll('.create-claim').forEach(button => {
        button.addEventListener('click', function() {
            const insuranceId = this.getAttribute('data-id');
            const patientName = this.getAttribute('data-patient');
            
            document.getElementById('claim_insurance_id').value = insuranceId;
            document.getElementById('claim_patient_name').value = patientName;
            
            new bootstrap.Modal(document.getElementById('createClaimModal')).show();
        });
    });

    // Save Claim
    document.getElementById('saveClaimBtn').addEventListener('click', function() {
        const form = document.getElementById('createClaimForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        data.action = 'claim';

        fetch('api/insurance.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Claim created successfully!');
                form.reset();
                bootstrap.Modal.getInstance(document.getElementById('createClaimModal')).hide();
                location.reload();
            } else {
                alert('Error: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while creating claim');
        });
    });

    // Update Claim Status
    document.querySelectorAll('.update-claim-status').forEach(button => {
        button.addEventListener('click', function() {
            const claimId = this.getAttribute('data-id');
            document.getElementById('update_claim_id').value = claimId;
            new bootstrap.Modal(document.getElementById('updateClaimStatusModal')).show();
        });
    });

    // Save Claim Status
    document.getElementById('updateClaimStatusBtn').addEventListener('click', function() {
        const form = document.getElementById('updateClaimStatusForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        data.action = 'claim_status';

        fetch('api/insurance.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Claim status updated successfully!');
                bootstrap.Modal.getInstance(document.getElementById('updateClaimStatusModal')).hide();
                location.reload();
            } else {
                alert('Error: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating claim status');
        });
    });

    // Delete Claim
    document.querySelectorAll('.delete-claim').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this claim?')) {
                const claimId = this.getAttribute('data-id');
                
                fetch(`api/insurance.php?action=claim&id=${claimId}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert('Claim deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + result.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting claim');
                });
            }
        });
    });
});

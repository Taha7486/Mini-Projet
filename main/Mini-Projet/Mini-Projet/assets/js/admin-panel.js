// Tab management
function showTab(tabName) {
    // Hide all content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active state from all tabs
    document.querySelectorAll('[id^="tab-"]').forEach(tab => {
        tab.classList.remove('bg-black', 'text-white');
        tab.classList.add('hover:bg-gray-100');
    });
    
    // Show selected content
    document.getElementById('content-' + tabName).classList.remove('hidden');
    
    // Activate selected tab
    const activeTab = document.getElementById('tab-' + tabName);
    activeTab.classList.add('bg-black', 'text-white');
    activeTab.classList.remove('hover:bg-gray-100');
}

// Handle organizer request
async function handleRequest(requestId, status) {
    const action = status === 'approved' ? 'approve' : 'reject';
    const confirmMsg = status === 'approved' 
        ? 'Are you sure you want to approve this request?' 
        : 'Are you sure you want to reject this request?';
    
    if (!confirm(confirmMsg)) {
        return;
    }
    
    try {
        const response = await fetch('../api/admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: action + '_organizer_request',
                request_id: requestId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(`Request ${status} successfully!`);
            location.reload();
        } else {
            alert(data.message || `Failed to ${action} request`);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    }
}

// Club management
let clubEditMode = false;

function openCreateClubDialog() {
    clubEditMode = false;
    document.getElementById('clubModalTitle').textContent = 'Create Club';
    document.getElementById('clubSubmitBtn').textContent = 'Create Club';
    document.getElementById('clubForm').reset();
    document.getElementById('clubId').value = '';
    document.getElementById('clubModal').classList.remove('hidden');
}

function editClub(club) {
    clubEditMode = true;
    document.getElementById('clubModalTitle').textContent = 'Edit Club';
    document.getElementById('clubSubmitBtn').textContent = 'Update Club';
    
    document.getElementById('clubId').value = club.club_id;
    document.getElementById('clubName').value = club.nom;
    document.getElementById('clubDescription').value = club.description || '';
    
    document.getElementById('clubModal').classList.remove('hidden');
}

function closeClubModal() {
    document.getElementById('clubModal').classList.add('hidden');
    document.getElementById('clubForm').reset();
}

document.getElementById('clubForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const submitBtn = document.getElementById('clubSubmitBtn');
    submitBtn.disabled = true;
    submitBtn.textContent = clubEditMode ? 'Updating...' : 'Creating...';
    
    const payload = {
        action: clubEditMode ? 'update_club' : 'create_club',
        club_id: formData.get('club_id'),
        nom: formData.get('nom'),
        description: formData.get('description')
    };
    
    try {
        const response = await fetch('../api/admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(clubEditMode ? 'Club updated successfully!' : 'Club created successfully!');
            location.reload();
        } else {
            alert(data.message || 'Failed to save club');
            submitBtn.disabled = false;
            submitBtn.textContent = clubEditMode ? 'Update Club' : 'Create Club';
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
        submitBtn.disabled = false;
        submitBtn.textContent = clubEditMode ? 'Update Club' : 'Create Club';
    }
});

async function deleteClub(clubId) {
    if (!confirm('Are you sure you want to delete this club? This will also delete all associated events.')) {
        return;
    }
    
    try {
        const response = await fetch('../api/admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'delete_club',
                club_id: clubId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Club deleted successfully!');
            location.reload();
        } else {
            alert(data.message || 'Failed to delete club');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    }
}

// User management
async function changeUserRole(accountId, currentRole) {
    const newRole = currentRole === 'user' ? 'organizer' : 'user';
    const confirmMsg = `Change user role from ${currentRole} to ${newRole}?`;
    
    if (!confirm(confirmMsg)) {
        return;
    }
    
    try {
        const response = await fetch('../api/admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'change_user_role',
                account_id: accountId,
                new_role: newRole
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('User role updated successfully!');
            location.reload();
        } else {
            alert(data.message || 'Failed to change user role');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    }
}

// Create admin
document.getElementById('addAdminForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();

    const form = e.target;
    const submitBtn = document.getElementById('addAdminSubmit');
    
    // Validate password confirmation
    const password = form.querySelector('#password').value;
    const confirmPassword = form.querySelector('#confirmPassword').value;
    
    if (password !== confirmPassword) {
        alert('Passwords do not match!');
        return;
    }
    
    if (password.length < 6) {
        alert('Password must be at least 6 characters long!');
        return;
    }
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Creating...';

    const formData = new FormData(form);
    const payload = {
        action: 'create_admin',
        nom: formData.get('nom'),
        email: formData.get('email'),
        password: formData.get('password')
    };

    try {
        const response = await fetch('../api/admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await response.json();
        if (data.success) {
            alert('Admin created successfully!');
            form.reset();
            // Optionally switch to Users tab to reflect changes after reload
            location.reload();
        } else {
            alert(data.message || 'Failed to create admin');
        }
    } catch (err) {
        console.error(err);
        alert('An error occurred. Please try again.');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Create Admin';
    }
});

// Delete user
async function deleteUser(accountId, userName) {
    const confirmMsg = `Are you sure you want to delete user "${userName}"? This action cannot be undone.`;
    
    if (!confirm(confirmMsg)) {
        return;
    }
    
    try {
        const response = await fetch('../api/admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'delete_user',
                account_id: accountId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('User deleted successfully!');
            location.reload();
        } else {
            alert(data.message || 'Failed to delete user');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    }
}
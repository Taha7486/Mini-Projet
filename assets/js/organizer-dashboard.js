// Global variables
let currentEventId = null;
let editMode = false;

// Open create dialog
function openCreateDialog() {
    editMode = false;
    document.getElementById('modalTitle').textContent = 'Create Event';
    document.getElementById('submitBtn').textContent = 'Create Event';
    document.getElementById('eventForm').reset();
    document.getElementById('eventId').value = '';
    document.getElementById('eventModal').classList.remove('hidden');
}

// Edit event
function editEvent(event) {
    editMode = true;
    document.getElementById('modalTitle').textContent = 'Edit Event';
    document.getElementById('submitBtn').textContent = 'Update Event';
    
    document.getElementById('eventId').value = event.event_id;
    document.getElementById('title').value = event.title;
    document.getElementById('description').value = event.description;
    document.getElementById('date_event').value = event.date_event;
    document.getElementById('time_event').value = event.time_event;
    document.getElementById('location').value = event.location;
    document.getElementById('club_id').value = event.club_id;
    document.getElementById('capacity').value = event.capacity;
    
    // Handle image field - keep existing image URL in hidden field
    document.getElementById('image_url').value = event.image_url;
    // Clear the file input for new uploads
    document.getElementById('event_image').value = '';
    
    document.getElementById('eventModal').classList.remove('hidden');
}

// Close event modal
function closeEventModal() {
    document.getElementById('eventModal').classList.add('hidden');
    document.getElementById('eventForm').reset();
}

// Submit event form
document.getElementById('eventForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.textContent = editMode ? 'Updating...' : 'Creating...';
    
    // Add action to FormData
    formData.append('action', editMode ? 'update' : 'create');
    
    try {
        const response = await fetch('../api/events.php', {
            method: 'POST',
            body: formData // Send FormData instead of JSON for file uploads
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(editMode ? 'Event updated successfully!' : 'Event created successfully!');
            location.reload();
        } else {
            alert(data.message || 'Failed to save event');
            submitBtn.disabled = false;
            submitBtn.textContent = editMode ? 'Update Event' : 'Create Event';
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
        submitBtn.disabled = false;
        submitBtn.textContent = editMode ? 'Update Event' : 'Create Event';
    }
});

// Delete event
async function deleteEvent(eventId) {
    if (!confirm('Are you sure you want to delete this event?')) {
        return;
    }
    
    try {
        const response = await fetch('../api/events.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'delete',
                event_id: eventId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Event deleted successfully!');
            location.reload();
        } else {
            alert(data.message || 'Failed to delete event');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    }
}

// View participants
async function viewParticipants(eventId) {
    currentEventId = eventId;
    
    try {
        const response = await fetch('../api/events.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'get_participants',
                event_id: eventId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            const participants = data.participants || [];
            document.getElementById('participantsCount').textContent = 
                `${participants.length} ${participants.length === 1 ? 'participant' : 'participants'} registered`;

            const tableHtml = participants.length === 0
                ? '<p class="text-center text-gray-600 py-8">No participants yet</p>'
                : `<table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left">Name</th>
                                <th class="px-4 py-3 text-left">Email</th>
                                <th class="px-4 py-3 text-left">Student ID</th>
                                <th class="px-4 py-3 text-left">Department</th>
                                <th class="px-4 py-3 text-left">Phone</th>
                                <th class="px-4 py-3 text-left">Registered</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${participants.map(p => `
                                <tr class="border-t" data-participant-id="${p.participant_id}">
                                    <td class="px-4 py-3">${p.nom}</td>
                                    <td class="px-4 py-3">${p.email}</td>
                                    <td class="px-4 py-3">${p.student_id}</td>
                                    <td class="px-4 py-3">${p.department}</td>
                                    <td class="px-4 py-3">${p.phone_number}</td>
                                    <td class="px-4 py-3">${new Date(p.registered_at).toLocaleDateString()}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>`;
            
            document.getElementById('participantsList').innerHTML = tableHtml;
            document.getElementById('participantsModal').classList.remove('hidden');
        } else {
            alert(data.message || 'Failed to load participants');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    }
}

// Close participants modal
function closeParticipantsModal() {
    document.getElementById('participantsModal').classList.add('hidden');
}

// Open attestations modal
async function openAttestationsModal(eventId, eventTitle) {
    currentEventId = eventId;
    document.getElementById('attestationsEventTitle').textContent = `Event: ${eventTitle}`;
    
    try {
        const response = await fetch('../api/events.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'get_participants',
                event_id: eventId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            const participants = data.participants || [];
            document.getElementById('attestationsCount').textContent = 
                `${participants.length} ${participants.length === 1 ? 'participant' : 'participants'} available`;

            const tableHtml = participants.length === 0
                ? '<p class="text-center text-gray-600 py-8">No participants yet</p>'
                : `<table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left w-10"></th>
                                <th class="px-4 py-3 text-left">Name</th>
                                <th class="px-4 py-3 text-left">Email</th>
                                <th class="px-4 py-3 text-left">Student ID</th>
                                <th class="px-4 py-3 text-left">Department</th>
                                <th class="px-4 py-3 text-left">Phone</th>
                                <th class="px-4 py-3 text-left">Registered</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${participants.map(p => `
                                <tr class="border-t" data-participant-id="${p.participant_id}">
                                    <td class="px-4 py-3">
                                        <input type="checkbox" class="attestationCheckbox w-4 h-4 border rounded" value="${p.participant_id}">
                                    </td>
                                    <td class="px-4 py-3">${p.nom}</td>
                                    <td class="px-4 py-3">${p.email}</td>
                                    <td class="px-4 py-3">${p.student_id}</td>
                                    <td class="px-4 py-3">${p.department}</td>
                                    <td class="px-4 py-3">${p.phone_number}</td>
                                    <td class="px-4 py-3">${new Date(p.registered_at).toLocaleDateString()}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>`;
            
            document.getElementById('attestationsList').innerHTML = tableHtml;
            wireAttestationsSelection();
            document.getElementById('attestationsModal').classList.remove('hidden');
        } else {
            alert(data.message || 'Failed to load participants');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    }
}

// Close attestations modal
function closeAttestationsModal() {
    document.getElementById('attestationsModal').classList.add('hidden');
}

// Open email history modal
async function openEmailHistoryModal(eventId, eventTitle) {
    document.getElementById('emailHistoryEventTitle').textContent = `Event: ${eventTitle}`;
    
    try {
        const response = await fetch('../api/events.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'get_email_history',
                event_id: eventId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            const history = data.history || [];
            displayEmailHistory(history);
            document.getElementById('emailHistoryModal').classList.remove('hidden');
        } else {
            alert(data.message || 'Failed to load email history');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    }
}

// Close email history modal
function closeEmailHistoryModal() {
    document.getElementById('emailHistoryModal').classList.add('hidden');
}

// Display email history
function displayEmailHistory(history) {
    const container = document.getElementById('emailHistoryList');
    
    if (history.length === 0) {
        container.innerHTML = '<p class="text-center text-gray-600 py-8">No email history found for this event.</p>';
        return;
    }
    
    const historyHtml = history.map(email => {
        const sentAt = new Date(email.sent_at).toLocaleString();
        const emailType = email.email_type === 'attestation' ? 'Attestation' : 'Custom Email';
        const typeColor = email.email_type === 'attestation' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800';
        const attachmentsCount = email.attachments ? email.attachments.length : 0;
        
        return `
            <div class="border rounded-lg p-4 mb-4 hover:bg-gray-50">
                <div class="flex items-start justify-between mb-2">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-2 py-1 rounded text-xs font-medium ${typeColor}">${emailType}</span>
                            <span class="text-sm text-gray-500">${sentAt}</span>
                        </div>
                        <h3 class="font-semibold text-lg mb-1">${email.subject}</h3>
                        <p class="text-gray-600 text-sm mb-2">${email.message.substring(0, 100)}${email.message.length > 100 ? '...' : ''}</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Recipients:</span>
                        <span class="font-medium">${email.recipients_count}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Sent:</span>
                        <span class="font-medium text-green-600">${email.sent_count}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Failed:</span>
                        <span class="font-medium text-red-600">${email.failed_count}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Attachments:</span>
                        <span class="font-medium">${attachmentsCount}</span>
                    </div>
                </div>
                
                ${attachmentsCount > 0 ? `
                    <div class="mt-3 pt-3 border-t">
                        <p class="text-sm text-gray-600 mb-2">Attachments:</p>
                        <div class="flex flex-wrap gap-2">
                            ${email.attachments.map(file => `
                                <span class="px-2 py-1 bg-gray-100 rounded text-xs">
                                    <i class="fas fa-paperclip mr-1"></i>${file.split('/').pop()}
                                </span>
                            `).join('')}
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Files are kept for 30 days after sending
                        </p>
                    </div>
                ` : ''}
            </div>
        `;
    }).join('');
    
    container.innerHTML = historyHtml;
}

// Wire selection and sending logic for attestations
function wireAttestationsSelection() {
    const selectAll = document.getElementById('selectAllAttestations');
    const checkboxes = Array.from(document.querySelectorAll('.attestationCheckbox'));
    const selectedCount = document.getElementById('selectedAttestationsCount');
    const sendBtn = document.getElementById('sendAttestationsBtn');

    const updateCount = () => {
        const count = checkboxes.filter(cb => cb.checked).length;
        selectedCount.textContent = `${count} ${count === 1 ? 'selected' : 'selected'}`;
        sendBtn.disabled = count === 0;
    };

    if (selectAll) {
        selectAll.addEventListener('change', (e) => {
            const checked = e.target.checked;
            checkboxes.forEach(cb => { cb.checked = checked; });
            updateCount();
        });
    }

    checkboxes.forEach(cb => cb.addEventListener('change', updateCount));
    updateCount();

    if (sendBtn) {
        sendBtn.addEventListener('click', () => sendSelectedAttestations());
    }
}

async function sendSelectedAttestations() {
    const sendBtn = document.getElementById('sendAttestationsBtn');
    const checkboxes = Array.from(document.querySelectorAll('.attestationCheckbox'));
    const selected = checkboxes.filter(cb => cb.checked).map(cb => parseInt(cb.value));

    if (selected.length === 0) {
        alert('Please select at least one participant.');
        return;
    }

    sendBtn.disabled = true;
    const originalText = sendBtn.textContent;
    sendBtn.textContent = 'Sending...';

    try {
        const response = await fetch('../api/events.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'send_attestations',
                event_id: currentEventId,
                participant_ids: selected
            })
        });

        const data = await response.json();
        if (data.success) {
            alert(`Sent ${data.sent || 0} of ${data.total || selected.length} attestations.`);
        } else {
            alert(data.message || 'Failed to send emails');
        }
    } catch (err) {
        console.error(err);
        alert('An error occurred while sending emails.');
    } finally {
        sendBtn.disabled = false;
        sendBtn.textContent = originalText;
    }
}

// Email functionality
let currentEmailEventId = null;
let currentEmailEventData = null;

function openEmailModal(eventId, eventTitle) {
    currentEmailEventId = eventId;
    document.getElementById('emailEventId').value = eventId;
    document.getElementById('emailEventTitle').textContent = `Event: ${eventTitle}`;
    document.getElementById('emailModal').classList.remove('hidden');
    
    // Load participants for this event
    loadParticipantsForEmail(eventId);
    
    // Set up real-time preview
    setupEmailPreview();
}

function closeEmailModal() {
    document.getElementById('emailModal').classList.add('hidden');
    document.getElementById('emailForm').reset();
    document.getElementById('emailPreview').innerHTML = '<p class="text-gray-500 italic">Preview will appear here as you type...</p>';
    currentEmailEventId = null;
    currentEmailEventData = null;
}

function loadParticipantsForEmail(eventId) {
    // Reuse the existing participants loading logic
    fetch('api/events.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'get_participants',
            event_id: eventId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const participants = data.participants || [];
            const container = document.getElementById('participantSelection');
            
            if (participants.length === 0) {
                container.innerHTML = '<p class="text-gray-500 italic">No participants found for this event.</p>';
            } else {
                container.innerHTML = participants.map(p => `
                    <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded">
                        <input type="checkbox" name="selected_participants[]" value="${p.participant_id}" class="participantEmailCheckbox w-4 h-4">
                        <span>${p.nom} (${p.email})</span>
                    </label>
                `).join('');
            }
        }
    })
    .catch(error => {
        console.error('Error loading participants:', error);
        document.getElementById('participantSelection').innerHTML = '<p class="text-red-500">Error loading participants.</p>';
    });
}

function setupEmailPreview() {
    const messageTextarea = document.getElementById('emailMessage');
    const previewDiv = document.getElementById('emailPreview');
    
    messageTextarea.addEventListener('input', () => {
        updateEmailPreview();
    });
    
    // Setup file selection display
    const fileInput = document.getElementById('emailAttachments');
    fileInput.addEventListener('change', displaySelectedFiles);
}

function displaySelectedFiles() {
    const fileInput = document.getElementById('emailAttachments');
    const selectedFilesDiv = document.getElementById('selectedFiles');
    const fileListDiv = document.getElementById('fileList');
    
    if (fileInput.files.length > 0) {
        selectedFilesDiv.classList.remove('hidden');
        
        const fileList = Array.from(fileInput.files).map(file => `
            <div class="flex items-center justify-between p-2 bg-gray-50 rounded border">
                <div class="flex items-center gap-2">
                    <i class="fas fa-file text-gray-500"></i>
                    <span class="text-sm">${file.name}</span>
                    <span class="text-xs text-gray-500">(${(file.size / 1024 / 1024).toFixed(2)} MB)</span>
                </div>
                <button type="button" onclick="removeFile(this)" class="text-red-500 hover:text-red-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `).join('');
        
        fileListDiv.innerHTML = fileList;
    } else {
        selectedFilesDiv.classList.add('hidden');
    }
}

function removeFile(button) {
    const fileInput = document.getElementById('emailAttachments');
    const fileList = Array.from(fileInput.files);
    const fileName = button.parentElement.querySelector('span').textContent;
    
    // Create new FileList without the removed file
    const newFiles = fileList.filter(file => file.name !== fileName);
    
    // Create a new DataTransfer object
    const dataTransfer = new DataTransfer();
    newFiles.forEach(file => dataTransfer.items.add(file));
    
    // Update the file input
    fileInput.files = dataTransfer.files;
    
    // Update display
    displaySelectedFiles();
}

function updateEmailPreview() {
    const subject = document.getElementById('emailSubject').value;
    const message = document.getElementById('emailMessage').value;
    const previewDiv = document.getElementById('emailPreview');
    
    if (!subject && !message) {
        previewDiv.innerHTML = '<p class="text-gray-500 italic">Preview will appear here as you type...</p>';
        return;
    }
    
    // Create a simple preview with sample data
    const sampleData = {
        name: 'John Doe',
        event_title: 'Sample Event',
        event_date: '2025-01-15',
        event_time: '14:00',
        event_location: 'Main Hall'
    };
    
    const personalizedMessage = message.replace(
        /\{name\}|\{event_title\}|\{event_date\}|\{event_time\}|\{event_location\}/g,
        (match) => sampleData[match.replace(/[{}]/g, '')] || match
    );
    
    previewDiv.innerHTML = `
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2 style="color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px;">${subject || 'Email Subject'}</h2>
            <div style="line-height: 1.6; color: #555;">
                ${personalizedMessage.replace(/\n/g, '<br>')}
            </div>
            <hr style="margin: 20px 0; border: none; border-top: 1px solid #eee;">
            <p style="font-size: 12px; color: #888;">
                This email was sent regarding the event: <strong>${sampleData.event_title}</strong><br>
                Best regards,<br>Event Organizer
            </p>
        </div>
    `;
}

function previewEmail() {
    updateEmailPreview();
}

// Handle recipient type change
document.addEventListener('change', (e) => {
    if (e.target.name === 'recipient_type') {
        const participantSelection = document.getElementById('participantSelection');
        if (e.target.value === 'selected') {
            participantSelection.classList.remove('hidden');
        } else {
            participantSelection.classList.add('hidden');
        }
    }
});

// Handle email form submission
document.getElementById('emailForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const submitBtn = document.getElementById('sendEmailBtn');
    
    // Validate form
    const subject = formData.get('subject');
    const message = formData.get('message');
    
    if (!subject.trim() || !message.trim()) {
        alert('Please fill in both subject and message.');
        return;
    }
    
    // Determine recipients
    let participantIds = null;
    if (formData.get('recipient_type') === 'selected') {
        const selectedCheckboxes = document.querySelectorAll('.participantEmailCheckbox:checked');
        participantIds = Array.from(selectedCheckboxes).map(cb => cb.value);
        
        if (participantIds.length === 0) {
            alert('Please select at least one participant.');
            return;
        }
    }
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Uploading files...';
    
    try {
        // First, upload files if any
        let uploadedFiles = [];
        const fileInput = document.getElementById('emailAttachments');
        if (fileInput.files.length > 0) {
            // Show upload progress
            const progressDiv = document.getElementById('uploadProgress');
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');
            
            progressDiv.classList.remove('hidden');
            progressBar.style.width = '0%';
            progressText.textContent = '0%';
            
            const uploadFormData = new FormData();
            for (let file of fileInput.files) {
                uploadFormData.append('attachments[]', file);
            }
            
            // Simulate progress (since we can't track real progress with fetch)
            let progress = 0;
            const progressInterval = setInterval(() => {
                progress += Math.random() * 20;
                if (progress > 90) progress = 90;
                progressBar.style.width = progress + '%';
                progressText.textContent = Math.round(progress) + '%';
            }, 200);
            
            const uploadResponse = await fetch('../api/upload.php', {
                method: 'POST',
                body: uploadFormData
            });
            
            clearInterval(progressInterval);
            progressBar.style.width = '100%';
            progressText.textContent = '100%';
            
            const uploadData = await uploadResponse.json();
            
            if (!uploadData.success) {
                progressDiv.classList.add('hidden');
                alert('Failed to upload files: ' + (uploadData.message || 'Unknown error'));
                if (uploadData.errors && uploadData.errors.length > 0) {
                    console.error('Upload errors:', uploadData.errors);
                }
                return;
            }
            
            uploadedFiles = uploadData.files || [];
            
            if (uploadData.errors && uploadData.errors.length > 0) {
                console.warn('Upload warnings:', uploadData.errors);
                // Show warnings to user
                const warnings = uploadData.errors.join('\n');
                if (confirm(`Some files had issues:\n\n${warnings}\n\nDo you want to continue anyway?`)) {
                    // Continue with upload
                } else {
                    progressDiv.classList.add('hidden');
                    return;
                }
            }
            
            // Hide progress after a short delay
            setTimeout(() => {
                progressDiv.classList.add('hidden');
            }, 1000);
        }
        
        // Update button text
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending email...';
        
        // Now send the email with uploaded file paths
        const response = await fetch('../api/events.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'send_custom_email',
                event_id: currentEmailEventId,
                participant_ids: participantIds,
                subject: subject,
                message: message,
                attachments: uploadedFiles.map(file => file.path)
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(`Email sent successfully! ${data.sent} out of ${data.total} emails delivered.`);
            closeEmailModal();
        } else {
            alert(data.message || 'Failed to send email');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Send Email';
    }
});

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
    document.getElementById('image_url').value = event.image_url;
    
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
    
    const payload = {
        action: editMode ? 'update' : 'create',
        event_id: formData.get('event_id'),
        title: formData.get('title'),
        description: formData.get('description'),
        date_event: formData.get('date_event'),
        time_event: formData.get('time_event'),
        location: formData.get('location'),
        club_id: formData.get('club_id'),
        capacity: parseInt(formData.get('capacity')),
        image_url: formData.get('image_url')
    };
    
    try {
        const response = await fetch('api/events.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
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
        const response = await fetch('api/events.php', {
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
        const response = await fetch('api/events.php', {
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
            
            const toolbarHtml = `
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input id="selectAllParticipants" type="checkbox" class="w-4 h-4 border rounded">
                            <span>Select all</span>
                        </label>
                        <span id="selectedCount" class="text-sm text-gray-600">0 selected</span>
                    </div>
                    <button id="sendAttestationsBtn" class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 disabled:opacity-60 disabled:cursor-not-allowed">Send attestations</button>
                </div>`;

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
                                        <input type="checkbox" class="participantCheckbox w-4 h-4 border rounded" value="${p.participant_id}">
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

            const listHtml = toolbarHtml + tableHtml;
            
            document.getElementById('participantsList').innerHTML = listHtml;
            wireParticipantsSelection();
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

// Wire selection and sending logic for participants
function wireParticipantsSelection() {
    const selectAll = document.getElementById('selectAllParticipants');
    const checkboxes = Array.from(document.querySelectorAll('.participantCheckbox'));
    const selectedCount = document.getElementById('selectedCount');
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
    const checkboxes = Array.from(document.querySelectorAll('.participantCheckbox'));
    const selected = checkboxes.filter(cb => cb.checked).map(cb => parseInt(cb.value));

    if (selected.length === 0) {
        alert('Please select at least one participant.');
        return;
    }

    sendBtn.disabled = true;
    const originalText = sendBtn.textContent;
    sendBtn.textContent = 'Sending...';

    try {
        const response = await fetch('api/events.php', {
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

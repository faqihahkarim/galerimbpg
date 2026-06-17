// booking.js - Frontend JavaScript for Tempahan Management

let bookingData = [];

// DOM Elements
let modal, modalClose;
let modalBookingRef, modalBookingStatus;
let modalOrganization, modalPhone, modalEmail, modalSlot;
let modalPackage, modalActivities, modalParticipants;
let modalRemark, modalTotalFee;
let actionBookingId, actionBookingStatus;
let approveBookingBtn, rejectBookingBtn, whatsappBookingBtn, editBookingBtn;
let rejectModal, rejectReason, confirmRejectBtn, cancelRejectBtn;
let bookingSearch, bookingTypeFilter, bookingStatusFilter;
let bookingResetBtn, bookingExportBtn, bookingAddBtn;
let bookingTableBody, bookingFooterText, bookingPaginationInfo;
let prevPageBtn, nextPageBtn;

// Edit modal elements
let editModal, editBookingForm;
let editBookingId, editOrganization, editContactPerson;
let editPhoneNumber, editEmail, editParticipants, editAdminComment;
let saveEditBtn, cancelEditBtn;

// Package and activity elements
let editPackageSelect;
let editSlotDisplay, editSlotDate, editSlotTime, editSlotId;
let editActivitySection, editActivityList;
let editActivitySum, editRequiredTotal, editTotalParticipantsLabel;
let editActivityWarning, editActivityValid;

// Data storage
let currentPage = 1;
const pageSize = 10;
let allRows = [];
let allPackages = [];
let allActivities = [];
let currentBookingActivities = {};
let currentPackageId;
let currentSlotId;
let availableSlotsData = [];

// Initialize everything when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Get booking data from PHP
    const bookingDataElement = document.getElementById('booking-data');
    if (bookingDataElement) {
        try {
            bookingData = JSON.parse(bookingDataElement.textContent);
            console.log('Booking data loaded:', bookingData.length, 'records');
        } catch(e) {
            console.error('Failed to parse booking data:', e);
        }
    }
    
    // Initialize DOM elements
    initializeDOMElements();
    
    // Set up all event listeners
    initializeEventListeners();
    
    // Initial table setup
    if (bookingTableBody) {
        allRows = Array.from(bookingTableBody.querySelectorAll('tr'));
        applyFilters();
    }
});

function initializeDOMElements() {
    // Modal elements
    modal = document.getElementById('bookingModal');
    modalClose = document.querySelector('.booking-modal-close');

    // Get data elements
    modalBookingRef = document.getElementById('modalBookingRef');
    modalBookingStatus = document.getElementById('modalBookingStatus');
    modalOrganization = document.getElementById('modalOrganization');
    modalPhone = document.getElementById('modalPhone');
    modalEmail = document.getElementById('modalEmail');
    modalSlot = document.getElementById('modalSlot');
    modalPackage = document.getElementById('modalPackage');
    modalActivities = document.getElementById('modalActivities');
    modalParticipants = document.getElementById('modalParticipants');
    modalRemark = document.getElementById('modalRemark');
    modalTotalFee = document.getElementById('modalTotalFee');

    // Get action elements
    actionBookingId = document.getElementById('actionBookingId');
    actionBookingStatus = document.getElementById('actionBookingStatus');

    // Buttons
    approveBookingBtn = document.getElementById('approveBookingBtn');
    rejectBookingBtn = document.getElementById('rejectBookingBtn');
    whatsappBookingBtn = document.getElementById('whatsappBookingBtn');
    editBookingBtn = document.getElementById('editBookingBtn');

    // Reject modal
    rejectModal = document.getElementById('rejectModal');
    rejectReason = document.getElementById('rejectReason');
    confirmRejectBtn = document.getElementById('confirmRejectBtn');
    cancelRejectBtn = document.getElementById('cancelRejectBtn');

    // Table elements
    bookingSearch = document.getElementById('bookingSearch');
    bookingTypeFilter = document.getElementById('bookingTypeFilter');
    bookingStatusFilter = document.getElementById('bookingStatusFilter');
    bookingResetBtn = document.getElementById('bookingResetBtn');
    bookingExportBtn = document.getElementById('bookingExportBtn');
    bookingAddBtn = document.getElementById('bookingAddBtn');
    bookingTableBody = document.querySelector('.table-wrap table tbody');
    bookingFooterText = document.getElementById('bookingFooterText');
    bookingPaginationInfo = document.getElementById('paginationInfo');
    prevPageBtn = document.getElementById('prevPageBtn');
    nextPageBtn = document.getElementById('nextPageBtn');

    // Edit modal elements
    editModal = document.getElementById('editModal');
    editBookingForm = document.getElementById('editBookingForm');
    editBookingId = document.getElementById('editBookingId');
    editOrganization = document.getElementById('editOrganization');
    editContactPerson = document.getElementById('editContactPerson');
    editPhoneNumber = document.getElementById('editPhoneNumber');
    editEmail = document.getElementById('editEmail');
    editParticipants = document.getElementById('editParticipants');
    editAdminComment = document.getElementById('editAdminComment');
    saveEditBtn = document.getElementById('saveEditBtn');
    cancelEditBtn = document.getElementById('cancelEditBtn');
    
    // Package and slot elements
    editPackageSelect = document.getElementById('editPackageSelect');
    editSlotDisplay = document.getElementById('editSlotDisplay');
    editSlotDate = document.getElementById('editSlotDate');
    editSlotTime = document.getElementById('editSlotTime');
    editSlotId = document.getElementById('editSlotId');
    
    // Activity elements
    editActivitySection = document.getElementById('editActivitySection');
    editActivityList = document.getElementById('editActivityList');
    editActivitySum = document.getElementById('editActivitySum');
    editRequiredTotal = document.getElementById('editRequiredTotal');
    editTotalParticipantsLabel = document.getElementById('editTotalParticipantsLabel');
    editActivityWarning = document.getElementById('editActivityWarning');
    editActivityValid = document.getElementById('editActivityValid');
    
    console.log('DOM elements initialized');
}

function initializeEventListeners() {
    // Booking detail links
    document.querySelectorAll('.booking-detail-link').forEach(link => {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            const bookingId = this.dataset.bookingId;
            const booking = findBooking(bookingId);
            
            if (!booking) {
                alert('Maklumat tempahan tidak dijumpai.');
                return;
            }
            
            showBookingModal(booking);
        });
    });

    // Approve button
    if (approveBookingBtn) {
        approveBookingBtn.addEventListener('click', async function (event) {
            event.preventDefault();
            
            const formData = new FormData();
            formData.append('booking_id', actionBookingId.value);
            formData.append('action', 'approved');
            formData.append('admin_comment', '');
            
            approveBookingBtn.classList.add('loading');
            approveBookingBtn.disabled = true;
            if (rejectBookingBtn) rejectBookingBtn.disabled = true;
            
            const data = await sendBookingStatus(formData);
            
            approveBookingBtn.classList.remove('loading');
            approveBookingBtn.disabled = false;
            if (rejectBookingBtn) rejectBookingBtn.disabled = false;
            
            if (data.success) {
                alert(data.message || 'Tempahan berjaya diluluskan.');
                
                modalBookingStatus.textContent = 'Diluluskan';
                modalBookingStatus.className = 'status approved';
                
                approveBookingBtn.style.display = 'none';
                if (rejectBookingBtn) rejectBookingBtn.style.display = 'none';
                
                const approvedBooking = findBooking(actionBookingId.value);
                if (approvedBooking) {
                    approvedBooking.booking_status = 'approved';
                    whatsappBookingBtn.href = createWhatsappLink(approvedBooking);
                } else {
                    whatsappBookingBtn.href = createWhatsappLink({
                        booking_status: 'approved',
                        phone_number: modalPhone.textContent,
                        display_id: modalBookingRef.textContent,
                        slot_display: modalSlot.textContent,
                        package_name: modalPackage.textContent,
                        activity_list: modalActivities.textContent,
                        total_participants: modalParticipants.textContent,
                        formatted_total_fee: modalTotalFee ? modalTotalFee.textContent : undefined
                    });
                }
                
                whatsappBookingBtn.style.display = 'flex';
            } else {
                alert(data.message || 'Gagal mengemaskini tempahan.');
            }
        });
    }

    // Reject button
    if (rejectBookingBtn) {
        rejectBookingBtn.addEventListener('click', function (event) {
            event.preventDefault();
            if (rejectReason) rejectReason.value = '';
            if (rejectModal) rejectModal.classList.add('active');
        });
    }

    // Confirm reject button
    if (confirmRejectBtn) {
        confirmRejectBtn.addEventListener('click', async function (event) {
            event.preventDefault();
            
            const reason = rejectReason ? rejectReason.value.trim() : '';
            
            if (reason === '') {
                alert('Sila masukkan sebab penolakan.');
                return;
            }
            
            const formData = new FormData();
            formData.append('booking_id', actionBookingId.value);
            formData.append('action', 'rejected');
            formData.append('admin_comment', reason);
            
            confirmRejectBtn.classList.add('loading');
            confirmRejectBtn.disabled = true;
            if (cancelRejectBtn) cancelRejectBtn.disabled = true;
            
            const data = await sendBookingStatus(formData);
            
            confirmRejectBtn.classList.remove('loading');
            confirmRejectBtn.disabled = false;
            if (cancelRejectBtn) cancelRejectBtn.disabled = false;
            
            if (data.success) {
                alert(data.message || 'Tempahan berjaya ditolak.');
                
                modalBookingStatus.textContent = 'Ditolak';
                modalBookingStatus.className = 'status rejected';
                if (modalRemark) modalRemark.textContent = reason;
                
                if (approveBookingBtn) approveBookingBtn.style.display = 'none';
                if (rejectBookingBtn) rejectBookingBtn.style.display = 'none';
                if (editBookingBtn) editBookingBtn.style.display = 'none';
                
                const rejectedBooking = findBooking(actionBookingId.value);
                if (rejectedBooking) {
                    rejectedBooking.booking_status = 'rejected';
                    rejectedBooking.admin_remark = reason;
                    whatsappBookingBtn.href = createWhatsappLink(rejectedBooking);
                } else {
                    whatsappBookingBtn.href = createWhatsappLink({
                        booking_status: 'rejected',
                        phone_number: modalPhone.textContent,
                        display_id: modalBookingRef.textContent,
                        admin_remark: reason,
                        formatted_total_fee: modalTotalFee ? modalTotalFee.textContent : undefined
                    });
                }
                
                whatsappBookingBtn.style.display = 'flex';
                
                if (rejectModal) rejectModal.classList.remove('active');
                if (rejectReason) rejectReason.value = '';
            } else {
                alert(data.message || 'Gagal mengemaskini tempahan.');
            }
        });
    }

    // Cancel reject button
    if (cancelRejectBtn) {
        cancelRejectBtn.addEventListener('click', function () {
            if (rejectModal) rejectModal.classList.remove('active');
            if (rejectReason) rejectReason.value = '';
        });
    }

    // Edit button in booking modal
    if (editBookingBtn) {
        editBookingBtn.addEventListener('click', function(event) {
            event.preventDefault();
            console.log('Edit button clicked');
            openEditModal();
        });
    }
    
    // Cancel edit button
    if (cancelEditBtn) {
        cancelEditBtn.addEventListener('click', function() {
            if (editModal) editModal.classList.remove('active');
        });
    }
    
    // Close edit modal on overlay click
    if (editModal) {
        editModal.addEventListener('click', function(event) {
            if (event.target === editModal) {
                editModal.classList.remove('active');
            }
        });
    }
    
    // Save edit form
    if (editBookingForm) {
        editBookingForm.addEventListener('submit', async function(event) {
            event.preventDefault();
            console.log('Form submit triggered');
            await saveEditBooking();
        });
    }
    
    // Direct click handler for save button (backup)
    if (saveEditBtn) {
        saveEditBtn.addEventListener('click', function(event) {
            event.preventDefault();
            console.log('Save button clicked directly');
            saveEditBooking();
        });
    }

    // Search and filter events
    if (bookingSearch) bookingSearch.addEventListener('input', applyFilters);
    if (bookingTypeFilter) bookingTypeFilter.addEventListener('change', applyFilters);
    if (bookingStatusFilter) bookingStatusFilter.addEventListener('change', applyFilters);
    if (bookingResetBtn) bookingResetBtn.addEventListener('click', resetFilters);
    if (bookingExportBtn) bookingExportBtn.addEventListener('click', exportVisibleRows);
    
    // Pagination
    if (prevPageBtn) prevPageBtn.addEventListener('click', () => changePage(-1));
    if (nextPageBtn) nextPageBtn.addEventListener('click', () => changePage(1));

    // Modal close events
    if (modalClose) {
        modalClose.addEventListener('click', () => {
            if (modal) modal.classList.remove('active');
        });
    }

    if (modal) {
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                modal.classList.remove('active');
            }
        });
    }

    if (rejectModal) {
        rejectModal.addEventListener('click', function (event) {
            if (event.target === rejectModal) {
                rejectModal.classList.remove('active');
            }
        });
    }
    
    console.log('Event listeners initialized');
}

// ========================
// HELPER FUNCTIONS
// ========================

function findBooking(bookingId) {
    return bookingData.find(item => String(item.booking_id) === String(bookingId));
}

function setStatusLabel(statusClass, label) {
    if (modalBookingStatus) {
        modalBookingStatus.textContent = label;
        modalBookingStatus.className = 'status ' + statusClass;
    }
}

function toProperCase(str) {
    if (!str) return '';
    return str.toLowerCase().replace(/\b\w/g, char => char.toUpperCase());
}

// Global function for inline onclick handlers
function openBookingModal(bookingId) {
    try {
        const booking = findBooking(bookingId);
        if (!booking) {
            alert('Maklumat tempahan tidak dijumpai.');
            return;
        }
        showBookingModal(booking);
    } catch (e) {
        console.error('openBookingModal error', e);
        alert('Gagal membuka modal. Semak konsol.');
    }
}

// ========================
// WHATSAPP LINK GENERATION
// ========================

function formatWhatsappPhone(phone) {
    let cleanPhone = String(phone || '').replace(/\D/g, '');
    if (cleanPhone.startsWith('0')) {
        cleanPhone = '6' + cleanPhone;
    }
    return cleanPhone;
}

function createWhatsappLink(booking) {
    const phone = formatWhatsappPhone(booking.phone_number || '');
    let message = '';

    if (booking.booking_status === 'approved') {
        const feeDisplay = booking.formatted_total_fee || (booking.total_fee !== undefined ? ('RM ' + Number(booking.total_fee).toFixed(2)) : 'RM 0.00');
        const isLawatan = String(booking.package_name || '').toLowerCase().includes('lawatan');
        message = `GALERI SERAMIK MBPG.
    
Tempahan anda, ${booking.display_id || '-'} telah DILULUSKAN.

Tarikh & Masa: ${booking.slot_display || '-'}
Pakej: ${booking.package_name || '-'}
Aktiviti: ${booking.activity_list || '-'}
Jumlah Peserta: ${booking.total_participants || '-'}
Jumlah Bayaran: ${feeDisplay}${isLawatan ? ' (jika pakej lawatan berkumpulan)' : ''}

Untuk maklumat lanjut, sila hubungi pihak Galeri Seramik MBPG.
019-20828241 (En. )

Terima kasih.`;
    }

    if (booking.booking_status === 'rejected') {
        message = `GALERI SERAMIK MBPG. 
    
Tempahan anda, ${booking.display_id || '-'} telah DITOLAK.

Sebab: ${booking.admin_remark || '-'}

Untuk maklumat lanjut, sila hubungi pihak Galeri Seramik MBPG.
019-20828241 (En. )

Terima kasih.`;
    }

    return `https://wa.me/${phone}?text=${encodeURIComponent(message)}`;
}

// ========================
// BOOKING MODAL FUNCTIONS
// ========================

function showBookingModal(booking) {
    if (modalBookingRef) modalBookingRef.textContent = booking.display_id || '-';
    setStatusLabel(booking.status_class || 'pending', booking.status_label || '-');
    
    if (modalOrganization) modalOrganization.textContent = toProperCase(booking.organization_name || '-');
    if (modalPhone) modalPhone.textContent = booking.phone_number || '-';
    if (modalEmail) modalEmail.textContent = (booking.email || '-').toLowerCase();
    if (modalSlot) modalSlot.textContent = booking.slot_display || '-';
    if (modalPackage) modalPackage.textContent = toProperCase(booking.package_name || '-');
    if (modalActivities) modalActivities.textContent = booking.activity_list || 'Tiada';
    if (modalParticipants) modalParticipants.textContent = booking.total_participants || '-';
    if (modalRemark) modalRemark.textContent = booking.admin_comment || booking.admin_remark || '-';
    if (modalTotalFee) modalTotalFee.textContent = booking.formatted_total_fee || 'RM 0.00';
    
    if (actionBookingId) actionBookingId.value = booking.booking_id || '';
    if (actionBookingStatus) actionBookingStatus.value = booking.booking_status || '';
    
    if (booking.booking_status === 'pending' || booking.status_class === 'pending') {
        if (approveBookingBtn) approveBookingBtn.style.display = 'inline-block';
        if (rejectBookingBtn) rejectBookingBtn.style.display = 'inline-block';
        if (whatsappBookingBtn) {
            whatsappBookingBtn.style.display = 'none';
            whatsappBookingBtn.href = '#';
        }
    } else {
        if (approveBookingBtn) approveBookingBtn.style.display = 'none';
        if (rejectBookingBtn) rejectBookingBtn.style.display = 'none';
        
        if (booking.booking_status === 'approved' || booking.booking_status === 'rejected') {
            if (whatsappBookingBtn) {
                whatsappBookingBtn.href = createWhatsappLink(booking);
                whatsappBookingBtn.style.display = 'flex';
            }
        } else {
            if (whatsappBookingBtn) {
                whatsappBookingBtn.style.display = 'none';
                whatsappBookingBtn.href = '#';
            }
        }
    }
    
    if (modal) modal.classList.add('active');
}

async function sendBookingStatus(formData) {
    const response = await fetch('booking_email.php', {
        method: 'POST',
        body: formData
    });
    
    const text = await response.text();
    
    console.log('PHP RESPONSE:', text);
    
    try {
        return JSON.parse(text);
    } catch (error) {
        console.error('JSON PARSE ERROR:', error);
        return {
            success: false,
            message: 'PHP tidak return JSON. Semak Console untuk PHP RESPONSE.'
        };
    }
}

// ========================
// EDIT BOOKING FUNCTIONS
// ========================

// Load packages
async function loadPackages() {
    try {
        const response = await fetch('get_packages.php');
        const data = await response.json();
        
        if (data.success && data.packages) {
            allPackages = data.packages;
            console.log('Packages loaded:', allPackages.length);
            
            if (editPackageSelect) {
                editPackageSelect.innerHTML = '<option value="">Pilih Pakej</option>';
                allPackages.forEach(pkg => {
                    editPackageSelect.innerHTML += `
                        <option value="${pkg.package_id}" data-requires-activity="${pkg.requires_activity_selection}">
                            ${pkg.package_name}
                        </option>
                    `;
                });
            }
        }
    } catch (error) {
        console.error('Error loading packages:', error);
    }
}

// Load all activities
async function loadAllActivities() {
    try {
        const response = await fetch('get_booking_activities.php?mode=all');
        const data = await response.json();
        
        if (data.success && data.activities) {
            allActivities = data.activities;
            console.log('All activities loaded:', allActivities.length);
        }
    } catch (error) {
        console.error('Error loading activities:', error);
    }
}

// Load current booking activities
async function loadCurrentBookingActivities(bookingId) {
    try {
        const response = await fetch(`get_booking_activities.php?booking_id=${bookingId}`);
        const data = await response.json();
        
        if (data.success && data.activities && data.activities.length > 0) {
            currentBookingActivities = {};
            data.activities.forEach(activity => {
                currentBookingActivities[activity.activity_id] = {
                    name: activity.activity_name,
                    count: activity.participant_count || 0,
                    max: activity.default_capacity || 0,
                    description: activity.description || '',
                    image_url: activity.image_url || ''
                };
            });
            console.log('Loaded current activities:', currentBookingActivities);
        } else {
            console.log('No current activities found');
            currentBookingActivities = {};
        }
    } catch (error) {
        console.error('Error loading booking activities:', error);
        currentBookingActivities = {};
    }
}

// Load available slots
async function loadAvailableSlots(packageId, currentSlotIdParam = null) {
    console.log('Loading slots for package:', packageId);
    
    try {
        const response = await fetch(`get_available_slots.php?package_id=${packageId}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Slots data:', data);
        
        if (data.success && data.slots && data.slots.length > 0) {
            availableSlotsData = data.slots;
            
            // Get unique dates
            const uniqueDates = [...new Set(data.slots.map(slot => slot.slot_date))];
            
            // Populate date dropdown
            if (editSlotDate) {
                editSlotDate.innerHTML = '<option value="">Pilih Tarikh</option>';
                uniqueDates.forEach(date => {
                    const formattedDate = new Date(date).toLocaleDateString('ms-MY', {
                        day: 'numeric',
                        month: 'short',
                        year: 'numeric'
                    });
                    editSlotDate.innerHTML += `<option value="${date}">${formattedDate}</option>`;
                });
                
                // Remove old event listener before adding new one
                editSlotDate.removeEventListener('change', handleSlotDateChange);
                editSlotDate.addEventListener('change', handleSlotDateChange);
            }
        } else {
            console.log('No available slots');
            if (editSlotDate) {
                editSlotDate.innerHTML = '<option value="">Tiada tarikh tersedia</option>';
            }
        }
    } catch (error) {
        console.error('Error loading slots:', error);
    }
}

// Handle slot date change
function handleSlotDateChange() {
    console.log('Date changed to:', this.value);
    loadSlotTimesForDate(this.value);
}

// Load time slots for date
function loadSlotTimesForDate(date, selectedSlotId = null) {
    if (editSlotTime) {
        editSlotTime.innerHTML = '<option value="">Pilih Slot Masa</option>';
        
        if (date && availableSlotsData) {
            const slotsForDate = availableSlotsData.filter(slot => slot.slot_date === date);
            
            console.log('Available slots for date', date, ':', slotsForDate.length);
            
            if (slotsForDate.length === 0) {
                editSlotTime.innerHTML += '<option value="" disabled>Tiada slot tersedia untuk tarikh ini</option>';
            } else {
                slotsForDate.forEach(slot => {
                    const startTime = new Date(`2000-01-01 ${slot.start_time}`).toLocaleTimeString('ms-MY', {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    });
                    const endTime = new Date(`2000-01-01 ${slot.end_time}`).toLocaleTimeString('ms-MY', {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    });
                    
                    editSlotTime.innerHTML += `
                        <option value="${slot.slot_id}">
                            ${startTime} - ${endTime}
                        </option>
                    `;
                });
            }
        }
    }
}

// Handle package change
function handlePackageChange() {
    const selectedOption = editPackageSelect.options[editPackageSelect.selectedIndex];
    const requiresActivity = selectedOption.dataset.requiresActivity === '1';
    const newPackageId = editPackageSelect.value;
    
    console.log('Package changed - Requires activity:', requiresActivity, 'Package ID:', newPackageId);
    
    if (requiresActivity) {
        // Show activity section
        if (editActivitySection) editActivitySection.style.display = 'block';
        
        // Reset activities with equal distribution
        const totalParticipants = parseInt(editParticipants?.value) || 0;
        resetActivityDistribution(totalParticipants);
        renderActivityList();
    } else {
        // Hide activity section for non-activity packages
        if (editActivitySection) editActivitySection.style.display = 'none';
        currentBookingActivities = {};
    }
    
    // Load slots for new package
    if (newPackageId) {
        loadAvailableSlots(newPackageId);
    }
    
    // Update current package ID
    currentPackageId = newPackageId;
}

// Handle participant count change
function handleParticipantChange() {
    const totalParticipants = parseInt(this.value) || 0;
    const selectedOption = editPackageSelect?.options[editPackageSelect?.selectedIndex];
    const requiresActivity = selectedOption?.dataset.requiresActivity === '1';
    
    if (requiresActivity && totalParticipants > 0) {
        resetActivityDistribution(totalParticipants);
        renderActivityList();
    }
    
    updateActivityTotal();
}

// Reset activity distribution evenly
function resetActivityDistribution(totalParticipants) {
    if (!allActivities || allActivities.length === 0) return;
    
    const activityCount = allActivities.length;
    const baseCount = Math.floor(totalParticipants / activityCount);
    let remainder = totalParticipants % activityCount;
    
    currentBookingActivities = {};
    
    allActivities.forEach((activity, index) => {
        let count = baseCount;
        if (remainder > 0) {
            count++;
            remainder--;
        }
        
        // Respect max capacity
        count = Math.min(count, activity.default_capacity);
        
        currentBookingActivities[activity.activity_id] = {
            name: activity.activity_name,
            count: count,
            max: activity.default_capacity,
            description: activity.description,
            image_url: activity.image_url
        };
    });
}

// Render activity list
function renderActivityList() {
    if (!editActivityList || !allActivities) return;
    
    let html = '';
    
    allActivities.forEach(activity => {
        const activityData = currentBookingActivities[activity.activity_id] || {
            count: 0,
            max: activity.default_capacity,
            name: activity.activity_name,
            description: activity.description,
            image_url: activity.image_url
        };
        
        html += `
            <div class="edit-activity-item" data-activity-id="${activity.activity_id}">
                <div class="edit-activity-info" style="flex: 1;">
                    <h5>${activity.activity_name}</h5>
                    <p>${activity.description || ''}</p>
                </div>
                
                <div>
                    <div class="edit-activity-counter">
                        <button type="button" class="edit-activity-minus" data-activity-id="${activity.activity_id}">−</button>
                        <input type="number" 
                               class="edit-activity-input" 
                               data-activity-id="${activity.activity_id}" 
                               value="${activityData.count || 0}" 
                               min="0" 
                               max="${activity.default_capacity || 999}">
                        <button type="button" class="edit-activity-plus" data-activity-id="${activity.activity_id}">+</button>
                    </div>
                    <div class="edit-activity-max">
                        Maksimum: ${activity.default_capacity || '-'} orang
                    </div>
                </div>
            </div>
        `;
    });
    
    editActivityList.innerHTML = html;
    
    // Add event listeners
    document.querySelectorAll('.edit-activity-plus').forEach(btn => {
        btn.addEventListener('click', function() {
            const activityId = this.dataset.activityId;
            changeActivityCount(activityId, 1);
        });
    });
    
    document.querySelectorAll('.edit-activity-minus').forEach(btn => {
        btn.addEventListener('click', function() {
            const activityId = this.dataset.activityId;
            changeActivityCount(activityId, -1);
        });
    });
    
    document.querySelectorAll('.edit-activity-input').forEach(input => {
        input.addEventListener('change', function() {
            const activityId = this.dataset.activityId;
            const newValue = parseInt(this.value) || 0;
            updateActivityCount(activityId, newValue);
        });
    });
    
    updateActivityTotal();
}

// Change activity count by delta
function changeActivityCount(activityId, delta) {
    if (currentBookingActivities[activityId] !== undefined) {
        const newCount = (currentBookingActivities[activityId].count || 0) + delta;
        updateActivityCount(activityId, newCount);
    }
}

// Update activity count
function updateActivityCount(activityId, newValue) {
    const max = allActivities.find(a => a.activity_id == activityId)?.default_capacity || 999;
    const count = Math.max(0, Math.min(newValue, max));
    
    if (currentBookingActivities[activityId]) {
        currentBookingActivities[activityId].count = count;
    } else {
        const activity = allActivities.find(a => a.activity_id == activityId);
        currentBookingActivities[activityId] = {
            name: activity?.activity_name || '',
            count: count,
            max: max,
            description: activity?.description || '',
            image_url: activity?.image_url || ''
        };
    }
    
    // Update input field
    const input = document.querySelector(`.edit-activity-input[data-activity-id="${activityId}"]`);
    if (input) input.value = count;
    
    // Update button states
    const minusBtn = document.querySelector(`.edit-activity-minus[data-activity-id="${activityId}"]`);
    const plusBtn = document.querySelector(`.edit-activity-plus[data-activity-id="${activityId}"]`);
    
    if (minusBtn) minusBtn.disabled = count <= 0;
    if (plusBtn) plusBtn.disabled = count >= max;
    
    updateActivityTotal();
}

// Update activity total display
function updateActivityTotal() {
    const totalParticipants = parseInt(editParticipants?.value) || 0;
    let totalActivity = 0;
    
    Object.values(currentBookingActivities).forEach(activity => {
        totalActivity += activity.count || 0;
    });
    
    if (editActivitySum) editActivitySum.textContent = totalActivity;
    if (editRequiredTotal) editRequiredTotal.textContent = totalParticipants;
    if (editTotalParticipantsLabel) editTotalParticipantsLabel.textContent = totalParticipants;
    
    // Show/hide warnings
    if (editActivitySection && editActivitySection.style.display !== 'none') {
        if (totalActivity === totalParticipants && totalParticipants > 0) {
            if (editActivityValid) editActivityValid.style.display = 'inline';
            if (editActivityWarning) editActivityWarning.style.display = 'none';
        } else {
            if (editActivityValid) editActivityValid.style.display = 'none';
            if (editActivityWarning) editActivityWarning.style.display = 'inline';
        }
    }
}

// Open edit modal
async function openEditModal() {
    const bookingId = actionBookingId.value;
    const booking = findBooking(bookingId);
    
    if (!booking) {
        alert('Maklumat tempahan tidak dijumpai.');
        return;
    }
    
    console.log('Opening edit modal for booking:', booking);
    
    // Store current values
    currentPackageId = booking.package_id;
    currentSlotId = booking.slot_id;
    
    // Populate basic fields
    if (editBookingId) editBookingId.value = booking.booking_id;
    if (editOrganization) editOrganization.value = toProperCase(booking.organization_name || '');
    if (editContactPerson) editContactPerson.value = toProperCase(booking.contact_person || '');
    if (editPhoneNumber) editPhoneNumber.value = booking.phone_number || '';
    if (editEmail) editEmail.value = (booking.email || '').toLowerCase();
    if (editParticipants) editParticipants.value = booking.total_participants || '';
    if (editAdminComment) editAdminComment.value = booking.admin_comment || booking.admin_remark || '';
    
    // Show current slot
    if (editSlotDisplay) {
        editSlotDisplay.value = `Semasa: ${booking.slot_display || '-'}`;
    }
    
    // Load packages and set current
    await loadPackages();
    if (editPackageSelect) {
        editPackageSelect.value = booking.package_id || '';
    }
    
    // Load all activities
    await loadAllActivities();
    
    // Load current booking activities
    await loadCurrentBookingActivities(bookingId);
    
    // Check if current package requires activities
    const selectedOption = editPackageSelect?.options[editPackageSelect?.selectedIndex];
    const requiresActivity = selectedOption?.dataset.requiresActivity === '1';
    
    if (requiresActivity) {
        if (editActivitySection) editActivitySection.style.display = 'block';
        
        // If no current activities, distribute evenly
        if (Object.keys(currentBookingActivities).length === 0) {
            resetActivityDistribution(parseInt(booking.total_participants) || 0);
        }
        renderActivityList();
    } else {
        if (editActivitySection) editActivitySection.style.display = 'none';
    }
    
    // Load available slots
    await loadAvailableSlots(booking.package_id);
    
    // Add event listeners
    if (editPackageSelect) {
        editPackageSelect.removeEventListener('change', handlePackageChange);
        editPackageSelect.addEventListener('change', handlePackageChange);
    }
    
    if (editParticipants) {
        editParticipants.removeEventListener('input', handleParticipantChange);
        editParticipants.addEventListener('input', handleParticipantChange);
    }
    
    // Show the edit modal
    if (editModal) editModal.classList.add('active');
    
    // Close the booking detail modal
    if (modal) modal.classList.remove('active');
}

// Save edit booking
async function saveEditBooking() {
    console.log('saveEditBooking called');
    
    if (!editBookingId) {
        console.error('editBookingId element not found');
        alert('Ralat: ID tempahan tidak dijumpai.');
        return;
    }
    
    const bookingId = editBookingId.value;
    
    if (!bookingId) {
        alert('ID tempahan tidak sah.');
        return;
    }
    
    if (!editOrganization || !editContactPerson || !editPhoneNumber || !editEmail || !editParticipants) {
        console.error('Required form fields not found');
        alert('Ralat: Borang tidak lengkap.');
        return;
    }
    
    const orgName = editOrganization.value.trim();
    const contactPerson = editContactPerson.value.trim();
    const phoneNumber = editPhoneNumber.value.trim();
    const email = editEmail.value.trim();
    const participants = editParticipants.value;
    
    if (!orgName || !contactPerson || !phoneNumber || !email || !participants) {
        alert('Sila isi semua maklumat yang diperlukan.');
        return;
    }
    
    // Check if package is selected
    if (!editPackageSelect || !editPackageSelect.value) {
        alert('Sila pilih pakej.');
        return;
    }
    
    // Check activity totals if package requires activities
    const selectedOption = editPackageSelect.options[editPackageSelect.selectedIndex];
    const requiresActivity = selectedOption?.dataset.requiresActivity === '1';
    
    if (requiresActivity) {
        const totalParticipants = parseInt(participants) || 0;
        let totalActivity = 0;
        
        Object.values(currentBookingActivities).forEach(activity => {
            totalActivity += activity.count || 0;
        });
        
        if (totalActivity !== totalParticipants) {
            alert('Jumlah agihan aktiviti mestilah sama dengan jumlah peserta!');
            return;
        }
    }
    
    const formData = new FormData();
    formData.append('booking_id', bookingId);
    formData.append('action', 'edit');
    formData.append('organization_name', orgName);
    formData.append('contact_person', contactPerson);
    formData.append('phone_number', phoneNumber);
    formData.append('email', email);
    formData.append('total_participants', participants);
    formData.append('admin_comment', editAdminComment ? editAdminComment.value : '');
    formData.append('package_id', editPackageSelect.value);
    
    // Add slot if changed
    if (editSlotTime && editSlotTime.value) {
        formData.append('slot_id', editSlotTime.value);
    }
    
    // Add activities if package requires them
    if (requiresActivity) {
        Object.entries(currentBookingActivities).forEach(([activityId, activity]) => {
            formData.append(`activity_participants[${activityId}]`, activity.count || 0);
        });
    }
    
    // Show loading state
    if (saveEditBtn) {
        saveEditBtn.classList.add('loading');
        saveEditBtn.disabled = true;
    }
    if (cancelEditBtn) cancelEditBtn.disabled = true;
    
    try {
        console.log('Sending edit request...');
        const response = await fetch('booking_email.php', {
            method: 'POST',
            body: formData
        });
        
        const text = await response.text();
        console.log('Response:', text);
        
        let data;
        try {
            data = JSON.parse(text);
        } catch (error) {
            console.error('JSON PARSE ERROR:', error);
            throw new Error('Invalid response from server');
        }
        
        if (data.success) {
            alert(data.message || 'Tempahan berjaya dikemaskini.');
            window.location.reload();
        } else {
            alert(data.message || 'Gagal mengemaskini tempahan.');
        }
    } catch (error) {
        console.error('Edit error:', error);
        alert('Ralat sistem: ' + error.message);
    } finally {
        if (saveEditBtn) {
            saveEditBtn.classList.remove('loading');
            saveEditBtn.disabled = false;
        }
        if (cancelEditBtn) cancelEditBtn.disabled = false;
    }
}

// ========================
// TABLE FUNCTIONS
// ========================

function normalizeText(text) {
    return String(text || '').trim().toLowerCase();
}

function rowMatchesFilters(row, searchTerm, typeFilter, statusFilter) {
    const cells = row.querySelectorAll('td');
    
    const rowText = [
        cells[1]?.textContent || '',
        cells[2]?.textContent || '',
        cells[3]?.textContent || '',
        cells[5]?.textContent || ''
    ].join(' ').toLowerCase();
    
    const statusText = normalizeText(cells[5]?.textContent || '');
    const typeText = normalizeText(cells[2]?.textContent || '');
    
    return (
        (!searchTerm || rowText.includes(searchTerm)) &&
        (typeFilter === 'all' || typeText === typeFilter) &&
        (statusFilter === 'all' || statusText === statusFilter)
    );
}

function getFilteredRows() {
    if (!bookingSearch || !bookingTypeFilter || !bookingStatusFilter) return allRows;
    
    const searchTerm = normalizeText(bookingSearch.value || '');
    const typeFilter = normalizeText(bookingTypeFilter.value || 'all');
    const statusFilter = normalizeText(bookingStatusFilter.value || 'all');
    
    return allRows.filter(row => rowMatchesFilters(row, searchTerm, typeFilter, statusFilter));
}

function renderTablePage() {
    const filteredRows = getFilteredRows();
    const totalPages = Math.max(1, Math.ceil(filteredRows.length / pageSize));
    
    if (currentPage > totalPages) {
        currentPage = totalPages;
    }
    
    allRows.forEach(row => {
        row.style.display = 'none';
    });
    
    filteredRows.forEach((row, index) => {
        const pageIndex = Math.floor(index / pageSize) + 1;
        row.style.display = pageIndex === currentPage ? '' : 'none';
    });
}

function updateFooter() {
    const filteredRows = getFilteredRows();
    const total = filteredRows.length;
    const totalPages = Math.max(1, Math.ceil(total / pageSize));
    const first = total === 0 ? 0 : (currentPage - 1) * pageSize + 1;
    const last = total === 0 ? 0 : Math.min(currentPage * pageSize, total);
    
    if (bookingFooterText) {
        bookingFooterText.textContent = `Showing ${first} to ${last} out of ${total} entries`;
    }
    
    if (bookingPaginationInfo) {
        bookingPaginationInfo.textContent = `Page ${currentPage} of ${totalPages}`;
    }
    
    if (prevPageBtn) {
        prevPageBtn.disabled = currentPage <= 1;
    }
    
    if (nextPageBtn) {
        nextPageBtn.disabled = currentPage >= totalPages;
    }
}

function applyFilters() {
    currentPage = 1;
    renderTablePage();
    updateFooter();
}

function changePage(offset) {
    const filteredRows = getFilteredRows();
    const totalPages = Math.max(1, Math.ceil(filteredRows.length / pageSize));
    
    currentPage = Math.min(Math.max(currentPage + offset, 1), totalPages);
    
    renderTablePage();
    updateFooter();
}

function resetFilters() {
    if (bookingSearch) bookingSearch.value = '';
    if (bookingTypeFilter) bookingTypeFilter.value = 'all';
    if (bookingStatusFilter) bookingStatusFilter.value = 'all';
    applyFilters();
}

function downloadCsv(filename, csvContent) {
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', filename);
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    URL.revokeObjectURL(url);
}

function exportVisibleRows() {
    if (!bookingTableBody) return;
    
    const rows = Array.from(bookingTableBody.querySelectorAll('tr')).filter(row => row.style.display !== 'none');
    
    if (rows.length === 0) {
        alert('Tiada baris untuk dieksport.');
        return;
    }
    
    const csvRows = [];
    csvRows.push(['ID Tempahan', 'Nama Organisasi', 'Jenis Tempahan', 'Tarikh & Slot Masa', 'Pax', 'Jumlah Bayaran', 'Status'].join(','));
    
    rows.forEach(row => {
        const cells = Array.from(row.querySelectorAll('td')).map(cell => `"${cell.textContent.trim().replace(/"/g, '""')}"`);
        csvRows.push(cells.join(','));
    });
    
    downloadCsv('tempahan-export.csv', csvRows.join('\n'));
}
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

// Add booking modal elements
let addBookingModal, addBookingForm;
let addOrganization, addContactPerson, addPhoneNumber, addEmail;
let addPackageSelect, addParticipants;
let addSlotDate, addSlotTime;
let addActivitySection, addActivityList;
let addActivitySum, addRequiredTotal, addTotalParticipantsLabel;
let addActivityWarning, addActivityValid;
let addAdminComment;
let saveAddBtn, cancelAddBtn;
let addBookingBtn;

// Add booking data storage
let addAvailableSlotsData = [];
let addCurrentActivities = {};

// Initialize everything when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    const bookingDataElement = document.getElementById('booking-data');
    if (bookingDataElement) {
        try {
            bookingData = JSON.parse(bookingDataElement.textContent);
            console.log('Booking data loaded:', bookingData.length, 'records');
        } catch(e) {
            console.error('Failed to parse booking data:', e);
        }
    }
    
    initializeDOMElements();
    initializeEventListeners();
    
    if (bookingTableBody) {
        allRows = Array.from(bookingTableBody.querySelectorAll('tr'));
        applyFilters();
    }
});

function initializeDOMElements() {
    modal = document.getElementById('bookingModal');
    modalClose = document.querySelector('.booking-modal-close');
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
    actionBookingId = document.getElementById('actionBookingId');
    actionBookingStatus = document.getElementById('actionBookingStatus');
    approveBookingBtn = document.getElementById('approveBookingBtn');
    rejectBookingBtn = document.getElementById('rejectBookingBtn');
    whatsappBookingBtn = document.getElementById('whatsappBookingBtn');
    editBookingBtn = document.getElementById('editBookingBtn');
    rejectModal = document.getElementById('rejectModal');
    rejectReason = document.getElementById('rejectReason');
    confirmRejectBtn = document.getElementById('confirmRejectBtn');
    cancelRejectBtn = document.getElementById('cancelRejectBtn');
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
    editPackageSelect = document.getElementById('editPackageSelect');
    editSlotDisplay = document.getElementById('editSlotDisplay');
    editSlotDate = document.getElementById('editSlotDate');
    editSlotTime = document.getElementById('editSlotTime');
    editSlotId = document.getElementById('editSlotId');
    editActivitySection = document.getElementById('editActivitySection');
    editActivityList = document.getElementById('editActivityList');
    editActivitySum = document.getElementById('editActivitySum');
    editRequiredTotal = document.getElementById('editRequiredTotal');
    editTotalParticipantsLabel = document.getElementById('editTotalParticipantsLabel');
    editActivityWarning = document.getElementById('editActivityWarning');
    editActivityValid = document.getElementById('editActivityValid');
    addBookingBtn = document.getElementById('addBookingBtn');
    addBookingModal = document.getElementById('addBookingModal');
    addBookingForm = document.getElementById('addBookingForm');
    addOrganization = document.getElementById('addOrganization');
    addContactPerson = document.getElementById('addContactPerson');
    addPhoneNumber = document.getElementById('addPhoneNumber');
    addEmail = document.getElementById('addEmail');
    addPackageSelect = document.getElementById('addPackageSelect');
    addParticipants = document.getElementById('addParticipants');
    addSlotDate = document.getElementById('addSlotDate');
    addSlotTime = document.getElementById('addSlotTime');
    addActivitySection = document.getElementById('addActivitySection');
    addActivityList = document.getElementById('addActivityList');
    addActivitySum = document.getElementById('addActivitySum');
    addRequiredTotal = document.getElementById('addRequiredTotal');
    addTotalParticipantsLabel = document.getElementById('addTotalParticipantsLabel');
    addActivityWarning = document.getElementById('addActivityWarning');
    addActivityValid = document.getElementById('addActivityValid');
    addAdminComment = document.getElementById('addAdminComment');
    saveAddBtn = document.getElementById('saveAddBtn');
    cancelAddBtn = document.getElementById('cancelAddBtn');
    
    console.log('DOM elements initialized');
    console.log('Add booking button found:', !!addBookingBtn);
}

function formatTimeString(timeString) {
    if (!timeString || !timeString.includes(':')) return timeString || '-';
    const parts = timeString.split(':');
    const hours = parseInt(parts[0]);
    const minutes = parts[1] || '00';
    const period = hours >= 12 ? 'PM' : 'AM';
    const displayHours = hours % 12 || 12;
    return `${displayHours}:${minutes} ${period}`;
}

function initializeEventListeners() {
    // ============ BOOKING DETAIL LINKS ============
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

    // ============ ADD BOOKING BUTTON ============
    if (addBookingBtn) {
        console.log('Attaching add booking button events');
        addBookingBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Add booking button clicked!');
            openAddBookingModal();
        });
    } else {
        console.error('Add booking button NOT FOUND!');
    }

    // ============ CANCEL ADD BUTTON ============
    if (cancelAddBtn) {
        cancelAddBtn.addEventListener('click', function() {
            if (addBookingModal) addBookingModal.classList.remove('active');
        });
    }

    // ============ CLOSE ADD MODAL ON OVERLAY CLICK ============
    if (addBookingModal) {
        addBookingModal.addEventListener('click', function(event) {
            if (event.target === addBookingModal) {
                addBookingModal.classList.remove('active');
            }
        });
    }

    // ============ ADD BOOKING FORM SUBMIT ============
    if (addBookingForm) {
        addBookingForm.addEventListener('submit', async function(event) {
            event.preventDefault();
            await saveAddBooking();
        });
    }

    // ============ APPROVE BUTTON ============
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

    // ============ REJECT BUTTON ============
    if (rejectBookingBtn) {
        rejectBookingBtn.addEventListener('click', function (event) {
            event.preventDefault();
            if (rejectReason) rejectReason.value = '';
            if (rejectModal) rejectModal.classList.add('active');
        });
    }

    // ============ CONFIRM REJECT BUTTON ============
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

    // ============ CANCEL REJECT BUTTON ============
    if (cancelRejectBtn) {
        cancelRejectBtn.addEventListener('click', function () {
            if (rejectModal) rejectModal.classList.remove('active');
            if (rejectReason) rejectReason.value = '';
        });
    }

    // ============ EDIT BUTTON ============
    if (editBookingBtn) {
        editBookingBtn.addEventListener('click', function(event) {
            event.preventDefault();
            console.log('Edit button clicked');
            openEditModal();
        });
    }
    
    // ============ CANCEL EDIT BUTTON ============
    if (cancelEditBtn) {
        cancelEditBtn.addEventListener('click', function() {
            if (editModal) editModal.classList.remove('active');
        });
    }
    
    // ============ CLOSE EDIT MODAL ON OVERLAY CLICK ============
    if (editModal) {
        editModal.addEventListener('click', function(event) {
            if (event.target === editModal) {
                editModal.classList.remove('active');
            }
        });
    }
    
    // ============ SAVE EDIT FORM ============
    if (editBookingForm) {
        editBookingForm.addEventListener('submit', async function(event) {
            event.preventDefault();
            await saveEditBooking();
        });
    }
    
    // ============ SAVE EDIT BUTTON DIRECT CLICK ============
    if (saveEditBtn) {
        saveEditBtn.addEventListener('click', function(event) {
            event.preventDefault();
            saveEditBooking();
        });
    }

    // ============ TABLE SEARCH & FILTERS ============
    if (bookingSearch) bookingSearch.addEventListener('input', applyFilters);
    if (bookingTypeFilter) bookingTypeFilter.addEventListener('change', applyFilters);
    if (bookingStatusFilter) bookingStatusFilter.addEventListener('change', applyFilters);
    if (bookingResetBtn) bookingResetBtn.addEventListener('click', resetFilters);
    if (bookingExportBtn) bookingExportBtn.addEventListener('click', exportVisibleRows);
    
    // ============ PAGINATION ============
    if (prevPageBtn) prevPageBtn.addEventListener('click', () => changePage(-1));
    if (nextPageBtn) nextPageBtn.addEventListener('click', () => changePage(1));

    // ============ MODAL CLOSE EVENTS ============
    if (modalClose) {
        modalClose.addEventListener('click', () => {
            if (modal) modal.classList.remove('active');
        });
    }
    if (modal) {
        modal.addEventListener('click', function (event) {
            if (event.target === modal) modal.classList.remove('active');
        });
    }
    if (rejectModal) {
        rejectModal.addEventListener('click', function (event) {
            if (event.target === rejectModal) {
                rejectModal.classList.remove('active');
            }
        });
    }
    
    console.log('All event listeners initialized');
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
    if (cleanPhone.startsWith('0')) cleanPhone = '6' + cleanPhone;
    return cleanPhone;
}

function createWhatsappLink(booking) {
    const phone = formatWhatsappPhone(booking.phone_number || '');
    let message = '';
    if (booking.booking_status === 'approved') {
        const feeDisplay = booking.formatted_total_fee || (booking.total_fee !== undefined ? ('RM ' + Number(booking.total_fee).toFixed(2)) : 'RM 0.00');
        const isLawatan = String(booking.package_name || '').toLowerCase().includes('lawatan');
        message = `GALERI SERAMIK MBPG.\n\nTempahan anda, ${booking.display_id || '-'} telah DILULUSKAN.\n\nTarikh & Masa: ${booking.slot_display || '-'}\nPakej: ${booking.package_name || '-'}\nAktiviti: ${booking.activity_list || '-'}\nJumlah Peserta: ${booking.total_participants || '-'}\nJumlah Bayaran: ${feeDisplay}${isLawatan ? ' (jika pakej lawatan berkumpulan)' : ''}\n\nUntuk maklumat lanjut, sila hubungi pihak Galeri Seramik MBPG.\n019-20828241 (En. )\n\nTerima kasih.`;
    }
    if (booking.booking_status === 'rejected') {
        message = `GALERI SERAMIK MBPG.\n\nTempahan anda, ${booking.display_id || '-'} telah DITOLAK.\n\nSebab: ${booking.admin_remark || '-'}\n\nUntuk maklumat lanjut, sila hubungi pihak Galeri Seramik MBPG.\n019-20828241 (En. )\n\nTerima kasih.`;
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
        if (whatsappBookingBtn) { whatsappBookingBtn.style.display = 'none'; whatsappBookingBtn.href = '#'; }
    } else {
        if (approveBookingBtn) approveBookingBtn.style.display = 'none';
        if (rejectBookingBtn) rejectBookingBtn.style.display = 'none';
        if (booking.booking_status === 'approved' || booking.booking_status === 'rejected') {
            if (whatsappBookingBtn) { whatsappBookingBtn.href = createWhatsappLink(booking); whatsappBookingBtn.style.display = 'flex'; }
        } else {
            if (whatsappBookingBtn) { whatsappBookingBtn.style.display = 'none'; whatsappBookingBtn.href = '#'; }
        }
    }
    if (modal) modal.classList.add('active');
}

async function sendBookingStatus(formData) {
    const response = await fetch('booking_email.php', { method: 'POST', body: formData });
    const text = await response.text();
    console.log('PHP RESPONSE:', text);
    try { return JSON.parse(text); } 
    catch (error) { console.error('JSON PARSE ERROR:', error); return { success: false, message: 'PHP tidak return JSON.' }; }
}

// ========================
// EDIT BOOKING FUNCTIONS
// ========================

async function loadPackages() {
    try {
        const response = await fetch('get_packages.php');
        const data = await response.json();
        if (data.success && data.packages) {
            allPackages = data.packages;
            if (editPackageSelect) {
                editPackageSelect.innerHTML = '<option value="">Pilih Pakej</option>';
                allPackages.forEach(pkg => {
                    editPackageSelect.innerHTML += `<option value="${pkg.package_id}" data-requires-activity="${pkg.requires_activity_selection}">${pkg.package_name}</option>`;
                });
            }
        }
    } catch (error) { console.error('Error loading packages:', error); }
}

async function loadAllActivities() {
    try {
        const response = await fetch('get_booking_activities.php?mode=all');
        const data = await response.json();
        if (data.success && data.activities) { allActivities = data.activities; }
    } catch (error) { console.error('Error loading activities:', error); }
}

async function loadCurrentBookingActivities(bookingId) {
    try {
        const response = await fetch(`get_booking_activities.php?booking_id=${bookingId}`);
        const data = await response.json();
        if (data.success && data.activities && data.activities.length > 0) {
            currentBookingActivities = {};
            data.activities.forEach(activity => {
                currentBookingActivities[activity.activity_id] = {
                    name: activity.activity_name, count: activity.participant_count || 0,
                    max: activity.default_capacity || 0, description: activity.description || '', image_url: activity.image_url || ''
                };
            });
        } else { currentBookingActivities = {}; }
    } catch (error) { console.error('Error loading booking activities:', error); currentBookingActivities = {}; }
}

async function loadAvailableSlots(packageId) {
    try {
        const response = await fetch(`get_available_slots.php?package_id=${packageId}`);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        const data = await response.json();
        if (data.success && data.slots && data.slots.length > 0) {
            availableSlotsData = data.slots;
            const uniqueDates = [...new Set(data.slots.map(slot => slot.slot_date))];
            if (editSlotDate) {
                editSlotDate.innerHTML = '<option value="">Pilih Tarikh</option>';
                uniqueDates.forEach(date => {
                    const formattedDate = new Date(date).toLocaleDateString('ms-MY', { day: 'numeric', month: 'short', year: 'numeric' });
                    editSlotDate.innerHTML += `<option value="${date}">${formattedDate}</option>`;
                });
                editSlotDate.removeEventListener('change', handleSlotDateChange);
                editSlotDate.addEventListener('change', handleSlotDateChange);
            }
        } else {
            if (editSlotDate) editSlotDate.innerHTML = '<option value="">Tiada tarikh tersedia</option>';
        }
    } catch (error) { console.error('Error loading slots:', error); }
}

function handleSlotDateChange() { loadSlotTimesForDate(this.value); }

function loadSlotTimesForDate(date, selectedSlotId = null) {
    if (editSlotTime) {
        editSlotTime.innerHTML = '<option value="">Pilih Slot Masa</option>';
        if (date && availableSlotsData) {
            const slotsForDate = availableSlotsData.filter(slot => slot.slot_date === date);
            if (slotsForDate.length === 0) {
                editSlotTime.innerHTML += '<option value="" disabled>Tiada slot tersedia</option>';
            } else {
                slotsForDate.forEach(slot => {
                    const startTime = formatTimeString(slot.start_time);
                    const endTime = formatTimeString(slot.end_time);
                    const selected = (selectedSlotId && slot.slot_id == selectedSlotId) ? 'selected' : '';
                    editSlotTime.innerHTML += `<option value="${slot.slot_id}" ${selected}>${startTime} - ${endTime}</option>`;
                });
            }
        }
    }
}

function handlePackageChange() {
    const selectedOption = editPackageSelect.options[editPackageSelect.selectedIndex];
    const requiresActivity = selectedOption.dataset.requiresActivity === '1';
    const newPackageId = editPackageSelect.value;
    if (requiresActivity) {
        if (editActivitySection) editActivitySection.style.display = 'block';
        resetActivityDistribution(parseInt(editParticipants?.value) || 0);
        renderActivityList();
    } else {
        if (editActivitySection) editActivitySection.style.display = 'none';
        currentBookingActivities = {};
    }
    if (newPackageId) loadAvailableSlots(newPackageId);
    currentPackageId = newPackageId;
}

function handleParticipantChange() {
    const totalParticipants = parseInt(this.value) || 0;
    const selectedOption = editPackageSelect?.options[editPackageSelect?.selectedIndex];
    if (selectedOption?.dataset.requiresActivity === '1' && totalParticipants > 0) {
        resetActivityDistribution(totalParticipants);
        renderActivityList();
    }
    updateActivityTotal();
}

function resetActivityDistribution(totalParticipants) {
    if (!allActivities || allActivities.length === 0) return;
    const activityCount = allActivities.length;
    const baseCount = Math.floor(totalParticipants / activityCount);
    let remainder = totalParticipants % activityCount;
    currentBookingActivities = {};
    allActivities.forEach((activity, index) => {
        let count = baseCount;
        if (remainder > 0) { count++; remainder--; }
        count = Math.min(count, activity.default_capacity);
        currentBookingActivities[activity.activity_id] = {
            name: activity.activity_name, count: count, max: activity.default_capacity,
            description: activity.description, image_url: activity.image_url
        };
    });
}

function renderActivityList() {
    if (!editActivityList || !allActivities) return;
    let html = '';
    allActivities.forEach(activity => {
        const ad = currentBookingActivities[activity.activity_id] || { count: 0, max: activity.default_capacity, name: activity.activity_name, description: activity.description };
        html += `<div class="edit-activity-item" data-activity-id="${activity.activity_id}">
            <div class="edit-activity-info" style="flex:1;"><h5>${activity.activity_name}</h5><p>${activity.description||''}</p></div>
            <div><div class="edit-activity-counter">
                <button type="button" class="edit-activity-minus" data-activity-id="${activity.activity_id}">−</button>
                <input type="number" class="edit-activity-input" data-activity-id="${activity.activity_id}" value="${ad.count||0}" min="0" max="${activity.default_capacity||999}">
                <button type="button" class="edit-activity-plus" data-activity-id="${activity.activity_id}">+</button>
            </div><div class="edit-activity-max">Maksimum: ${activity.default_capacity||'-'} orang</div></div></div>`;
    });
    editActivityList.innerHTML = html;
    document.querySelectorAll('.edit-activity-plus').forEach(btn => btn.addEventListener('click', function() { changeActivityCount(this.dataset.activityId, 1); }));
    document.querySelectorAll('.edit-activity-minus').forEach(btn => btn.addEventListener('click', function() { changeActivityCount(this.dataset.activityId, -1); }));
    document.querySelectorAll('.edit-activity-input').forEach(input => input.addEventListener('change', function() { updateActivityCount(this.dataset.activityId, parseInt(this.value)||0); }));
    updateActivityTotal();
}

function changeActivityCount(activityId, delta) {
    if (currentBookingActivities[activityId]) updateActivityCount(activityId, (currentBookingActivities[activityId].count||0) + delta);
}

function updateActivityCount(activityId, newValue) {
    const max = allActivities.find(a => a.activity_id == activityId)?.default_capacity || 999;
    const count = Math.max(0, Math.min(newValue, max));
    if (!currentBookingActivities[activityId]) {
        const a = allActivities.find(x => x.activity_id == activityId);
        currentBookingActivities[activityId] = { name: a?.activity_name||'', count: 0, max: max, description: a?.description||'', image_url: a?.image_url||'' };
    }
    currentBookingActivities[activityId].count = count;
    const input = document.querySelector(`.edit-activity-input[data-activity-id="${activityId}"]`);
    if (input) input.value = count;
    const minusBtn = document.querySelector(`.edit-activity-minus[data-activity-id="${activityId}"]`);
    const plusBtn = document.querySelector(`.edit-activity-plus[data-activity-id="${activityId}"]`);
    if (minusBtn) minusBtn.disabled = count <= 0;
    if (plusBtn) plusBtn.disabled = count >= max;
    updateActivityTotal();
}

function updateActivityTotal() {
    const tp = parseInt(editParticipants?.value) || 0;
    let ta = 0;
    Object.values(currentBookingActivities).forEach(a => ta += a.count||0);
    if (editActivitySum) editActivitySum.textContent = ta;
    if (editRequiredTotal) editRequiredTotal.textContent = tp;
    if (editTotalParticipantsLabel) editTotalParticipantsLabel.textContent = tp;
    if (editActivitySection && editActivitySection.style.display !== 'none') {
        if (ta === tp && tp > 0) {
            if (editActivityValid) editActivityValid.style.display = 'inline';
            if (editActivityWarning) editActivityWarning.style.display = 'none';
        } else {
            if (editActivityValid) editActivityValid.style.display = 'none';
            if (editActivityWarning) editActivityWarning.style.display = 'inline';
        }
    }
}

async function openEditModal() {
    const bookingId = actionBookingId.value;
    const booking = findBooking(bookingId);
    if (!booking) { alert('Maklumat tempahan tidak dijumpai.'); return; }
    currentPackageId = booking.package_id;
    currentSlotId = booking.slot_id;
    if (editBookingId) editBookingId.value = booking.booking_id;
    if (editOrganization) editOrganization.value = toProperCase(booking.organization_name||'');
    if (editContactPerson) editContactPerson.value = toProperCase(booking.contact_person||'');
    if (editPhoneNumber) editPhoneNumber.value = booking.phone_number||'';
    if (editEmail) editEmail.value = (booking.email||'').toLowerCase();
    if (editParticipants) editParticipants.value = booking.total_participants||'';
    if (editAdminComment) editAdminComment.value = booking.admin_comment||booking.admin_remark||'';
    if (editSlotDisplay) editSlotDisplay.value = `Semasa: ${booking.slot_display||'-'}`;
    await loadPackages();
    if (editPackageSelect) editPackageSelect.value = booking.package_id||'';
    await loadAllActivities();
    await loadCurrentBookingActivities(bookingId);
    const selectedOption = editPackageSelect?.options[editPackageSelect?.selectedIndex];
    const requiresActivity = selectedOption?.dataset.requiresActivity === '1';
    if (requiresActivity) {
        if (editActivitySection) editActivitySection.style.display = 'block';
        if (Object.keys(currentBookingActivities).length === 0) resetActivityDistribution(parseInt(booking.total_participants)||0);
        renderActivityList();
    } else {
        if (editActivitySection) editActivitySection.style.display = 'none';
    }
    await loadAvailableSlots(booking.package_id);
    if (editPackageSelect) { editPackageSelect.removeEventListener('change', handlePackageChange); editPackageSelect.addEventListener('change', handlePackageChange); }
    if (editParticipants) { editParticipants.removeEventListener('input', handleParticipantChange); editParticipants.addEventListener('input', handleParticipantChange); }
    if (editModal) editModal.classList.add('active');
    if (modal) modal.classList.remove('active');
}

async function saveEditBooking() {
    if (!editBookingId) { alert('Ralat: ID tempahan tidak dijumpai.'); return; }
    const bookingId = editBookingId.value;
    if (!bookingId) { alert('ID tempahan tidak sah.'); return; }
    const orgName = editOrganization?.value.trim();
    const contactPerson = editContactPerson?.value.trim();
    const phoneNumber = editPhoneNumber?.value.trim();
    const email = editEmail?.value.trim();
    const participants = editParticipants?.value;
    if (!orgName||!contactPerson||!phoneNumber||!email||!participants) { alert('Sila isi semua maklumat.'); return; }
    if (!editPackageSelect?.value) { alert('Sila pilih pakej.'); return; }
    const selectedOption = editPackageSelect.options[editPackageSelect.selectedIndex];
    const requiresActivity = selectedOption?.dataset.requiresActivity === '1';
    if (requiresActivity) {
        const tp = parseInt(participants)||0;
        let ta = 0;
        Object.values(currentBookingActivities).forEach(a => ta += a.count||0);
        if (ta !== tp) { alert('Jumlah agihan aktiviti mestilah sama dengan jumlah peserta!'); return; }
    }
    const formData = new FormData();
    formData.append('booking_id', bookingId);
    formData.append('action', 'edit');
    formData.append('organization_name', orgName);
    formData.append('contact_person', contactPerson);
    formData.append('phone_number', phoneNumber);
    formData.append('email', email);
    formData.append('total_participants', participants);
    formData.append('admin_comment', editAdminComment?.value||'');
    formData.append('package_id', editPackageSelect.value);
    if (editSlotTime?.value) formData.append('slot_id', editSlotTime.value);
    if (requiresActivity) {
        Object.entries(currentBookingActivities).forEach(([id, a]) => formData.append(`activity_participants[${id}]`, a.count||0));
    }
    if (saveEditBtn) { saveEditBtn.classList.add('loading'); saveEditBtn.disabled = true; }
    if (cancelEditBtn) cancelEditBtn.disabled = true;
    try {
        const response = await fetch('booking_email.php', { method: 'POST', body: formData });
        const data = await response.json();
        if (data.success) { alert(data.message||'Tempahan berjaya dikemaskini.'); window.location.reload(); }
        else { alert(data.message||'Gagal mengemaskini tempahan.'); }
    } catch (error) { alert('Ralat sistem: ' + error.message); }
    finally {
        if (saveEditBtn) { saveEditBtn.classList.remove('loading'); saveEditBtn.disabled = false; }
        if (cancelEditBtn) cancelEditBtn.disabled = false;
    }
}

// ========================
// ADD BOOKING FUNCTIONS
// ========================

async function openAddBookingModal() {
    console.log('Opening add booking modal');
    if (addBookingForm) addBookingForm.reset();
    addCurrentActivities = {};
    addAvailableSlotsData = [];
    if (addActivitySection) addActivitySection.style.display = 'none';
    if (addSlotDate) { addSlotDate.disabled = true; addSlotDate.innerHTML = '<option value="">Pilih Pakej Dahulu</option>'; }
    if (addSlotTime) { addSlotTime.disabled = true; addSlotTime.innerHTML = '<option value="">Pilih Tarikh Dahulu</option>'; }
    await loadAddPackages();
    await loadAllActivities();
    if (addPackageSelect) { addPackageSelect.removeEventListener('change', handleAddPackageChange); addPackageSelect.addEventListener('change', handleAddPackageChange); }
    if (addParticipants) { addParticipants.removeEventListener('input', handleAddParticipantChange); addParticipants.addEventListener('input', handleAddParticipantChange); }
    if (addSlotDate) { addSlotDate.removeEventListener('change', handleAddSlotDateChange); addSlotDate.addEventListener('change', handleAddSlotDateChange); }
    if (addBookingModal) addBookingModal.classList.add('active');
}

async function loadAddPackages() {
    try {
        const response = await fetch('get_packages.php');
        const data = await response.json();
        if (data.success && data.packages && addPackageSelect) {
            addPackageSelect.innerHTML = '<option value="">Pilih Pakej</option>';
            data.packages.forEach(pkg => {
                addPackageSelect.innerHTML += `<option value="${pkg.package_id}" data-requires-activity="${pkg.requires_activity_selection}">${pkg.package_name}</option>`;
            });
        }
    } catch (error) { console.error('Error loading packages:', error); }
}

function handleAddPackageChange() {
    const selectedOption = addPackageSelect.options[addPackageSelect.selectedIndex];
    const requiresActivity = selectedOption.dataset.requiresActivity === '1';
    const packageId = addPackageSelect.value;
    if (packageId) {
        if (addSlotDate) { addSlotDate.disabled = false; addSlotDate.innerHTML = '<option value="">Memuatkan tarikh...</option>'; }
        loadAddAvailableSlots(packageId);
    } else {
        if (addSlotDate) { addSlotDate.disabled = true; addSlotDate.innerHTML = '<option value="">Pilih Pakej Dahulu</option>'; }
        if (addSlotTime) { addSlotTime.disabled = true; addSlotTime.innerHTML = '<option value="">Pilih Tarikh Dahulu</option>'; }
    }
    if (requiresActivity) {
        if (addActivitySection) addActivitySection.style.display = 'block';
        resetAddActivityDistribution(parseInt(addParticipants?.value)||0);
        renderAddActivityList();
    } else {
        if (addActivitySection) addActivitySection.style.display = 'none';
        addCurrentActivities = {};
    }
}

function handleAddParticipantChange() {
    const tp = parseInt(this.value)||0;
    const selectedOption = addPackageSelect?.options[addPackageSelect?.selectedIndex];
    if (selectedOption?.dataset.requiresActivity === '1' && tp > 0) { resetAddActivityDistribution(tp); renderAddActivityList(); }
    updateAddActivityTotal();
}

async function loadAddAvailableSlots(packageId) {
    try {
        const response = await fetch(`get_available_slots.php?package_id=${packageId}`);
        const data = await response.json();
        if (data.success && data.slots) {
            addAvailableSlotsData = data.slots.filter(slot => slot.slot_status === 'available');
            const uniqueDates = [...new Set(addAvailableSlotsData.map(slot => slot.slot_date))];
            if (addSlotDate) {
                addSlotDate.innerHTML = '<option value="">Pilih Tarikh</option>';
                if (uniqueDates.length === 0) {
                    addSlotDate.innerHTML += '<option value="" disabled>Tiada tarikh tersedia</option>';
                } else {
                    uniqueDates.forEach(date => {
                        const d = new Date(date);
                        const fd = d.toLocaleDateString('ms-MY', { day: 'numeric', month: 'short', year: 'numeric' });
                        addSlotDate.innerHTML += `<option value="${date}">${fd}</option>`;
                    });
                }
            }
            if (addSlotTime) { addSlotTime.disabled = true; addSlotTime.innerHTML = '<option value="">Pilih Tarikh Dahulu</option>'; }
        } else {
            if (addSlotDate) addSlotDate.innerHTML = '<option value="">Tiada tarikh tersedia</option>';
        }
    } catch (error) { console.error('Error loading add slots:', error); }
}

function handleAddSlotDateChange() {
    const selectedDate = this.value;
    if (addSlotTime) {
        if (selectedDate && addAvailableSlotsData.length > 0) {
            addSlotTime.disabled = false;
            addSlotTime.innerHTML = '<option value="">Pilih Slot Masa</option>';
            const slotsForDate = addAvailableSlotsData.filter(slot => slot.slot_date === selectedDate);
            if (slotsForDate.length === 0) {
                addSlotTime.innerHTML += '<option value="" disabled>Tiada slot tersedia</option>';
            } else {
                slotsForDate.forEach(slot => {
                    const st = formatTimeString(slot.start_time);
                    const et = formatTimeString(slot.end_time);
                    addSlotTime.innerHTML += `<option value="${slot.slot_id}">${st} - ${et}</option>`;
                });
            }
        } else {
            addSlotTime.disabled = true;
            addSlotTime.innerHTML = '<option value="">Pilih Tarikh Dahulu</option>';
        }
    }
}

function resetAddActivityDistribution(tp) {
    if (!allActivities||allActivities.length===0) return;
    const ac = allActivities.length;
    const bc = Math.floor(tp/ac);
    let rem = tp % ac;
    addCurrentActivities = {};
    allActivities.forEach((a, i) => {
        let c = bc;
        if (rem>0) { c++; rem--; }
        c = Math.min(c, a.default_capacity);
        addCurrentActivities[a.activity_id] = { name: a.activity_name, count: c, max: a.default_capacity, description: a.description };
    });
}

function renderAddActivityList() {
    if (!addActivityList||!allActivities) return;
    let html = '';
    allActivities.forEach(a => {
        const ad = addCurrentActivities[a.activity_id] || { count:0, max:a.default_capacity, name:a.activity_name, description:a.description };
        html += `<div class="edit-activity-item" data-activity-id="${a.activity_id}">
            <div class="edit-activity-info" style="flex:1;"><h5>${a.activity_name}</h5><p>${a.description||''}</p></div>
            <div><div class="edit-activity-counter">
                <button type="button" class="add-activity-minus" data-activity-id="${a.activity_id}">−</button>
                <input type="number" class="add-activity-input" data-activity-id="${a.activity_id}" value="${ad.count||0}" min="0" max="${a.default_capacity||999}">
                <button type="button" class="add-activity-plus" data-activity-id="${a.activity_id}">+</button>
            </div><div class="edit-activity-max">Maksimum: ${a.default_capacity||'-'} orang</div></div></div>`;
    });
    addActivityList.innerHTML = html;
    document.querySelectorAll('.add-activity-plus').forEach(b => b.addEventListener('click', function(){ changeAddActivityCount(this.dataset.activityId,1); }));
    document.querySelectorAll('.add-activity-minus').forEach(b => b.addEventListener('click', function(){ changeAddActivityCount(this.dataset.activityId,-1); }));
    document.querySelectorAll('.add-activity-input').forEach(i => i.addEventListener('change', function(){ updateAddActivityCount(this.dataset.activityId, parseInt(this.value)||0); }));
    updateAddActivityTotal();
}

function changeAddActivityCount(id, d) { if(addCurrentActivities[id]) updateAddActivityCount(id, (addCurrentActivities[id].count||0)+d); }
function updateAddActivityCount(id, v) {
    const max = allActivities.find(a=>a.activity_id==id)?.default_capacity||999;
    const c = Math.max(0, Math.min(v, max));
    if(addCurrentActivities[id]) addCurrentActivities[id].count = c;
    const inp = document.querySelector(`.add-activity-input[data-activity-id="${id}"]`); if(inp) inp.value=c;
    const mb = document.querySelector(`.add-activity-minus[data-activity-id="${id}"]`); if(mb) mb.disabled=c<=0;
    const pb = document.querySelector(`.add-activity-plus[data-activity-id="${id}"]`); if(pb) pb.disabled=c>=max;
    updateAddActivityTotal();
}
function updateAddActivityTotal() {
    const tp = parseInt(addParticipants?.value)||0;
    let ta = 0;
    Object.values(addCurrentActivities).forEach(a=>ta+=a.count||0);
    if(addActivitySum) addActivitySum.textContent=ta;
    if(addRequiredTotal) addRequiredTotal.textContent=tp;
    if(addTotalParticipantsLabel) addTotalParticipantsLabel.textContent=tp;
    if(addActivitySection&&addActivitySection.style.display!=='none') {
        if(ta===tp&&tp>0){ if(addActivityValid) addActivityValid.style.display='inline'; if(addActivityWarning) addActivityWarning.style.display='none'; }
        else { if(addActivityValid) addActivityValid.style.display='none'; if(addActivityWarning) addActivityWarning.style.display='inline'; }
    }
}

async function saveAddBooking() {
    if(!addOrganization?.value.trim()){alert('Sila isi nama organisasi.');return;}
    if(!addContactPerson?.value.trim()){alert('Sila isi nama pegawai.');return;}
    if(!addPhoneNumber?.value.trim()){alert('Sila isi nombor telefon.');return;}
    if(!addPackageSelect?.value){alert('Sila pilih pakej.');return;}
    if(!addParticipants?.value||parseInt(addParticipants.value)<1){alert('Sila isi jumlah peserta.');return;}
    if(!addSlotDate?.value){alert('Sila pilih tarikh.');return;}
    if(!addSlotTime?.value){alert('Sila pilih slot masa.');return;}
    const so = addPackageSelect.options[addPackageSelect.selectedIndex];
    if(so?.dataset.requiresActivity==='1'){
        const tp=parseInt(addParticipants.value)||0; let ta=0;
        Object.values(addCurrentActivities).forEach(a=>ta+=a.count||0);
        if(ta!==tp){alert('Jumlah agihan aktiviti mestilah sama dengan jumlah peserta!');return;}
    }
    const fd=new FormData();
    fd.append('action','add'); fd.append('organization_name',addOrganization.value.trim());
    fd.append('contact_person',addContactPerson.value.trim()); fd.append('phone_number',addPhoneNumber.value.trim());
    fd.append('email',addEmail?.value.trim()||''); fd.append('package_id',addPackageSelect.value);
    fd.append('total_participants',addParticipants.value); fd.append('slot_id',addSlotTime.value);
    fd.append('admin_comment',addAdminComment?.value.trim()||'Walk-in booking');
    if(so?.dataset.requiresActivity==='1') Object.entries(addCurrentActivities).forEach(([id,a])=>fd.append(`activity_participants[${id}]`,a.count||0));
    if(saveAddBtn){saveAddBtn.classList.add('loading');saveAddBtn.disabled=true;}
    if(cancelAddBtn)cancelAddBtn.disabled=true;
    try{
        const r=await fetch('booking_email.php',{method:'POST',body:fd});
        const d=await r.json();
        if(d.success){alert(d.message||'Tempahan berjaya ditambah!');if(addBookingModal)addBookingModal.classList.remove('active');window.location.reload();}
        else alert(d.message||'Gagal menambah tempahan.');
    }catch(e){alert('Ralat sistem: '+e.message);}
    finally{if(saveAddBtn){saveAddBtn.classList.remove('loading');saveAddBtn.disabled=false;}if(cancelAddBtn)cancelAddBtn.disabled=false;}
}

// ========================
// TABLE FUNCTIONS
// ========================

function normalizeText(text) { return String(text||'').trim().toLowerCase(); }
function rowMatchesFilters(row, st, tf, sf) {
    const cells=row.querySelectorAll('td');
    const rt=[cells[1]?.textContent||'',cells[2]?.textContent||'',cells[3]?.textContent||'',cells[5]?.textContent||''].join(' ').toLowerCase();
    return (!st||rt.includes(st))&&(tf==='all'||normalizeText(cells[2]?.textContent||'')===tf)&&(sf==='all'||normalizeText(cells[5]?.textContent||'')===sf);
}
function getFilteredRows() {
    if(!bookingSearch||!bookingTypeFilter||!bookingStatusFilter)return allRows;
    return allRows.filter(r=>rowMatchesFilters(r,normalizeText(bookingSearch.value||''),normalizeText(bookingTypeFilter.value||'all'),normalizeText(bookingStatusFilter.value||'all')));
}
function renderTablePage() {
    const fr=getFilteredRows(),tp=Math.max(1,Math.ceil(fr.length/pageSize));
    if(currentPage>tp)currentPage=tp;
    allRows.forEach(r=>r.style.display='none');
    fr.forEach((r,i)=>{if(Math.floor(i/pageSize)+1===currentPage)r.style.display='';});
}
function updateFooter() {
    const fr=getFilteredRows(),t=fr.length,tp=Math.max(1,Math.ceil(t/pageSize));
    if(bookingFooterText)bookingFooterText.textContent=`Showing ${t===0?0:(currentPage-1)*pageSize+1} to ${Math.min(currentPage*pageSize,t)} out of ${t} entries`;
    if(bookingPaginationInfo)bookingPaginationInfo.textContent=`Page ${currentPage} of ${tp}`;
    if(prevPageBtn)prevPageBtn.disabled=currentPage<=1;
    if(nextPageBtn)nextPageBtn.disabled=currentPage>=tp;
}
function applyFilters(){currentPage=1;renderTablePage();updateFooter();}
function changePage(o){const fr=getFilteredRows();currentPage=Math.min(Math.max(currentPage+o,1),Math.max(1,Math.ceil(fr.length/pageSize)));renderTablePage();updateFooter();}
function resetFilters(){if(bookingSearch)bookingSearch.value='';if(bookingTypeFilter)bookingTypeFilter.value='all';if(bookingStatusFilter)bookingStatusFilter.value='all';applyFilters();}
function downloadCsv(fn,cc){const b=new Blob([cc],{type:'text/csv;charset=utf-8;'}),l=document.createElement('a'),u=URL.createObjectURL(b);l.setAttribute('href',u);l.setAttribute('download',fn);document.body.appendChild(l);l.click();document.body.removeChild(l);URL.revokeObjectURL(u);}
function exportVisibleRows(){
    if(!bookingTableBody)return;
    const rows=Array.from(bookingTableBody.querySelectorAll('tr')).filter(r=>r.style.display!=='none');
    if(rows.length===0){alert('Tiada baris untuk dieksport.');return;}
    const cr=[['ID Tempahan','Nama Organisasi','Jenis Tempahan','Tarikh & Slot Masa','Pax','Jumlah Bayaran','Status'].join(',')];
    rows.forEach(r=>{cr.push(Array.from(r.querySelectorAll('td')).map(c=>`"${c.textContent.trim().replace(/"/g,'""')}"`).join(','));});
    downloadCsv('tempahan-export.csv',cr.join('\n'));
}
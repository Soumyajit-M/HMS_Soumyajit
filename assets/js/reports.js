// Reports JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize reports
    initializeReports();
});

const currencySymbolMap = {
    USD: '$',
    EUR: '€',
    GBP: '£',
    INR: '₹',
    JPY: '¥',
    AUD: 'A$',
    CAD: 'C$',
    CHF: 'CHF ',
    CNY: '¥'
};

let reportsCurrencyCode = null;
let reportsCurrencySymbol = '₹';

async function ensureCurrencyPreferences() {
    if (reportsCurrencyCode) {
        return reportsCurrencyCode;
    }

    try {
        const stored = window.localStorage ? window.localStorage.getItem('hms_default_currency') : null;
        if (stored) {
            reportsCurrencyCode = String(stored).toUpperCase();
        }
    } catch (e) {
        reportsCurrencyCode = null;
    }

    if (!reportsCurrencyCode) {
        try {
            const response = await fetch('api/settings.php');
            const json = await response.json();
            const code = json?.settings?.general?.currency_default;
            if (code) {
                reportsCurrencyCode = String(code).toUpperCase();
                try {
                    window.localStorage && window.localStorage.setItem('hms_default_currency', reportsCurrencyCode);
                } catch (err) {
                    // Ignore storage errors
                }
            }
        } catch (err) {
            reportsCurrencyCode = null;
        }
    }

    if (!reportsCurrencyCode) {
        reportsCurrencyCode = 'INR';
    }

    reportsCurrencySymbol = symbolForCurrency(reportsCurrencyCode);
    return reportsCurrencyCode;
}

function symbolForCurrency(code) {
    const upper = (code || '').toUpperCase();
    return currencySymbolMap[upper] || `${upper} `;
}

function coerceAmount(value) {
    const numeric = Number.parseFloat(value);
    return Number.isFinite(numeric) ? numeric : 0;
}

function formatCurrency(amount) {
    if (!reportsCurrencyCode) {
        reportsCurrencyCode = 'INR';
    }
    if (!reportsCurrencySymbol) {
        reportsCurrencySymbol = symbolForCurrency(reportsCurrencyCode);
    }
    const numeric = coerceAmount(amount);
    const formatted = numeric.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    return `${reportsCurrencySymbol}${formatted}`;
}

async function initializeReports() {
    await ensureCurrencyPreferences();
    // Set default dates
    const today = new Date();
    const startDate = new Date(today.getFullYear(), today.getMonth(), 1);
    const endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);

    document.getElementById('startDate').value = startDate.toISOString().split('T')[0];
    document.getElementById('endDate').value = endDate.toISOString().split('T')[0];

    // Populate filters
    loadDoctors();
    loadDepartments();

    // Generate initial report
    generateReport();
}

async function generateReport() {
    await ensureCurrencyPreferences();
    const reportType = document.getElementById('reportType').value;
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;

    // Show loading
    const reportContent = document.getElementById('reportContent');
    reportContent.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';

    // Fetch report data (core stats unaffected by doctor/department filters for now)
    fetch(`api/dashboard-stats.php?report=${reportType}&start=${startDate}&end=${endDate}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayReport(data, reportType);
            } else {
                reportContent.innerHTML = '<div class="alert alert-danger">Error loading report data</div>';
            }
        })
        .catch(error => {
            console.error('Error generating report:', error);
            reportContent.innerHTML = '<div class="alert alert-danger">Error loading report data</div>';
        });
}

function displayReport(data, reportType) {
    let html = '';

    switch (reportType) {
        case 'patients':
            html = generatePatientReport(data);
            break;
        case 'appointments':
            html = generateAppointmentReport(data);
            break;
        case 'billing':
            html = generateBillingReport(data);
            break;
        case 'doctors':
            html = generateDoctorReport(data);
            break;
        default:
            html = '<div class="alert alert-info">Select a report type to generate</div>';
    }

    document.getElementById('reportContent').innerHTML = html;
}

function generatePatientReport(data) {
    return `
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Patient Report</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Total Patients</th>
                                        <th>New Patients (30 days)</th>
                                        <th>Male Patients</th>
                                        <th>Female Patients</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>${data.patient_stats?.total_patients || 0}</td>
                                        <td>${data.patient_stats?.new_patients_30_days || 0}</td>
                                        <td>${data.patient_stats?.male_patients || 0}</td>
                                        <td>${data.patient_stats?.female_patients || 0}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function generateAppointmentReport(data) {
    return `
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Appointment Report</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Total Appointments</th>
                                        <th>Scheduled</th>
                                        <th>Completed</th>
                                        <th>Cancelled</th>
                                        <th>Today's Appointments</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>${data.appointment_stats?.total_appointments || 0}</td>
                                        <td>${data.appointment_stats?.scheduled_appointments || 0}</td>
                                        <td>${data.appointment_stats?.completed_appointments || 0}</td>
                                        <td>${data.appointment_stats?.cancelled_appointments || 0}</td>
                                        <td>${data.appointment_stats?.today_appointments || 0}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function generateBillingReport(data) {
    const stats = data.billing_stats || {};
    return `
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Billing Report</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Total Bills</th>
                                        <th>Total Revenue</th>
                                        <th>Paid Amount</th>
                                        <th>Balance</th>
                                        <th>Paid Bills</th>
                                        <th>Pending Bills</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>${stats.total_bills || 0}</td>
                                        <td>${formatCurrency(stats.total_revenue)}</td>
                                        <td>${formatCurrency(stats.total_paid)}</td>
                                        <td>${formatCurrency(stats.total_balance)}</td>
                                        <td>${stats.paid_bills || 0}</td>
                                        <td>${stats.pending_bills || 0}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function generateDoctorReport(data) {
    return `
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Doctor Report</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Total Doctors</th>
                                        <th>Cardiology</th>
                                        <th>Neurology</th>
                                        <th>Orthopedics</th>
                                        <th>Pediatrics</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>${data.doctor_stats?.total_doctors || 0}</td>
                                        <td>${data.doctor_stats?.cardiology_doctors || 0}</td>
                                        <td>${data.doctor_stats?.neurology_doctors || 0}</td>
                                        <td>${data.doctor_stats?.orthopedics_doctors || 0}</td>
                                        <td>${data.doctor_stats?.pediatrics_doctors || 0}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function exportReport(format = 'csv') {
    const reportType = document.getElementById('reportType').value;
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const doctorId = document.getElementById('filterDoctor')?.value || '';
    const departmentId = document.getElementById('filterDepartment')?.value || '';
    const map = { overview: 'dashboard', patients: 'patients', appointments: 'appointments', billing: 'billing', doctors: 'dashboard' };
    const type = map[reportType] || 'dashboard';
    if (format === 'pdf') {
        const url = new URL('api/reports-print.php', window.location.origin);
        url.searchParams.set('type', type);
        if (startDate) url.searchParams.set('start', startDate);
        if (endDate) url.searchParams.set('end', endDate);
        if (doctorId) url.searchParams.set('doctor_id', doctorId);
        if (departmentId) url.searchParams.set('department_id', departmentId);
        window.open(url.toString(), '_blank');
    } else {
        const url = new URL('api/export.php', window.location.origin);
        url.searchParams.set('type', type);
        if (startDate) url.searchParams.set('start', startDate);
        if (endDate) url.searchParams.set('end', endDate);
        if (doctorId) url.searchParams.set('doctor_id', doctorId);
        if (departmentId) url.searchParams.set('department_id', departmentId);
        window.location.href = url.toString();
    }
}

function loadDoctors() {
    const select = document.getElementById('filterDoctor');
    if (!select) return;
    fetch('api/doctors.php')
        .then(r => r.json())
        .then(j => {
            if (j.success && Array.isArray(j.doctors)) {
                j.doctors.forEach(d => {
                    const opt = document.createElement('option');
                    const name = [d.first_name || d.doctor_first_name, d.last_name || d.doctor_last_name].filter(Boolean).join(' ');
                    opt.value = d.id || d.doctor_id || '';
                    opt.textContent = name || `Doctor #${opt.value}`;
                    if (opt.value) select.appendChild(opt);
                });
            }
        })
        .catch(() => {});
}

function loadDepartments() {
    const select = document.getElementById('filterDepartment');
    if (!select) return;
    fetch('api/departments.php')
        .then(r => r.json())
        .then(j => {
            if (j.success && Array.isArray(j.departments)) {
                j.departments.forEach(dep => {
                    const opt = document.createElement('option');
                    opt.value = dep.id;
                    opt.textContent = dep.name;
                    select.appendChild(opt);
                });
            }
        })
        .catch(() => {});
}

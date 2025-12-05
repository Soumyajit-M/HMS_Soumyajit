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
    
    // Load and render charts
    loadCharts();
}

async function generateReport() {
    await ensureCurrencyPreferences();
    const reportType = document.getElementById('reportType').value;
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;

    // Fetch report data (core stats unaffected by doctor/department filters for now)
    fetch(`api/dashboard-stats.php?report=${reportType}&start=${startDate}&end=${endDate}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateSummaryCards(data);
            }
        })
        .catch(error => {
            console.error('Error generating report:', error);
        });
    
    // Reload charts with new date range
    loadCharts();
}

function displayReport(data, reportType) {
    // Update summary cards
    updateSummaryCards(data);
}

function updateSummaryCards(data) {
    // Update total patients
    const totalPatients = data.patient_stats?.total_patients || 0;
    document.getElementById('totalPatients').textContent = totalPatients;

    // Update total appointments
    const totalAppointments = data.appointment_stats?.total_appointments || 0;
    document.getElementById('totalAppointments').textContent = totalAppointments;

    // Update total revenue
    const totalRevenue = data.billing_stats?.total_revenue || 0;
    document.getElementById('totalRevenue').textContent = formatCurrency(totalRevenue);

    // Update active doctors
    const activeDoctors = data.doctor_stats?.active_doctors || 0;
    document.getElementById('activeDoctors').textContent = activeDoctors;
}

function generateOverviewReport(data) {
    return `
        <div class="row mb-4">
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Appointments Trend</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="appointmentsTrendChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Patient Demographics</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="patientDemographicsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Revenue Analysis</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="revenueAnalysisChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Payment Status</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="paymentStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Detailed Report</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="detailedReportTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Patients</th>
                                        <th>Appointments</th>
                                        <th>Revenue</th>
                                        <th>New Patients</th>
                                        <th>Completed</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="6" class="text-center">No data available</td>
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

// Chart instances
let appointmentsTrendChart = null;
let demographicsChart = null;
let revenueChart = null;
let paymentStatusChart = null;

// Load and render all charts
async function loadCharts() {
    await ensureCurrencyPreferences();
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    // Load each chart
    loadAppointmentsTrendChart(startDate, endDate);
    loadDemographicsChart();
    loadRevenueChart(startDate, endDate);
    loadPaymentStatusChart(startDate, endDate);
    loadDetailedReportTable(startDate, endDate);
}

// Appointments Trend Chart
async function loadAppointmentsTrendChart(startDate, endDate) {
    try {
        const response = await fetch(`api/reports-api.php?action=appointment_statistics&start_date=${startDate}&end_date=${endDate}`);
        const data = await response.json();
        
        const canvas = document.getElementById('appointmentsTrendChart');
        if (!canvas) return;
        
        // Destroy existing chart if it exists
        if (appointmentsTrendChart) {
            appointmentsTrendChart.destroy();
        }
        
        const ctx = canvas.getContext('2d');
        
        // Prepare data
        const labels = data.data?.map(item => item.status) || ['Scheduled', 'Completed', 'Cancelled'];
        const counts = data.data?.map(item => item.count) || [0, 0, 0];
        
        appointmentsTrendChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Appointments',
                    data: counts,
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(255, 99, 132, 0.6)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: false
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error loading appointments trend chart:', error);
    }
}

// Patient Demographics Chart
async function loadDemographicsChart() {
    try {
        const response = await fetch('api/reports-api.php?action=patient_demographics');
        const data = await response.json();
        
        const canvas = document.getElementById('demographicsChart');
        if (!canvas) return;
        
        // Destroy existing chart if it exists
        if (demographicsChart) {
            demographicsChart.destroy();
        }
        
        const ctx = canvas.getContext('2d');
        
        const demographics = data.data || {};
        const maleCount = parseInt(demographics.male_count) || 0;
        const femaleCount = parseInt(demographics.female_count) || 0;
        const otherCount = parseInt(demographics.other_count) || 0;
        
        demographicsChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Male', 'Female', 'Other'],
                datasets: [{
                    data: [maleCount, femaleCount, otherCount],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(255, 206, 86, 0.6)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(255, 206, 86, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error loading demographics chart:', error);
    }
}

// Revenue Analysis Chart
async function loadRevenueChart(startDate, endDate) {
    try {
        const response = await fetch(`api/reports-api.php?action=revenue&start_date=${startDate}&end_date=${endDate}`);
        const data = await response.json();
        
        const canvas = document.getElementById('revenueChart');
        if (!canvas) return;
        
        // Destroy existing chart if it exists
        if (revenueChart) {
            revenueChart.destroy();
        }
        
        const ctx = canvas.getContext('2d');
        
        // Prepare data
        const revenueData = data.data || [];
        const labels = revenueData.map(item => item.date).reverse();
        const revenue = revenueData.map(item => parseFloat(item.total_revenue) || 0).reverse();
        const collected = revenueData.map(item => parseFloat(item.amount_collected) || 0).reverse();
        
        revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Revenue',
                    data: revenue,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.4
                }, {
                    label: 'Amount Collected',
                    data: collected,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatCurrency(value);
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + formatCurrency(context.parsed.y);
                            }
                        }
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error loading revenue chart:', error);
    }
}

// Payment Status Chart
async function loadPaymentStatusChart(startDate, endDate) {
    try {
        const response = await fetch(`api/reports-api.php?action=revenue&start_date=${startDate}&end_date=${endDate}`);
        const data = await response.json();
        
        const canvas = document.getElementById('paymentStatusChart');
        if (!canvas) return;
        
        // Destroy existing chart if it exists
        if (paymentStatusChart) {
            paymentStatusChart.destroy();
        }
        
        const ctx = canvas.getContext('2d');
        
        // Calculate totals
        const revenueData = data.data || [];
        let totalRevenue = 0;
        let totalCollected = 0;
        
        revenueData.forEach(item => {
            totalRevenue += parseFloat(item.total_revenue) || 0;
            totalCollected += parseFloat(item.amount_collected) || 0;
        });
        
        const outstanding = totalRevenue - totalCollected;
        
        paymentStatusChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Paid', 'Outstanding'],
                datasets: [{
                    data: [totalCollected, outstanding],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(255, 99, 132, 0.6)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + formatCurrency(context.parsed);
                            }
                        }
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error loading payment status chart:', error);
    }
}

// Load Detailed Report Table
async function loadDetailedReportTable(startDate, endDate) {
    try {
        const response = await fetch(`api/reports-api.php?action=revenue&start_date=${startDate}&end_date=${endDate}`);
        const data = await response.json();
        
        const tbody = document.getElementById('reportsTableBody');
        if (!tbody) return;
        
        tbody.innerHTML = '';
        
        const revenueData = data.data || [];
        
        if (revenueData.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center">No data available for selected date range</td></tr>';
            return;
        }
        
        // Get appointment data
        const appointmentResponse = await fetch(`api/reports-api.php?action=appointment_statistics&start_date=${startDate}&end_date=${endDate}`);
        const appointmentData = await appointmentResponse.json();
        const appointments = appointmentData.data || [];
        
        // Get total appointments count
        const totalAppointments = appointments.reduce((sum, item) => sum + (parseInt(item.count) || 0), 0);
        const completedAppointments = appointments.find(item => item.status === 'Completed')?.count || 0;
        
        revenueData.reverse().forEach(item => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${item.date}</td>
                <td>-</td>
                <td>${item.bill_count || 0}</td>
                <td>${formatCurrency(item.total_revenue)}</td>
                <td>-</td>
                <td>${item.bill_count || 0}</td>
            `;
            tbody.appendChild(row);
        });
        
        // Add summary row
        const totalRow = document.createElement('tr');
        totalRow.style.fontWeight = 'bold';
        const totalRevenue = revenueData.reduce((sum, item) => sum + (parseFloat(item.total_revenue) || 0), 0);
        const totalBills = revenueData.reduce((sum, item) => sum + (parseInt(item.bill_count) || 0), 0);
        
        totalRow.innerHTML = `
            <td>Total</td>
            <td>-</td>
            <td>${totalAppointments}</td>
            <td>${formatCurrency(totalRevenue)}</td>
            <td>-</td>
            <td>${completedAppointments}</td>
        `;
        tbody.appendChild(totalRow);
    } catch (error) {
        console.error('Error loading detailed report table:', error);
        const tbody = document.getElementById('reportsTableBody');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error loading data</td></tr>';
        }
    }
}

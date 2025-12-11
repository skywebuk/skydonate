<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Include dashboard class if not already included
if ( ! class_exists( 'Skydonate_Dashboard' ) ) {
    require_once SKYDONATE_ADMIN_PATH . '/class-skydonate-dashboard.php';
}

// Get dashboard data
$comparison = Skydonate_Dashboard::get_comparison_stats( 30 );
$monthly_data = Skydonate_Dashboard::get_monthly_donations();
$campaigns = Skydonate_Dashboard::get_donations_by_campaign( 6 );
$countries = Skydonate_Dashboard::get_donations_by_country( 8 );
$distribution = Skydonate_Dashboard::get_donation_distribution();
$top_donors = Skydonate_Dashboard::get_top_donors( 5 );
$recent_donations = Skydonate_Dashboard::get_recent_donations( 5 );
$currency_symbol = html_entity_decode( get_woocommerce_currency_symbol( get_option('woocommerce_currency') ) );

// Prepare chart data
$monthly_labels = wp_json_encode( array_column( $monthly_data, 'label' ) );
$monthly_amounts = wp_json_encode( array_column( $monthly_data, 'amount' ) );
$monthly_counts = wp_json_encode( array_column( $monthly_data, 'count' ) );

$campaign_labels = wp_json_encode( array_column( $campaigns, 'name' ) );
$campaign_amounts = wp_json_encode( array_column( $campaigns, 'amount' ) );

$country_labels = wp_json_encode( array_column( $countries, 'name' ) );
$country_amounts = wp_json_encode( array_column( $countries, 'amount' ) );

$distribution_labels = wp_json_encode( array_column( $distribution, 'label' ) );
$distribution_counts = wp_json_encode( array_column( $distribution, 'count' ) );
?>

<div class="sky-dashboard-wrap">
    <!-- Dashboard Header -->
    <div class="sky-dashboard-header">
        <div class="sky-dashboard-title">
            <h1><?php esc_html_e( 'Donation Analytics', 'skydonate' ); ?></h1>
            <p><?php esc_html_e( 'Track your donation performance and donor engagement', 'skydonate' ); ?></p>
        </div>
        <div class="sky-dashboard-actions">
            <div class="sky-select-wrapper">
                <select id="sky-date-range" class="sky-select">
                    <option value="30"><?php esc_html_e( 'Last 30 Days', 'skydonate' ); ?></option>
                    <option value="60"><?php esc_html_e( 'Last 60 Days', 'skydonate' ); ?></option>
                    <option value="90"><?php esc_html_e( 'Last 90 Days', 'skydonate' ); ?></option>
                    <option value="365"><?php esc_html_e( 'Last 12 Months', 'skydonate' ); ?></option>
                </select>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="sky-stats-grid">
        <div class="sky-stat-card">
            <div class="sky-stat-icon total">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
            </div>
            <div class="sky-stat-content">
                <span class="sky-stat-label"><?php esc_html_e( 'Total Raised', 'skydonate' ); ?></span>
                <span class="sky-stat-value"><?php echo esc_html( $currency_symbol . number_format( $comparison['total']['current'], 0 ) ); ?></span>
                <span class="sky-stat-change <?php echo $comparison['total']['change'] >= 0 ? 'positive' : 'negative'; ?>">
                    <?php if ( $comparison['total']['change'] >= 0 ) : ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                    <?php else : ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline><polyline points="17 18 23 18 23 12"></polyline></svg>
                    <?php endif; ?>
                    <?php echo esc_html( abs( $comparison['total']['change'] ) . '%' ); ?>
                </span>
            </div>
        </div>

        <div class="sky-stat-card">
            <div class="sky-stat-icon count">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
            </div>
            <div class="sky-stat-content">
                <span class="sky-stat-label"><?php esc_html_e( 'Total Donations', 'skydonate' ); ?></span>
                <span class="sky-stat-value"><?php echo esc_html( number_format( $comparison['count']['current'] ) ); ?></span>
                <span class="sky-stat-change <?php echo $comparison['count']['change'] >= 0 ? 'positive' : 'negative'; ?>">
                    <?php if ( $comparison['count']['change'] >= 0 ) : ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                    <?php else : ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline><polyline points="17 18 23 18 23 12"></polyline></svg>
                    <?php endif; ?>
                    <?php echo esc_html( abs( $comparison['count']['change'] ) . '%' ); ?>
                </span>
            </div>
        </div>

        <div class="sky-stat-card">
            <div class="sky-stat-icon donors">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div class="sky-stat-content">
                <span class="sky-stat-label"><?php esc_html_e( 'Unique Donors', 'skydonate' ); ?></span>
                <span class="sky-stat-value"><?php echo esc_html( number_format( $comparison['donors']['current'] ) ); ?></span>
                <span class="sky-stat-sub"><?php esc_html_e( 'Last 30 days', 'skydonate' ); ?></span>
            </div>
        </div>

        <div class="sky-stat-card">
            <div class="sky-stat-icon average">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
            </div>
            <div class="sky-stat-content">
                <span class="sky-stat-label"><?php esc_html_e( 'Average Donation', 'skydonate' ); ?></span>
                <span class="sky-stat-value"><?php echo esc_html( $currency_symbol . number_format( $comparison['average']['current'], 2 ) ); ?></span>
                <span class="sky-stat-sub"><?php esc_html_e( 'Per transaction', 'skydonate' ); ?></span>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="sky-charts-row">
        <div class="sky-chart-card sky-chart-large">
            <div class="sky-chart-header">
                <h3><?php esc_html_e( 'Donation Trends', 'skydonate' ); ?></h3>
                <div class="sky-chart-legend">
                    <span class="sky-legend-item amount"><span class="sky-legend-dot"></span><?php esc_html_e( 'Amount', 'skydonate' ); ?></span>
                    <span class="sky-legend-item count"><span class="sky-legend-dot"></span><?php esc_html_e( 'Count', 'skydonate' ); ?></span>
                </div>
            </div>
            <div class="sky-chart-body">
                <canvas id="donationTrendsChart"></canvas>
            </div>
        </div>

        <div class="sky-chart-card">
            <div class="sky-chart-header">
                <h3><?php esc_html_e( 'By Campaign', 'skydonate' ); ?></h3>
            </div>
            <div class="sky-chart-body">
                <canvas id="campaignChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="sky-charts-row">
        <div class="sky-chart-card">
            <div class="sky-chart-header">
                <h3><?php esc_html_e( 'By Country', 'skydonate' ); ?></h3>
            </div>
            <div class="sky-chart-body">
                <canvas id="countryChart"></canvas>
            </div>
        </div>

        <div class="sky-chart-card">
            <div class="sky-chart-header">
                <h3><?php esc_html_e( 'Amount Distribution', 'skydonate' ); ?></h3>
            </div>
            <div class="sky-chart-body">
                <canvas id="distributionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Tables Row -->
    <div class="sky-tables-row">
        <div class="sky-table-card">
            <div class="sky-table-header">
                <h3><?php esc_html_e( 'Top Donors', 'skydonate' ); ?></h3>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-orders' ) ); ?>" class="sky-view-all"><?php esc_html_e( 'View All', 'skydonate' ); ?></a>
            </div>
            <div class="sky-table-body">
                <table class="sky-data-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Donor', 'skydonate' ); ?></th>
                            <th><?php esc_html_e( 'Donations', 'skydonate' ); ?></th>
                            <th><?php esc_html_e( 'Total', 'skydonate' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( ! empty( $top_donors ) ) : ?>
                            <?php foreach ( $top_donors as $donor ) : ?>
                                <tr>
                                    <td>
                                        <div class="sky-donor-info">
                                            <span class="sky-donor-avatar"><?php echo esc_html( strtoupper( substr( $donor['name'], 0, 1 ) ) ); ?></span>
                                            <div class="sky-donor-details">
                                                <span class="sky-donor-name"><?php echo esc_html( $donor['name'] ); ?></span>
                                                <span class="sky-donor-email"><?php echo esc_html( $donor['email'] ); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="sky-badge"><?php echo esc_html( $donor['count'] ); ?></span></td>
                                    <td><strong><?php echo esc_html( $currency_symbol . number_format( $donor['amount'], 0 ) ); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="3" class="sky-empty-state"><?php esc_html_e( 'No donations yet', 'skydonate' ); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="sky-table-card">
            <div class="sky-table-header">
                <h3><?php esc_html_e( 'Recent Donations', 'skydonate' ); ?></h3>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-orders' ) ); ?>" class="sky-view-all"><?php esc_html_e( 'View All', 'skydonate' ); ?></a>
            </div>
            <div class="sky-table-body">
                <table class="sky-data-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Donor', 'skydonate' ); ?></th>
                            <th><?php esc_html_e( 'Amount', 'skydonate' ); ?></th>
                            <th><?php esc_html_e( 'Date', 'skydonate' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( ! empty( $recent_donations ) ) : ?>
                            <?php foreach ( $recent_donations as $donation ) : ?>
                                <tr>
                                    <td>
                                        <div class="sky-donor-info">
                                            <span class="sky-donor-avatar"><?php echo esc_html( strtoupper( substr( $donation['name'], 0, 1 ) ) ); ?></span>
                                            <span class="sky-donor-name"><?php echo esc_html( $donation['name'] ); ?></span>
                                        </div>
                                    </td>
                                    <td><strong><?php echo esc_html( get_woocommerce_currency_symbol( $donation['currency'] ) . number_format( $donation['amount'], 0 ) ); ?></strong></td>
                                    <td>
                                        <span class="sky-time-ago"><?php echo esc_html( $donation['time_ago'] ); ?> <?php esc_html_e( 'ago', 'skydonate' ); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="3" class="sky-empty-state"><?php esc_html_e( 'No donations yet', 'skydonate' ); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Campaign Performance Table -->
    <div class="sky-full-table-card">
        <div class="sky-table-header">
            <h3><?php esc_html_e( 'Campaign Performance', 'skydonate' ); ?></h3>
        </div>
        <div class="sky-table-body">
            <table class="sky-data-table sky-campaign-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Campaign', 'skydonate' ); ?></th>
                        <th><?php esc_html_e( 'Donations', 'skydonate' ); ?></th>
                        <th><?php esc_html_e( 'Total Raised', 'skydonate' ); ?></th>
                        <th><?php esc_html_e( 'Progress', 'skydonate' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $max_amount = ! empty( $campaigns ) ? max( array_column( $campaigns, 'amount' ) ) : 1;
                    if ( ! empty( $campaigns ) ) :
                        foreach ( $campaigns as $campaign ) :
                            $progress = ( $campaign['amount'] / $max_amount ) * 100;
                    ?>
                        <tr>
                            <td>
                                <a href="<?php echo esc_url( admin_url( 'post.php?post=' . $campaign['id'] . '&action=edit' ) ); ?>" class="sky-campaign-link">
                                    <?php echo esc_html( $campaign['name'] ); ?>
                                </a>
                            </td>
                            <td><span class="sky-badge"><?php echo esc_html( number_format( $campaign['count'] ) ); ?></span></td>
                            <td><strong><?php echo esc_html( $currency_symbol . number_format( $campaign['amount'], 0 ) ); ?></strong></td>
                            <td>
                                <div class="sky-progress-bar">
                                    <div class="sky-progress-fill" style="width: <?php echo esc_attr( $progress ); ?>%"></div>
                                </div>
                            </td>
                        </tr>
                    <?php
                        endforeach;
                    else :
                    ?>
                        <tr>
                            <td colspan="4" class="sky-empty-state"><?php esc_html_e( 'No campaigns yet', 'skydonate' ); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Analytics AJAX configuration
    var skydonateAnalytics = {
        ajaxUrl: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
        nonce: '<?php echo esc_js( wp_create_nonce( 'skydonate_analytics_nonce' ) ); ?>',
        currencySymbol: '<?php echo esc_js( $currency_symbol ); ?>'
    };

    // Chart.js default configuration
    Chart.defaults.font.family = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif';
    Chart.defaults.color = '#64748b';

    // Color palette
    var colors = {
        primary: '#4f46e5',
        primaryLight: 'rgba(79, 70, 229, 0.1)',
        secondary: '#ec4899',
        secondaryLight: 'rgba(236, 72, 153, 0.1)',
        chartColors: ['#4f46e5', '#ec4899', '#10b981', '#f59e0b', '#8b5cf6', '#06b6d4', '#f97316', '#84cc16', '#6366f1', '#14b8a6']
    };

    // Store chart instances for updates
    var charts = {};

    // Initialize Trends Chart
    var trendsCtx = document.getElementById('donationTrendsChart');
    if (trendsCtx) {
        charts.trends = new Chart(trendsCtx, {
            type: 'bar',
            data: {
                labels: <?php echo $monthly_labels; ?>,
                datasets: [
                    { type: 'line', label: 'Amount', data: <?php echo $monthly_amounts; ?>, borderColor: colors.primary, backgroundColor: colors.primaryLight, borderWidth: 3, fill: true, tension: 0.4, yAxisID: 'y', pointBackgroundColor: colors.primary, pointBorderColor: '#fff', pointBorderWidth: 2, pointRadius: 4, pointHoverRadius: 6 },
                    { type: 'bar', label: 'Donations', data: <?php echo $monthly_counts; ?>, backgroundColor: colors.secondaryLight, borderColor: colors.secondary, borderWidth: 1, borderRadius: 4, yAxisID: 'y1' }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
                plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1e293b', titleColor: '#fff', bodyColor: '#e2e8f0', padding: 12, cornerRadius: 8, displayColors: true } },
                scales: {
                    x: { grid: { display: false }, border: { display: false } },
                    y: { type: 'linear', display: true, position: 'left', grid: { color: 'rgba(0, 0, 0, 0.05)' }, border: { display: false }, ticks: { callback: function(value) { return skydonateAnalytics.currencySymbol + value.toLocaleString(); } } },
                    y1: { type: 'linear', display: true, position: 'right', grid: { drawOnChartArea: false }, border: { display: false } }
                }
            }
        });
    }

    // Initialize Campaign Chart
    var campaignCtx = document.getElementById('campaignChart');
    if (campaignCtx) {
        charts.campaign = new Chart(campaignCtx, {
            type: 'doughnut',
            data: { labels: <?php echo $campaign_labels; ?>, datasets: [{ data: <?php echo $campaign_amounts; ?>, backgroundColor: colors.chartColors, borderWidth: 0, hoverOffset: 4 }] },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '65%',
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true, pointStyle: 'circle' } },
                    tooltip: { backgroundColor: '#1e293b', titleColor: '#fff', bodyColor: '#e2e8f0', padding: 12, cornerRadius: 8, callbacks: { label: function(context) { return context.label + ': ' + skydonateAnalytics.currencySymbol + context.raw.toLocaleString(); } } }
                }
            }
        });
    }

    // Initialize Country Chart
    var countryCtx = document.getElementById('countryChart');
    if (countryCtx) {
        charts.country = new Chart(countryCtx, {
            type: 'bar',
            data: { labels: <?php echo $country_labels; ?>, datasets: [{ data: <?php echo $country_amounts; ?>, backgroundColor: colors.chartColors, borderWidth: 0, borderRadius: 4 }] },
            options: {
                indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1e293b', titleColor: '#fff', bodyColor: '#e2e8f0', padding: 12, cornerRadius: 8, callbacks: { label: function(context) { return skydonateAnalytics.currencySymbol + context.raw.toLocaleString(); } } } },
                scales: { x: { grid: { color: 'rgba(0, 0, 0, 0.05)' }, border: { display: false }, ticks: { callback: function(value) { return skydonateAnalytics.currencySymbol + value.toLocaleString(); } } }, y: { grid: { display: false }, border: { display: false } } }
            }
        });
    }

    // Initialize Distribution Chart
    var distributionCtx = document.getElementById('distributionChart');
    if (distributionCtx) {
        charts.distribution = new Chart(distributionCtx, {
            type: 'bar',
            data: { labels: <?php echo $distribution_labels; ?>, datasets: [{ data: <?php echo $distribution_counts; ?>, backgroundColor: colors.primary, borderWidth: 0, borderRadius: 4 }] },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1e293b', titleColor: '#fff', bodyColor: '#e2e8f0', padding: 12, cornerRadius: 8, callbacks: { label: function(context) { return context.raw + ' donations'; } } } },
                scales: { x: { grid: { display: false }, border: { display: false } }, y: { grid: { color: 'rgba(0, 0, 0, 0.05)' }, border: { display: false }, ticks: { stepSize: 1 } } }
            }
        });
    }

    // Date range change handler
    var dateRangeSelect = document.getElementById('sky-date-range');
    if (dateRangeSelect) {
        dateRangeSelect.addEventListener('change', function() {
            var days = this.value;
            var wrap = document.querySelector('.sky-dashboard-wrap');
            if (wrap) { wrap.style.opacity = '0.6'; wrap.style.pointerEvents = 'none'; }

            var formData = new FormData();
            formData.append('action', 'skydonate_get_analytics');
            formData.append('nonce', skydonateAnalytics.nonce);
            formData.append('days', days);

            fetch(skydonateAnalytics.ajaxUrl, { method: 'POST', body: formData })
            .then(function(response) { return response.json(); })
            .then(function(result) {
                if (result.success && result.data) {
                    var data = result.data;
                    var symbol = data.currency_symbol;
                    skydonateAnalytics.currencySymbol = symbol;

                    // Update stat cards
                    updateStatCards(data.comparison, symbol, data.days);

                    // Update all charts
                    if (charts.trends && data.trends) {
                        charts.trends.data.labels = data.trends.labels;
                        charts.trends.data.datasets[0].data = data.trends.amounts;
                        charts.trends.data.datasets[1].data = data.trends.counts;
                        charts.trends.update();
                    }
                    if (charts.campaign && data.campaigns) {
                        charts.campaign.data.labels = data.campaigns.labels;
                        charts.campaign.data.datasets[0].data = data.campaigns.amounts;
                        charts.campaign.update();
                    }
                    if (charts.country && data.countries) {
                        charts.country.data.labels = data.countries.labels;
                        charts.country.data.datasets[0].data = data.countries.amounts;
                        charts.country.update();
                    }
                    if (charts.distribution && data.distribution) {
                        charts.distribution.data.labels = data.distribution.labels;
                        charts.distribution.data.datasets[0].data = data.distribution.counts;
                        charts.distribution.update();
                    }

                    // Update tables
                    updateTopDonorsTable(data.top_donors, symbol);
                    updateRecentDonationsTable(data.recent_donations);
                    updateCampaignTable(data.campaigns.data, symbol);
                }
            })
            .catch(function(error) { console.error('Analytics update failed:', error); })
            .finally(function() { if (wrap) { wrap.style.opacity = '1'; wrap.style.pointerEvents = 'auto'; } });
        });
    }

    function updateStatCards(comp, symbol, days) {
        var totalValue = document.querySelector('.sky-stat-card:nth-child(1) .sky-stat-value');
        var totalChange = document.querySelector('.sky-stat-card:nth-child(1) .sky-stat-change');
        if (totalValue) totalValue.textContent = symbol + Number(comp.total.current).toLocaleString(undefined, {maximumFractionDigits: 0});
        if (totalChange) { totalChange.className = 'sky-stat-change ' + (comp.total.change >= 0 ? 'positive' : 'negative'); totalChange.innerHTML = getChangeIcon(comp.total.change >= 0) + Math.abs(comp.total.change) + '%'; }

        var countValue = document.querySelector('.sky-stat-card:nth-child(2) .sky-stat-value');
        var countChange = document.querySelector('.sky-stat-card:nth-child(2) .sky-stat-change');
        if (countValue) countValue.textContent = Number(comp.count.current).toLocaleString();
        if (countChange) { countChange.className = 'sky-stat-change ' + (comp.count.change >= 0 ? 'positive' : 'negative'); countChange.innerHTML = getChangeIcon(comp.count.change >= 0) + Math.abs(comp.count.change) + '%'; }

        var donorsValue = document.querySelector('.sky-stat-card:nth-child(3) .sky-stat-value');
        var donorsSub = document.querySelector('.sky-stat-card:nth-child(3) .sky-stat-sub');
        if (donorsValue) donorsValue.textContent = Number(comp.donors.current).toLocaleString();
        if (donorsSub) donorsSub.textContent = 'Last ' + days + ' days';

        var avgValue = document.querySelector('.sky-stat-card:nth-child(4) .sky-stat-value');
        if (avgValue) avgValue.textContent = symbol + Number(comp.average.current).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function getChangeIcon(positive) {
        return positive
            ? '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>'
            : '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline><polyline points="17 18 23 18 23 12"></polyline></svg>';
    }

    function updateTopDonorsTable(donors, symbol) {
        var tbody = document.querySelector('.sky-tables-row .sky-table-card:first-child tbody');
        if (!tbody) return;
        if (!donors || donors.length === 0) { tbody.innerHTML = '<tr><td colspan="3" class="sky-empty-state"><?php echo esc_js( __( 'No donations yet', 'skydonate' ) ); ?></td></tr>'; return; }
        var html = '';
        donors.forEach(function(donor) {
            html += '<tr><td><div class="sky-donor-info"><span class="sky-donor-avatar">' + donor.name.charAt(0).toUpperCase() + '</span><div class="sky-donor-details"><span class="sky-donor-name">' + escapeHtml(donor.name) + '</span><span class="sky-donor-email">' + escapeHtml(donor.email) + '</span></div></div></td><td><span class="sky-badge">' + donor.count + '</span></td><td><strong>' + symbol + Number(donor.amount).toLocaleString(undefined, {maximumFractionDigits: 0}) + '</strong></td></tr>';
        });
        tbody.innerHTML = html;
    }

    function updateRecentDonationsTable(donations) {
        var tbody = document.querySelector('.sky-tables-row .sky-table-card:last-child tbody');
        if (!tbody) return;
        if (!donations || donations.length === 0) { tbody.innerHTML = '<tr><td colspan="3" class="sky-empty-state"><?php echo esc_js( __( 'No donations yet', 'skydonate' ) ); ?></td></tr>'; return; }
        var html = '';
        donations.forEach(function(donation) {
            html += '<tr><td><div class="sky-donor-info"><span class="sky-donor-avatar">' + donation.name.charAt(0).toUpperCase() + '</span><span class="sky-donor-name">' + escapeHtml(donation.name) + '</span></div></td><td><strong>' + donation.currency + Number(donation.amount).toLocaleString(undefined, {maximumFractionDigits: 0}) + '</strong></td><td><span class="sky-time-ago">' + donation.time_ago + ' <?php echo esc_js( __( 'ago', 'skydonate' ) ); ?></span></td></tr>';
        });
        tbody.innerHTML = html;
    }

    function updateCampaignTable(campaigns, symbol) {
        var tbody = document.querySelector('.sky-full-table-card tbody');
        if (!tbody) return;
        if (!campaigns || campaigns.length === 0) { tbody.innerHTML = '<tr><td colspan="4" class="sky-empty-state"><?php echo esc_js( __( 'No campaigns yet', 'skydonate' ) ); ?></td></tr>'; return; }
        var maxAmount = Math.max.apply(Math, campaigns.map(function(c) { return c.amount; })) || 1;
        var html = '';
        campaigns.forEach(function(campaign) {
            var progress = (campaign.amount / maxAmount) * 100;
            html += '<tr><td><a href="<?php echo esc_url( admin_url( 'post.php?post=' ) ); ?>' + campaign.id + '&action=edit" class="sky-campaign-link">' + escapeHtml(campaign.name) + '</a></td><td><span class="sky-badge">' + Number(campaign.count).toLocaleString() + '</span></td><td><strong>' + symbol + Number(campaign.amount).toLocaleString(undefined, {maximumFractionDigits: 0}) + '</strong></td><td><div class="sky-progress-bar"><div class="sky-progress-fill" style="width: ' + progress + '%"></div></div></td></tr>';
        });
        tbody.innerHTML = html;
    }

    function escapeHtml(text) { var div = document.createElement('div'); div.textContent = text; return div.innerHTML; }
});
</script>

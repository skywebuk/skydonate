<?php
/**
 * Dashboard Main Template
 *
 * Modern WooCommerce Donations Analytics Dashboard
 *
 * @package    Skyweb_Donation_System
 */

if (!defined('ABSPATH')) {
    exit;
}

$license_manager = skydonate_license();
$is_licensed = $license_manager->is_license_valid();
$features = $license_manager->get_features();
$currency_symbol = function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '$';

// Get basic stats
$stats = skydonate_get_dashboard_stats();
?>

<div class="skydonate-dashboard-wrap">
    <!-- Header Section -->
    <div class="skydonate-dashboard-header">
        <div class="header-content">
            <h1><?php esc_html_e('Donation Dashboard', 'skydonate'); ?></h1>
            <p class="subtitle"><?php esc_html_e('Monitor your donation performance and insights', 'skydonate'); ?></p>
        </div>
        <div class="header-actions">
            <select id="dashboard-period" class="skydonate-select">
                <option value="7"><?php esc_html_e('Last 7 Days', 'skydonate'); ?></option>
                <option value="30" selected><?php esc_html_e('Last 30 Days', 'skydonate'); ?></option>
                <option value="90"><?php esc_html_e('Last 90 Days', 'skydonate'); ?></option>
                <option value="365"><?php esc_html_e('Last Year', 'skydonate'); ?></option>
            </select>
            <?php if ($is_licensed && isset($features['pro_widgets']) && $features['pro_widgets']): ?>
            <button type="button" class="skydonate-btn skydonate-btn-outline" id="export-donations">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <?php esc_html_e('Export', 'skydonate'); ?>
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="skydonate-stats-grid">
        <div class="stat-card stat-primary">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label"><?php esc_html_e('Total Donations', 'skydonate'); ?></span>
                <span class="stat-value" id="stat-total"><?php echo esc_html($currency_symbol . number_format($stats['total_amount'], 2)); ?></span>
                <span class="stat-change positive" id="stat-total-change">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M7 14l5-5 5 5z"/>
                    </svg>
                    <span><?php echo esc_html($stats['total_change']); ?>%</span>
                </span>
            </div>
        </div>

        <div class="stat-card stat-success">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label"><?php esc_html_e('Total Donors', 'skydonate'); ?></span>
                <span class="stat-value" id="stat-donors"><?php echo esc_html(number_format($stats['unique_donors'])); ?></span>
                <span class="stat-change positive" id="stat-donors-change">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M7 14l5-5 5 5z"/>
                    </svg>
                    <span><?php echo esc_html($stats['donors_change']); ?>%</span>
                </span>
            </div>
        </div>

        <div class="stat-card stat-info">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                    <line x1="1" y1="10" x2="23" y2="10"></line>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label"><?php esc_html_e('Donations Count', 'skydonate'); ?></span>
                <span class="stat-value" id="stat-count"><?php echo esc_html(number_format($stats['order_count'])); ?></span>
                <span class="stat-change neutral" id="stat-count-change">
                    <span><?php esc_html_e('this period', 'skydonate'); ?></span>
                </span>
            </div>
        </div>

        <div class="stat-card stat-warning">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"></path>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label"><?php esc_html_e('Avg. Donation', 'skydonate'); ?></span>
                <span class="stat-value" id="stat-avg"><?php echo esc_html($currency_symbol . number_format($stats['average_donation'], 2)); ?></span>
                <span class="stat-change neutral" id="stat-avg-change">
                    <span><?php esc_html_e('per donation', 'skydonate'); ?></span>
                </span>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="skydonate-charts-grid">
        <!-- Donations Trend Chart -->
        <div class="chart-card chart-large">
            <div class="chart-header">
                <h3><?php esc_html_e('Donation Trends', 'skydonate'); ?></h3>
                <div class="chart-legend">
                    <span class="legend-item"><span class="legend-color primary"></span><?php esc_html_e('Amount', 'skydonate'); ?></span>
                    <span class="legend-item"><span class="legend-color secondary"></span><?php esc_html_e('Count', 'skydonate'); ?></span>
                </div>
            </div>
            <div class="chart-body">
                <canvas id="donations-trend-chart"></canvas>
            </div>
        </div>

        <!-- Donation Type Breakdown -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><?php esc_html_e('Donation Types', 'skydonate'); ?></h3>
            </div>
            <div class="chart-body">
                <canvas id="donation-types-chart"></canvas>
            </div>
            <div class="chart-footer">
                <div class="type-stats">
                    <div class="type-stat">
                        <span class="type-label"><?php esc_html_e('One-time', 'skydonate'); ?></span>
                        <span class="type-value" id="onetime-total"><?php echo esc_html($currency_symbol . number_format($stats['one_time_total'], 2)); ?></span>
                    </div>
                    <div class="type-stat">
                        <span class="type-label"><?php esc_html_e('Recurring', 'skydonate'); ?></span>
                        <span class="type-value" id="recurring-total"><?php echo esc_html($currency_symbol . number_format($stats['recurring_total'], 2)); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tables Section -->
    <div class="skydonate-tables-grid">
        <!-- Top Projects -->
        <div class="table-card">
            <div class="table-header">
                <h3><?php esc_html_e('Top Projects', 'skydonate'); ?></h3>
                <a href="<?php echo esc_url(admin_url('edit.php?post_type=product')); ?>" class="view-all"><?php esc_html_e('View All', 'skydonate'); ?></a>
            </div>
            <div class="table-body">
                <table class="skydonate-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Project', 'skydonate'); ?></th>
                            <th><?php esc_html_e('Donations', 'skydonate'); ?></th>
                            <th><?php esc_html_e('Total', 'skydonate'); ?></th>
                            <th><?php esc_html_e('Progress', 'skydonate'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="top-projects-list">
                        <?php if (!empty($stats['top_projects'])): ?>
                            <?php foreach ($stats['top_projects'] as $project): ?>
                            <tr>
                                <td>
                                    <div class="project-info">
                                        <span class="project-name"><?php echo esc_html($project['name']); ?></span>
                                    </div>
                                </td>
                                <td><?php echo esc_html(number_format($project['count'])); ?></td>
                                <td><?php echo esc_html($currency_symbol . number_format($project['total'], 2)); ?></td>
                                <td>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo esc_attr(min(100, ($project['total'] / max(1, $stats['total_amount'])) * 100)); ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="no-data"><?php esc_html_e('No projects found', 'skydonate'); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Donations -->
        <div class="table-card">
            <div class="table-header">
                <h3><?php esc_html_e('Recent Donations', 'skydonate'); ?></h3>
                <a href="<?php echo esc_url(admin_url('edit.php?post_type=shop_order')); ?>" class="view-all"><?php esc_html_e('View All', 'skydonate'); ?></a>
            </div>
            <div class="table-body">
                <table class="skydonate-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Donor', 'skydonate'); ?></th>
                            <th><?php esc_html_e('Project', 'skydonate'); ?></th>
                            <th><?php esc_html_e('Amount', 'skydonate'); ?></th>
                            <th><?php esc_html_e('Date', 'skydonate'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="recent-donations-list">
                        <?php if (!empty($stats['recent_donations'])): ?>
                            <?php foreach ($stats['recent_donations'] as $donation): ?>
                            <tr>
                                <td>
                                    <div class="donor-info">
                                        <div class="donor-avatar"><?php echo esc_html(strtoupper(substr($donation['name'], 0, 1))); ?></div>
                                        <span class="donor-name"><?php echo esc_html($donation['name']); ?></span>
                                    </div>
                                </td>
                                <td><?php echo esc_html($donation['project']); ?></td>
                                <td class="amount-cell"><?php echo esc_html($currency_symbol . number_format($donation['amount'], 2)); ?></td>
                                <td class="date-cell"><?php echo esc_html(human_time_diff(strtotime($donation['date']), current_time('timestamp')) . ' ago'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="no-data"><?php esc_html_e('No recent donations', 'skydonate'); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if (!$is_licensed): ?>
    <!-- Upgrade Notice -->
    <div class="skydonate-upgrade-notice">
        <div class="upgrade-content">
            <div class="upgrade-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                </svg>
            </div>
            <div class="upgrade-text">
                <h3><?php esc_html_e('Unlock Advanced Analytics', 'skydonate'); ?></h3>
                <p><?php esc_html_e('Get detailed donor insights, export capabilities, and AI-powered recommendations with a Pro license.', 'skydonate'); ?></p>
            </div>
            <a href="<?php echo esc_url(admin_url('admin.php?page=skydonation-licenses')); ?>" class="skydonate-btn skydonate-btn-primary">
                <?php esc_html_e('Activate License', 'skydonate'); ?>
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    // Initialize Chart.js if available
    if (typeof Chart !== 'undefined') {
        initDashboardCharts();
    }

    // Period selector
    $('#dashboard-period').on('change', function() {
        refreshDashboardData($(this).val());
    });

    // Export button
    $('#export-donations').on('click', function() {
        exportDonations();
    });
});

function initDashboardCharts() {
    // Donations Trend Chart
    var trendCtx = document.getElementById('donations-trend-chart');
    if (trendCtx) {
        new Chart(trendCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: <?php echo wp_json_encode(array_column($stats['daily_data'], 'date')); ?>,
                datasets: [{
                    label: '<?php esc_html_e('Amount', 'skydonate'); ?>',
                    data: <?php echo wp_json_encode(array_column($stats['daily_data'], 'total')); ?>,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    fill: true,
                    tension: 0.4
                }, {
                    label: '<?php esc_html_e('Count', 'skydonate'); ?>',
                    data: <?php echo wp_json_encode(array_column($stats['daily_data'], 'count')); ?>,
                    borderColor: '#10b981',
                    backgroundColor: 'transparent',
                    yAxisID: 'y1',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    y1: {
                        position: 'right',
                        beginAtZero: true,
                        grid: { display: false }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // Donation Types Chart
    var typesCtx = document.getElementById('donation-types-chart');
    if (typesCtx) {
        new Chart(typesCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['<?php esc_html_e('One-time', 'skydonate'); ?>', '<?php esc_html_e('Recurring', 'skydonate'); ?>'],
                datasets: [{
                    data: [<?php echo floatval($stats['one_time_total']); ?>, <?php echo floatval($stats['recurring_total']); ?>],
                    backgroundColor: ['#6366f1', '#10b981'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
}

function refreshDashboardData(period) {
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'skydonate_get_dashboard_stats',
            period: period,
            nonce: '<?php echo wp_create_nonce('skydonate_dashboard_nonce'); ?>'
        },
        success: function(response) {
            if (response.success) {
                updateDashboardUI(response.data);
            }
        }
    });
}

function exportDonations() {
    var period = jQuery('#dashboard-period').val();
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'skydonate_export_donations',
            period: period,
            format: 'csv',
            nonce: '<?php echo wp_create_nonce('skydonate_analytics_nonce'); ?>'
        },
        success: function(response) {
            if (response.success && response.data.content) {
                var blob = new Blob([response.data.content], { type: 'text/csv' });
                var link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = response.data.filename;
                link.click();
            }
        }
    });
}
</script>

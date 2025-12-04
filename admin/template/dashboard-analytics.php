<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Include dashboard class if not already included
if ( ! class_exists( 'Skyweb_Donation_Dashboard' ) ) {
    require_once SKYWEB_DONATION_SYSTEM_ADMIN_PATH . '/class-skyweb-donation-dashboard.php';
}

// Get dashboard data
$comparison = Skyweb_Donation_Dashboard::get_comparison_stats( 30 );
$monthly_data = Skyweb_Donation_Dashboard::get_monthly_donations();
$campaigns = Skyweb_Donation_Dashboard::get_donations_by_campaign( 6 );
$countries = Skyweb_Donation_Dashboard::get_donations_by_country( 8 );
$distribution = Skyweb_Donation_Dashboard::get_donation_distribution();
$top_donors = Skyweb_Donation_Dashboard::get_top_donors( 5 );
$recent_donations = Skyweb_Donation_Dashboard::get_recent_donations( 5 );
$currency_symbol = get_woocommerce_currency_symbol();

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
            <h1><?php esc_html_e( 'Donation Analytics', 'skydonation' ); ?></h1>
            <p><?php esc_html_e( 'Track your donation performance and donor engagement', 'skydonation' ); ?></p>
        </div>
        <div class="sky-dashboard-actions">
            <select id="sky-date-range" class="sky-select">
                <option value="30"><?php esc_html_e( 'Last 30 Days', 'skydonation' ); ?></option>
                <option value="60"><?php esc_html_e( 'Last 60 Days', 'skydonation' ); ?></option>
                <option value="90"><?php esc_html_e( 'Last 90 Days', 'skydonation' ); ?></option>
                <option value="365"><?php esc_html_e( 'Last 12 Months', 'skydonation' ); ?></option>
            </select>
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
                <span class="sky-stat-label"><?php esc_html_e( 'Total Raised', 'skydonation' ); ?></span>
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
                <span class="sky-stat-label"><?php esc_html_e( 'Total Donations', 'skydonation' ); ?></span>
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
                <span class="sky-stat-label"><?php esc_html_e( 'Unique Donors', 'skydonation' ); ?></span>
                <span class="sky-stat-value"><?php echo esc_html( number_format( $comparison['donors']['current'] ) ); ?></span>
                <span class="sky-stat-sub"><?php esc_html_e( 'Last 30 days', 'skydonation' ); ?></span>
            </div>
        </div>

        <div class="sky-stat-card">
            <div class="sky-stat-icon average">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
            </div>
            <div class="sky-stat-content">
                <span class="sky-stat-label"><?php esc_html_e( 'Average Donation', 'skydonation' ); ?></span>
                <span class="sky-stat-value"><?php echo esc_html( $currency_symbol . number_format( $comparison['average']['current'], 2 ) ); ?></span>
                <span class="sky-stat-sub"><?php esc_html_e( 'Per transaction', 'skydonation' ); ?></span>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="sky-charts-row">
        <div class="sky-chart-card sky-chart-large">
            <div class="sky-chart-header">
                <h3><?php esc_html_e( 'Donation Trends', 'skydonation' ); ?></h3>
                <div class="sky-chart-legend">
                    <span class="sky-legend-item amount"><span class="sky-legend-dot"></span><?php esc_html_e( 'Amount', 'skydonation' ); ?></span>
                    <span class="sky-legend-item count"><span class="sky-legend-dot"></span><?php esc_html_e( 'Count', 'skydonation' ); ?></span>
                </div>
            </div>
            <div class="sky-chart-body">
                <canvas id="donationTrendsChart"></canvas>
            </div>
        </div>

        <div class="sky-chart-card">
            <div class="sky-chart-header">
                <h3><?php esc_html_e( 'By Campaign', 'skydonation' ); ?></h3>
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
                <h3><?php esc_html_e( 'By Country', 'skydonation' ); ?></h3>
            </div>
            <div class="sky-chart-body">
                <canvas id="countryChart"></canvas>
            </div>
        </div>

        <div class="sky-chart-card">
            <div class="sky-chart-header">
                <h3><?php esc_html_e( 'Amount Distribution', 'skydonation' ); ?></h3>
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
                <h3><?php esc_html_e( 'Top Donors', 'skydonation' ); ?></h3>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-orders' ) ); ?>" class="sky-view-all"><?php esc_html_e( 'View All', 'skydonation' ); ?></a>
            </div>
            <div class="sky-table-body">
                <table class="sky-data-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Donor', 'skydonation' ); ?></th>
                            <th><?php esc_html_e( 'Donations', 'skydonation' ); ?></th>
                            <th><?php esc_html_e( 'Total', 'skydonation' ); ?></th>
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
                                <td colspan="3" class="sky-empty-state"><?php esc_html_e( 'No donations yet', 'skydonation' ); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="sky-table-card">
            <div class="sky-table-header">
                <h3><?php esc_html_e( 'Recent Donations', 'skydonation' ); ?></h3>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-orders' ) ); ?>" class="sky-view-all"><?php esc_html_e( 'View All', 'skydonation' ); ?></a>
            </div>
            <div class="sky-table-body">
                <table class="sky-data-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Donor', 'skydonation' ); ?></th>
                            <th><?php esc_html_e( 'Amount', 'skydonation' ); ?></th>
                            <th><?php esc_html_e( 'Date', 'skydonation' ); ?></th>
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
                                        <span class="sky-time-ago"><?php echo esc_html( $donation['time_ago'] ); ?> <?php esc_html_e( 'ago', 'skydonation' ); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="3" class="sky-empty-state"><?php esc_html_e( 'No donations yet', 'skydonation' ); ?></td>
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
            <h3><?php esc_html_e( 'Campaign Performance', 'skydonation' ); ?></h3>
        </div>
        <div class="sky-table-body">
            <table class="sky-data-table sky-campaign-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Campaign', 'skydonation' ); ?></th>
                        <th><?php esc_html_e( 'Donations', 'skydonation' ); ?></th>
                        <th><?php esc_html_e( 'Total Raised', 'skydonation' ); ?></th>
                        <th><?php esc_html_e( 'Progress', 'skydonation' ); ?></th>
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
                            <td colspan="4" class="sky-empty-state"><?php esc_html_e( 'No campaigns yet', 'skydonation' ); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart.js default configuration
    Chart.defaults.font.family = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif';
    Chart.defaults.color = '#64748b';

    // Color palette
    const colors = {
        primary: '#4f46e5',
        primaryLight: 'rgba(79, 70, 229, 0.1)',
        secondary: '#ec4899',
        secondaryLight: 'rgba(236, 72, 153, 0.1)',
        success: '#10b981',
        warning: '#f59e0b',
        danger: '#ef4444',
        chartColors: [
            '#4f46e5', '#ec4899', '#10b981', '#f59e0b', '#8b5cf6',
            '#06b6d4', '#f97316', '#84cc16', '#6366f1', '#14b8a6'
        ]
    };

    // Donation Trends Chart (Line + Bar combo)
    const trendsCtx = document.getElementById('donationTrendsChart');
    if (trendsCtx) {
        new Chart(trendsCtx, {
            type: 'bar',
            data: {
                labels: <?php echo $monthly_labels; ?>,
                datasets: [
                    {
                        type: 'line',
                        label: 'Amount (<?php echo esc_js( $currency_symbol ); ?>)',
                        data: <?php echo $monthly_amounts; ?>,
                        borderColor: colors.primary,
                        backgroundColor: colors.primaryLight,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y',
                        pointBackgroundColor: colors.primary,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        type: 'bar',
                        label: 'Donations',
                        data: <?php echo $monthly_counts; ?>,
                        backgroundColor: colors.secondaryLight,
                        borderColor: colors.secondary,
                        borderWidth: 1,
                        borderRadius: 4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#fff',
                        bodyColor: '#e2e8f0',
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: true
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        border: {
                            display: false
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        border: {
                            display: false
                        },
                        ticks: {
                            callback: function(value) {
                                return '<?php echo esc_js( $currency_symbol ); ?>' + value.toLocaleString();
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false
                        },
                        border: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Campaign Doughnut Chart
    const campaignCtx = document.getElementById('campaignChart');
    if (campaignCtx) {
        new Chart(campaignCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo $campaign_labels; ?>,
                datasets: [{
                    data: <?php echo $campaign_amounts; ?>,
                    backgroundColor: colors.chartColors,
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 16,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#fff',
                        bodyColor: '#e2e8f0',
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return context.label + ': <?php echo esc_js( $currency_symbol ); ?>' + context.raw.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // Country Horizontal Bar Chart
    const countryCtx = document.getElementById('countryChart');
    if (countryCtx) {
        new Chart(countryCtx, {
            type: 'bar',
            data: {
                labels: <?php echo $country_labels; ?>,
                datasets: [{
                    data: <?php echo $country_amounts; ?>,
                    backgroundColor: colors.chartColors,
                    borderWidth: 0,
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#fff',
                        bodyColor: '#e2e8f0',
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return '<?php echo esc_js( $currency_symbol ); ?>' + context.raw.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        border: {
                            display: false
                        },
                        ticks: {
                            callback: function(value) {
                                return '<?php echo esc_js( $currency_symbol ); ?>' + value.toLocaleString();
                            }
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        },
                        border: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Distribution Bar Chart
    const distributionCtx = document.getElementById('distributionChart');
    if (distributionCtx) {
        new Chart(distributionCtx, {
            type: 'bar',
            data: {
                labels: <?php echo $distribution_labels; ?>,
                datasets: [{
                    data: <?php echo $distribution_counts; ?>,
                    backgroundColor: colors.primary,
                    borderWidth: 0,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#fff',
                        bodyColor: '#e2e8f0',
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return context.raw + ' donations';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        border: {
                            display: false
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        border: {
                            display: false
                        },
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
});
</script>

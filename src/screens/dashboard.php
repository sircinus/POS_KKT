<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../css/POS.css">
    <title>POS Kedai Kopi Kita</title>

    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <?php
    include('../functions/navbar.php');
    include('../../db.php');

    // Get the current date and current month
    $currentDate = date('Y-m-d');  // Current date in 'YYYY-MM-DD' format
    $currentMonth = date('Y-m');  // Current month in 'YYYY-MM' format
    
    // Query for total sales today
    $sql_sales_today = "SELECT SUM(total_amount) as sales_today FROM orders WHERE DATE(order_date) = '$currentDate' AND status='Paid'";
    $result_sales_today = $conn->query($sql_sales_today);
    $sales_today = $result_sales_today->fetch_assoc()['sales_today'];

    // Query for total sales this month
    $sql_sales_month = "SELECT SUM(total_amount) as sales_month FROM orders WHERE DATE_FORMAT(order_date, '%Y-%m') = '$currentMonth' AND status='Paid'";
    $result_sales_month = $conn->query($sql_sales_month);
    $sales_month = $result_sales_month->fetch_assoc()['sales_month'];

    // Query for daily sales data in the current month
    $sql_daily_sales = "SELECT DATE(order_date) as order_day, SUM(total_amount) as daily_sales FROM orders 
                        WHERE DATE_FORMAT(order_date, '%Y-%m') = '$currentMonth'
                        GROUP BY DATE(order_date)
                        ORDER BY DATE(order_date) ASC";
    $result_daily_sales = $conn->query($sql_daily_sales);

    // Prepare sales data for the daily chart
    $daily_labels = [];
    $daily_sales_data = [];

    while ($row = $result_daily_sales->fetch_assoc()) {
        $daily_labels[] = $row['order_day']; // Date labels
        $daily_sales_data[] = $row['daily_sales']; // Sales data per day
    }

    // Query for monthly sales data for the last 6 months
    $sql_monthly_sales = "SELECT DATE_FORMAT(order_date, '%Y-%m') as month, SUM(total_amount) as monthly_sales 
                          FROM orders 
                          WHERE DATE_FORMAT(order_date, '%Y-%m') BETWEEN DATE_FORMAT(NOW() - INTERVAL 6 MONTH, '%Y-%m') AND '$currentMonth'
                          GROUP BY month
                          ORDER BY month ASC";
    $result_monthly_sales = $conn->query($sql_monthly_sales);

    // Prepare sales data for the monthly chart
    $monthly_labels = [];
    $monthly_sales_data = [];

    while ($row = $result_monthly_sales->fetch_assoc()) {
        $monthly_labels[] = $row['month']; // Month labels
        $monthly_sales_data[] = $row['monthly_sales']; // Monthly sales data
    }

    // Query for total number of food products
    $sql_makanan = "SELECT COUNT(*) as total_makanan FROM produk WHERE kategori='makanan'";
    $result_makanan = $conn->query($sql_makanan);
    $makanan_count = $result_makanan->fetch_assoc()['total_makanan'];

    // Query for total number of drink products
    $sql_minuman = "SELECT COUNT(*) as total_minuman FROM produk WHERE kategori='minuman'";
    $result_minuman = $conn->query($sql_minuman);
    $minuman_count = $result_minuman->fetch_assoc()['total_minuman'];
    ?>

    <div class="wrapContainer spaceEvenly">
        <div class="container yellow">
            <p>Penjualan Hari Ini</p>
            <p>Rp <?php echo number_format($sales_today, 0, ',', '.'); ?></p>
        </div>
        <div class="container red">
            <p>Penjualan Bulan Ini</p>
            <p>Rp <?php echo number_format($sales_month, 0, ',', '.'); ?></p>
        </div>
        <div class="container blue">
            <p>Jumlah Produk Makanan</p>
            <p><?php echo $makanan_count; ?></p>
        </div>
        <div class="container green">
            <p>Jumlah Produk Minuman</p>
            <p><?php echo $minuman_count; ?></p>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="chartSection">
        <div class="chartContainer">
            <canvas id="salesChart"></canvas>
        </div>

        <div class="chartContainer">
            <canvas id="monthlySalesChart"></canvas>
        </div>
    </div>


    <script>
        // Get the data for the daily chart (from PHP)
        var dailyLabels = <?php echo json_encode($daily_labels); ?>;
        var dailySalesData = <?php echo json_encode($daily_sales_data); ?>;

        // Create the daily sales chart
        var ctx = document.getElementById('salesChart').getContext('2d');
        var salesChart = new Chart(ctx, {
            type: 'line', // Line chart for daily sales
            data: {
                labels: dailyLabels, // Labels for each day
                datasets: [{
                    label: 'Penjualan Per Hari (Rp)',
                    data: dailySalesData, // Data from PHP
                    borderColor: '#edad3c', // Line color
                    tension: 0.4, // Smooth line
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        labels: {
                            color: '#000', // Legend label color
                        }
                    },
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Tanggal',
                            color: '#000',
                        },
                        ticks: {
                            color: '#000', // X-axis label color
                            autoSkip: true,
                            maxTicksLimit: 7, // Limit the number of ticks on the x-axis
                        },
                        grid: {
                            color: '#000', // Grid line color
                            lineWidth: 0.5, // Grid line width
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Total Penjualan (Rp)',
                            color: '#000',
                        },
                        ticks: {
                            color: '#000', // Y-axis label color
                            callback: function (value) {
                                return 'Rp ' + value.toLocaleString(); // Format numbers as currency
                            }
                        },
                        grid: {
                            color: '#000', // Grid line color
                            lineWidth: 0.5, // Grid line width
                        }
                    }
                }
            }
        });

        // Get the data for the monthly chart (from PHP)
        var monthlyLabels = <?php echo json_encode($monthly_labels); ?>;
        var monthlySalesData = <?php echo json_encode($monthly_sales_data); ?>;

        // Create the monthly sales chart
        var ctxMonthly = document.getElementById('monthlySalesChart').getContext('2d');
        var monthlySalesChart = new Chart(ctxMonthly, {
            type: 'line', // Line chart for monthly sales
            data: {
                labels: monthlyLabels, // Labels for each month
                datasets: [{
                    label: 'Penjualan Per Bulan (Rp)',
                    data: monthlySalesData, // Data from PHP
                    borderColor: '#edad3c', // Line color
                    tension: 0.4, // Smooth line
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        labels: {
                            color: '#000', // Legend label color
                        }
                    },
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Bulan',
                            color: '#000',
                        },
                        ticks: {
                            color: '#000', // X-axis label color
                            autoSkip: true,
                            maxTicksLimit: 6, // Limit the number of ticks on the x-axis
                        },
                        grid: {
                            color: '#000', // Grid line color

                            lineWidth: 0.5, // Grid line width
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Total Penjualan (Rp)',
                            color: '#000',
                        },
                        ticks: {
                            color: '#000', // Y-axis label color
                            callback: function (value) {
                                return 'Rp ' + value.toLocaleString(); // Format numbers as currency
                            }
                        },
                        grid: {
                            color: '#000', // Grid line color
                            lineWidth: 0.5, // Grid line width

                        }
                    }
                }
            }
        });
    </script>

</body>

</html>
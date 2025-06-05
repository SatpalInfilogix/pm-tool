<?php
require_once '../includes/header.php';
$user_values = userProfile();

if ($user_values['role'] && ($user_values['role'] !== 'hr' && $user_values['role'] !== 'admin')) {
    $redirectUrl = $_SERVER['HTTP_REFERER'] ?? '/pm-tool';
    $_SESSION['toast'] = "Access denied. Employees only.";
    header("Location: " . $redirectUrl);
    exit();
}

function mapStatus($derivedStatus)
{
    // map internal status string to display
    switch ($derivedStatus) {
        case 'present':
            return 'Present';
        case 'late':
            return 'Late'; // optional, if you want to add late logic
        case 'short_leave':
            return 'Short Leave';
        case 'half_day':
            return 'Half Day';
        case 'absent':
            return 'Absent';
        default:
            return 'Unknown';
    }
}

function deriveStatusFromTimes($inTime, $outTime)
{
    if (!$inTime || !$outTime) {
        return 'absent';
    }

    $inTimestamp = strtotime($inTime);
    $outTimestamp = strtotime($outTime);
    if (!$inTimestamp || !$outTimestamp || $outTimestamp <= $inTimestamp) {
        return 'absent';
    }

    $diffSeconds = $outTimestamp - $inTimestamp;
    $hours = $diffSeconds / 3600;

    if ($hours >= 8) {
        return 'present';
    } elseif ($hours >= 6) {
        return 'short_leave';
    } elseif ($hours >= 3) {
        return 'half_day';
    } else {
        return 'absent';
    }
}

function getAttendanceCounts($conn, $startDate, $endDate)
{
    $sql = "SELECT in_time, out_time FROM attendance WHERE date BETWEEN '$startDate' AND '$endDate'";
    $result = mysqli_query($conn, $sql);

    $counts = [
        'present' => 0,
        'short_leave' => 0,
        'half_day' => 0,
        'absent' => 0,
    ];

    while ($row = mysqli_fetch_assoc($result)) {
        $status = deriveStatusFromTimes($row['in_time'], $row['out_time']);
        if (isset($counts[$status])) {
            $counts[$status]++;
        } else {
            $counts['absent']++; // fallback
        }
    }

    // Format for chart - array of ['name' => ..., 'value' => ...]
    $formatted = [];
    foreach ($counts as $key => $count) {
        $formatted[] = [
            'name' => mapStatus($key),
            'value' => $count
        ];
    }
    return $formatted;
}

$week_start = date('Y-m-d', strtotime('monday this week'));
$week_end = date('Y-m-d', strtotime('sunday this week'));
$weekly_data = getAttendanceCounts($conn, $week_start, $week_end);

$month_start = date('Y-m-01');
$month_end = date('Y-m-t');
$monthly_data = getAttendanceCounts($conn, $month_start, $month_end);

$prev_month_start = date('Y-m-01', strtotime('first day of last month'));
$prev_month_end = date('Y-m-t', strtotime('last day of last month'));
$previous_month_data = getAttendanceCounts($conn, $prev_month_start, $prev_month_end);

?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box pb-3 d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Attendance Report</h4>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body">
                        <div id="weekly_chart" style="height: 400px;"></div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body">
                        <div id="monthly_chart" style="height: 400px;"></div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body">
                        <div id="previous_month_chart" style="height: 400px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>

<script>
    const weeklyData = <?php echo json_encode($weekly_data); ?>;
    const monthlyData = <?php echo json_encode($monthly_data); ?>;
    const previousMonthData = <?php echo json_encode($previous_month_data); ?>;

    const initPieChart = (elementId, title, data) => {
        const chart = echarts.init(document.getElementById(elementId));
        chart.setOption({
            title: {
                text: title,
                left: 'center'
            },
            tooltip: {
                trigger: 'item'
            },
            legend: {
                bottom: '0%',
                left: 'center'
            },
            series: [{
                name: 'Status',
                type: 'pie',
                radius: '50%',
                data: data,
                emphasis: {
                    itemStyle: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                    }
                }
            }]
        });
    };

    initPieChart('weekly_chart', 'Weekly Attendance', weeklyData);
    initPieChart('monthly_chart', 'Monthly Attendance', monthlyData);
    initPieChart('previous_month_chart', 'Previous Month Attendance', previousMonthData);
</script>

<?php require_once '../includes/footer.php'; ?>
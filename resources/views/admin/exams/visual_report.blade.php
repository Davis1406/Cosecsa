@extends('layout.app')

@push('styles')
    <style>
        .chart-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .chart-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        .chart-wrapper {
            position: relative;
            height: 400px;
            margin: 20px 0;
        }

        .chart-wrapper canvas {
            height: 100% !important;
            width: 100% !important;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #a02626, #c73333);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(160, 38, 38, 0.3);
        }

        .stat-number {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }

        .back-button,
        .back-button:link,
        .back-button:visited,
        .back-button:focus,
        .back-button:hover {
            background-color: #a02626;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
        }

        .back-button:hover {
            background-color: #8b1f1f;
        }

        .print-button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
            margin-left: 10px;
        }

        .print-button:hover {
            background-color: #218838;
            color: white;
            text-decoration: none;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .chart-container {
                page-break-inside: avoid;
            }
        }
    </style>
@endpush

@section('content')
    <div class="wrapper">
        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>{{ $header_title }}</h1>
                        </div>
                    </div>
                </div>
            </section>

            <div class="col-md-12">
                @include('_message')
            </div>

            <section class="content">
                <div class="container-fluid">
                    <div class="no-print">
                        <a href="{{ url('admin/exams/examiner-confirmation') }}" class="back-button">
                            <i class="fa fa-arrow-left"></i> Back to Examiner List
                        </a>
                        <button onclick="window.print()" class="print-button">
                            <i class="fa fa-print"></i> Print Report
                        </button>
                    </div>

                    <!-- Statistics Overview -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number">{{ array_sum($availabilityData) }}</div>
                            <div class="stat-label">Total Examiners</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number">
                                {{ ($availabilityData['FCS'] ?? 0) + ($availabilityData['MCS'] ?? 0) + ($availabilityData['FCS and MCS'] ?? 0) }}
                            </div>
                            <div class="stat-label">Available Examiners</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number">{{ $availabilityData['Not Available'] ?? 0 }}</div>
                            <div class="stat-label">Not Available</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number">{{ count($countryData) }}</div>
                            <div class="stat-label">Countries Represented</div>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="chart-container">
                                <h3 class="chart-title">Examiner Availability</h3>
                                <div class="chart-wrapper">
                                    <canvas id="availabilityChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="chart-container">
                                <h3 class="chart-title">Participation Type</h3>
                                <div class="chart-wrapper">
                                    <canvas id="participationChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Country Distribution Chart -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="chart-container">
                                <h3 class="chart-title">Top 10 Countries by Examiner Count</h3>
                                <div class="chart-wrapper">
                                    <canvas id="countryChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Hidden JSON data for chart rendering -->
    <script type="application/json" id="chartData">
    {
        "availability": {
            "labels": @json(array_keys($availabilityData)),
            "data": @json(array_values($availabilityData))
        },
        "participation": {
            "labels": @json(array_keys($participationData)),
            "data": @json(array_values($participationData))
        },
        "country": {
            "labels": @json(array_keys($countryData)),
            "data": @json(array_values($countryData))
        }
    }
    </script>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>

    <script>
        $(document).ready(function () {
            let chartData;
            try {
                chartData = JSON.parse($('#chartData').text());
            } catch (e) {
                console.error('Error parsing chart data:', e);
                return;
            }

            const createChart = (id, config) => {
                const canvas = document.getElementById(id);
                if (!canvas) return;
                const ctx = canvas.getContext('2d');
                new Chart(ctx, config);
            };

            // Availability Chart
            if (chartData.availability.data.some(val => val > 0)) {
                createChart('availabilityChart', {
                    type: 'doughnut',
                    data: {
                        labels: chartData.availability.labels,
                        datasets: [{
                            data: chartData.availability.data,
                            backgroundColor: ['#28a745', '#17a2b8', '#007bff', '#dc3545'],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' },
                            datalabels: {
                                color: '#fff',
                                font: { weight: 'bold', size: 14 },
                                formatter: value => value === 0 ? '' : value
                            }
                        }
                    },
                    plugins: [ChartDataLabels]
                });
            }

            // Participation Chart
            if (chartData.participation.data.some(val => val > 0)) {
                createChart('participationChart', {
                    type: 'pie',
                    data: {
                        labels: chartData.participation.labels,
                        datasets: [{
                            data: chartData.participation.data,
                            backgroundColor: ['#a02626', '#ffc107', '#6c757d', '#fd7e14'],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' },
                            datalabels: {
                                color: '#fff',
                                font: { weight: 'bold', size: 14 },
                                formatter: value => value === 0 ? '' : value
                            }
                        }
                    },
                    plugins: [ChartDataLabels]
                });
            }

            // Country Chart
            if (chartData.country.data.some(val => val > 0)) {
                createChart('countryChart', {
                    type: 'bar',
                    data: {
                        labels: chartData.country.labels,
                        datasets: [{
                            label: 'Number of Examiners',
                            data: chartData.country.data,
                            backgroundColor: 'rgba(160, 38, 38, 0.8)',
                            borderColor: '#a02626',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            datalabels: {
                                anchor: 'center',
                                align: 'center',
                                color: '#fff',
                                font: { size: 12, weight: 'bold' },
                                formatter: value => value === 0 ? '' : value
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { stepSize: 1, precision: 0 }
                            },
                            x: {
                                ticks: {
                                    maxRotation: 45,
                                    font: { size: 11 }
                                }
                            }
                        }
                    },
                    plugins: [ChartDataLabels]
                });
            }
        });
    </script>
@endpush

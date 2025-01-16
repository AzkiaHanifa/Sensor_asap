<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Sensor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        async function fetchSensorData() {
            try {
                const response = await fetch('/api/sensor-data');
                if (!response.ok) {
                    throw new Error('Failed to fetch sensor data');
                }
                const data = await response.json();

                // Take only the most recent record
                const mostRecent = data[0];

                // Render data to table
                const tableBody = document.getElementById('sensor-table-body');
                tableBody.innerHTML = '';

                if (mostRecent) {
                    const row = `<tr>
                        <td>${mostRecent.temperature}</td>
                        <td>${mostRecent.humidity}</td>
                        <td>${mostRecent.smoke}</td>
                    </tr>`;
                    tableBody.innerHTML = row;
                }
            } catch (error) {
                document.getElementById('error-message').innerText = error.message;
            }
        }

        setInterval(fetchSensorData, 1500); // Refresh every 1.5 seconds
        window.onload = fetchSensorData;
    </script>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h1 class="h4">Dashboard Sensor</h1>
                    </div>
                    <div class="card-body">
                        <p id="error-message" class="text-danger text-center"></p>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Temperature (&#8451;)</th>
                                        <th>Humidity (%)</th>
                                        <th>Smoke (ppm)</th>
                                    </tr>
                                </thead>
                                <tbody id="sensor-table-body">
                                    <!-- Data will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-center text-muted">
                        <small>Updated every 1.5 seconds</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
    
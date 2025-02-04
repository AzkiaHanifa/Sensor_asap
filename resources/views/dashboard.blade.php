<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Sensor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body {
            background-image: url('images/background.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }

        .container {
            max-width: 800px;
            width: 100%;
            background: rgba(0, 0, 0, 0); /* Transparent black */
            border-radius: 15px;
            padding: 20px;
        }

        .card {
            background-color: #004085; /* Dark blue for readability */
            border-radius: 10px;
            color: white;
        }

        .card-header, .card-footer {
            background-color: #0069d9; /* Lighter blue for header/footer */
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
    </style>

    <script>
        let alertSound = new Audio('/audio/alert.mp3');

        function showAlertModal(message) {
            document.getElementById('alertModalMessage').innerText = message;
            const myModal = new bootstrap.Modal(document.getElementById('alertModal'), {
                keyboard: false,
                backdrop: 'static'
            });
            myModal.show();
        }

        function stopAlert() {
            alertSound.pause();
            alertSound.currentTime = 0;
            const modal = bootstrap.Modal.getInstance(document.getElementById('alertModal'));
            if (modal) {
                modal.hide();
            }
        }

        async function fetchSensorData() {
            try {
                const response = await fetch('http://192.168.18.11/sensor-data'); // Update with correct IP
                if (!response.ok) {
                    throw new Error('Failed to fetch sensor data');
                }
                const mostRecent = await response.json();

                const tableBody = document.getElementById('sensor-table-body');
                tableBody.innerHTML = '';

                if (mostRecent) {
                    const temperatureColor = mostRecent.temperature > 60 ? 'text-danger' : '';
                    const row = `<tr>
                        <td class="${temperatureColor}"><i class="fas fa-thermometer-half"></i> ${mostRecent.temperature}</td>
                        <td><i class="fas fa-tint text-primary"></i> ${mostRecent.humidity}</td>
                        <td><i class="fas fa-smoking text-secondary"></i> ${mostRecent.smoke}</td>
                    </tr>`;
                    tableBody.innerHTML = row;

                    if (mostRecent.temperature > 60 || mostRecent.smoke >= 500) {
                        if (alertSound.ended || alertSound.paused) {
                            alertSound.loop = true;
                            alertSound.play().then(() => {
                                const alertMessage = mostRecent.temperature > 60 ? 'High temperature detected!' : 'High smoke levels detected!';
                                showAlertModal(alertMessage);
                            });
                        }
                    } else {
                        stopAlert();
                    }
                } else {
                    tableBody.innerHTML = '<tr><td colspan="3" class="text-center text-danger">No data available</td></tr>';
                }
            } catch (error) {
                document.getElementById('error-message').innerText = error.message;
                const tableBody = document.getElementById('sensor-table-body');
                tableBody.innerHTML = '<tr><td colspan="3" class="text-center text-danger">Error fetching data</td></tr>';
            }
        }

        setInterval(fetchSensorData, 1500);
        window.onload = fetchSensorData;
    </script>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header text-center">
                        <h1 class="h4">Dashboard Sensor</h1>
                    </div>
                    <div class="card-body">
                        <p id="error-message" class="text-danger text-center"></p>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Temperature (&#8451;)</th>
                                        <th>Humidity (%)</th>
                                        <th>Smoke (ppm)</th>
                                    </tr>
                                </thead>
                                <tbody id="sensor-table-body">
                                    <tr class="table-active">
                                        <td colspan="3">Loading...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                       
                    </div>
                    <div class="card-footer text-center">
                        <small>Updated every 1.5 seconds</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Modal -->
    <div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="alertModalLabel">Alert!</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="alertModalMessage"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Sensor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
    body {
        background: url('/images/background.jpg') no-repeat center center fixed;
        background-size: cover;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }
    .card {
        border-radius: 10px;
        overflow: hidden;
    }
    .card-header {
        background: rgb(0, 0, 0);
        color: #ffffff;
    }
    .card-footer {
        background: rgb(0, 0, 0);
        color: #ffffff;
    }
    .table-dark {
        background-color: #000000; /* Black background */
        color: #ffd700; /* Golden yellow text */
        border: 1px solid #ffd700; /* Border to match text color */
    }
    .table-dark th, .table-dark td {
        border-color: #ffd700; /* Border for each cell */
    }
    .table-dark tbody tr:hover {
        background-color: #333333; /* Darker background on hover */
    }
    .modal-header {
        background-color: #ef4444;
        color: #ffffff;
    }
    .modal-footer {
        background-color: #f87171;
    }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let alertSound = new Audio('/audio/alert.mp3'); // Replace with the correct path

        // Function to display the alert modal
        function showAlertModal() {
            const myModal = new bootstrap.Modal(document.getElementById('alertModal'), {
                keyboard: false,
                backdrop: 'static'
            });
            myModal.show();
        }

        // Function to stop the alert sound
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
                const response = await fetch('/api/sensor-data');
                if (!response.ok) {
                    throw new Error('Failed to fetch sensor data');
                }
                const data = await response.json();

                // Get the most recent data
                const mostRecent = data[0];

                // Render data into the table
                const tableBody = document.getElementById('sensor-table-body');
                tableBody.innerHTML = '';

                if (mostRecent) {
                    const temperatureColor = mostRecent.temperature > 30 ? 'text-danger' : '';
                    const row = `<tr>
                        <td class="${temperatureColor}"><i class="fas fa-thermometer-half"></i> ${mostRecent.temperature}</td>
                        <td><i class="fas fa-tint text-primary"></i> ${mostRecent.humidity}</td>
                        <td><i class="fas fa-smoking text-secondary"></i> ${mostRecent.smoke}</td>
                    </tr>`;
                    tableBody.innerHTML = row;

                    // Check if smoke value has increased above or equal to 500
                    if (mostRecent.smoke >= 500) {
                        if (alertSound.ended || alertSound.paused) {
                            alertSound.loop = true;
                            alertSound.play().then(() => {
                                showAlertModal(); // Show modal only after sound starts playing
                            });
                        }
                    } else {
                        // Stop sound if smoke value drops below 500
                        stopAlert();
                    }
                }
            } catch (error) {
                document.getElementById('error-message').innerText = error.message;
            }
        }

        setInterval(fetchSensorData, 1500); // Refresh every 1.5 seconds
        window.onload = fetchSensorData;
    </script>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header text-center">
                        <h1 class="h4">Dashboard Sensor</h1>
                    </div>
                    <div class="card-body">
                        <p id="error-message" class="text-danger text-center"></p>
                        <div class="table-responsive">
                            <table class="table table-dark">
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
                        <div class="text-center mt-4">
                            <button type="button" class="btn btn-primary" onclick="stopAlert()">Stop Alarm</button>
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
                    <h5 class="modal-title" id="alertModalLabel">Fire Alert!</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Potential fire detected, please check your home immediately.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

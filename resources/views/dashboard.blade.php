<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Sensor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
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
                const response = await fetch('http://192.168.112.18/sensor-data'); // Ganti dengan IP ESP8266 Anda
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
                                const alertMessage = mostRecent.temperature > 60 ?  'High temperature detected!' : 'High smoke levels detected!';
                                showAlertModal(alertMessage);
                            });
                        }
                    } else {
                        stopAlert();
                    }
                }
            } catch (error) {
                document.getElementById('error-message').innerText = error.message;
            }
        }

        setInterval(fetchSensorData, 1500);
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

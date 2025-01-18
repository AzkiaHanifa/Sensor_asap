<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Sensor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let previousSmokeValue = 0; // Menyimpan nilai smoke sebelumnya
        let alertSound = new Audio('/audio/alert.mp3'); // Ganti dengan path yang sesuai
        alertSound.loop = true; // Membuat suara berulang (looping)
        let modal; // Untuk menyimpan referensi modal

        // Fungsi untuk menampilkan modal
        function showAlertModal() {
            // Menampilkan modal peringatan
            const myModal = new bootstrap.Modal(document.getElementById('alertModal'), {
                keyboard: false, // Menghindari menutup modal dengan tombol ESC
                backdrop: 'static' // Modal tetap terbuka sampai ditutup dengan klik tombol
            });
            myModal.show();
        }

        async function fetchSensorData() {
            try {
                const response = await fetch('/api/sensor-data');
                if (!response.ok) {
                    throw new Error('Failed to fetch sensor data');
                }
                const data = await response.json();

                // Ambil data terbaru
                const mostRecent = data[0];

                // Render data ke tabel
                const tableBody = document.getElementById('sensor-table-body');
                tableBody.innerHTML = '';

                if (mostRecent) {
                    const row = `<tr>
                        <td>${mostRecent.temperature}</td>
                        <td>${mostRecent.humidity}</td>
                        <td>${mostRecent.smoke}</td>
                    </tr>`;
                    tableBody.innerHTML = row;

                    // Cek apakah nilai smoke naik dan mainkan suara
                    if (mostRecent.smoke > previousSmokeValue) {
                        alertSound.play(); // Mainkan suara

                        // Tampilkan modal jika smoke naik
                        showAlertModal();
                    }

                    // Simpan nilai smoke terakhir
                    previousSmokeValue = mostRecent.smoke;

                    // Matikan suara jika nilai smoke turun ke 0
                    if (mostRecent.smoke === 0) {
                        alertSound.pause(); // Hentikan suara
                        alertSound.currentTime = 0; // Reset suara
                    }
                }
            } catch (error) {
                document.getElementById('error-message').innerText = error.message;
            }
        }

        setInterval(fetchSensorData, 1500); // Refresh setiap 1.5 detik
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

    <!-- Modal Peringatan -->
    <div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="alertModalLabel">Peringatan Kebakaran!</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Potensial kebakaran, silakan cek rumah Anda kembali.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

</body>
</html>

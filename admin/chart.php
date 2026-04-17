<?php include '../config/koneksi.php'; ?>

<canvas id="chartNilai"></canvas>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
fetch('chart_data.php')
    .then(res => res.json())
    .then(data => {

        const ctx = document.getElementById('chartNilai');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.label,
                datasets: [{
                    label: 'Rata-rata Nilai',
                    data: data.nilai
                }]
            }
        });

    });
</script>
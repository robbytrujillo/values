fetch("admin/chart_data.php")
  .then((res) => res.json())
  .then((data) => {
    new Chart(document.getElementById("chartNilai"), {
      type: "line",
      data: {
        labels: data.label,
        datasets: [
          {
            label: "Nilai Mapel",
            data: data.nilai,
            fill: true,
          },
        ],
      },
    });
  });

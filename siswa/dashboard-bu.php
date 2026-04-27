<div class="container mt-4">

    <!-- HEADER -->
    <div class="card text-center" data-aos="fade-down">
        <div class="card-body">
            <h4><strong><?= $_SESSION['user']['nama']; ?></strong></h4>
            <p>Kelas: <strong><?= $siswa['nama_kelas']; ?></strong></p>
        </div>
    </div>

    <!-- FILTER -->
    <div class="d-flex justify-content-center align-items-center mb-3" style="gap:10px" data-aos="fade-up">
        <label><strong>Mapel:</strong></label>
        <select id="filterMapel" class="form-control" style="max-width:220px;">
            <option value="">Semua</option>
        </select>
    </div>

    <!-- STATISTIK -->
    <div class="row text-center mb-4">

        <div class="col-md-3" data-aos="zoom-in">
            <div class="card shadow">
                <div class="card-body d-flex align-items-center">
                    <i class='bx bx-line-chart text-primary' style="font-size:40px;"></i>
                    <div class="ml-3 text-left">
                        <small>Rata-rata</small>
                        <h4 id="rata"><?= $rata_siswa ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3" data-aos="zoom-in" data-aos-delay="100">
            <div class="card shadow">
                <div class="card-body d-flex align-items-center">
                    <i class='bx bx-trophy text-success' style="font-size:40px;"></i>
                    <div class="ml-3 text-left">
                        <small>Tertinggi</small>
                        <h4 id="max"><?= $max_siswa ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3" data-aos="zoom-in" data-aos-delay="200">
            <div class="card shadow">
                <div class="card-body d-flex align-items-center">
                    <i class='bx bx-down-arrow text-danger' style="font-size:40px;"></i>
                    <div class="ml-3 text-left">
                        <small>Terendah</small>
                        <h4 id="min"><?= $min_siswa ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3" data-aos="zoom-in" data-aos-delay="300">
            <div class="card shadow">
                <div class="card-body d-flex align-items-center">
                    <i class='bx bx-bar-chart text-dark' style="font-size:40px;"></i>
                    <div class="ml-3 text-left">
                        <small>Total</small>
                        <h4 id="total"><?= $total_siswa ?></h4>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- RANKING -->
    <div class="row mb-4 text-white">

        <div class="col-md-4" data-aos="fade-up">
            <div class="card shadow" style="background:linear-gradient(135deg,#3b82f6,#6366f1);">
                <div class="card-body">
                    <small>Ranking Harian</small>
                    <h2 id="rank_harian"><?= $rank_harian ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-4" data-aos="fade-up" data-aos-delay="150">
            <div class="card shadow" style="background:linear-gradient(135deg,#f59e0b,#f97316);">
                <div class="card-body">
                    <small>Ranking Bulanan</small>
                    <h2 id="rank_bulanan"><?= $rank_bulanan ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
            <div class="card shadow" style="background:linear-gradient(135deg,#10b981,#059669);">
                <div class="card-body">
                    <small>Ranking Semester</small>
                    <h2 id="rank_semester"><?= $rank_semester ?></h2>
                </div>
            </div>
        </div>

    </div>

    <!-- TOP 5 -->
    <div class="card shadow" data-aos="fade-up">
        <div class="card-body">
            <h5 class="text-center">🏆 Top 5 Siswa</h5>

            <div id="topSiswaContainer">
                <?php $no=1; while($t=mysqli_fetch_assoc($q_top)){ ?>
                <div class="d-flex align-items-center mb-3 p-2" data-aos="fade-right">
                    <div style="width:40px;"><?= $no ?></div>
                    <img src="https://api.dicebear.com/7.x/adventurer/svg?seed=<?= urlencode($t['nama']) ?>" width="45"
                        style="border-radius:50%;">
                    <div class="ml-3 flex-grow-1">
                        <div><?= $t['nama'] ?></div>
                        <small class="text-muted">Total: <?= $t['total'] ?></small>
                    </div>
                </div>
                <?php $no++; } ?>
            </div>
        </div>
    </div>

    <!-- CHART -->
    <div class="card" data-aos="zoom-in">
        <div class="card-body">
            <canvas id="chartNilai"></canvas>
        </div>
    </div>

    <!-- PROGRESS -->
    <div class="card" data-aos="fade-up">
        <div class="card-body">
            <h6>Progress Nilai</h6>
            <div class="progress">
                <div class="progress-bar bg-success" id="progressBar" style="width: <?= $rata_siswa ?>%">
                    <?= $rata_siswa ?>%
                </div>
            </div>
        </div>
    </div>

    <!-- TABLE -->
    <div class="card" data-aos="fade-up">
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Mapel</th>
                        <th>Nilai</th>
                        <th>Jenis</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                </tbody>
            </table>
        </div>
    </div>

</div>
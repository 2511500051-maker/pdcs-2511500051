<?php

require_once "../config.php";
require_once "../helpers/response.php";

$q = "SELECT m.Id, m.Nama, m.Nim, j.Nama_jurusan AS jurusan
FROM mahasiswa m
LEFT JOIN jurusan j ON m.Jurusan_id = j.Id
ORDER BY m.Id DESC";

$r = mysqli_query($GLOBALS['koneksi'], $q);

if (!$r) {
    sendResponse(false, "query gagal: " . mysqli_error($GLOBALS['koneksi']), null, 500);
}

$data = [];

while ($row = mysqli_fetch_assoc($r)) {
    $data[] = $row;
}

sendResponse(true, "berhasil", $data, 200);
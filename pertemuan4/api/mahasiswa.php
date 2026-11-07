<?php

require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../helpers/response.php";

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':

        if (isset($_GET['Id'])) {
            $id = $_GET['Id'];

            $q = "SELECT  
                m.Id,  
                m.Nama,  
                m.Nim,  
                j.Nama_jurusan AS jurusan  
                FROM mahasiswa m  
                LEFT JOIN jurusan j on m.Jurusan_id = j.Id  
                WHERE m.Id = '$id'";

            $r = mysqli_query($koneksi, $q);
            if (!$r) {
                sendResponse(
                    false,
                    "query gagal: " . mysqli_error($koneksi),
                    null,
                    500
                );
            }


            $data = mysqli_fetch_assoc($r);

            if (!$data) {
                sendResponse(
                    false,
                    "Mahasiswa tidak ditemukan",
                    null,
                    404
                );
            }

            sendResponse(true, "berhasil", $data, 200);
        }

        // search 
        if (isset($_GET['search'])) {
            $search = $_GET['search'];

            $q = "SELECT  
                m.Id,  
                m.Nama,  
                m.Nim,  
                j.Nama_jurusan AS jurusan  
                FROM mahasiswa m  
                LEFT JOIN jurusan j  
                on m.Jurusan_id = j.Id  
                WHERE m.Nama LIKE '%$search%'  
                    OR m.Nim LIKE '%$search%' 
                ORDER BY m.Id DESC";

            $r = mysqli_query($koneksi, $q);

            if (!$r) {
                sendResponse(
                    false,
                    "query gagal: " . mysqli_error($koneksi),
                    null,
                    500
                );
            }

            $data = [];

            while ($row = mysqli_fetch_assoc($r)) {
                $data[] = $row;
            }

            sendResponse(true, "berhasil", $data, 200);
        }

        // pegination 
        if (isset($_GET['page']) || isset($_GET['limit'])) {

            $page = isset($_GET['page'])
                ? (int)$_GET['page']
                : 1;

            $limit = isset($_GET['limit'])
                ? (int)$_GET['limit']
                : 10;

            if ($page < 1) {
                $page = 1;
            }

            if ($limit < 1) {
                $limit = 10;
            }

            $offset = ($page - 1) * $limit;


            $q = "SELECT  
                m.Id,  
                m.Nama,  
                m.Nim,  
                j.Nama_jurusan AS jurusan  
                FROM mahasiswa m  
                LEFT JOIN jurusan j  
                on m.Jurusan_id = j.Id  
                ORDER BY m.Id DESC 
                LIMIT $limit OFFSET $offset";

            $r = mysqli_query($koneksi, $q);

            if (!$r) {
                sendResponse(
                    false,
                    "query gagal: " . mysqli_error($koneksi),
                    null,
                    500
                );
            }

            $data = [];

            while ($row = mysqli_fetch_assoc($r)) {
                $data[] = $row;
            }

            sendResponse(true, "berhasil", $data, 200);
        }
        break;

    case 'POST':

        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || !isset($input['Nama']) || !isset($input['Nim']) || !isset($input['Jurusan_id'])) {
            sendResponse(false, "field Nama, Nim, Jurusan_id Wajib", null, 400);
        }

        $Nama = $input['Nama'];
        $Nim = $input['Nim'];
        $Jurusan_id = (int) $input['Jurusan_id'];

        $cek = mysqli_query($koneksi, "SELECT Id FROM Jurusan WHERE Id = '$Jurusan_id'");

        if (mysqli_num_rows($cek) == 0) {
            sendResponse(false, "Jurusan_id tidak ditemukan", null, 404);
        }

        // Tambahan: menyimpan data mahasiswa
        $q = "INSERT INTO mahasiswa (Nama, Nim, Jurusan_id)
                  VALUES ('$Nama', '$Nim', '$Jurusan_id')";

        $r = mysqli_query($koneksi, $q);

        if (!$r) {
            sendResponse(
                false,
                "query gagal: " . mysqli_error($koneksi),
                null,
                500
            );
        }

        $data = [
            "Id" => mysqli_insert_id($koneksi),
            "Nama" => $Nama,
            "Nim" => $Nim,
            "Jurusan_id" => $Jurusan_id
        ];

        sendResponse(true, "Mahasiswa berhasil ditambahkan", $data, 201);

        break;
    default:
        sendResponse(false, "Method tidak diizinkan", null, 405);
        break;
}

die;

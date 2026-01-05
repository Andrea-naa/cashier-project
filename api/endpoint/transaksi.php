<?php

// endpoint untuk transaksi
require_once '../config/database.php';
require_once '../model/Transaksi.php';

$database = new Database();
$db = $database->getConnection();
$transaksi = new Transaksi($db);
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        // baca data transaksi
        if(isset($_GET['id'])) {
            $transaksi->id = $_GET['id'];
            if($transaksi->readOne()) {
                echo json_encode([
                    "success" => true,
                    "data" => [
                        "id" => $transaksi->id,
                        "nomor_surat" => $transaksi->nomor_surat,
                        "user_id" => $transaksi->user_id,
                        "username" => $transaksi->username,
                        "jenis_transaksi" => $transaksi->jenis_transaksi,
                        "nominal" => $transaksi->nominal,
                        "keterangan" => $transaksi->keterangan,
                        "tanggal_transaksi" => $transaksi->tanggal_transaksi,
                        "is_approved" => $transaksi->is_approved
                    ]
                ]);
            } else {
                http_response_code(404);
                echo json_encode(["success" => false, "message" => "Data tidak ditemukan"]);
            }
        } else {
            // ngambil semua data dengan filter
            $filter = [];
            if(isset($_GET['jenis_transaksi'])) $filter['jenis_transaksi'] = $_GET['jenis_transaksi'];
            if(isset($_GET['date_from'])) $filter['date_from'] = $_GET['date_from'];
            if(isset($_GET['date_to'])) $filter['date_to'] = $_GET['date_to'];
            if(isset($_GET['is_approved'])) $filter['is_approved'] = $_GET['is_approved'];

            $stmt = $transaksi->read($filter);
            $transaksi_arr = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $transaksi_arr[] = $row;
            }

            echo json_encode([
                "success" => true,
                "count" => count($transaksi_arr),
                "data" => $transaksi_arr
            ]);
        }
        break;

    case 'POST':
        // Buat transaksi baru
        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->user_id) && 
           !empty($data->username) && 
           !empty($data->jenis_transaksi) && 
           !empty($data->nominal) && 
           !empty($data->keterangan)) {

            $transaksi->user_id = $data->user_id;
            $transaksi->username = $data->username;
            $transaksi->jenis_transaksi = $data->jenis_transaksi;
            $transaksi->nominal = $data->nominal;
            $transaksi->keterangan = $data->keterangan;
            $transaksi->is_approved = isset($data->is_approved) ? $data->is_approved : 0;

            try {
                if($transaksi->create()) {
                    http_response_code(201);
                    echo json_encode([
                        "success" => true,
                        "message" => "Transaksi berhasil disimpan",
                        "data" => [
                            "id" => $transaksi->id,
                            "nomor_surat" => $transaksi->nomor_surat
                        ]
                    ]);
                } else {
                    http_response_code(503);
                    echo json_encode(["success" => false, "message" => "Gagal menyimpan transaksi"]);
                }
            } catch(Exception $e) {
                http_response_code(500);
                echo json_encode([
                    "success" => false, 
                    "message" => "Error: " . $e->getMessage()
                ]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Data tidak lengkap"]);
        }
        break;

    case 'PUT':
        // update transaksi
        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->id) && !empty($data->nominal) && !empty($data->keterangan)) {
            $transaksi->id = $data->id;
            $transaksi->nominal = $data->nominal;
            $transaksi->keterangan = $data->keterangan;

            if($transaksi->update()) {
                echo json_encode([
                    "success" => true,
                    "message" => "Transaksi berhasil diupdate"
                ]);
            } else {
                http_response_code(503);
                echo json_encode(["success" => false, "message" => "Gagal update transaksi"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Data tidak lengkap"]);
        }
        break;

    case 'DELETE':
        // delete transaksi
        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->id)) {
            $transaksi->id = $data->id;

            if($transaksi->delete()) {
                echo json_encode([
                    "success" => true,
                    "message" => "Transaksi berhasil dihapus"
                ]);
            } else {
                http_response_code(503);
                echo json_encode(["success" => false, "message" => "Gagal hapus transaksi"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "ID tidak ditemukan"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["success" => false, "message" => "Method tidak diizinkan"]);
        break;
}
?>
<?php
// helper api untuk transaksi

class ApiTransaksi {
    private $api_url = 'http://localhost/cashier-project/api/endpoint/transaksi.php';
    
//    bagian method untuk request ke api transaksi
    public function getAll($filters = []) {
        $query_string = '';
        if (!empty($filters)) {
            $query_string = '?' . http_build_query($filters);
        }
        
        $response = file_get_contents($this->api_url . $query_string);
        return json_decode($response, true);
    }
    
// bagian method untuk request ke api transaksi berdasarkan id
    public function getById($id) {
        $response = file_get_contents($this->api_url . '?id=' . intval($id));
        return json_decode($response, true);
    }
    
// bagian method untuk request ke api transaksi untuk create, update, delete
    public function create($data) {
        $options = [
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-Type: application/json',
                'content' => json_encode($data)
            ]
        ];
        
        $context = stream_context_create($options);
        $response = file_get_contents($this->api_url, false, $context);
        return json_decode($response, true);
    }
    
// bagian method untuk request ke api transaksi untuk update
    public function update($data) {
        $options = [
            'http' => [
                'method'  => 'PUT',
                'header'  => 'Content-Type: application/json',
                'content' => json_encode($data)
            ]
        ];
        
        $context = stream_context_create($options);
        $response = file_get_contents($this->api_url, false, $context);
        return json_decode($response, true);
    }
    
// bagian method untuk request ke api transaksi untuk delete
    public function delete($id) {
        $options = [
            'http' => [
                'method'  => 'DELETE',
                'header'  => 'Content-Type: application/json',
                'content' => json_encode(['id' => intval($id)])
            ]
        ];
        
        $context = stream_context_create($options);
        $response = file_get_contents($this->api_url, false, $context);
        return json_decode($response, true);
    }
}
?>

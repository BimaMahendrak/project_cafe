<?php
include '../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $halaman = $_POST['halaman']; // halaman dikirim dari form
    $redirectPage = '';

    // Tentukan halaman redirect berdasarkan halaman
    switch ($halaman) {
        case 'Main Course':
            $redirectPage = '../../modul/admin/menu/main_course_admin.php';
            break;
        case 'Dessert':
            $redirectPage = '../../modul/admin/menu/dessert_admin.php';
            break;
        case 'Coffee':
            $redirectPage = '../../modul/admin/menu/coffee_admin.php';
            break;
        case 'Non Coffee':
            $redirectPage = '../../modul/admin/menu/non_coffee_admin.php';
            break;
        default:
            die('Kategori tidak valid.');
    }

    if ($action === 'edit') {
        // Logika untuk Edit
        $id_produk = $_POST['id_produk'];
        $nama = $_POST['nama'];
        $harga = $_POST['harga'];
        $fotoLama = $_POST['foto_lama'];
        $fotoDatabasePath = $fotoLama;
        $kategori = $_POST['kategori'];

        // Proses upload gambar jika ada file baru
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../public/admin/';
            $fileName = basename($_FILES['foto']['name']);
            $fotoBaru = $uploadDir . $fileName;
            $fotoDatabasePath = 'public/admin/' . $fileName;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $fotoBaru)) {
                // Hapus foto lama jika file baru berhasil diunggah
                if (file_exists('../../' . $fotoLama)) {
                    unlink('../../' . $fotoLama);
                }
            } else {
                die('Gagal mengunggah gambar baru.');
            }
        }

        // Validasi input
        if (empty($id_produk) || empty($nama) || empty($kategori) || empty($harga)) {
            die('Semua field harus diisi.');
        }

        // Update data di database
        $query = "UPDATE menu SET nama_produk = ?, kategori = ?, foto = ?, harga = ? WHERE id_produk = ?";
        $stmt = $koneksi->prepare($query);
        $stmt->bind_param("sssii", $nama, $kategori, $fotoDatabasePath, $harga, $id_produk);

        if ($stmt->execute()) {
            header("Location: $redirectPage?status=success");
            exit;
        } else {
            die('Error: ' . $stmt->error);
        }
    } elseif ($action === 'delete') {
        // Logika untuk Delete
        $id_produk = $_POST['id_produk'];
        $foto = $_POST['foto'];

        if (empty($id_produk)) {
            die('ID produk tidak ditemukan.');
        }

        // Hapus data dari database
        $query = "DELETE FROM menu WHERE id_produk = ?";
        $stmt = $koneksi->prepare($query);
        $stmt->bind_param("i", $id_produk);

        if ($stmt->execute()) {
            // Hapus file foto jika ada
            if (!empty($foto) && file_exists('../../' . $foto)) {
                unlink('../../' . $foto);
            }
            header("Location: $redirectPage?status=deleted");
            exit;
        } else {
            die('Error: ' . $stmt->error);
        }
    } else {
        die('Aksi tidak valid.');
    }
}
?>
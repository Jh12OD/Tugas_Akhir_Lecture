// Tunggu sampai seluruh halaman selesai dimuat
document.addEventListener('DOMContentLoaded', function() {
    console.log("ZeroWaste Tracker App Loaded!");

    // Contoh: Konfirmasi Logout
    const logoutLinks = document.querySelectorAll('a[href="logout.php"]');
    logoutLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm("Apakah Anda yakin ingin keluar?")) {
                e.preventDefault();
            }
        });
    });

    // Contoh: Validasi Form Sederhana (jika ada form input jumlah)
    const qtyInput = document.querySelector('input[name="quantity"]');
    if (qtyInput) {
        qtyInput.addEventListener('change', function() {
            if (this.value < 1) {
                alert("Jumlah minimal adalah 1!");
                this.value = 1;
            }
        });
    }
});
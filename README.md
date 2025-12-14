TUGAS PROJECT AKHIR Programming for Business (Lecture): 
Assignment 1: 
1.	Login, Home member dan Home Admin: 
1.1.	 Login 
 
Jika member dan admin ingin melakukan login maka, member harus mendaftar terlebih dahulu di halaman registrasi dengan mengklik Daftar, sedangkan untuk admin dapat melakukan login dengan akun yang telah disediakan di dalam database zerowaste. untuk setiap password yang dimasukan ke dalam website zerowaste akan dihash menggunakan branch hasing, dimana password akan dienkripsi menjadi 20-40 karakter acak. Ketentuannya terdiri dari: 
-	User dapat mengisi bagian username atau email 
-	User harus mengisi email dengan format @gmail.com 
-	User harus mengisi Password dengan jumlah karakter lebih dari 6 sesuai dengan yang telah didaftarkan 
-	Kedua password, email atau username tidak boleh kosong










Halaman login terdiri dari username dan password, dimana jika: 
1.2.	Halaman Home Member
 
•	Member melakukan login maka dia akan pergi ke halaman Home dengan tampilan untuk member dimana terdiri dari ringkasan keseluruhan aktivitas dari member di aplikasi zerowaste seperti Poin, sampah yang dihasilkan per kilogram, status pengajuan poin untuk pnukaran sampah, level, event-event di aplikasi zerowaste.  

1.3.	 Halaman Home Admin 
 
•	Admin melakukan login maka dia akan pergi ke halaman Dashboard dengan tampilan untuk admin dimana dashboard terdiri dari ringkasan informasi yang dibutuhkan untuk si admin seperti jumlah user, jumlah pengajuan penukaran poin, sampah yang dikumpulkan dari seluruh bank sampah, jumlah forum yang diungah, top user, sampah terkumpul per 6 bulan terakhir dan presentase sampah per kategori. 


2.	Registration
 
Halaman Registration dikhususkan untuk member yang belum memiliki akun. Halaman member diwajibkan untuk mengisi nama lengkap, username, email, no.telp, passoword dan konfirmasi password. Untuk setiap pengisian data-data di atas memiliki ketentuan sebagai berikut: 
•	Nama lengkap wajib diisi
•	Username wajib diisi 
•	Email wajib diisi dan berakhiran @gmail.com
•	No telp wajib diisi dengan angka 
•	Password harus setidaknya berisikan 6 charcter 
•	Konfirmasi password harus sama dengan password

Jika sudah lengkap maka member dapat pergi ke halaman login dengan mengklik tulisan Login di bawah tombol daftar untuk login ke halaman home member
















3.	Home Guest
 
Halaman Guest terdiri dari 4 section utama yaitu tentang website, fitur-fitur di dalam website, dan Lokasi cabang bank sampah di Jakarta: 

•	tentang Zerowaste
 

•	fitur-fitur Zerowaste
 

•	Lokasi bank-bank sampah di Jakarta
Untuk Lokasi bank sampah user dapat melihat Lokasi pasti bank sampah dengan menekan tombol lihat di maps untuk direction langsung google maps untuk melihat rute ataupun Lokasi tepat bank sampah yang ingin dituju. 
 

Assignment 2: 
1.	Member
1.1.	 Tukar Sampah
 
Fitur "Tukar Sampah" adalah sebuah antarmuka digital yang dirancang untuk memfasilitasi proses penukaran sampah yang dapat didaur ulang menjadi poin penghargaan. Tujuannya adalah mendorong pengguna untuk aktif memilah dan menyetorkan sampah mereka ke bank sampah terdaftar. Antarmuka ini terbagi menjadi tiga komponen utama yang memungkinkan pengguna mengajukan penukaran, melacak statusnya, dan memahami perhitungan poin yang mereka peroleh.
Poin Penting Fitur:
•	Form Pengajuan (Formulir Pengajuan):
o	Pengguna dapat mengajukan penukaran sampah dengan mengisi detail penting seperti Kategori sampah (contoh: Kertas, Plastik, Logam), Bank Sampah tujuan, Lokasi Penukaran, Jumlah sampah (dalam Kilogram), dan Deskripsi singkat mengenai jenis sampah.
o	Pengajuan wajib menyertakan Foto Sampah sebagai bukti, dengan batasan format dan ukuran file tertentu (maksimal 5MB, format PNG, JPG).
•	Riwayat Pengajuan (Riwayat Transaksi):
o	Menampilkan daftar lengkap dari semua pengajuan penukaran sampah yang pernah dilakukan, diurutkan berdasarkan Tanggal.
o	Memberikan visibilitas status real-time untuk setiap pengajuan: Approved (Disetujui), Rejected (Ditolak), atau status lain yang mungkin ada.
o	Mencantumkan detail penukaran, termasuk Kategori, Lokasi penyerahan, Jumlah (berat), dan total Poin yang berhasil diperoleh (hanya pada status Approved).
o	Contohnya, pada gambar terlihat penukaran Plastik seberat 10.0 kg mendapatkan +100 Poin.
•	Informasi Poin (Sistem Penghargaan):
o	Menjelaskan rasio konversi sampah menjadi poin: 1 Kg = 10 Poin.
o	Memberi tahu pengguna mengenai durasi verifikasi pengajuan, yaitu 1-2 hari kerja, yang menandakan proses ini melibatkan tinjauan manual oleh petugas.
Fitur ini secara keseluruhan menciptakan ekosistem yang terstruktur untuk mengelola proses daur ulang skala kecil, memberikan insentif berupa poin yang kemungkinan besar dapat ditukarkan dengan reward atau manfaat lain di kemudian hari.

1.2.	 Catatan Harian 
 
Fitur ini dirancang untuk memungkinkan pengguna mencatat dan memantau produksi atau pengumpulan sampah mereka secara harian. Ini berfungsi sebagai dashboard personal yang memberikan visualisasi tren dan komposisi sampah pengguna.
•	Form Tambah Catatan:
-	Pengguna dapat menambahkan data sampah harian dengan menentukan Tanggal pencatatan.
-	Pengguna harus memilih Jenis Sampah (contoh: Kertas, Plastik, Organik).
-	Menginput Jumlah sampah yang dicatat, dengan pilihan Satuan (terlihat kg dan pcs digunakan dalam riwayat).
-	Terdapat kolom Catatan opsional untuk deskripsi kontekstual, misalnya, dari mana sampah itu berasal ("Contoh: Sisa makanan siang").
•	Visualisasi Data (Tren, Distribusi, dan Kategori):
-	Tren Sampah 14 Hari: Grafik garis menunjukkan akumulasi atau rata-rata jumlah sampah yang dicatat dalam kurun waktu 14 hari, memberikan gambaran visual tentang peningkatan atau penurunan produksi sampah pengguna.
-	Distribusi Sampah: Diagram lingkaran (pie chart) menampilkan proporsi atau persentase dari setiap jenis sampah yang telah dicatat (Kertas, Organik, Plastik) dari total keseluruhan.
-	Per Kategori: Grafik batang (bar chart) menunjukkan jumlah total atau frekuensi spesifik dari setiap jenis sampah yang dicatat, memberikan perbandingan yang jelas antar kategori.
•	Riwayat Catatan:
-	Tabel yang menampilkan daftar semua catatan sampah yang telah dibuat, diurutkan berdasarkan Tanggal.
-	Detail yang disajikan meliputi Tanggal, Jenis Sampah, Jumlah (beserta satuan), dan Catatan yang diberikan.
-	Terdapat opsi Aksi berupa ikon tempat sampah merah, yang memungkinkan pengguna untuk menghapus catatan yang sudah ada.
Fitur ini secara keseluruhan membantu pengguna untuk lebih sadar dan akuntabel terhadap kebiasaan pengelolaan sampah pribadi mereka melalui pencatatan dan analisis visual yang sederhana.







1.3.	Profile 
 
Aplikasi ZeroWaste menyediakan dasbor "Profile Saya" yang komprehensif, berfungsi sebagai pusat identitas dan statistik pengguna. Profil ini menampilkan data inti seperti nama pengguna (Budi Santoso) dan username (@budi), serta total Poin yang berhasil dikumpulkan (saat ini 465 Poin). Bagian Edit Profile memungkinkan pengguna memperbarui informasi pribadi mereka, termasuk Username (tidak dapat diubah setelah dibuat), Email, Nama Lengkap, No. Telepon, dan Alamat. Selain itu, terdapat opsi untuk Ubah Password demi menjaga keamanan akun.
Level Progress memotivasi pengguna dengan sistem gamifikasi, di mana mereka naik level dari Newcomer (0 poin) hingga Eco Champion (1000 poin) berdasarkan akumulasi poin mereka. Level saat ini, Eco Enthusiast, tercapai karena poin Budi berada di atas ambang batas 200 poin. Profil juga mencatat Informasi Akun seperti tanggal bergabung (03 Des 2023 00:17) dan Status Akun yang tertulis Aktif. Secara keseluruhan, fitur profil ini menjadi pusat data performa dan insentif, yang didukung oleh input data harian dari fitur Catatan Sampah Harian.









1.4.	 Forum 
 
Forum Komunitas pada aplikasi ZeroWaste adalah wadah interaktif yang dirancang bagi pengguna untuk saling berbagi pengetahuan, tips, dan pengalaman seputar pengelolaan sampah dan gaya hidup minim limbah. Fitur ini memungkinkan pengguna untuk Buat Postingan baru, serta melihat dan berinteraksi dengan postingan dari anggota komunitas lainnya. Contoh postingan yang ada meliputi saran praktis, seperti "Cara Mengurangi Sampah Plastik" dari Andi Wijaya, yang membagikan tips untuk membawa tas belanja dan botol minum sendiri. 
 
Fitur melihat detail postingan dan memberikan komentar di Forum Komunitas berfungsi sebagai pusat diskusi terperinci. Pengguna dapat membaca postingan lengkap, seperti tips dari Andi Wijaya tentang cara mengurangi sampah plastik, kemudian langsung berinteraksi dengan Kolom Komentar. 



1.5.	 Inbox
 
Fitur Inbox berfungsi sebagai pusat notifikasi dan informasi terkini bagi pengguna, menampilkan Pesan mengenai status transaksi (seperti "Penukaran Sampah Ditolak" atau "Penukaran Sampah Disetujui" yang menambahkan poin ke akun). Selain itu, fitur ini juga menyajikan informasi tentang Event Mendatang, seperti "Aksi Bersih Pantai Jakarta" atau "Workshop Daur Ulang Kreatif," yang memungkinkan pengguna untuk tetap terinformasi dan terlibat dalam kegiatan komunitas ZeroWaste. Pengguna memiliki opsi untuk Tandai Semua Dibaca untuk mengelola notifikasi dan memastikan mereka tidak melewatkan berita atau pembaruan penting mengenai akun dan acara.

1.6.	 Lokasi Cabang
 
Fitur Lokasi Cabang Daur Ulang berfungsi sebagai direktori interaktif yang memungkinkan pengguna mencari drop point terdekat untuk menukarkan sampah mereka. Pengguna dapat mencari lokasi berdasarkan nama tempat atau alamat dan memfilter hasilnya berdasarkan Kota. Setiap kartu lokasi, seperti "Bank Sampah (RT 002 RW 01)" atau "Bank Sampah Hijau Selaras Mandiri," mencantumkan detail penting: alamat lengkap, jam operasional, Jenis Sampah yang Diterima (misalnya, Plastik, Kertas, Logam, Organik), dan tombol Lihat di Google Maps untuk navigasi. Fitur ini penting untuk memudahkan pengguna ZeroWaste menemukan tempat penyetoran sampah yang paling sesuai dan terdekat. 

2.	Admin 
2.1.	 Manage User 
 
Fitur Manage Users di Admin Panel aplikasi ZeroWaste berfungsi sebagai alat kendali utama bagi Administrator untuk mengelola data pengguna terdaftar. Administrator dapat melihat daftar lengkap pengguna, termasuk Username, Email, Nomor Telepon, total Poin yang dimiliki, Status akun (seperti Aktif), dan Tanggal Terdaftar. Selain fungsi pencarian dan filter, fitur ini juga memberikan Aksi kontrol administratif yang krusial, seperti Nonaktifkan akun, Reset Poin, dan Block Forum untuk menjaga ketertiban dan keamanan platform.

2.2.	 Penukaran Sampah
 
Fitur Penukaran Sampah di Admin Panel dirancang untuk memungkinkan Administrator mengelola dan memproses semua permintaan penukaran sampah yang diajukan oleh pengguna. Administrator dapat mencari permintaan berdasarkan User atau Kategori sampah dan memfilter status permintaan, yang secara default disetel ke Pending. Meskipun pada tampilan saat ini tidak ada permintaan yang ditemukan, fitur ini menyediakan tabel yang akan menampilkan detail penting seperti Tanggal, User, Kategori, Jumlah sampah, Foto bukti, dan Status, serta kolom Aksi yang krusial untuk menyetujui atau menolak permintaan penukaran sampah.

2.3.	Laporan 
 
Fitur Laporan Tracking Sampah di Admin Panel berfungsi sebagai dashboard analitik yang menyajikan ringkasan dan tren data penukaran sampah secara menyeluruh. Laporan ini menunjukkan total 75.5 kg Sampah Terkumpul, 13 Total Transaksi, dan 647 Total Poin Diberikan. Selain itu, fitur ini menampilkan visualisasi berupa Tren Bulanan (12 Bulan) dan Per Kategori (diagram lingkaran) yang memvisualisasikan komposisi sampah. Di bawahnya, terdapat tabel terperinci mengenai Sampah per Kategori yang menunjukkan jumlah transaksi, total berat (kg), dan poin yang diberikan, serta daftar User Paling Aktif yang diurutkan berdasarkan jumlah transaksi, total berat sampah, dan poin, dengan opsi untuk mengunduh data dalam format Export CSV. 

2.4.	 Kelola Event 
 
Fitur Kelola Event & Banner di Admin Panel memungkinkan Administrator untuk membuat, mengelola, dan menyiarkan informasi mengenai acara-acara komunitas kepada pengguna. Administrator dapat membuat acara baru melalui tombol + Buat Event atau menyebarkan pengumuman kepada semua pengguna melalui Broadcast. Tampilan saat ini menunjukkan tiga acara yang akan datang: Kompetisi Eco-Warrior di Taman Kota Surabaya (02 Jan 2025), Workshop Daur Ulang Kreatif di Balai Kota Bandung (24 Dec 2025), dan Aksi Bersih Pantai Jakarta di Pantai Ancol (17 Dec 2025), lengkap dengan detail tanggal, waktu, lokasi, dan tombol untuk mengedit atau menghapus acara tersebut.

 
Fitur Edit Event di Admin Panel, yang diakses dari menu tombol pencil, memungkinkan Administrator untuk memodifikasi detail acara yang sudah ada, seperti "Kompetisi Eco-Warrior". Administrator dapat mengubah Judul Event, Deskripsi ("Jadilah Eco-Warrior dengan mengumpulkan sampah terbanyak! Hadiah menarik menanti pemenang"), Tanggal (misalnya, menjadi 02/01/2026), Waktu (08:00), dan Lokasi (Taman Kota Surabaya atau lokasi baru). Selain itu, tersedia opsi untuk mengunggah Poster Baru dan mengaktifkan atau menonaktifkan status Event Aktif, sebelum menyimpan perubahan pada acara tersebut.
 
Fitur Broadcast ke Semua User adalah alat komunikasi satu arah pada Admin Panel yang memungkinkan Administrator mengirimkan pesan massal kepada seluruh pengguna terdaftar dengan menekan tombol broadcast. Administrator dapat memilih Tipe pesan, seperti Notifikasi, dan mengisi formulir dengan Judul dan Pesan yang ingin disampaikan. Setelah konten pesan siap, Administrator menggunakan tombol Kirim Broadcast untuk mendistribusikan informasi, pengumuman, atau pembaruan penting secara serentak ke semua pengguna aplikasi.
2.5.	 Moderasi Forum
 
Fitur Moderasi Forum di Admin Panel berfungsi sebagai pusat pengawasan dan pengelolaan konten yang dibuat oleh pengguna di Forum Komunitas. Administrator dapat meninjau semua Postingan Forum yang ada, seperti postingan dari Andi Wijaya, Siti Aminah, dan Budi Santoso, lengkap dengan Judul, jumlah Komentar, dan Tanggal posting. Fitur ini menyediakan Aksi moderasi yang krusial, di mana Administrator dapat View (melihat detail postingan), Delete (menghapus postingan yang melanggar), Block (memblokir pengguna dari forum), atau Unblock pengguna. Selain itu, terdapat kolom User Ditolak dan Panduan Moderasi untuk memastikan implementasi kebijakan forum yang konsisten.  
 
Fitur Detail Postingan di Admin Panel adalah halaman yang memberikan Administrator tampilan lengkap dari sebuah post forum, dalam hal ini postingan "Cara Mengurangi Sampah Plastik" oleh Andi Wijaya. Halaman ini menampilkan konten penuh post, dua komentar yang ada (dari Dewi Lestari dan Budi Santoso), dan panel Info Postingan yang menyajikan metadata penting seperti Post ID (#9), User ID (#4), Tanggal Dibuat, jumlah Komentar (2), dan Status postingan (Aktif). Selain itu, terdapat tombol Hapus Post dan ikon tempat sampah di setiap komentar, yang memungkinkan Administrator untuk melakukan moderasi konten secara mendalam dan spesifik. 

2.6.	 Kelola Lokasi Cabang 
 

Fitur Kelola Lokasi Cabang adalah alat administratif esensial yang berfungsi sebagai database utama untuk mengelola informasi drop point atau bank sampah yang terdaftar, memastikan data yang tersedia bagi pengguna selalu akurat dan terkini. Administrator dapat mengakses daftar lengkap lokasi dengan detail penting seperti nama, alamat, dan informasi kontak, serta memiliki serangkaian kemampuan pengelolaan data. Kemampuan ini mencakup penambahan lokasi baru, pengeditan detail lokasi yang sudah ada (seperti mengubah jam operasional atau jenis sampah yang diterima), dan penghapusan lokasi yang sudah tidak aktif. Secara keseluruhan, fitur ini sangat krusial karena mendukung fungsionalitas utama aplikasi dalam memfasilitasi penukaran sampah, memastikan bahwa pengguna dapat dengan efisien menemukan tempat yang tepat dan andal untuk menyetor sampah daur ulang mereka. 

 

Fitur Edit Lokasi pada Admin Panel berfungsi untuk memodifikasi informasi detail dari drop point atau bank sampah yang sudah terdaftar, seperti "Bank Sampah (RT 002 RW 01)". Administrator dapat memperbarui data krusial, termasuk Nama Lokasi dan Kota, detail alamat lengkap, Telepon, Jam Operasional, dan tautan Link Google Maps. Selain itu, fitur ini memungkinkan Admin untuk mengoreksi atau memperjelas Jenis Sampah yang Diterima (misalnya, hanya Botol plastik) dan mengelola status Lokasi Aktif atau non-aktif, sebelum menyimpan perubahan tersebut.

Assignment 3 (Challenge): 
-	 Fitur tampilan detail sampah per Lokasi bank sampah: 
 
Tabel "Statistik Penukaran Per Bank Sampah" ini menyajikan ringkasan kinerja dari beberapa lokasi Bank Sampah, merinci data kunci seperti Total Transaksi yang telah terjadi, Total Sampah (Kg) yang berhasil dikumpulkan, Rata-rata Sampah / Transaksi (Kg) yang menggambarkan efisiensi setoran, serta Kategori Terbanyak dari jenis sampah yang disetorkan (seperti Plastik, Elektronik, atau Kaca), sehingga memberikan gambaran komprehensif mengenai aktivitas dan komposisi sampah di setiap Bank Sampah.

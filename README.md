# WordPress + ACF (REST API)

WordPress di localhost memakai XAMPP, dengan plugin ACF (Advanced Custom Fields).
Data kustom tersedia lewat REST API WordPress pada kunci `acf`.

## Menjalankan
1. Pasang XAMPP, jalankan Apache dan MySQL.
2. Buat database `wordpress_db` di http://localhost/phpmyadmin
3. Salin folder ini ke C:\xampp\htdocs\wordpress
4. Salin `wp-config-sample.php` menjadi `wp-config.php`, isi data database.
5. Buka http://localhost/wordpress dan selesaikan instalasi.
6. Settings > Permalinks > Post name > Save.

## ACF
1. Plugin ACF ada di `wp-content/plugins/`; aktifkan di menu Plugins.
2. ACF > Tools > Import Field Groups, pilih `acf-export/acf-export.json`.

## Endpoint
- GET /wp-json/wp/v2/posts?_fields=id,title,acf

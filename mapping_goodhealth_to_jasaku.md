**GoodHealth → jasaku_api mapping**

This file lists the discovered GoodHealth PHP endpoints and recommended router mappings for `jasaku_api`.

Summary of mappings

- `login.php` (POST)  
  - suggested: `api.php?resource=auth&action=login` (POST)
  - body: `{ "username": "...", "password": "...", "idPasien": "..." }`

- `pasien/create.php` (POST)  
  - suggested: `api.php?resource=users&action=create_pasien` (POST)  
  - body: `{ "nama": "...", "hp": "...", "email": "..." }`

- `pesan_obat/index.php` (GET)  
  - suggested: `api.php?resource=orders&action=list&pasien_id=...&is_selesai=...`  
  - returns list `[{ id_pesan_obat, waktu, total_biaya, ... }]`

- `pesan_obat/create.php` (POST)  
  - suggested: `api.php?resource=orders&action=create` (POST)  
  - body: `{ "id_pasien":..., "alamat":..., "list_pesanan":..., "total_biaya":... }`

- `pesan_obat/delete.php?id=...` (GET)  
  - suggested: `api.php?resource=orders&action=delete&id=...`  

- `regis_poli/index.php?id_pasien=...` (GET)  
  - suggested: `api.php?resource=registrations&action=list&pasien_id=...`

- `regis_poli/create.php` (POST)  
  - suggested: `api.php?resource=registrations&action=create`  
  - body: `{ "id_pasien":..., "id_dokter":..., "tgl_booking":..., "poli":... }`

Notes and migration guidance

- GoodHealth uses `http://10.0.2.2/goodhealth/...` (Android emulator host). For desktop apps running on the same machine as XAMPP, use `http://localhost/jasaku_api/api/api.php?...`.
- Some GoodHealth endpoints use GET for delete/update actions; it's safer to accept POST on the router. If you add bridge PHP files, have them accept GET and translate to router POST when necessary.
- If your `jasaku_api` handlers use different resource names, replace `orders`/`registrations` with the actual handler names in `jasaku_api/api/handlers/`.

Next steps (I can perform):
- Create bridge PHP files for each GoodHealth endpoint that forward requests to `api.php` (quick compatibility).  
- OR update the GoodHealth Flutter client to call the router URLs directly (recommended long-term).  

Which do you want me to do next? Reply with `bridges` or `client` or `both` to proceed.

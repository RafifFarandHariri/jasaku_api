Server example: `api/user/profile.php`

Purpose
- Example PHP endpoint that returns a user's profile by `email` in JSON.
- Matches the Flutter client expectation `{ "success": true, "data": { ... } }`.

Install
1. Copy `api_user_profile.php` into your webroot, for example:
   - Windows/XAMPP: `C:\xampp\htdocs\jasakuapp\api\user\profile.php`
   - Linux: `/var/www/html/jasakuapp/api/user/profile.php`
2. Edit DB config variables at top of the file (`$dbHost`, `$dbUser`, `$dbPass`, `$dbName`).
3. Ensure your `users` table has columns used in the SELECT query (id, nrp, nama, email, phone, profile_image, role, is_verified_provider, provider_since, provider_description).
4. If you host behind a firewall, allow access to port 80.

Security & notes
- This example allows CORS from anywhere (`Access-Control-Allow-Origin: *`) for debugging. Restrict it in production.
- Use HTTPS in production and validate inputs more strictly.
- Prefer using `id` or an authenticated session token instead of raw `email` to identify users.

Testing
- From host machine (where XAMPP runs):
  - `curl -X POST -H "Content-Type: application/json" -d '{"email":"user@example.com"}' http://localhost/jasakuapp/api/user/profile.php`
- From Android emulator (maps `10.0.2.2` to host):
  - `curl -X POST -H "Content-Type: application/json" -d '{"email":"user@example.com"}' http://10.0.2.2/jasakuapp/api/user/profile.php`

Client integration
- The Flutter client already calls `api/user/profile.php` as a fallback when login returns no `data`. After deploying this file and ensuring the DB/config are correct, the Flutter app should be able to fetch and display full profile information.

If you want, I can also:
- Provide a sample SQL `CREATE TABLE` and `INSERT` statement to seed a test user.
- Update the Flutter client to call this profile endpoint using `user id` instead of `email`.



# Jasaku API

Backend API untuk aplikasi Jasaku (Flutter).

## Aplikasi Frontend
Frontend aplikasi tersedia di: [jasaku](https://github.com/RafifFarandHariri/jasaku)

## Teknologi
- Node.js/Express.js
- Database: [sesuaikan]

## API Endpoints
[daftar endpoint]

## Menjalankan dengan Frontend
1. Clone repo frontend: `git clone https://github.com/RafifFarandHariri/jasaku`
2. Setup environment sesuai kebutuhan

<img width="1454" height="879" alt="Screenshot 2025-12-05 140210" src="https://github.com/user-attachments/assets/81829f00-9735-4372-b988-4fd402bc3dcc" />




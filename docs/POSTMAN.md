How to use the Postman tests

- Import `docs/postman_collection.json` into Postman.
- Import `docs/postman_environment.json` as an environment and select it.
- Ensure backend runs on `http://localhost:8000` or update the `base_url` environment value.

Run order
- Auth → Register Student
- Auth → Login Student
- Auth → Me (Student)
- Auth → Logout (Student)
- Negative Cases (optional): run to validate errors.
- Admin:
- Run DB seed once to create default super admin.
- Credentials: `admin@example.com` / `AdminPass123!`.
- In the Admin folder, run "Set Admin Defaults" then "Login Admin".

Notes
- The collection sets `auth_token` automatically after Register/Login.
- Email and passport use a timestamp to avoid duplicates.
 - Super admin is created by database seeder. Run: `php artisan migrate --seed`.

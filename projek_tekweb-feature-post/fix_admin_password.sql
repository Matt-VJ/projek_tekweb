-- Fix admin password to 'admin123'
UPDATE users SET password = '$2y$10$RzLonyU77epy7fiwpnFBfu/Rq0lgxZdob.MuYPfRlkZCCAma3YHPm' WHERE username = 'admin';

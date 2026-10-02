-- Patch 02 Oktober 2026: Nonaktifkan / Hide Menu Asisten AI (Bot WA) untuk semua role
-- Mandat Dany Triyatna (02 Okt 2026)

-- 1. Unpublish modul ID 66 (Asisten AI)
UPDATE sec_modul 
SET publish = 0 
WHERE id = 66;

-- 2. Matikan hak akses view/CRUD di seluruh role
UPDATE sec_role_priv 
SET allow_view = 0, 
    allow_new = 0, 
    allow_edit = 0, 
    allow_delete = 0, 
    allow_print = 0, 
    allow_approve = 0 
WHERE module_id = 66;

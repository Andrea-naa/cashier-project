-- Trigger: Audit logging untuk INSERT, UPDATE, dan DELETE pada tabel users

-- Trigger untuk INSERT
DROP TRIGGER IF EXISTS trg_users_after_insert;
DELIMITER $$
CREATE TRIGGER trg_users_after_insert
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (user_id, username, action, ip_address, timestamp)
    VALUES (NEW.id, NEW.username, CONCAT('Akun dibuat: ', NEW.username, ' (Role: ', NEW.role, ')'), NULL, NOW());
END$$
DELIMITER ;

-- Trigger untuk UPDATE
DROP TRIGGER IF EXISTS trg_users_after_update;
DELIMITER $$
CREATE TRIGGER trg_users_after_update
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    DECLARE changed_fields VARCHAR(255) DEFAULT '';
    
    IF OLD.username <> NEW.username THEN
        SET changed_fields = CONCAT(changed_fields, 'Username: ', OLD.username, ' → ', NEW.username, '; ');
    END IF;
    
    IF OLD.nama_lengkap <> NEW.nama_lengkap THEN
        SET changed_fields = CONCAT(changed_fields, 'Nama: ', OLD.nama_lengkap, ' → ', NEW.nama_lengkap, '; ');
    END IF;
    
    IF OLD.role <> NEW.role THEN
        SET changed_fields = CONCAT(changed_fields, 'Role: ', OLD.role, ' → ', NEW.role, '; ');
    END IF;
    
    IF changed_fields <> '' THEN
        INSERT INTO audit_log (user_id, username, action, ip_address, timestamp)
        VALUES (NEW.id, NEW.username, CONCAT('Akun diupdate: ', changed_fields), NULL, NOW());
    END IF;
END$$
DELIMITER ;

-- Trigger untuk DELETE
DROP TRIGGER IF EXISTS trg_users_after_delete;
DELIMITER $$
CREATE TRIGGER trg_users_after_delete
AFTER DELETE ON users
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (user_id, username, action, ip_address, timestamp)
    VALUES (OLD.id, OLD.username, CONCAT('Akun dihapus: ', OLD.username, ' (Role: ', OLD.role, ')'), NULL, NOW());
END$$
DELIMITER ;

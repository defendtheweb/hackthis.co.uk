-- This SQL creates the triggers to maintain data integrity.
USE hackthis; -- Make sure we are in the correct database.

delimiter |
CREATE TRIGGER delete_user BEFORE DELETE ON users FOR EACH ROW
	BEGIN
		DELETE FROM users_profile WHERE OLD.user_id = user_id;		
		-- Add other tables to be removed.
	END;
|
delimiter ; 

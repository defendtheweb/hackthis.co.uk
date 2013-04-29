-- This SQL creates the triggers to maintain data integrity.
USE hackthis; -- Make sure we are in the correct database.

delimiter |
DROP TRIGGER IF EXISTS delete_user;
CREATE TRIGGER delete_user BEFORE DELETE ON users FOR EACH ROW
	BEGIN
		DELETE FROM users_profile WHERE OLD.user_id = user_id;		
		-- Add other tables to be removed.
	END;


DROP PROCEDURE IF EXISTS award_medal;
CREATE PROCEDURE `award_medal` (IN _user_id INT, IN _medal_id INT)
COMMENT 'A procedure'  
BEGIN  
	DECLARE REWARD INT;
	SET REWARD = (SELECT medals_colours.reward FROM `medals` INNER JOIN `medals_colours` on medals.colour_id = medals_colours.colour_id WHERE medals.medal_id = _medal_id LIMIT 1);

	SELECT REWARD;
	UPDATE users SET score = score + REWARD WHERE user_id = _user_id LIMIT 1;
END;

DROP TRIGGER IF EXISTS insert_medal;
CREATE TRIGGER insert_medal AFTER INSERT ON users_medals FOR EACH ROW
	BEGIN
		CALL award_medal(NEW.user_id, NEW.medal_id);
	END;
|
delimiter ;
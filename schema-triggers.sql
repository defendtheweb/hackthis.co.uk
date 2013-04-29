-- This SQL creates the triggers to maintain data integrity.
USE hackthis; -- Make sure we are in the correct database.

delimiter |
DROP TRIGGER IF EXISTS delete_user;
CREATE TRIGGER delete_user BEFORE DELETE ON users FOR EACH ROW
	BEGIN
		DELETE FROM users_profile WHERE OLD.user_id = user_id;		
		-- Add other tables to be removed.
	END;
|

-- ARTICLES
-- TODO: Pull the user id and a comment made to the new version.

DROP TRIGGER IF EXISTS articales_draft_update_audit;
CREATE TRIGGER articales_draft_update_audit BEFORE UPDATE ON articles_draft FOR EACH ROW
	BEGIN
		IF OLD.body <> NEW.body THEN
			INSERT INTO articles_audit (article_id, draft, field, old_value, new_value) 
				VALUES(NEW.article_id, 1, 'body', OLD.body, NEW.body);
		END IF;

		-- TODO: Get the string version of cat id. 
		IF OLD.category_id <> NEW.category_id THEN
			INSERT INTO articles_audit (article_id, draft, field, old_value, new_value) 
				VALUES(NEW.article_id, 1, 'category_id', OLD.category_id, NEW.category_id);
		END IF;

		IF OLD.title <> NEW.title THEN
			INSERT INTO articles_audit (article_id, draft, field, old_value, new_value) 
				VALUES(NEW.article_id, 1, 'title', OLD.title, NEW.title);
		END IF;
	END;
|
delimiter ; 

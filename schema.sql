CREATE DATABASE hackthis;
USE hackthis;

/*
	USERS
*/
CREATE TABLE users (
	`user_id` int(7) NOT NULL AUTO_INCREMENT,
	`username` varchar(16) NOT NULL,
	`password` varchar(64),
	`oauth_id` int(7),
	`email` varchar(128) NOT NULL,
	`score` mediumint(6) NOT NULL DEFAULT 0,
	`status` tinyint(1) NOT NULL DEFAULT 1,
	PRIMARY KEY (`user_id`),
	UNIQUE KEY (`username`),
	UNIQUE KEY (`email`)
) ENGINE=InnoDB;

CREATE TABLE users_oauth (
	id int(7) AUTO_INCREMENT,
	uid varchar(128),
	provider enum('facebook','twitter'),
	PRIMARY KEY (`id`),
	UNIQUE (`uid`, `provider`)
) ENGINE=InnoDB;

CREATE TABLE users_levels (
	`user_id` int(7) NOT NULL,
	`level_id` int(3) NOT NULL,
	`time` timestamp DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`user_id`),
	FOREIGN KEY (`user_id`) REFERENCES users (`user_id`)
	-- FOREIGN KEY (`level_id`) REFERENCES levels (`level_id`)
) ENGINE=InnoDB;

CREATE TABLE users_profile (
	`user_id` int(7) NOT NULL,
	`name` varchar(32),
	`img` varchar(32),
	`gravatar` tinyint(1) DEFAULT 0,
	`country` tinyint(3) UNSIGNED,
	`dob` DATE,
	`show_dob` tinyint(1),
	`gender` enum('male','female'),
	`show_email` tinyint(1),
	`website` varchar(256),
	`about` text,
	`lastfm` varchar(16),
	`forum_signature` text,
	PRIMARY KEY (`user_id`),
	FOREIGN KEY (`user_id`) REFERENCES users (`user_id`)
) ENGINE=InnoDB;

/*
 * Assigns users different privileges for site sections
 * Default of 1 indicates accesses, 0 being no access
 * Values above 1 indicated extended privileges
 */
CREATE TABLE users_priv (
	`user_id` int(7) NOT NULL,
	`site_priv` tinyint(1) NOT NULL DEFAULT 1,
	`pm_priv` tinyint(1) NOT NULL DEFAULT 1,
	`forum_priv` tinyint(1) NOT NULL DEFAULT 1,
	`pub_priv` tinyint(1) NOT NULL DEFAULT 1,
	PRIMARY KEY (`user_id`),
	FOREIGN KEY (`user_id`) REFERENCES users (`user_id`)
) ENGINE=InnoDB;

/*
 * Stores the relationship between different users
 * status shows if the relationship has been accepted
 * or denied by friend_id
 */
CREATE TABLE users_friends (
	`user_id` int(7) NOT NULL,
	`friend_id` int(7) NOT NULL,
	`status` tinyint(1) NOT NULL DEFAULT 0,
	`time` timestamp DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`user_id`, `friend_id`),
	FOREIGN KEY (`user_id`) REFERENCES users (`user_id`),
	FOREIGN KEY (`friend_id`) REFERENCES users (`user_id`)
) ENGINE=InnoDB;

/*
 * Remove the ability for blocked_id from contacting
 * user_id via any communication channel
 */
CREATE TABLE users_blocks (
	`user_id` int(7) NOT NULL,
	`blocked_id` int(7) NOT NULL,
	`time` timestamp DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`user_id`, `blocked_id`),
	FOREIGN KEY (`user_id`) REFERENCES users (`user_id`),
	FOREIGN KEY (`blocked_id`) REFERENCES users (`user_id`)
) ENGINE=InnoDB;

CREATE TABLE users_activity (
	`user_id` int(7) NOT NULL,
	`joined` timestamp DEFAULT CURRENT_TIMESTAMP,
	`last_active` timestamp,
	`last_login` timestamp,
	`current_login` timestamp,
	`login_count` int(5) DEFAULT 0,
	`consecutive` int(4) DEFAULT 0,
	`consecutive_most` int(4) DEFAULT 0,
	PRIMARY KEY (`user_id`),
	FOREIGN KEY (`user_id`) REFERENCES users (`user_id`)
) ENGINE=InnoDB;

/*
 * Notification types:
 * see https://github.com/HackThis/hackthis.co.uk/wiki/Database#notification-types
 */
CREATE TABLE users_notifications (
	`notification_id` int(6) NOT NULL AUTO_INCREMENT,
	`user_id` int(7) NOT NULL,
	`type` enum('friend','friend_accepted', 'medal','forum_reply','forum_mention','comment_reply','comment_mention','article') NOT NULL,
	`from_id` int(7),
	`item_id` int(7),
	`time` timestamp DEFAULT CURRENT_TIMESTAMP,
	`seen` tinyint(1) DEFAULT 0,
	PRIMARY KEY (`notification_id`),
	FOREIGN KEY (`user_id`) REFERENCES users (`user_id`)
) ENGINE=InnoDB;

/*
 * Feed types:
 * see https://github.com/HackThis/hackthis.co.uk/wiki/Database#feed-types
 */
CREATE TABLE users_feed (
	`feed_id` int(6) NOT NULL AUTO_INCREMENT,
	`user_id` int(7) NOT NULL,
	`type` enum('join','level','friend','medal','thread','post','post_mention','karma','comment','comment_mention','favourite','article','image') NOT NULL,
	`item_id` int(7),
	`time` timestamp DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`feed_id`),
	FOREIGN KEY (`user_id`) REFERENCES users (`user_id`)
) ENGINE=InnoDB;

/*
	MEDALS
*/
CREATE TABLE medals_colours (
	`colour_id` tinyint(1) NOT NULL AUTO_INCREMENT,
	`reward` int(4) NOT NULL DEFAULT 0,
	`colour` varchar(6) NOT NULL,
	PRIMARY KEY (`colour_id`)
) ENGINE=InnoDB;

CREATE TABLE medals (
	`medal_id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT,
	`label` varchar(16) NOT NULL,
	`colour_id` tinyint(1) NOT NULL,
	`description` text NOT NULL,
	PRIMARY KEY (`medal_id`),
	FOREIGN KEY (`colour_id`) REFERENCES medals_colours (`colour_id`)
) ENGINE=InnoDB;

CREATE TABLE users_medals (
	`user_id` int(7) NOT NULL,
	`medal_id` tinyint(3) UNSIGNED NOT NULL,
	`time` timestamp DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`user_id`, `medal_id`),
	FOREIGN KEY (`user_id`) REFERENCES users (`user_id`),
	FOREIGN KEY (`medal_id`) REFERENCES medals (`medal_id`)
) ENGINE=InnoDB;


/*
	MESSAGES
*/
CREATE TABLE pm (
	`pm_id` int(7) NOT NULL AUTO_INCREMENT,
	`title` varchar(64) NOT NULL,
	PRIMARY KEY (`pm_id`)
) ENGINE=InnoDB;

CREATE TABLE pm_messages (
	`message_id` int(7) NOT NULL AUTO_INCREMENT,
	`pm_id` int(7) NOT NULL,
	`user_id` int(7),
	`message` text NOT NULL,
	`time` timestamp DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`message_id`),
	FOREIGN KEY (`pm_id`) REFERENCES pm (`pm_id`),
	FOREIGN KEY (`user_id`) REFERENCES users (`user_id`)
) ENGINE=InnoDB;

CREATE TABLE pm_users (
	`pm_id` int(7) NOT NULL,
	`user_id` int(7) NOT NULL,
	`seen` timestamp NULL,
	PRIMARY KEY (`pm_id`, `user_id`),
	FOREIGN KEY (`pm_id`) REFERENCES pm (`pm_id`),
	FOREIGN KEY (`user_id`) REFERENCES users (`user_id`)
) ENGINE=InnoDB;


/*
	ARTICLES
*/
CREATE TABLE articles_categories (
	`category_id` int(3) NOT NULL AUTO_INCREMENT,
	`parent_id` int(3),
	`title` varchar(64),
	`slug` varchar(64),
	PRIMARY KEY (`category_id`),
	UNIQUE (`slug`)
) ENGINE=InnoDB;

-- TODO: Timestamps man TIME!!
CREATE TABLE articles (
	`article_id` int(6) NOT NULL AUTO_INCREMENT,
	`user_id` int(7),
	`title` varchar(128) NOT NULL,
	`slug` varchar(64) NOT NULL,
	`category_id` int(3) NOT NULL,
	`body` TEXT  NOT NULL,
	`thumbnail` varchar(16), 
	`submitted` timestamp DEFAULT CURRENT_TIMESTAMP,
	`updated` timestamp,
	`featured` int(1),
	`views` int(5) DEFAULT 0,
	PRIMARY KEY (`article_id`),
    FOREIGN KEY (`user_id`) REFERENCES users (`user_id`),
    FOREIGN KEY (`category_id`) REFERENCES articles_categories (`category_id`)
) ENGINE=InnoDB;

CREATE TABLE articles_draft (
	`article_id` int(6) NOT NULL AUTO_INCREMENT,
	`user_id` int(7) NOT NULL,
	`title` varchar(128) NOT NULL,
	`category_id` int(3) NOT NULL,
	`body` TEXT NOT NULL,
	`time` timestamp DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`article_id`),
	FOREIGN KEY (`user_id`) REFERENCES users (`user_id`),
	FOREIGN KEY (`category_id`) REFERENCES articles_categories (`category_id`) 
) ENGINE=InnoDB;

CREATE TABLE articles_audit (
	`audit_id` int(7) NOT NULL AUTO_INCREMENT,
	`article_id` int(6) NOT NULL, 
	`draft` tinyint(1) NOT NULL,
	`field` varchar(32) NOT NULL,
	`old_value` TEXT NOT NULL,
	`new_value` TEXT NOT NULL,
	`time` timestamp DEFAULT CURRENT_TIMESTAMP,
	`user_id` int(7) NOT NULL,
	`comment` TEXT NULL,
	PRIMARY KEY (`audit_id`,`article_id`,`draft`,`field`)-- ,
	-- FOREIGN KEY (`user_id`) REFERENCES users (`user_id`) -- TODO: Provide the ability to get the user id from within the trigger.
) ENGINE=InnoDB;

CREATE TABLE articles_comments (
	`comment_id` int(6) NOT NULL AUTO_INCREMENT,
	`article_id` int(6) NOT NULL,
	`user_id` int(7),
	`parent_id` int(6) NOT NULL DEFAULT 0,
	`comment` text NOT NULL,
	`reported` tinyint(1), -- Number of times this comment has been reported
	`deleted` int(7),
	`time` timestamp DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`comment_id`),
	FOREIGN KEY (`article_id`) REFERENCES articles (`article_id`),
	FOREIGN KEY (`user_id`) REFERENCES users (`user_id`)
) ENGINE=InnoDB;

CREATE TABLE articles_favourites (
	`article_id` int(6) NOT NULL,
	`user_id` int(7) NOT NULL,
	`time` timestamp DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`article_id`, `user_id`),
	FOREIGN KEY (`article_id`) REFERENCES articles (`article_id`),
	FOREIGN KEY (`user_id`) REFERENCES users (`user_id`)
) ENGINE=InnoDB;


/*
	MODERATOR TABLES
*/
CREATE TABLE reports (
	`report_id` int(6) NOT NULL AUTO_INCREMENT,
	`user_id` int(7) NOT NULL,
	`type` enum('comment', 'article', 'user', 'forum', 'level') NOT NULL,
	`about` int(7) NOT NULL,
	`subject` varchar(64),
	`body` text,
	PRIMARY KEY (`report_id`),
	FOREIGN KEY (`user_id`) REFERENCES users (`user_id`)
) ENGINE=InnoDB;



/*
	TRIGGERS
*/
delimiter |
-- USERS
DROP TRIGGER IF EXISTS insert_user;
CREATE TRIGGER insert_user AFTER INSERT ON users FOR EACH ROW
	BEGIN
		INSERT INTO users_activity (`user_id`) VALUES (NEW.user_id);
		CALL user_feed(NEW.user_id, 'join', NULL);
	END;

DROP TRIGGER IF EXISTS delete_user;
CREATE TRIGGER delete_user BEFORE DELETE ON users FOR EACH ROW
	BEGIN
		DELETE FROM users_profile WHERE OLD.user_id = user_id;
		DELETE FROM users_priv WHERE OLD.user_id = user_id;
		DELETE FROM users_friends WHERE OLD.user_id = user_id OR OLD.user_id = friend_id;
		DELETE FROM users_blocks WHERE OLD.user_id = user_id OR OLD.user_id = blocked_id;
		DELETE FROM users_activity WHERE OLD.user_id = user_id;
		DELETE FROM users_notifications WHERE OLD.user_id = user_id;
		DELETE FROM users_medals WHERE OLD.user_id = user_id;
		DELETE FROM users_feed WHERE OLD.user_id = user_id;
		DELETE FROM articles_favourites WHERE OLD.user_id = user_id;
		DELETE FROM articles_draft WHERE OLD.user_id = user_id;
		DELETE FROM pm_users WHERE OLD.user_id = user_id;
		-- Add other tables to be removed.

		-- Update other contributions to NULL so they aren't lost
		UPDATE articles_comments SET user_id = NULL WHERE user_id = OLD.user_id;
		UPDATE articles SET user_id = NULL WHERE user_id = OLD.user_id;
		UPDATE pm_messages SET user_id = NULL WHERE user_id = OLD.user_id;
	END;

-- NOTIFICATIONS
DROP PROCEDURE IF EXISTS user_notify;
CREATE PROCEDURE user_notify(user_id INT, type TEXT, from_id INT, item_id INT)
  BEGIN
  	INSERT INTO users_notifications (`user_id`, `type`, `from_id`, `item_id`) VALUES (user_id, type, from_id, item_id);
  END;

DROP PROCEDURE IF EXISTS user_notify_remove;
CREATE PROCEDURE user_notify_remove(_user_id INT, _type TEXT, _from_id INT, _item_id INT)
  BEGIN
  	IF _item_id IS NULL THEN
  		DELETE FROM users_notifications WHERE `user_id` = _user_id AND `type` = _type AND `from_id` = _from_id LIMIT 1;
  	ELSE
  		DELETE FROM users_notifications WHERE `user_id` = _user_id AND `type` = _type AND `from_id` = _from_id AND `item_id` = _item_id LIMIT 1;
  	END IF;
  END;

DROP PROCEDURE IF EXISTS user_feed;
CREATE PROCEDURE user_feed(user_id INT, type TEXT, item_id INT)
  BEGIN
  	INSERT INTO users_feed (`user_id`, `type`, `item_id`) VALUES (user_id, type, item_id);
  END;

DROP PROCEDURE IF EXISTS user_feed_remove;
CREATE PROCEDURE user_feed_remove(_user_id INT, _type TEXT, _item_id INT)
  BEGIN
  	DELETE FROM users_feed WHERE `user_id` = _user_id AND `type` = _type AND `item_id` = _item_id LIMIT 1;
  END;

-- When a user completes a level and an item is added to users_levels
-- Give user the relevant score and add to users feed
DROP TRIGGER IF EXISTS insert_user_level;
CREATE TRIGGER insert_user_level AFTER INSERT ON users_levels FOR EACH ROW
	BEGIN
		CALL user_feed(NEW.user_id, 'level', NEW.level_id);
	END;

DROP TRIGGER IF EXISTS delete_user_level;
CREATE TRIGGER delete_user_level AFTER DELETE ON users_levels FOR EACH ROW
	BEGIN
		CALL user_feed_remove(OLD.user_id, 'level', OLD.level_id);
	END;

DROP TRIGGER IF EXISTS insert_friend_before;
CREATE TRIGGER insert_friend_before BEFORE INSERT ON users_friends FOR EACH ROW
    BEGIN
        declare alreadyexists integer;
        SELECT count(*) > 0 into alreadyexists FROM users_friends
            WHERE user_id = NEW.friend_id AND friend_id = NEW.user_id;

        IF alreadyexists = 1 THEN
            SELECT `erroorororororor` INTO alreadyexists FROM users_friends;
        END IF;
    END;

DROP TRIGGER IF EXISTS insert_friend;
CREATE TRIGGER insert_friend AFTER INSERT ON users_friends FOR EACH ROW
	BEGIN
		CALL user_notify(NEW.friend_id, 'friend', NEW.user_id, null);
	END;

DROP TRIGGER IF EXISTS update_friend;
CREATE TRIGGER update_friend AFTER UPDATE ON users_friends FOR EACH ROW
	BEGIN
		IF NEW.status = 1 THEN
			-- Alert user who sent request
			CALL user_notify(NEW.user_id, 'friend_accepted', NEW.friend_id, null);

			-- Add to both users feeds
			CALL user_feed(NEW.user_id, 'friend', NEW.friend_id);
			CALL user_feed(NEW.friend_id, 'friend', NEW.user_id);
		END IF;
	END;

DROP TRIGGER IF EXISTS delete_friend;
CREATE TRIGGER delete_friend AFTER DELETE ON users_friends FOR EACH ROW
	BEGIN
		CALL user_notify_remove(OLD.friend_id, 'friend', OLD.user_id, null);
		CALL user_notify_remove(OLD.user_id, 'friend_accepted', OLD.friend_id, null);
		CALL user_feed_remove(OLD.user_id, 'friend', OLD.friend_id);
		CALL user_feed_remove(OLD.friend_id, 'friend', OLD.user_id);
	END;

-- MEDALS
DROP TRIGGER IF EXISTS insert_medal;
CREATE TRIGGER insert_medal AFTER INSERT ON users_medals FOR EACH ROW
	BEGIN
		DECLARE REWARD INT;
		SET REWARD = (SELECT medals_colours.reward FROM `medals` INNER JOIN `medals_colours` on medals.colour_id = medals_colours.colour_id WHERE medals.medal_id = NEW.medal_id LIMIT 1);

		UPDATE users SET score = score + REWARD WHERE user_id = NEW.user_id LIMIT 1;

		CALL user_notify(NEW.user_id, 'medal', null, NEW.medal_id);
		CALL user_feed(NEW.user_id, 'medal', NEW.medal_id);
	END;

DROP TRIGGER IF EXISTS delete_medal;
CREATE TRIGGER delete_medal AFTER DELETE ON users_medals FOR EACH ROW
	BEGIN
		DECLARE REWARD INT;
		DECLARE _e INT;
		DECLARE CONTINUE HANDLER FOR 1442 SET _e = 1;

		SET REWARD = (SELECT medals_colours.reward FROM `medals` INNER JOIN `medals_colours` on medals.colour_id = medals_colours.colour_id WHERE medals.medal_id = OLD.medal_id LIMIT 1);

		UPDATE users SET score = score - REWARD WHERE user_id = OLD.user_id LIMIT 1;

		CALL user_feed_remove(OLD.user_id, 'medal', OLD.medal_id);
	END;

-- ARTICLES
DROP TRIGGER IF EXISTS insert_article;
CREATE TRIGGER insert_article AFTER INSERT ON articles FOR EACH ROW
	BEGIN
		CALL user_notify(NEW.user_id, 'article', null, NEW.article_id);
		CALL user_feed(NEW.user_id, 'article', NEW.article_id);
	END;

-- TODO: Pull the user id and a comment made to the new version.
DROP TRIGGER IF EXISTS articles_draft_update_audit;
CREATE TRIGGER articles_draft_update_audit BEFORE UPDATE ON articles_draft FOR EACH ROW
	BEGIN
		IF OLD.body <> NEW.body THEN
			INSERT INTO articles_audit (article_id, draft, field, old_value, new_value) 
				VALUES(NEW.article_id, 1, 'body', OLD.body, NEW.body);
		END IF;

		IF OLD.category_id <> NEW.category_id THEN
			-- Get the string title of the category from the cat id.
			SET @old_cat = (SELECT title FROM articles_categories WHERE category_id = OLD.category_id);
			SET @new_cat = (SELECT title FROM articles_categories WHERE category_id = NEW.category_id);

			INSERT INTO articles_audit (article_id, draft, field, old_value, new_value) 
				VALUES(NEW.article_id, 1, 'category_id', @old_cat, @new_cat);
		END IF;

		IF OLD.title <> NEW.title THEN
			INSERT INTO articles_audit (article_id, draft, field, old_value, new_value) 
				VALUES(NEW.article_id, 1, 'title', OLD.title, NEW.title);
		END IF;
	END;

DROP TRIGGER IF EXISTS articles_update_audit;
CREATE TRIGGER articles_update_audit BEFORE UPDATE ON articles FOR EACH ROW
	BEGIN
		IF OLD.title <> NEW.title THEN
			INSERT INTO articles_audit (article_id, draft, field, old_value, new_value) 
				VALUES(NEW.article_id, 0, 'title', OLD.title, NEW.title);
		END IF;

		IF OLD.slug <> NEW.slug THEN
			INSERT INTO articles_audit (article_id, draft, field, old_value, new_value) 
				VALUES(NEW.article_id, 0, 'slug', OLD.slug, NEW.slug);
		END IF;

		IF OLD.category_id <> NEW.category_id THEN	
			-- Get the string title of the category from the cat id.
			SET @old_cat = (SELECT title FROM articles_categories WHERE category_id = OLD.category_id);
			SET @new_cat = (SELECT title FROM articles_categories WHERE category_id = NEW.category_id);

			INSERT INTO articles_audit (article_id, draft, field, old_value, new_value) 
				VALUES(NEW.article_id, 0, 'category_id', @old_cat, @new_cat);
		END IF;

		IF OLD.body <> NEW.body THEN
			INSERT INTO articles_audit (article_id, draft, field, old_value, new_value) 
				VALUES(NEW.article_id, 0, 'body', OLD.body, NEW.body);
		END IF;

		IF OLD.thumbnail <> NEW.thumbnail THEN
			INSERT INTO articles_audit (article_id, draft, field, old_value, new_value) 
				VALUES(NEW.article_id, 0, 'thumbnail', OLD.thumbnail, NEW.thumbnail);
		END IF;

		IF OLD.featured <> NEW.featured THEN
			INSERT INTO articles_audit (article_id, draft, field, old_value, new_value) 
				VALUES(NEW.article_id, 0, 'featured', OLD.featured, NEW.featured);
		END IF;

	END;

DROP TRIGGER IF EXISTS insert_article_categories;
CREATE TRIGGER insert_article_categories BEFORE INSERT ON articles_categories FOR EACH ROW
	BEGIN
		IF NEW.parent_id IS NOT NULL THEN
			SET NEW.slug = CONCAT_WS('/', (SELECT `slug` FROM articles_categories WHERE category_id = NEW.parent_id), NEW.slug);
		END IF;
	END;

DROP TRIGGER IF EXISTS insert_article_comment;
CREATE TRIGGER insert_article_comment AFTER INSERT ON articles_comments FOR EACH ROW
	BEGIN
		CALL user_feed(NEW.user_id, 'comment', NEW.comment_id);
	END;

DROP TRIGGER IF EXISTS delete_article_comment;
CREATE TRIGGER delete_article_comment AFTER UPDATE ON articles_comments FOR EACH ROW
	BEGIN
		IF NEW.deleted IS NOT NULL THEN
			CALL user_feed_remove(OLD.user_id, 'comment', OLD.comment_id);
		END IF;
	END;

DROP TRIGGER IF EXISTS insert_article_favourites;
CREATE TRIGGER insert_article_favourites AFTER INSERT ON articles_favourites FOR EACH ROW
	BEGIN
		CALL user_feed(NEW.user_id, 'favourite', NEW.article_id);
	END;

DROP TRIGGER IF EXISTS delete_article_favourites;
CREATE TRIGGER delete_article_favourites AFTER DELETE ON articles_favourites FOR EACH ROW
	BEGIN
		CALL user_feed_remove(OLD.user_id, 'favourite', OLD.article_id);
	END;

|
delimiter ;

CREATE DATABASE hackthis;
USE hackthis;

/*
    USERS
*/
CREATE TABLE IF NOT EXISTS users (
    `user_id` int(7) NOT NULL AUTO_INCREMENT,
    `username` varchar(32) NOT NULL,
    `password` varchar(64),
    `old_password` tinyint(1) NOT NULL DEFAULT 0,
    `oauth_id` int(7),
    `email` varchar(128) NOT NULL,
    `verified` tinyint(1) NOT NULL DEFAULT 0,
    `score` mediumint(6) NOT NULL DEFAULT 0,
    `g_auth` tinyint(1),
    `g_secret` varchar(255),
    PRIMARY KEY (`user_id`),
    UNIQUE KEY (`username`),
    UNIQUE KEY (`email`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users_oauth (
    id int(7) AUTO_INCREMENT,
    uid varchar(128),
    provider enum('facebook','twitter'),
    PRIMARY KEY (`id`),
    UNIQUE (`uid`, `provider`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users_levels (
    `user_id` int(7) NOT NULL,
    `level_id` tinyint(3) UNSIGNED NOT NULL,
    `started` timestamp DEFAULT CURRENT_TIMESTAMP,
    `completed` timestamp,
    `attempts` smallint(3) UNSIGNED DEFAULT 0,
    PRIMARY KEY (`user_id`, `level_id`),
    FOREIGN KEY (`user_id`) REFERENCES users (`user_id`)
    -- Level constraint added later
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users_levels_data (
    `user_id` int(7) NOT NULL,
    `level_id` tinyint(3) UNSIGNED NOT NULL,
    `data` text,
    `time` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`, `level_id`),
    FOREIGN KEY (`user_id`) REFERENCES users (`user_id`)
    -- Level constraint added later
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users_profile (
    `user_id` int(7) NOT NULL,
    `name` varchar(32),
    `show_name` tinyint(1) DEFAULT 1,
    `img` varchar(32),
    `gravatar` tinyint(1) DEFAULT 0,
    `country` tinyint(3) UNSIGNED,
    `dob` DATE,
    `show_dob` tinyint(1) DEFAULT 0,
    `gender` enum('male','female','alien'),
    `show_gender` tinyint(1) DEFAULT 0,
    `show_email` tinyint(1) DEFAULT 0,
    `website` text,
    `about` text,
    `forum_signature` text,
    `show_online` tinyint(1) DEFAULT 1,
    `show_leaderboard` tinyint(1) DEFAULT 1,
    PRIMARY KEY (`user_id`),
    FOREIGN KEY (`user_id`) REFERENCES users (`user_id`),
    INDEX (`show_online`),
    INDEX (`show_leaderboard`)
) ENGINE=InnoDB;

/*
 * Assigns users different privileges for site sections
 * Default of 1 indicates accesses, 0 being no access
 * Values above 1 indicated extended privileges
 */
CREATE TABLE IF NOT EXISTS users_priv (
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
CREATE TABLE IF NOT EXISTS users_friends (
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
CREATE TABLE IF NOT EXISTS users_blocks (
    `user_id` int(7) NOT NULL,
    `blocked_id` int(7) NOT NULL,
    `time` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`, `blocked_id`),
    FOREIGN KEY (`user_id`) REFERENCES users (`user_id`),
    FOREIGN KEY (`blocked_id`) REFERENCES users (`user_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users_activity (
    `user_id` int(7) NOT NULL,
    `joined` timestamp DEFAULT CURRENT_TIMESTAMP,
    `last_active` timestamp,
    `last_login` timestamp,
    `current_login` timestamp,
    `login_count` int(5) DEFAULT 0,
    `consecutive` int(4) DEFAULT 0,
    `consecutive_most` int(4) DEFAULT 0,
    `days` int(5) DEFAULT 0,
    PRIMARY KEY (`user_id`),
    FOREIGN KEY (`user_id`) REFERENCES users (`user_id`)
) ENGINE=InnoDB;

/*
 * Notification types:
 * see https://github.com/HackThis/hackthis.co.uk/wiki/Database#notification-types
 */
CREATE TABLE IF NOT EXISTS users_notifications (
    `notification_id` int(6) NOT NULL AUTO_INCREMENT,
    `user_id` int(7) NOT NULL,
    `type` enum('friend','friend_accepted', 'medal','forum_post','forum_mention','comment_reply','comment_mention','article', 'mod_contact', 'mod_report') NOT NULL,
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
CREATE TABLE IF NOT EXISTS users_feed (
    `feed_id` int(6) NOT NULL AUTO_INCREMENT,
    `user_id` int(7) NOT NULL,
    `type` enum('join','level','friend','medal','thread','forum_post','karma','comment','favourite','article','image') NOT NULL,
    `item_id` int(7),
    `time` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`feed_id`),
    FOREIGN KEY (`user_id`) REFERENCES users (`user_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users_data (
    `data_id` int(4)  NOT NULL AUTO_INCREMENT,
    `user_id` int(7) NOT NULL,
    `type` varchar(32) NOT NULL,
    `value` varchar(64) NOT NULL,
    `extra` text,
    `time` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`data_id`),
    FOREIGN KEY (`user_id`) REFERENCES users (`user_id`),
    UNIQUE (`type`, `value`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `users_donations` (
  `user_id` int(7) DEFAULT NULL,
  `amount` decimal(6,2) DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id` text NOT NULL,
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users_settings (
    `user_id` int(7) NOT NULL,
    `email_pm` tinyint(1) NOT NULL DEFAULT 1,
    `email_forum_reply` tinyint(1) NOT NULL DEFAULT 1,
    `email_forum_mention` tinyint(1) NOT NULL DEFAULT 1,
    `email_friend` tinyint(1) NOT NULL DEFAULT 1,
    `email_news` tinyint(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`user_id`),
    FOREIGN KEY (`user_id`) REFERENCES users (`user_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users_registration (
    `user_id` int(7) NOT NULL,
    `ip` bigint(12) NOT NULL,
    `time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`)
) ENGINE=InnoDB;


/*
    MEDALS
*/
CREATE TABLE IF NOT EXISTS medals_colours (
    `colour_id` tinyint(1) NOT NULL AUTO_INCREMENT,
    `reward` int(4) NOT NULL DEFAULT 0,
    `colour` varchar(6) NOT NULL,
    PRIMARY KEY (`colour_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS medals (
    `medal_id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT,
    `label` varchar(16) NOT NULL,
    `colour_id` tinyint(1) NOT NULL,
    `description` text NOT NULL,
    PRIMARY KEY (`medal_id`),
    FOREIGN KEY (`colour_id`) REFERENCES medals_colours (`colour_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users_medals (
    `user_id` int(7) NOT NULL,
    `medal_id` tinyint(3) UNSIGNED NOT NULL,
    `time` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`, `medal_id`),
    FOREIGN KEY (`user_id`) REFERENCES users (`user_id`),
    FOREIGN KEY (`medal_id`) REFERENCES medals (`medal_id`)
) ENGINE=InnoDB;


/*
    LEVELS
*/
CREATE TABLE IF NOT EXISTS levels_groups (
    `title` varchar(16),
    `order` tinyint(1),
    PRIMARY KEY (`title`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS levels (
    `level_id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` varchar(8) NOT NULL,
    `group` varchar(16) NOT NULL,
    PRIMARY KEY (`level_id`),
    UNIQUE (`name`, `group`),
    FOREIGN KEY (`group`) REFERENCES levels_groups (`title`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS levels_data (
    `level_id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT,
    `key` enum('author', 'reward', 'form', 'answer', 'articles', 'hint', 'description', 'solution', 'code', 'uptime') NOT NULL,
    `value` text NOT NULL,
    PRIMARY KEY (`level_id`, `key`),
    FOREIGN KEY (`level_id`) REFERENCES levels (`level_id`)
) ENGINE=InnoDB;

ALTER TABLE users_levels
ADD FOREIGN KEY (`level_id`) REFERENCES levels (`level_id`);
ALTER TABLE users_levels_data
ADD FOREIGN KEY (`level_id`) REFERENCES levels (`level_id`);

/*
    MESSAGES
*/
CREATE TABLE IF NOT EXISTS pm (
    `pm_id` int(7) NOT NULL AUTO_INCREMENT,
    `title` varchar(64) NOT NULL,
    PRIMARY KEY (`pm_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pm_messages (
    `message_id` int(7) NOT NULL AUTO_INCREMENT,
    `pm_id` int(7) NOT NULL,
    `user_id` int(7),
    `message` text NOT NULL,
    `time` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`message_id`),
    FOREIGN KEY (`pm_id`) REFERENCES pm (`pm_id`),
    FOREIGN KEY (`user_id`) REFERENCES users (`user_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pm_users (
    `pm_id` int(7) NOT NULL,
    `user_id` int(7) NOT NULL,
    `seen` timestamp NULL DEFAULT NULL,
    `deleted` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`pm_id`, `user_id`),
    FOREIGN KEY (`pm_id`) REFERENCES pm (`pm_id`),
    FOREIGN KEY (`user_id`) REFERENCES users (`user_id`)
) ENGINE=InnoDB;


/*
    FORUM
*/
CREATE TABLE IF NOT EXISTS forum_sections (
    `section_id` int(3) NOT NULL AUTO_INCREMENT,
    `parent_id` int(3),
    `title` varchar(32),
    `slug` varchar(255),
    `description` text,
    `priv_level` tinyint(3) UNSIGNED NULL DEFAULT NULL,
    PRIMARY KEY (`section_id`),
    UNIQUE (`slug`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS forum_threads (
    `thread_id` int(6) NOT NULL AUTO_INCREMENT,
    `section_id` int(3) NOT NULL,
    `title` varchar(128) NOT NULL,
    `slug` varchar(255),
    `owner` int(7), 
    `deleted` tinyint(1) DEFAULT 0,
    `closed` tinyint(1) DEFAULT 0,
    `sticky` tinyint(1) DEFAULT 0,
    PRIMARY KEY (`thread_id`),
    UNIQUE (`slug`),
    FOREIGN KEY (`owner`) REFERENCES users (`user_id`),
    FOREIGN KEY (`section_id`) REFERENCES forum_sections (`section_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS forum_posts (
    `post_id` int(6) NOT NULL AUTO_INCREMENT,
    `thread_id` int(6) NOT NULL,
    `body` TEXT  NOT NULL,
    `author` int(7), 
    `posted` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated` timestamp,
    `deleted` tinyint(1) DEFAULT 0,
    PRIMARY KEY (`post_id`),
    FOREIGN KEY (`author`) REFERENCES users (`user_id`),
    FOREIGN KEY (`thread_id`) REFERENCES forum_threads (`thread_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS forum_posts_audit (
    `audit_id` int(7) NOT NULL AUTO_INCREMENT,
    `post_id` int(6) NOT NULL,
    `field` varchar(32) NOT NULL,
    `old_value` TEXT NOT NULL,
    `new_value` TEXT NOT NULL,
    `time` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`audit_id`,`post_id`,`field`)-- ,
    -- FOREIGN KEY (`user_id`) REFERENCES users (`user_id`) -- TODO: Provide the ability to get the user id from within the trigger.
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS forum_posts_flags (
    `flag_id` int(6) NOT NULL AUTO_INCREMENT,
    `post_id` int(6) NOT NULL,
    `user_id` int(7), 
    `reason` tinyint(1) NOT NULL,
    `details` TEXT,
    `time` timestamp DEFAULT CURRENT_TIMESTAMP,
    `response` tinyint(1) DEFAULT 0,
    PRIMARY KEY (`flag_id`),
    FOREIGN KEY (`user_id`) REFERENCES users (`user_id`),
    FOREIGN KEY (`post_id`) REFERENCES forum_posts (`post_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS forum_users (
    `user_id` int(7) NOT NULL,
    `thread_id` int(6) NOT NULL,
    `viewed` timestamp DEFAULT CURRENT_TIMESTAMP,
    `watching` tinyint(1) DEFAULT 0,
    PRIMARY KEY (`user_id`, `thread_id`),
    FOREIGN KEY (`user_id`) REFERENCES users (`user_id`),
    FOREIGN KEY (`thread_id`) REFERENCES forum_threads (`thread_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users_forum (
    `user_id` int(7) NOT NULL,
    `post_id` int(6) NOT NULL,
    `karma` tinyint(1) DEFAULT 0,
    `time` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`, `post_id`),
    FOREIGN KEY (`user_id`) REFERENCES users (`user_id`),
    FOREIGN KEY (`post_id`) REFERENCES forum_posts (`post_id`)
) ENGINE=InnoDB;


/*
    ARTICLES
*/
CREATE TABLE IF NOT EXISTS articles_categories (
    `category_id` int(3) NOT NULL AUTO_INCREMENT,
    `parent_id` int(3),
    `title` varchar(32),
    `slug` varchar(255),
    PRIMARY KEY (`category_id`),
    UNIQUE (`slug`)
) ENGINE=InnoDB;

-- TODO: Timestamps man TIME!!
CREATE TABLE IF NOT EXISTS articles (
    `article_id` int(6) NOT NULL AUTO_INCREMENT,
    `user_id` int(7),
    `title` varchar(128) NOT NULL,
    `slug` varchar(255) NOT NULL,
    `category_id` int(3) NOT NULL,
    `body` TEXT  NOT NULL,
    `thumbnail` varchar(64), 
    `submitted` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated` timestamp,
    `views` int(5) DEFAULT 0,
    PRIMARY KEY (`article_id`),
    UNIQUE (`slug`),
    FOREIGN KEY (`user_id`) REFERENCES users (`user_id`),
    FOREIGN KEY (`category_id`) REFERENCES articles_categories (`category_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS articles_draft (
    `article_id` int(6) NOT NULL AUTO_INCREMENT,
    `user_id` int(7) NOT NULL,
    `title` varchar(128) NOT NULL,
    `category_id` int(3) NOT NULL,
    `body` TEXT NOT NULL,
    `time` timestamp DEFAULT CURRENT_TIMESTAMP,
    `note` TEXT,
    PRIMARY KEY (`article_id`),
    FOREIGN KEY (`user_id`) REFERENCES users (`user_id`),
    FOREIGN KEY (`category_id`) REFERENCES articles_categories (`category_id`) 
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS articles_audit (
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

CREATE TABLE IF NOT EXISTS articles_comments (
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

CREATE TABLE IF NOT EXISTS articles_favourites (
    `article_id` int(6) NOT NULL,
    `user_id` int(7) NOT NULL,
    `time` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`article_id`, `user_id`),
    FOREIGN KEY (`article_id`) REFERENCES articles (`article_id`),
    FOREIGN KEY (`user_id`) REFERENCES users (`user_id`)
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS home_ticker (
    `id` int(6) NOT NULL AUTO_INCREMENT,
    `source` varchar(32) NOT NULL,
    `text` varchar(256) NOT NULL,
    `url` text NOT NULL,
    `user_id` int(7) NOT NULL,
    `status` tinyint(1) DEFAULT 0,
    `time` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES users (`user_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS home_ticker_votes (
    `ticker_id` int(6) NOT NULL,
    `user_id` int(7) NOT NULL,
    PRIMARY KEY (`ticker_id`, `user_id`),
    FOREIGN KEY (`ticker_id`) REFERENCES home_ticker (`id`),
    FOREIGN KEY (`user_id`) REFERENCES users (`user_id`)
) ENGINE=InnoDB;


/*
    MODERATOR TABLES
*/
CREATE TABLE IF NOT EXISTS mod_reports (
    `report_id` int(6) NOT NULL AUTO_INCREMENT,
    `user_id` int(7) NOT NULL,
    `type` enum('comment', 'article', 'user', 'forum', 'forum_thread', 'level') NOT NULL,
    `about` int(7) NOT NULL,
    `subject` varchar(64),
    `body` text,
    `visible` tinyint(1) DEFAULT 1, 
    `time` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`report_id`),
    FOREIGN KEY (`user_id`) REFERENCES users (`user_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS mod_contact (
    `message_id` int(6) NOT NULL AUTO_INCREMENT,
    `parent_id` int(6),
    `user_id` int(7) NOT NULL,
    `from` varchar(128),
    `body` text,
    `javascript` tinyint(1) DEFAULT NULL,
    `browser` varchar(32) DEFAULT NULL,
    `sent` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`message_id`)
) ENGINE=InnoDB;

/*
    EMAIL TABLES
*/
CREATE TABLE IF NOT EXISTS email_queue (
    `email_id` int(6) NOT NULL AUTO_INCREMENT,
    `user_id` int(7) DEFAULT NULL,
    `recipient` varchar(128) NOT NULL,
    `type` enum('password', 'ticket_reply', 'forum_mention', 'forum_reply', 'friend', 'pm', 'email_confirmation', 'digest') NOT NULL,
    `data` text NOT NULL,
    `sent` timestamp DEFAULT CURRENT_TIMESTAMP,
    `status` tinyint(1) DEFAULT 0, -- 0 waiting, 1 sending, 2 sent, 3+ error (error * 3mins = wait)
    PRIMARY KEY (`email_id`),
    FOREIGN KEY (`user_id`) REFERENCES users (`user_id`)
) ENGINE=InnoDB;


/*
    API
*/
CREATE TABLE IF NOT EXISTS `api_clients` (
  `client_id` int(3) NOT NULL AUTO_INCREMENT,
  `user_id` int(7) DEFAULT NULL,
  `identifier` text NOT NULL,
  `domain` text NOT NULL,
  `key` varchar(64) NOT NULL,
  `privileges` text NOT NULL,
  PRIMARY KEY (`client_id`),
  UNIQUE KEY `secret_key` (`key`)
) ENGINE=InnoDB;

/*
    IRC LOGS
*/
CREATE TABLE IF NOT EXISTS `irc_stats` (
    `nick` varchar(64) NOT NULL,
    `lines` int(5) NOT NULL,
    `words` int(8) NOT NULL,
    `chars` int(10) NOT NULL,
    `time` timestamp DEFAULT CURRENT_TIMESTAMP,
    `username` varchar(35) NOT NULL,
    PRIMARY KEY (`nick`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `irc_logs` (
    `log_id` int(11) NOT NULL AUTO_INCREMENT,
    `nick` varchar(64) NOT NULL,
    `channel` varchar(25) NOT NULL,
    `log` text NOT NULL,
    `time` timestamp DEFAULT CURRENT_TIMESTAMP,
    `removed` int(1) NOT NULL,
    PRIMARY KEY (`log_id`)
) ENGINE=MyISAM;


/*
    TRIGGERS
*/
delimiter $$
-- USERS
DROP TRIGGER IF EXISTS insert_user$$
CREATE TRIGGER insert_user AFTER INSERT ON users FOR EACH ROW
    BEGIN
        INSERT INTO users_activity (`user_id`) VALUES (NEW.user_id);
        CALL user_feed(NEW.user_id, 'join', NULL);
    END$$

DROP TRIGGER IF EXISTS update_user$$
CREATE TRIGGER update_user BEFORE UPDATE ON users FOR EACH ROW
    BEGIN
        IF OLD.email <> NEW.email THEN
            SET NEW.verified = 0;
        END IF;
    END$$

DROP TRIGGER IF EXISTS delete_user$$
CREATE TRIGGER delete_user BEFORE DELETE ON users FOR EACH ROW
    BEGIN
        DELETE FROM users_oauth WHERE OLD.oauth_id = id;
        DELETE FROM users_profile WHERE OLD.user_id = user_id;
        DELETE FROM users_priv WHERE OLD.user_id = user_id;
        DELETE FROM users_friends WHERE OLD.user_id = user_id OR OLD.user_id = friend_id;
        DELETE FROM users_blocks WHERE OLD.user_id = user_id OR OLD.user_id = blocked_id;
        DELETE FROM users_activity WHERE OLD.user_id = user_id;
        DELETE FROM users_notifications WHERE OLD.user_id = user_id OR OLD.user_id = from_id;
        DELETE FROM users_medals WHERE OLD.user_id = user_id;
        DELETE FROM users_levels WHERE OLD.user_id = user_id;
        DELETE FROM users_levels_data WHERE OLD.user_id = user_id;
        DELETE FROM users_data WHERE OLD.user_id = user_id;
        DELETE FROM mod_reports WHERE OLD.user_id = user_id;
        DELETE FROM articles_favourites WHERE OLD.user_id = user_id;
        DELETE FROM articles_draft WHERE OLD.user_id = user_id;
        DELETE FROM users_forum WHERE OLD.user_id = user_id;
        DELETE FROM forum_users WHERE OLD.user_id = user_id;
        DELETE FROM pm_users WHERE OLD.user_id = user_id;
        DELETE FROM users_feed WHERE OLD.user_id = user_id;
        DELETE FROM users_settings WHERE OLD.user_id = user_id;
        DELETE FROM home_ticker WHERE OLD.user_id = user_id;
        DELETE FROM email_queue WHERE OLD.user_id = user_id;
        -- Add other tables to be removed.

        -- Update other contributions to NULL so they aren't lost
        UPDATE users_donations SET user_id = NULL WHERE user_id = OLD.user_id;
        UPDATE articles_comments SET user_id = NULL WHERE user_id = OLD.user_id;
        UPDATE articles SET user_id = NULL WHERE user_id = OLD.user_id;
        UPDATE forum_posts SET author = NULL WHERE author = OLD.user_id;
        UPDATE forum_threads SET owner = NULL WHERE owner = OLD.user_id;
        UPDATE pm_messages SET user_id = NULL WHERE user_id = OLD.user_id;
    END$$

-- NOTIFICATIONS
DROP PROCEDURE IF EXISTS user_notify$$
CREATE PROCEDURE user_notify(user_id INT, type TEXT, from_id INT, item_id INT)
  BEGIN
    INSERT INTO users_notifications (`user_id`, `type`, `from_id`, `item_id`) VALUES (user_id, type, from_id, item_id);
  END$$

DROP PROCEDURE IF EXISTS user_notify_remove$$
CREATE PROCEDURE user_notify_remove(_user_id INT, _type TEXT, _from_id INT, _item_id INT)
  BEGIN
    IF _item_id IS NULL THEN
        DELETE FROM users_notifications WHERE `user_id` = _user_id AND `type` = _type AND `from_id` = _from_id LIMIT 1;
    ELSE
        DELETE FROM users_notifications WHERE `user_id` = _user_id AND `type` = _type AND `from_id` = _from_id AND `item_id` = _item_id LIMIT 1;
    END IF;
  END$$

DROP PROCEDURE IF EXISTS user_feed$$
CREATE PROCEDURE user_feed(user_id INT, type TEXT, item_id INT)
  BEGIN
    INSERT INTO users_feed (`user_id`, `type`, `item_id`) VALUES (user_id, type, item_id);
  END$$

DROP PROCEDURE IF EXISTS user_feed_remove$$
CREATE PROCEDURE user_feed_remove(_user_id INT, _type TEXT, _item_id INT)
  BEGIN
    DELETE FROM users_feed WHERE `user_id` = _user_id AND `type` = _type AND `item_id` = _item_id LIMIT 1;
  END$$

-- When a user completes a level and an item is added to users_levels
-- Give user the relevant score and add to users feed
DROP TRIGGER IF EXISTS insert_user_level$$
CREATE TRIGGER insert_user_level AFTER INSERT ON users_levels FOR EACH ROW
    BEGIN
        DECLARE REWARD INT;
        IF NEW.completed > 0 THEN
            CALL user_feed(NEW.user_id, 'level', NEW.level_id);

            SET REWARD = (SELECT `value` FROM `levels_data` WHERE level_id = NEW.level_id AND `key` = 'reward' LIMIT 1);
            UPDATE users SET score = score + REWARD WHERE user_id = NEW.user_id LIMIT 1;
        END IF;
    END$$

DROP TRIGGER IF EXISTS update_user_level$$
CREATE TRIGGER update_user_level AFTER UPDATE ON users_levels FOR EACH ROW
    BEGIN
        DECLARE REWARD INT;
        IF NEW.completed > 0 THEN
            CALL user_feed(NEW.user_id, 'level', NEW.level_id);

            SET REWARD = (SELECT `value` FROM `levels_data` WHERE level_id = NEW.level_id AND `key` = 'reward' LIMIT 1);
            UPDATE users SET score = score + REWARD WHERE user_id = NEW.user_id LIMIT 1;
        END IF;
    END$$

DROP TRIGGER IF EXISTS delete_user_level$$
CREATE TRIGGER delete_user_level AFTER DELETE ON users_levels FOR EACH ROW
    BEGIN
        DECLARE REWARD INT;
        DECLARE _e INT;
        DECLARE CONTINUE HANDLER FOR 1442 SET _e = 1;
        SET REWARD = (SELECT `value` FROM `levels_data` WHERE level_id = OLD.level_id AND `key` = 'reward' LIMIT 1);
        UPDATE users SET score = score - REWARD WHERE user_id = OLD.user_id LIMIT 1;

        CALL user_feed_remove(OLD.user_id, 'level', OLD.level_id);
    END$$


-- Update users scores when the reward given for a level is altered
DROP TRIGGER IF EXISTS update_levels_data$$
CREATE TRIGGER update_levels_data AFTER UPDATE ON levels_data FOR EACH ROW
    BEGIN
        IF NEW.key = 'reward' THEN
            UPDATE users JOIN users_levels ON users.user_id = users_levels.user_id SET users.score = users.score - OLD.value + NEW.value WHERE users_levels.level_id = NEW.level_id AND users_levels.completed > 0;
        END IF;
    END$$


DROP TRIGGER IF EXISTS insert_friend_before$$
CREATE TRIGGER insert_friend_before BEFORE INSERT ON users_friends FOR EACH ROW
    BEGIN
        declare alreadyexists integer;
        SELECT count(*) > 0 into alreadyexists FROM users_friends
            WHERE user_id = NEW.friend_id AND friend_id = NEW.user_id;

        IF alreadyexists = 1 THEN
            SELECT `erroorororororor` INTO alreadyexists FROM users_friends;
        END IF;
    END$$

DROP TRIGGER IF EXISTS insert_friend$$
CREATE TRIGGER insert_friend AFTER INSERT ON users_friends FOR EACH ROW
    BEGIN
        CALL user_notify(NEW.friend_id, 'friend', NEW.user_id, null);
    END$$

DROP TRIGGER IF EXISTS update_friend$$
CREATE TRIGGER update_friend AFTER UPDATE ON users_friends FOR EACH ROW
    BEGIN
        IF NEW.status = 1 THEN
            -- Alert user who sent request
            CALL user_notify(NEW.user_id, 'friend_accepted', NEW.friend_id, null);

            -- Add to both users feeds
            CALL user_feed(NEW.user_id, 'friend', NEW.friend_id);
            CALL user_feed(NEW.friend_id, 'friend', NEW.user_id);
        END IF;
    END$$

DROP TRIGGER IF EXISTS delete_friend$$
CREATE TRIGGER delete_friend AFTER DELETE ON users_friends FOR EACH ROW
    BEGIN
        CALL user_notify_remove(OLD.friend_id, 'friend', OLD.user_id, null);
        CALL user_notify_remove(OLD.user_id, 'friend_accepted', OLD.friend_id, null);
        CALL user_feed_remove(OLD.user_id, 'friend', OLD.friend_id);
        CALL user_feed_remove(OLD.friend_id, 'friend', OLD.user_id);
    END$$

-- MEDALS
DROP TRIGGER IF EXISTS insert_medal$$
CREATE TRIGGER insert_medal AFTER INSERT ON users_medals FOR EACH ROW
    BEGIN
        DECLARE REWARD INT;
        SET REWARD = (SELECT medals_colours.reward FROM `medals` INNER JOIN `medals_colours` on medals.colour_id = medals_colours.colour_id WHERE medals.medal_id = NEW.medal_id LIMIT 1);

        UPDATE users SET score = score + REWARD WHERE user_id = NEW.user_id LIMIT 1;

        CALL user_notify(NEW.user_id, 'medal', null, NEW.medal_id);
        CALL user_feed(NEW.user_id, 'medal', NEW.medal_id);
    END$$

DROP TRIGGER IF EXISTS delete_medal$$
CREATE TRIGGER delete_medal AFTER DELETE ON users_medals FOR EACH ROW
    BEGIN
        DECLARE REWARD INT;
        DECLARE _e INT;
        DECLARE CONTINUE HANDLER FOR 1442 SET _e = 1;

        SET REWARD = (SELECT medals_colours.reward FROM `medals` INNER JOIN `medals_colours` on medals.colour_id = medals_colours.colour_id WHERE medals.medal_id = OLD.medal_id LIMIT 1);

        UPDATE users SET score = score - REWARD WHERE user_id = OLD.user_id LIMIT 1;

        CALL user_feed_remove(OLD.user_id, 'medal', OLD.medal_id);
    END$$

DROP TRIGGER IF EXISTS update_medal_reward$$
create trigger update_medal_reward after update on medals_colours
    for each row 
    begin
    DECLARE done INT DEFAULT FALSE;
    DECLARE colour_count int;
    DECLARE user_id_fetch int;
    DECLARE reward INT;
    DECLARE reward_recal int ;
    DECLARE cur CURSOR FOR 
    select 
    um.user_id, count(*) as total
    from users_medals um
    join medals m on m.medal_id = um.medal_id 
    where m.colour_id = new.colour_id
    group by um.user_id;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    SET reward = OLD.reward - NEW.reward;
    OPEN cur;
        update_loop: LOOP
            FETCH cur INTO user_id_fetch,colour_count;
            IF done THEN
                LEAVE update_loop;
            END IF;
        set reward_recal = colour_count*reward ;
            update users 
        SET score = score - reward_recal
        where user_id = user_id_fetch ; 
        END LOOP;
    CLOSE cur;
    end ; $$

-- FORUM
DROP TRIGGER IF EXISTS insert_forum_post$$
CREATE TRIGGER insert_forum_post AFTER INSERT ON forum_posts FOR EACH ROW
    BEGIN
        CALL user_feed(NEW.author, 'forum_post', NEW.post_id);
    END$$

DROP TRIGGER IF EXISTS delete_forum_post$$
CREATE TRIGGER delete_forum_post BEFORE DELETE ON forum_posts FOR EACH ROW
    BEGIN
        DELETE FROM users_forum WHERE post_id = OLD.post_id;
    END$$

DROP TRIGGER IF EXISTS forum_posts_update_audit$$
CREATE TRIGGER forum_posts_update_audit BEFORE UPDATE ON forum_posts FOR EACH ROW
    BEGIN
        IF OLD.body <> NEW.body THEN
            INSERT INTO forum_posts_audit (post_id, field, old_value, new_value) 
                VALUES(NEW.post_id, 'body', OLD.body, NEW.body);
        END IF;
    END$$

DROP TRIGGER IF EXISTS delete_forum_thread$$
CREATE TRIGGER delete_forum_thread BEFORE DELETE ON forum_threads FOR EACH ROW
    BEGIN
        DELETE FROM forum_posts WHERE thread_id = OLD.thread_id;
        DELETE FROM forum_users WHERE thread_id = OLD.thread_id;
    END$$

DROP TRIGGER IF EXISTS update_forum_thread$$
CREATE TRIGGER update_forum_thread AFTER UPDATE ON forum_threads FOR EACH ROW
    BEGIN
        IF NEW.deleted = 1 THEN
            UPDATE forum_posts SET deleted = 1 WHERE thread_id = OLD.thread_id;
        END IF;
    END$$


-- ARTICLES
DROP TRIGGER IF EXISTS insert_article$$
CREATE TRIGGER insert_article AFTER INSERT ON articles FOR EACH ROW
    BEGIN
        CALL user_notify(NEW.user_id, 'article', null, NEW.article_id);
        CALL user_feed(NEW.user_id, 'article', NEW.article_id);
    END$$

-- TODO: Pull the user id and a comment made to the new version.
DROP TRIGGER IF EXISTS articles_draft_update_audit$$
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
    END$$

DROP TRIGGER IF EXISTS articles_update_audit$$
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

    END$$

DROP TRIGGER IF EXISTS insert_article_categories$$
CREATE TRIGGER insert_article_categories BEFORE INSERT ON articles_categories FOR EACH ROW
    BEGIN
        IF NEW.parent_id IS NOT NULL THEN
            SET NEW.slug = CONCAT_WS('/', (SELECT `slug` FROM articles_categories WHERE category_id = NEW.parent_id), NEW.slug);
        END IF;
    END$$

DROP TRIGGER IF EXISTS insert_article_comment$$
CREATE TRIGGER insert_article_comment AFTER INSERT ON articles_comments FOR EACH ROW
    BEGIN
        CALL user_feed(NEW.user_id, 'comment', NEW.comment_id);
    END$$

DROP TRIGGER IF EXISTS delete_article_comment$$
CREATE TRIGGER delete_article_comment AFTER UPDATE ON articles_comments FOR EACH ROW
    BEGIN
        IF NEW.deleted IS NOT NULL THEN
            CALL user_feed_remove(OLD.user_id, 'comment', OLD.comment_id);
        END IF;
    END$$

DROP TRIGGER IF EXISTS insert_article_favourites$$
CREATE TRIGGER insert_article_favourites AFTER INSERT ON articles_favourites FOR EACH ROW
    BEGIN
        CALL user_feed(NEW.user_id, 'favourite', NEW.article_id);
    END$$

DROP TRIGGER IF EXISTS delete_article_favourites$$
CREATE TRIGGER delete_article_favourites AFTER DELETE ON articles_favourites FOR EACH ROW
    BEGIN
        CALL user_feed_remove(OLD.user_id, 'favourite', OLD.article_id);
    END$$

-- Ticker
DROP TRIGGER IF EXISTS delete_home_ticker$$
CREATE TRIGGER delete_home_ticker BEFORE DELETE ON home_ticker FOR EACH ROW
    BEGIN
        DELETE FROM home_ticker_votes WHERE ticker_id = OLD.id;
    END$$

$$
delimiter ;

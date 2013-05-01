CREATE DATABASE hackthis;
USE hackthis;

/* USERS */
CREATE TABLE users (
	`user_id` int(7) NOT NULL AUTO_INCREMENT,
	`username` varchar(16) NOT NULL,
	`password` varchar(256) NOT NULL,
	`score` mediumint(6) NOT NULL DEFAULT 0,
	`status` tinyint(1) NOT NULL DEFAULT 1,
	PRIMARY KEY (`user_id`),
	UNIQUE KEY (`username`)
) ENGINE=InnoDB;

CREATE TABLE users_profile (
	`user_id` int(7) NOT NULL,
	`name` varchar(32),
	`joined` timestamp DEFAULT CURRENT_TIMESTAMP,
	`gravatar` tinyint(1) DEFAULT 1,
	`country` tinyint(3) UNSIGNED,
	`dob` DATE,
	`show_dob` tinyint(1),
	`gender` char(1),
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
	`comments_priv` tinyint(1) NOT NULL DEFAULT 1,
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
	`last_active` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`last_login` timestamp,
	`current_login` timestamp,
	`login_count` int(5) DEFAULT 1,
	`consecutive` int(4) DEFAULT 0,
	`consecutive_most` int(4) DEFAULT 0,
	PRIMARY KEY (`user_id`),
	FOREIGN KEY (`user_id`) REFERENCES users (`user_id`)
) ENGINE=InnoDB;

CREATE TABLE users_notifications (
	`notification_id` int(7) NOT NULL AUTO_INCREMENT,
	`user_id` int(7) NOT NULL,
	`type` tinyint(1) NOT NULL,
	`item_id` int(6) NOT NULL,
	`time` timestamp DEFAULT CURRENT_TIMESTAMP,
	`seen` tinyint(1) DEFAULT 0,
	PRIMARY KEY (`notification_id`),
	FOREIGN KEY (`user_id`) REFERENCES users (`user_id`)
) ENGINE=InnoDB;


/* MEDALS */
CREATE TABLE medals_colours (
	`colour_id` tinyint(1) NOT NULL AUTO_INCREMENT,
	`reward` int(4) NOT NULL DEFAULT 0,
	`hex` char(6) NOT NULL,
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
	ARTICLES
*/
CREATE TABLE articles_categories (
	`category_id` int(7) NOT NULL AUTO_INCREMENT,
	`parent_id` int(7) DEFAULT 0,
	`title` varchar(32),
	PRIMARY KEY (`category_id`)
) ENGINE=InnoDB;

-- TODO: Timestamps man TIME!!
CREATE TABLE articles (
	`article_id` int(7) NOT NULL AUTO_INCREMENT,
	`user_id` int(7) NOT NULL,
	`title` varchar(32) NOT NULL,
	`slug` varchar(32) NOT NULL,
	`category_id` int(7) NOT NULL,
	`body` TEXT  NOT NULL,
	`thumbnail` varchar(16), 
	`submitted` timestamp ,
	`updated` timestamp,
	`featured` int(1),
	`views` int(5),
	PRIMARY KEY (`article_id`),
    FOREIGN KEY (`user_id`) REFERENCES users (`user_id`),
    FOREIGN KEY (`category_id`) REFERENCES articles_categories (`category_id`)
) ENGINE=InnoDB;

CREATE TABLE articles_draft (
	`article_id` int(7) NOT NULL AUTO_INCREMENT,
	`user_id` int(7) NOT NULL,
	`title` varchar(32) NOT NULL,
	`category_id` int(7) NOT NULL,
	`body` TEXT NOT NULL,
	`time` timestamp DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`article_id`),
	FOREIGN KEY (`user_id`) REFERENCES users (`user_id`),
	FOREIGN KEY (`category_id`) REFERENCES articles_categories (`category_id`) 
) ENGINE=InnoDB;

CREATE TABLE articles_audit (
	`audit_id` int(7) NOT NULL AUTO_INCREMENT,
	`article_id` int(7) NOT NULL, 
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

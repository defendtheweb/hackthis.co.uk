DROP DATABASE hackthis;

CREATE DATABASE hackthis;
USE hackthis;


CREATE TABLE users (
	`user_id` int(7) NOT NULL AUTO_INCREMENT,
	`username` varchar(125) NOT NULL,
	`password` varchar(125) NOT NULL,
	`score` mediumint(5) NOT NULL DEFAULT 0,
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
	ARTICLES
*/
CREATE TABLE articles_categories (
	`category_id` int(7) NOT NULL AUTO_INCREMENT,
	`parent_id` int(7) DEFAULT 0,
	`title` varchar(32),
	PRIMARY KEY (`category_id`)
) ENGINE=InnoDB;

CREATE TABLE articles (
	`article_id` int(7) NOT NULL AUTO_INCREMENT,
	`user_id` int(7) NOT NULL,
	`title` varchar(32) NOT NULL,
	`slug` varchar(32) NOT NULL,
	`category_id` int(7) NOT NULL,
	`body` TEXT  NOT NULL,
	`thumbnail` varchar(16), 
	`submitted` timestamp DEFAULT CURRENT_TIMESTAMP,
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
	`field` varchar(9) NOT NULL,
	`old_value` TEXT NOT NULL,
	`new_value` TEXT NOT NULL,
	`time` timestamp DEFAULT CURRENT_TIMESTAMP,
	`user_id` int(7) NOT NULL,
	`comment` TEXT NULL,
	PRIMARY KEY (`audit_id`,`article_id`,`draft`,`field`),
	FOREIGN KEY (`user_id`) REFERENCES users (`user_id`)
) ENGINE=InnoDB;

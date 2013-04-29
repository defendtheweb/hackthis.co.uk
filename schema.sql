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
-- Some test data.
USE hackthis;

-- Remove any data.
DELETE FROM users;
DELETE FROM users_profile;
DELETE FROM articles_categories;
DELETE FROM articles_draft;
DELETE FROM articles_audit;

-- Add a new user.
INSERT INTO users (`username`, `password`, `email`) VALUES ('Osaka', '$2a$10$C5Ko0q9jlcTHKsZYvzYJxOD7DsxTsO9RFrihVdY3sGnkflf/e5XKe', 'test@gmail.com');
INSERT INTO users_profile (`user_id`, `name`) VALUES (1, 'Pete');

INSERT INTO users (`username`, `password`, `email`) VALUES ('flabbyrabbit', '$2a$10$C5Ko0q9jlcTHKsZYvzYJxOD7DsxTsO9RFrihVdY3sGnkflf/e5XKe', 'test2@gmail.com');
INSERT INTO users (`username`, `password`, `email`) VALUES ('verath', '$2a$10$C5Ko0q9jlcTHKsZYvzYJxOD7DsxTsO9RFrihVdY3sGnkflf/e5XKe', 'test3@gmail.com');
INSERT INTO users (`username`, `password`, `email`) VALUES ('daMage', '$2a$10$C5Ko0q9jlcTHKsZYvzYJxOD7DsxTsO9RFrihVdY3sGnkflf/e5XKe', 'test4@gmail.com');

-- Give privilages
INSERT INTO users_priv (`user_id`, `pub_priv`) VALUES (2, 2);

-- Medals
INSERT INTO medals_colours (`reward`, `colour`) VALUES (100, 'bronze');
INSERT INTO medals_colours (`reward`, `colour`) VALUES (200, 'silver');
INSERT INTO medals (`label`, `colour_id`, `description`) VALUES ('Test', 1, 'Test');
INSERT INTO medals (`label`, `colour_id`, `description`) VALUES ('Test', 2, 'Test');

-- Award medal
INSERT INTO users_medals (`user_id`, `medal_id`) VALUES (1, 1);
INSERT INTO users_medals (`user_id`, `medal_id`) VALUES (2, 1);
INSERT INTO users_medals (`user_id`, `medal_id`) VALUES (2, 2);

-- Send friend request
INSERT INTO users_friends (`user_id`, `friend_id`) VALUES (1, 2);
INSERT INTO users_friends (`user_id`, `friend_id`) VALUES (3, 2);
INSERT INTO users_friends (`user_id`, `friend_id`) VALUES (2, 4);
UPDATE users_friends SET status = 1 WHERE user_id = '2' AND friend_id = '4';


-- Send pm
INSERT INTO pm (`title`) VALUES ('YO');
INSERT INTO pm_users (`pm_id`, `user_id`) VALUES (1, 1);
INSERT INTO pm_users (`pm_id`, `user_id`) VALUES (1, 2);
INSERT INTO pm_messages (`pm_id`, `user_id`, `message`) VALUES (1, 1, 'Hello young sir');
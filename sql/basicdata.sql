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
INSERT INTO medals_colours (`reward`, `colour`) VALUES (500, 'gold');
INSERT INTO medals_colours (`reward`, `colour`) VALUES (50, 'green');
INSERT INTO `medals` (`medal_id`, `colour_id`, `label`, `description`) VALUES
(1, 1, 'Score', 'Earn 1000 points'),
(2, 2, 'Score', 'Earn 2500 points'),
(3, 3, 'Score', 'Earn 5000 points'),
(4, 1, 'Visits', 'Login everyday for a week'),
(5, 2, 'Visits', 'Login everyday for a fortnight'),
(6, 3, 'Visits', 'Login everyday for a month'),
(7, 2, 'Veteran', 'Member for 1 year'),
(10, 1, 'Alien', 'Become one of the aliens'),
(11, 1, 'Cheese', 'Upload a profile image'),
(12, 1, 'Veteran', 'Member for a month'),
(13, 2, 'Journalist', 'Write a news post and get it published'),
(14, 2, 'Writer', 'Write an article and get it published'),
(15, 4, 'helper', 'Make a considerable effort to help the site'),
(16, 1, 'Forum', 'Post 50 forum messages'),
(17, 2, 'Forum', 'Post 250 forum messages'),
(18, 3, 'Forum', 'Post 1000 forum messages'),
(19, 4, 'Donator', 'Make a donation greater than &pound;5');


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
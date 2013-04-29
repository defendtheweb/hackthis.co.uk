-- Some test data.
USE hackthis;

-- Add a new user.
INSERT INTO users (`user_id`, `username`, `password`) VALUES (-1, 'Osaka', MD5('pass') );
INSERT INTO users_profile (`user_id`, `name`) VALUES (-1, 'Pete');


INSERT INTO medals_colours (`reward`, `hex`) VALUES (100, 'FF9900');
INSERT INTO medals_colours (`reward`, `hex`) VALUES (200, 'FFFF00');
INSERT INTO medals (`label`, `colour_id`, `description`) VALUES ('Test', 1, 'Test');
INSERT INTO medals (`label`, `colour_id`, `description`) VALUES ('Test', 2, 'Test');
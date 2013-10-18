<?php
class DBTest extends PHPUnit_Framework_TestCase {
    public function __construct() {
        try {
            $dsn = "mysql:host=localhost";
            $dsn .= ";dbname=hackthis";
            $this->db = new PDO($dsn, 'ubuntu');
        } catch(PDOException $e) {
            die($e->getMessage());
        }
    }

    public function testInsertUser() {
        $this->db->query("INSERT INTO users (`username`, `password`, `email`) VALUES ('flabbyrabbit', 'pass', 'test@test.com');");
        $this->db->query("INSERT INTO users (`username`, `password`, `email`) VALUES ('osaka', 'pass2', 'test2@test.com');");

        $st = $this->db->query("SELECT count(user_id) AS count FROM users;");
        $row = $st->fetch();
        $this->assertEquals(2, $row['count']);

        // $st = $this->db->query("SELECT password, score FROM users WHERE username = 'flabbyrabbit");
        // $row = $st->fetch();
        // $this->assertEquals('pass', $row['password']);
        // $this->assertEquals(0, $row['score']);
    }

    /**
     * @depends testInsertUser
     */
    public function testMedals() {
        // Medals
        $this->db->query("INSERT INTO medals_colours (`reward`, `colour`) VALUES (100, 'bronze')");
        $this->db->query("INSERT INTO medals_colours (`reward`, `colour`) VALUES (200, 'silver')");
        $this->db->query("INSERT INTO medals (`label`, `colour_id`, `description`) VALUES ('Test', 1, 'Test')");
        $this->db->query("INSERT INTO medals (`label`, `colour_id`, `description`) VALUES ('Test', 2, 'Test')");

        // Award medal
        $this->db->query("INSERT INTO users_medals (`user_id`, `medal_id`) VALUES (1, 1)");
        $this->db->query("INSERT INTO users_medals (`user_id`, `medal_id`) VALUES (2, 1)");
        $this->db->query("INSERT INTO users_medals (`user_id`, `medal_id`) VALUES (2, 2)");

        $res = $this->db->query("INSERT INTO users_medals (`user_id`, `medal_id`) VALUES (2, 2)");
        $this->assertFalse($res);

        // Check user scores
        $st = $this->db->query("SELECT score FROM users WHERE user_id = 1");
        $row = $st->fetch();
        $this->assertEquals(100, $row['score']);

        $st = $this->db->query("SELECT score FROM users WHERE user_id = 2");
        $row = $st->fetch();
        $this->assertEquals(300, $row['score']);

        // Remove medal and check user score
        $this->db->query("DELETE FROM users_medals WHERE `user_id` = 2 AND `medal_id` = 2");

        $st = $this->db->query("SELECT score FROM users WHERE user_id = 2");
        $row = $st->fetch();
        $this->assertEquals(100, $row['score']);
    }
}
?>
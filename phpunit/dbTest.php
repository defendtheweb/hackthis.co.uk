<?php
class DBTest extends PHPUnit_Framework_TestCase {
    public function testUser() {
        try {
            $dsn = "mysql:host=localhost";
            $dsn .= ";dbname=hackthis";
            $db = new PDO($dsn, 'ubuntu');
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die($e->getMessage());
        }

        $st = $db->prepare("INSERT INTO users (`username`, `password`) VALUES ('flabbyrabbit', 'pass');");
        $st->execute();
        $st = $db->prepare("INSERT INTO users (`username`, `password`) VALUES ('osaka', 'pass2');");
        $st->execute();

        $st = $db->prepare("SELECT count(user_id) AS count FROM users");
        $row = $st->fetch();
        print_r($row);
        $this->assertEquals(2, $row['count']);

        $st = $db->prepare("SELECT password, score, status FROM users WHERE username = 'flabbyrabbit'");
        $row = $st->fetch();
        $this->assertEquals('pass', $row['password']);
        $this->assertEquals(0, $row['score']);
        $this->assertEquals(1, $row['status']);
    }
}
?>
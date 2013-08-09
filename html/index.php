<?php
    define("PAGE_PUBLIC", true);

    if ($user->loggedIn)
        $custom_css = array('articles.scss', 'highlight.css');

    require_once('header.php');

    if ($user->loggedIn) {
        require_once("news.php");
    } else {
?>

                    <h1 class='title'>About</h1>
                    <p>
                        Welcome to HackThis!!, this site was set up in 2008 as a safe place for internet users to learn the art of hacking in a controlled environment, teaching the most common flaws in internet security.
                    </p>
                    <section class='row features'>
                        <div class='col span_8'>
                            <h2>Challenges</h2>
                            <i class='icon-flag'></i><br/>
                            Test your skills with 30+ levels,<br/>
                            covering all aspects of security
                        </div>
                        <div class='col span_8'>
                            <h2>Community</h2>
                            <i class='icon-domain2'></i><br/>
                            Join in with 150,000+<br/>like-minded members
                        </div>
                        <div class='col span_8'>
                            <h2>Articles</h2>
                            <i class='icon-insertpictureleft'></i><br/>
                            Learn from our online<br/>collection of articles
                        </div>
                    </section>
                    <p>
                        HackThis.co.uk is presented in the format of a series of fun challenges; the user will be expected to employ their logic and wits, along with some of the better known web development tools,
                        to extract sensitive information from dummy pages. The levels range from very easy to highly perplexing, but help is on hand from staff members and the HackThis forum 24/7.
                        Most of the levels also have tips and spoilers that can be uncovered if you are too overwhelmed by the task at hand.
                    </p>
 <!--                    <p class='center spacer'>
                        <img alt="hackthis hack tshirt" src="/files/images/tshirt.jpg">
                    </p> -->
                    <p>
                        Whilst the majority of internet users invoke the services of the World Wide Web for legitimate reasons, there are many who seek to defraud and cheat their way around our public network using hacking techniques.
                        HackThis.co.uk specialises in the art of security exploitation in an effort to teach users of the Internet how to protect themselves and their websites from these unscrupulous individuals.
                        Our aim is far from malicious. We hope to educate the average web user into being more aware of the security threats around them and make web developers think more carefully about the security of the software they produce.
                    </p>
                    <blockquote>
                        Hackthis, an innovative, educational and entertaining new website where you can learn the tricks and tips of website security.
                        This well designed and well executed concept has proven to be a huge success and will only advance further.
                        Attracting audiences to promote the importance of keeping your website secure, Hackthis is educating the internet for a more safer and secure tomorrow.
                        Whilst education is commonly considered boring, Hackthis teaches you by putting you in the place of the hacker, exploiting websites to gain access to information required to advance to the next level.
                        If that's not enough Hackthis also includes a great, friendly support and community forum where you can get help with problems, meet new people and help others with their problems.
                        <span>JrC Network</span>
                    </blockquote>

<?php
    }
    require_once('footer.php');
?>
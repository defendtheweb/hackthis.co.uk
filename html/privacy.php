<?php
    $custom_css = array('articles.scss', 'highlight.css', 'faq.scss');
    $custom_js = array('articles.js', 'highlight.js');
    define("_SIDEBAR", false);
    define("PAGE_PUBLIC", true);

    require_once('init.php');
    $app->page->title = 'Privacy';
    $app->page->canonical = 'http://www.hackthis.co.uk/privacy';
    require_once('header.php');
?>

<section class="row">
    
    <div class="col span_24 article-main">
        <article class='bbcode body' itemscope itemtype="http://schema.org/Article">
            <header class='clearfix'>
                <div class='width-center'>
                    <h1 itemprop="name">Privacy Policy</h1>
                    <div class='meta'>
                        <i class="icon-clock"></i> June 8, 2014
                        <i class="icon-user"></i> <a rel='author' itemprop='author' href='/user/flabbyrabbit>'>flabbyrabbit</a>
                    </div>
                </div>
            </header>
            <div itemprop='articleBody' class='articleBody width-center'>
                <p>
                    <h2>What information do we collect?</h2></br>
                    We collect information from you when you register on our site, place an order, subscribe to our newsletter, respond to a survey or fill out a form. When ordering or registering on our site, as appropriate, you may be asked to enter your name or e-mail address.
                    <br/><br/>
                    Google, as a third party vendor, uses cookies to serve ads on your site. Google's use of the DART cookie enables it to serve ads to your users based on their visit to your sites and other sites on the Internet. Users may opt out of the use of the DART cookie by visiting the Google ad and content network privacy policy.
                </p>
                <p>
                    <h2>What do we use your information for?</h2></br>
                    Any of the information we collect from you may be used in one of the following ways:
                    <ol>
                        <li>To personalize your experience</li>
                        <li>To improve our website</li>
                        <li>To process transactions</li>
                        <li>To build publicly accessible profiles</li>
                    </ol>

                    Your information, whether public or private, will not be sold, exchanged, transferred, or given to any other company for any reason whatsoever, without your consent, other than for the express purpose of delivering the purchased product or service requested.
                    <br/><br/>
                    The email address you provide for order processing, may be used to send you information and updates pertaining to your order, in addition to receiving occasional company news, updates, related product or service information, etc.
                    <br/><br/>
                    Note: If at any time you would like to unsubscribe from receiving future emails, we include detailed unsubscribe instructions at the bottom of each email.
                </p>
                <p>
                    <h2>How do we protect your information?</h2></br>
                    We implement a variety of security measures to maintain the safety of your personal information when you enter, submit, or access your personal information.
                    <br/><br/>
                    We offer the use of a secure server. All supplied sensitive/credit information is transmitted via Secure Socket Layer (SSL) technology and then encrypted into our Payment gateway providers database only to be accessible by those authorized with special access rights to such systems, and are required to keep the information confidential.
                    <br/><br/>
                    After a transaction, your private information (credit cards, social security numbers, financials, etc.) will be kept on file for more than 60 days in order to proccess transactions.
                </p>
                <p>
                    <h2>Do we use cookies?</h2></br>
                    Yes (Cookies are small files that a site or its service provider transfers to your computers hard drive through your Web browser (if you allow) that enables the sites or service providers systems to recognize your browser and capture and remember certain information.
                    <br/><br/>
                    We use cookies to help us remember and process the items in your shopping cart and understand and save your preferences for future visits.
                </p>
                <p>
                    <h2>Do we disclose any information to outside parties?</h2></br>
                    We do not sell, trade, or otherwise transfer to outside parties your personally identifiable information. This does not include trusted third parties who assist us in operating our website, conducting our business, or servicing you, so long as those parties agree to keep this information confidential. We may also release your information when we believe release is appropriate to comply with the law, enforce our site policies, or protect ours or others rights, property, or safety. However, non-personally identifiable visitor information may be provided to other parties for marketing, advertising, or other uses.
                </p>
                <p>
                    <h2>Third party links</h2></br>
                    Occasionally, at our discretion, we may include or offer third party products or services on our website. These third party sites have separate and independent privacy policies. We therefore have no responsibility or liability for the content and activities of these linked sites. Nonetheless, we seek to protect the integrity of our site and welcome any feedback about these sites.
                </p>
                <p>
                    <h2>California Online Privacy Protection Act Compliance</h2></br>
                    Because we value your privacy we have taken the necessary precautions to be in compliance with the California Online Privacy Protection Act. We therefore will not distribute your personal information to outside parties without your consent.
                    <br/><br/>
                    As part of the California Online Privacy Protection Act, all users of our site may make any changes to their information at any time by logging into their control panel and going to the 'Edit Profile' page.
                </p>
                <p>
                    <h2>Children's Online Privacy Protection Act Compliance</h2></br>
                    We are in compliance with the requirements of COPPA (Children's Online Privacy Protection Act), we do not collect any information from anyone under 13 years of age. Our website, products and services are all directed to people who are at least 13 years old or older.
                </p>
                <p>
                    <h2>Online Privacy Policy Only</h2></br>
                    This online privacy policy applies only to information collected through our website and not to information collected offline.
                </p>
                <p>
                    <h2>Terms and Conditions</h2></br>
                    Please also visit our Terms and Conditions section establishing the use, disclaimers, and limitations of liability governing the use of our website at http://www.hackthis.co.uk/terms
                </p>
                <p>
                    <h2>Your Consent</h2></br>
                    By using our site, you consent to our online privacy policy.
                </p>
            </div>
        </article>
    </div>
</section>

<?php  
    require_once('footer.php');
?>

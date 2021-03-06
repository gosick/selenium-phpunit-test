<?php 

require_once('phpunit-muzikOnline.php');

class Muzik extends WebTest {

    protected $websiteUrl = 'http://dev.muzik-online.com/tw';

/*******************************************************************************
* $browsers default setting
*/

    public static $browsers = array(

        
        array(
            'browserName' => 'phantomjs', 
            'host'=>'localhost', 
            'port'=>4444,
        ),

        array(
            'browserName' => 'chrome',
            'host'=>'localhost',
            'port'=>4444,
        ), 

        array(
            'browserName' => 'firefox',
            'host'=>'localhost',
            'port'=>4444,
        ),
        
        
    );       

    public static $vars = array(
        'account' => '',
        'password' => '',
    );

	protected function setUp() {

		parent::elementSetUp();
        $this->setHostAndPortByUser();
        $this->setBrowserUrl($this->websiteUrl);
	}

    public function setHostAndPortByUser() {

        global $argv, $argc;

        $count = 0;
        foreach ($argv as $value) {
            $count += 1;
            if(strcmp($value, "--host_ip_user") === 0){
                $this->setHost($argv[$count]);
            }
            if(strcmp($value, "--host_port_user") === 0){
                $this->setPort((int)$argv[$count]);
            }
        }
    }

/*******************************************************************************
*equivalence search testing
*/
    public function testSearchMozartDataCorrection() {

        $this->url($this->websiteUrl);

        $flag = false;
        parent::waitForElement('byCssSelector', 'header.header', 10);
        parent::search("莫札特 No");
        parent::waitForElement('byCssSelector', 'section.search-work', 10);
        if($this->byCssSelector('section.search-work')->displayed()){
            $target = $this->byXPath('//section[@class="search-work"]/ul/li[1]/a');
            $string = $target->attribute('title');
            if(strcmp($string, "莫札特, 沃夫岡 阿瑪迪斯 - D大調第四十七號交響曲，作品K.97") === 0) {
                $flag = true;
            }
            $this->assertTrue($flag);
            parent::wait(1);
            $target->click();
            parent::waitForElement('byCssSelector', 'section.play-playlist.js-play-playlist', 10);
            while(!$this->byCssSelector('section.play-playlist.js-play-playlist')->displayed()){
                parent::scrollView();
            }
            parent::wait(1);
            $this->assertEquals($this->byXPath('//section[@class="play-playlist js-play-playlist"]/div/div[2]/ul/li[1]/div[2]')->text(), "第一樂章");
        }

        $this->url('http://dev.muzik-online.com/en');
        parent::refresh();
        parent::waitForElement('byCssSelector', 'header.header', 10);

        parent::search("Mozart No");
        parent::waitForElement('byCssSelector', 'section.search-work', 10);
        if($this->byCssSelector('section.search-work')->displayed()){
            $target = $this->byXPath('//section[@class="search-work"]/ul/li[1]/a');
            $string = $target->attribute('title');
            if(strcmp($string, "Mozart, Wolfgang Amadeus - Symphony No.47 in D, K.97") === 0) {
                $flag = true;
            }
            $this->assertTrue($flag);
            parent::wait(1);
            $target->click();
            parent::waitForElement('byCssSelector', 'section.play-playlist.js-play-playlist', 10);
            while(!$this->byCssSelector('section.play-playlist.js-play-playlist')->displayed()){
                parent::scrollView();
            }
            parent::wait(1);
            $this->assertEquals($this->byXPath('//section[@class="play-playlist js-play-playlist"]/div/div[2]/ul/li[1]/div[2]')->text(), "第一樂章"); 
        }
    }

/******************************************************************************
* for a new generating account, do ads clicking, and then sets the accounts and password to other tests
*/
    public function testClickAds(){

        $this->url($this->websiteUrl);
        parent::waitForElement('byCssSelector', 'header.header', 10);
        sleep(2);
        parent::countMenuList();
        parent::menu('register', $this->total['register'], 1);
        sleep(2);  
        parent::waitForElement('byCssSelector', 'div.primary-content.js-controller-content', 10);
        parent::wait(1);
        $this->password = parent::memberPasswordGenerate();
        $this->account = parent::memberAccountGenerate();
        self::$vars['account'] = $this->account;
        self::$vars['password'] = $this->password;
        parent::register($this->account, $this->password);
        sleep(2);   
        parent::ads();
        sleep(14);
        parent::menu('logout', $this->total['logout'], 1);

    }

/*******************************************************************************
* testing for the first song list establishing and entering the song list 404 error 
*/
    public function testFirstSongListEnter() {

        $this->url($this->websiteUrl);

        parent::waitForElement('byCssSelector', 'header.header', 10);
        parent::countMenuList();
        parent::menu('login', $this->total['login'], 1);
        parent::waitForElement('byCssSelector', 'div.primary-content.js-controller-content', 10);
        parent::wait(1);
        parent::login(self::$vars['account'], self::$vars['password']);
        parent::wait(1);
        parent::waitForElement('byCssSelector', 'header.header', 10);
        parent::menu('memberProfile', $this->total['memberProfile'], 1);

        parent::memberSongListOperation('del', 1); 
        parent::fillSongListTitleAndDescription("test3232", "22ssdfest");
        parent::memberSongListOperation('new list', 1);
        parent::memberSongListOperation('enter', 1);
        
        $this->assertNotEquals(parent::responseCode(10000, $this->url()), 404, "status code is 404");

    }

/*******************************************************************************
* taking a screenshot of homepage
*/

    public function testScreenshotOfHomepage() {

        $this->url($this->websiteUrl);

        if($this->getBrowser() == 'phantomjs') {
            $fp = fopen('./report/phantomjs_homepage_width.jpg', 'wb');   
        }
        else if($this->getBrowser() == 'chrome') {
            $fp = fopen('./report/chrome_homepage_width.jpg', 'wb');   
        }
        else if($this->getBrowser() == 'firefox') {
            $fp = fopen('./report/firefox_homepage_width.jpg', 'wb');   
        }
        else{
            $fp = fopen('./report/homepage.jpg', 'wb');
        }
        fwrite($fp, $this->currentScreenshot());
        fclose($fp);
    }

/*******************************************************************************
*
*/
    public function testSituationA() {

        $this->url($this->websiteUrl);
        $result = array();
        parent::waitForElement('byCssSelector', 'header.header', 5);
        parent::countMenuList();

        parent::menu('allMusic', $this->total['allMusic'], 5);
        parent::waitForElement('byXPath', '//div[@class="album-list"]/ul/li[1]/div', 10);
        $url = $this->url();
        array_push($result, parent::getUrlList($url));

        parent::menu('allMusic', $this->total['allMusic'], 4);
        parent::waitForElement('byXPath', '//div[@class="list js-composer-category-list"]/ul/li/a[1]', 10);
        $url = $this->url();
        array_push($result, parent::getUrlList($url));

        parent::menu('allMusic', $this->total['allMusic'], 3);
        parent::waitForElement('byXPath', '//div[@class="listen-list"]/ul/li[1]/div', 10);
        $url = $this->url();
        array_push($result, parent::getUrlList($url));

        parent::menu('allMusic', $this->total['allMusic'], 2);
        parent::waitForElement('byXPath', '//div[@class="listen-list"]/ul/li[1]/div', 10);
        $url = $this->url();
        array_push($result, parent::getUrlList($url));
        
        parent::menu('allMusic', $this->total['allMusic'], 1);
        parent::waitForElement('byXPath', '//div[@class="listen-list"]/ul/li[1]/div', 10);
        $url = $this->url();
        array_push($result, parent::getUrlList($url));
        
        $this->assertNull($result, "responseCode contains: 4xx, 5xx");    
        
    }
 
    public function testSituationB() {

        $this->url($this->websiteUrl);
        $result = array();
        parent::waitForElement('byCssSelector', 'header.header', 5);
        parent::countMenuList();

        parent::menu('article', $this->total['article'], 1);
        parent::waitForElement('byXPath', '//div[@class="article-list"]/ul/li[1]/a[1]', 10);
        $url = $this->url();
        array_push($result, parent::getUrlList($url));

        parent::menu('article', $this->total['article'], 2);
        parent::waitForElement('byXPath', '//div[@class="article-list"]/ul/li[1]/a[1]', 10);
        $url = $this->url();
        array_push($result, parent::getUrlList($url));

        parent::menu('article', $this->total['article'], 3);
        parent::waitForElement('byXPath', '//div[@class="article-list"]/ul/li[1]/a[1]', 10);
        $url = $this->url();
        array_push($result, parent::getUrlList($url));
        
        $this->assertNull($result, "responseCode contains: 4xx, 5xx");    
    
    }

    public function testSituationC() {

        $this->url($this->websiteUrl);
        $result = array();
        parent::waitForElement('byCssSelector', 'header.header', 5);
        parent::countMenuList();

        parent::menu('concert', $this->total['concert'], 1);
        parent::waitForElement('byXPath', '//div[@class="concert-list"]/ul/li[1]/a[1]', 10);
        $url = $this->url();
        array_push($result, parent::getUrlList($url));

        $this->assertNull($result, "responseCode contains: 4xx, 5xx");

    }

    public function testSituationD() {

        $this->url($this->websiteUrl);
        $result = array();
        parent::waitForElement('byCssSelector', 'header.header', 5);
        parent::countMenuList();

        parent::menu('periodical', $this->total['periodical'], 1);
        parent::waitForElement('byXPath', '//div[@class="mag-edition"]/ul/li[1]/a', 10);
        $url = $this->url();
        array_push($result, parent::getUrlList($url));

        $this->assertNull($result, "responseCode contains: 4xx, 5xx");

    }

    public function testSituationE() {

        $this->url($this->websiteUrl);
        $result = array();
        parent::waitForElement('byCssSelector', 'header.header', 5);
        parent::countMenuList();

        parent::menu('memberCenter', $this->total['memberCenter'], 1);
        $url = $this->url();
        array_push($result, parent::getUrlList($url));

        parent::menu('memberCenter', $this->total['memberCenter'], 2);
        $url = $this->url();
        array_push($result, parent::getUrlList($url));

        $this->assertNull($result, "responseCode contains: 4xx, 5xx");  
    }
 
    public function testSituationF() {

        $this->url($this->websiteUrl);

        parent::waitForElement('byCssSelector', 'header.header', 10);
        parent::countMenuList();
        parent::menu('login', $this->total['login'], 1);
        parent::waitForElement('byId', 'account', 10);
        parent::login(self::$vars['account'], self::$vars['password']);
        parent::waitForElement('byCssSelector', 'header.header', 10);

        parent::menu('memberProfile', $this->total['memberProfile'], 1);

        parent::memberProfileSelect('songList');
        parent::memberProfileSelect('collection');
        parent::memberProfileSelect('profile');

        sleep(3);
        parent::playerOpen();

        parent::playerheaderSelect('myList');

        parent::playerheaderSelect('myCollection');
        
    }

    public function testSituationG() {

        $this->url($this->websiteUrl);

        parent::waitForElement('byCssSelector', 'header.header', 5);
        parent::countMenuList();

        parent::menu('login', $this->total['login'], 1);
        parent::waitForElement('byId', 'account', 10);
        parent::login(self::$vars['account'], self::$vars['password']);
        parent::waitForElement('byCssSelector', 'header.header', 10);
        
        parent::menu('allMusic', $this->total['allMusic'], 1);
        parent::waitForElement('byXPath', '//div[@class="listen-list"]/ul/li[1]/div', 10);

        $this->byXPath('//div[@class="listen-list"]/ul/li[1]/div/a')->click();

        sleep(5);//sleep for playing time

        $this->byXPath('//div[@class="listen-list"]/ul/li[2]/div/a')->click();

        sleep(5);//sleep for playing time

        $this->byXPath('//div[@class="listen-list"]/ul/li[3]/div/a')->click();

        sleep(5);//sleep for playing time

        $this->byXPath('//div[@class="listen-list"]/ul/li[3]/a[2]')->click();

        $this->byXPath('//div[@class="listen-list"]/ul/li[4]/div/a')->click();

        sleep(5);//sleep for playing time

        $this->byXPath('//div[@class="listen-list"]/ul/li[5]/div/a')->click();

        sleep(5);//sleep for playing time
    }

    public function testSituationH() {

        $this->url($this->websiteUrl);

        parent::waitForElement('byCssSelector', 'header.header', 5);
        parent::countMenuList();

        parent::menu('login', $this->total['login'], 1);
        parent::waitForElement('byId', 'account', 10);
        parent::login(self::$vars['account'], self::$vars['password']);
        parent::waitForElement('byCssSelector', 'header.header', 10);

        parent::menu('memberProfile', $this->total['memberProfile'], 1);
        
        parent::memberProfileSelect('collection');

        parent::memberCollection('enter', 1);

        parent::memberProfileSelect('songList');

        parent::memberSongListOperation('enter', 1);

        parent::memberSongListSongSelect('add to list', 1);
        parent::fillSongListTitleAndDescription('mylist 1', 'description1');
        parent::addToListSelect('new list', 1);

        parent::memberSongListSongSelect('add to list', 2);
        parent::addToListSelect('my list', 2);

        parent::memberSongListSongSelect('add to list', 2);
        parent::addToListSelect('temporary list', 1);

        parent::memberSongListSongSelect('play', 3);
        parent::memberSongListSongSelect('info', 5);

        parent::menu('memberProfile', $this->total['memberProfile'], 1);

        parent::menu('logout', $this->total['logout'], 1);

    }

    public function testSituationI() {

        $this->url($this->websiteUrl);
        parent::waitForElement('byCssSelector', 'header.header', 5);
        parent::countMenuList();
        parent::menu('login', $this->total['login'], 1);
        parent::waitForElement('byId', 'account', 10);
        parent::login(self::$vars['account'], self::$vars['password']);
        parent::waitForElement('byCssSelector', 'header.header', 10);
        
        parent::search('Chopin, Frederic');

        $this->byXPath('//div[@class="table"]/div[2]/ul/li[1]/div[4]/a[4]')->click();
        parent::fillSongListTitleAndDescription('Chopin, Frederic', 'Chopin, Frederic');
        parent::addToListSelect('new list', 1);

        $this->byXPath('//div[@class="table"]/div[2]/ul/li[2]/div[4]/a[4]')->click();
        parent::addToListSelect('my list', 1);

        $this->byXPath('//div[@class="table"]/div[2]/ul/li[3]/div[4]/a[4]')->click();
        parent::addToListSelect('my list', 1);

        $this->byXPath('//div[@class="table"]/div[2]/ul/li[4]/div[4]/a[4]')->click();
        parent::addToListSelect('my list', 1);

        $this->byXPath('//div[@class="table"]/div[2]/ul/li[5]/div[4]/a[4]')->click();
        parent::addToListSelect('my list', 1);

        sleep(3);

        parent::playerOpen();

        parent::playerheaderSelect('myList');

        parent::playerLeftContentSelect('choose', 1);

        parent::playerSongContentfunc('play', 1);

        parent::menu('article', $this->total['article'], 1);

        parent::menu('allMusic', $this->total['allMusic'], 5);

        $this->byXPath('//div[@class="album-list"]/ul/li/div/a[1]')->click();
        
        $this->playerCheckAlbum();

        parent::menu('concert', $this->total['concert'], 1);

        $this->byCssSelector('input.keyword')->value('chopin');

        parent::menu('logout', $this->total['logout'], 1);

    }

    public function testSituationJ() {

        $this->url($this->websiteUrl);
        parent::waitForElement('byCssSelector', 'header.header', 5);
        parent::countMenuList();
        parent::menu('login', $this->total['login'], 1);
        parent::waitForElement('byCssSelector', 'div.primary-content.js-controller-content', 5);
        parent::wait(1);
        parent::login(self::$vars['account'], self::$vars['password']);

        parent::menu('periodical', $this->total['periodical'], 1);

        $this->byXPath('//div[@class="mag-edition"]/ul/li[1]/a')->click();

        $this->byXPath('//div[@class="primary-content js-controller-content"]/div[3]/ul/li[1]/div/a')->click();
        
        if($this->byXPath('//div[@class="main-inner js-controller-inner js-main-inner periodical page"]')->displayed()) {

            if($this->byXPath('//div[@class="primary-content js-controller-content"]/article/table/tbody/tr[1]/td[1]')->displayed()) {

                $tr1TextP1 = $this->byXPath('//div[@class="primary-content js-controller-content"]/article/table/tbody/tr[1]/td[1]/p[1]')->text();
                $tr1TextP2 = $this->byXPath('//div[@class="primary-content js-controller-content"]/article/table/tbody/tr[1]/td[1]/p[2]')->text();
                $tr1TextP3 = $this->byXPath('//div[@class="primary-content js-controller-content"]/article/table/tbody/tr[1]/td[1]/p[3]')->text();
                $tr1TextP4 = $this->byXPath('//div[@class="primary-content js-controller-content"]/article/table/tbody/tr[1]/td[1]/p[4]')->text();
                $tr1TextP5 = $this->byXPath('//div[@class="primary-content js-controller-content"]/article/table/tbody/tr[1]/td[1]/p[6]')->text();
                $tr1TextP6 = $this->byXPath('//div[@class="primary-content js-controller-content"]/article/table/tbody/tr[1]/td[1]/p[7]')->text();
                
                $this->assertNotNull($tr1TextP1);
                $this->assertNotNull($tr1TextP2);
                $this->assertNotNull($tr1TextP3);
                $this->assertNotNull($tr1TextP4);
                $this->assertNotNull($tr1TextP5);
                $this->assertNotNull($tr1TextP6);
            }
            if($this->byXPath('//div[@class="primary-content js-controller-content"]/article/table/tbody/tr[2]/td[1]/table')->displayed()) {

                $tr2Tr1Text1 = $this->byXPath('//div[@class="primary-content js-controller-content"]/article/table/tbody/tr[2]/td[1]/table/tbody/tr[1]/td[2]')->text();
                $tr2Tr1Text2 = $this->byXPath('//div[@class="primary-content js-controller-content"]/article/table/tbody/tr[2]/td[1]/table/tbody/tr[1]/td[3]')->text();
                $tr2Tr1Text3 = $this->byXPath('//div[@class="primary-content js-controller-content"]/article/table/tbody/tr[2]/td[1]/table/tbody/tr[1]/td[4]')->text();
                $tr2Tr1Text4 = $this->byXPath('//div[@class="primary-content js-controller-content"]/article/table/tbody/tr[2]/td[1]/table/tbody/tr[1]/td[5]')->text();

                $tr2Tr2Text1 = $this->byXPath('//div[@class="primary-content js-controller-content"]/article/table/tbody/tr[2]/td[1]/table/tbody/tr[2]/td[2]')->text();
                $tr2Tr2Text2 = $this->byXPath('//div[@class="primary-content js-controller-content"]/article/table/tbody/tr[2]/td[1]/table/tbody/tr[2]/td[3]')->text();
                $tr2Tr2Text3 = $this->byXPath('//div[@class="primary-content js-controller-content"]/article/table/tbody/tr[2]/td[1]/table/tbody/tr[2]/td[4]')->text();
                $tr2Tr2Text4 = $this->byXPath('//div[@class="primary-content js-controller-content"]/article/table/tbody/tr[2]/td[1]/table/tbody/tr[2]/td[5]')->text();

                $this->assertNotNull($tr2Tr1Text1);
                $this->assertNotNull($tr2Tr1Text2);
                $this->assertNotNull($tr2Tr1Text3);
                $this->assertNotNull($tr2Tr1Text4);
                $this->assertNotNull($tr2Tr2Text1);
                $this->assertNotNull($tr2Tr2Text2);
                $this->assertNotNull($tr2Tr2Text3);
                $this->assertNotNull($tr2Tr2Text4);
                
            }
            if($this->byXPath('//div[@class="primary-content js-controller-content"]/article/table/tbody/tr[1]/td[3]/p/img')->displayed()) {

                $url = $this->byXPath('//div[@class="primary-content js-controller-content"]/article/table/tbody/tr[1]/td[3]/p/img')->attribute('src');
                $requestcode = strval(parent::responseCode(10000, $url));
                if((strpos($requestcode, "4", 0) === 0) ||(strpos($requestcode, "5", 0) === 0)) {
                    $temp = array("responseCode" => $requestcode, "url" => $url);
                    array_push($recordList, $temp);
                }
            }
            if($this->byXPath('//div[@class="primary-content js-controller-content"]/article/p[5]')->displayed()) {
                $text1 = $this->byXPath('//div[@class="primary-content js-controller-content"]/article/p[5]')->text();
                $this->assertNotNull($text1);
            }
            if($this->byXPath('//div[@class="primary-content js-controller-content"]/article/p[7]')->displayed()) {
                $text2 = $this->byXPath('//div[@class="primary-content js-controller-content"]/article/p[7]')->text();
                $this->assertNotNull($text2);
            }

        }
    }
   

/*******************************************************************************
* test RWD of browser width and take pictures
*/
    function testRWDOfWidth() {

        $this->url('http://event.muzik-online.com/piano/');

        $this->prepareSession()->currentWindow()->maximize();
        sleep(5);

        if($this->getBrowser() == 'phantomjs') {
            $fp = fopen('./report/phantomjs_rwd1_width.jpg', 'wb');   
        }
        else if($this->getBrowser() == 'chrome') {
            $fp = fopen('./report/chrome_rwd1_width.jpg', 'wb');   
        }
        else if($this->getBrowser() == 'firefox') {
            $fp = fopen('./report/firefox_rwd1_width.jpg', 'wb');   
        }
        else{
            $fp = fopen('./report/rwd1.jpg', 'wb');
        }
        sleep(5);
        fwrite($fp, $this->currentScreenshot());
        fclose($fp);

        $window = $this->currentWindow();
        $window->size(array('width' => 720, 'height' => 480));

        if($this->getBrowser() == 'phantomjs') {
            $fp = fopen('./report/phantomjs_rwd2_width.jpg', 'wb');   
        }
        else if($this->getBrowser() == 'chrome') {
            $fp = fopen('./report/chrome_rwd2_width.jpg', 'wb');   
        }
        else if($this->getBrowser() == 'firefox') {
            $fp = fopen('./report/firefox_rwd2_width.jpg', 'wb');   
        }
        else{
            $fp = fopen('./report/rwd2.jpg', 'wb');
        }
        sleep(5);
        fwrite($fp, $this->currentScreenshot());
        fclose($fp);
    }

/*******************************************************************************
* user-agent settings and test RWD of user-agent of iphone and take pictures
*/
    public function useragent() {

        $testString = "Mozilla/5.0 (iPhone; CPU iPhone OS 6_1_4 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10B350 Safari/8536.25";
        $para = "general.useragent.override";

        shell_exec("mkdir firefox-profile");
        shell_exec("cd ./firefox-profile && echo 'user_pref(\"$para\", \"$testString\");' >> prefs.js && zip -r ../firefox-profile *");
        $data = file_get_contents('firefox-profile.zip');
        shell_exec("rm -rf firefox-profile && rm firefox-profile.zip");
        return base64_encode($data);

    }
    public function testUserAgentIphone() {
        
        $this->useragent();
        $this->url('https://instagram.com/p/PYM9zAkpCR/');
        if($this->getBrowser() == 'chrome') {
            $fp = fopen('./report/chrome_rwd_userAgent_Iphone.jpg', 'wb');
        }
        else if($this->getBrowser() == 'firefox') {
            $fp = fopen('./report/firefox_rwd_userAgent_Iphone.jpg', 'wb');   
        }
        else if($this->getBrowser() == 'phantomjs') {
            $fp = fopen('./report/phantomjs_rwd_userAgent_Iphone.jpg', 'wb');   
        }
        else {
            $fp = fopen('./report/rwd3.jpg', 'wb');
        }
        
        fwrite($fp, $this->currentScreenshot());
        fclose($fp);
    }
      
}
?>
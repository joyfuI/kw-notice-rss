<?php
include_once './lib/simple_html_dom.php';
include_once './lib/rollingcurlx.class.php';

$URL = 'https://www.kw.ac.kr/ko/life/notice.jsp?BoardMode=list&tpage=';
$PAGE = 3; // 해당 페이지까지 파싱

header('Content-Type: text/xml; charset=UTF-8');
date_default_timezone_set('Asia/Seoul'); // 기본 시간대 설정

$urlComponents = parse_url($URL);
$options = array(
    CURLOPT_HEADER => 0, // 헤더는 제외하고 content만 받음
    CURLOPT_RETURNTRANSFER => 1, // 응답 값을 브라우저에 표시하지 말고 값을 리턴
    CURLOPT_USERAGENT => 'Mozilla/5.0' // 유저에이전트 미지정 시 웹방화벽에서 차단당함
);

for ($i = 0; $i < $PAGE; $i++) {
    $ch = curl_init();
    curl_setopt_array($ch, $options);
    curl_setopt($ch, CURLOPT_URL, $URL . ($i + 1));
    $htmls[$i] = str_get_html(curl_exec($ch));
    curl_close($ch);
}
$RCX = new RollingCurlX(20);

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<rss version="2.0">' . "\n";
echo "\t<channel>\n";
echo "\t\t<title>광운대학교 공지사항</title>\n";
$orgin = $urlComponents['scheme'] . '://' . $urlComponents['host'];
echo "\t\t<link>" . htmlspecialchars($orgin . $urlComponents['path']) . "</link>\n";
echo "\t\t<description>공지사항은 광운인 모두가 꼭 알아야 할 사항을 모아놓은 공간입니다.</description>\n";
echo "\t\t<language>ko</language>\n";
echo "\t\t<pubDate>" . date('r') . "</pubDate>\n";
$i = 0;
foreach ($htmls as $html) {
    foreach ($html->find('#jwxe_main_content .notice .list-box .board-list-box .board-text') as $element) {
        $aTag = $element->find('a', 0);
        $title = trim($aTag->plaintext);
        $title = preg_replace('/\s{30,}.*$/', '', $title); // 텍스트 끝에 신규게시물 등 쓸데없는 텍스트 제거
        $title = preg_replace('/\s+/', ' ', $title); // 공백 압축
        $items[$i]['title'] = htmlspecialchars($title);

        $link = $orgin . $aTag->href;
        $items[$i]['link'] = htmlspecialchars($link);

        $RCX->addRequest($link, null, 'callback', [$i], $options, null);

        if (preg_match('/^\[(.+?)\]/', $title, $arr)) { // 카테고리 추출
            $items[$i]['category'] = $arr[1];
        }

        $info = $element->find('.info', 0)->plaintext;
        preg_match('/작성일 .+ \| 수정일 (.+) \| (.+)/', $info, $arr); // 날짜와 작성자 추출
        $items[$i]['author'] = trim($arr[2]);

        $date = strtotime(trim($arr[1]));
        $items[$i]['pubDate'] = date('r', $date);

        $items[$i]['guid'] = htmlspecialchars($link . '#' . $arr[1]);

        $i++;
    }
}
$RCX->execute();
foreach ($items as $i => $item) {
    echo "\t\t<item>\n";
    echo "\t\t\t<title>" . $item['title'] . "</title>\n";
    echo "\t\t\t<link>" . $item['link'] . "</link>\n";
    echo "\t\t\t<description>" . $item['description'] . "</description>\n";
    if (array_key_exists('category', $item)) {
        echo "\t\t\t<category>" . $item['category'] . "</category>\n";
    }
    echo "\t\t\t<author>" . $item['author'] . "</author>\n";
    echo "\t\t\t<pubDate>" . $item['pubDate'] . "</pubDate>\n";
    echo "\t\t\t<guid>" . $item['guid'] . "</guid>\n";
    echo "\t\t</item>\n";
}
echo "\t</channel>\n";
echo "</rss>";

function callback($response, $url, $request_info, $user_data, $time) {
    global $items;
    // @$contents = str_get_html($response)->find('#jwxe_main_content .notice .board-view-box .contents', 0)->plaintext; // 내용 추출
    $contents = new simple_html_dom();
    @$contents = $contents->load($response)->find('#jwxe_main_content .notice .board-view-box .contents', 0)->plaintext; // 내용 추출
    $contents = preg_replace('/\n/', '<br>', $contents); // 줄바꿈 처리
    $contents = preg_replace('/\s+/', ' ', $contents); // 공백 압축
    $items[$user_data[0]]['description'] = htmlspecialchars(trim($contents));
}

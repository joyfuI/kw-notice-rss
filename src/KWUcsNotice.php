<?php
include_once './lib/simple_html_dom.php';
include_once './lib/rollingcurlx.class.php';

$URL = 'https://cs.kw.ac.kr:501/department_office/lecture.php?page=';
$PAGE = 3; // 해당 페이지까지 파싱

header('Content-Type: text/xml; charset=UTF-8');
date_default_timezone_set('Asia/Seoul'); // 기본 시간대 설정

$urlComponents = parse_url($URL);
$options = array(
    CURLOPT_HEADER => 0, // 헤더는 제외하고 content만 받음
    CURLOPT_RETURNTRANSFER => 1, // 응답 값을 브라우저에 표시하지 말고 값을 리턴
    CURLOPT_USERAGENT => 'Mozilla/5.0', // 유저에이전트 미지정 시 웹방화벽에서 차단당함
    CURLOPT_SSL_VERIFYPEER => FALSE // 왠지 모르겠지만 인증서 오류가 나서 일단끔
);

for ($i = 0; $i < $PAGE; $i++) {
    $ch = curl_init();
    curl_setopt_array($ch, $options);
    curl_setopt($ch, CURLOPT_URL, $URL . ($i + 1));
    $htmls[$i] = str_get_html(curl_exec($ch));
    curl_close($ch);
}
$RCX = new RollingCurlX(10);

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<rss version="2.0">' . "\n";
echo "\t<channel>\n";
echo "\t\t<title>광운대학교 소프트웨어학부 공지사항</title>\n";
$orgin = $urlComponents['scheme'] . '://' . $urlComponents['host'] . ':' . $urlComponents['port'];
echo "\t\t<link>" . htmlspecialchars($orgin . $urlComponents['path']) . "</link>\n";
echo "\t\t<description>소프트웨어학부 행정을 안내합니다.</description>\n";
echo "\t\t<language>ko</language>\n";
echo "\t\t<pubDate>" . date('r') . "</pubDate>\n";
$i = 0;
foreach ($htmls as $html) {
    foreach ($html->find('#inner_wrap .rightW .sub_con .board_listW .board-list .subject') as $element) {
        $aTag = $element->find('a', 0);
        $title = trim($aTag->plaintext);
        $items[$i]['title'] = htmlspecialchars($title);

        $link = $orgin . $aTag->href;
        $items[$i]['link'] = htmlspecialchars($link);

        $RCX->addRequest($link, null, 'callback', [$i], $options, null);

        if (preg_match('/^\[(.+?)\]/', $title, $arr)) { // 카테고리 추출
            $items[$i]['category'] = $arr[1];
        }

        $date = strtotime(trim($element->parent()->children(2)->plaintext)); // 날짜 추출
        $items[$i]['pubDate'] = date('r', $date);

        $items[$i]['guid'] = htmlspecialchars($link);

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
    echo "\t\t\t<pubDate>" . $item['pubDate'] . "</pubDate>\n";
    echo "\t\t\t<guid>" . $item['guid'] . "</guid>\n";
    echo "\t\t</item>\n";
}
echo "\t</channel>\n";
echo "</rss>";

function callback($response, $url, $request_info, $user_data, $time) {
    global $items;
    @$contents = str_get_html($response)->find('#inner_wrap .rightW .sub_con .table_styleW .board-view .view_td', 0)->innertext; // 내용 추출
    $contents = preg_replace('/\s+/', ' ', $contents); // 공백 압축
    $items[$user_data[0]]['description'] = htmlspecialchars(trim($contents));
}

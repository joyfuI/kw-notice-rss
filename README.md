# kw-notice-rss
광운대학교 공지사항이 RSS를 제공 안 해서 RSS로 구독할 수 있도록 직접 PHP로 만들었습니다.  
이제 학교도 졸업해서 유지보수할 이유가 없어서 사용할 사람은 가져가서 사용하라고 깃허브에 공개합니다.

## RSS 주소
제 웹호스팅 서버에 php 파일 올려놓고 피드버너에 등록한 주소입니다. 예고 없이 링크가 사라지거나 학교 사이트 구조가 바뀌어서 RSS 갱신이 안 될 수도 있습니다.
 게시판 | 주소
 --- | ---
[광운대학교 공지사항](https://www.kw.ac.kr/ko/life/notice.jsp) | [http://feeds.feedburner.com/KWUNotice](http://feeds.feedburner.com/KWUNotice)
[광운대학교 소프트웨어학부 공지사항](https://cs.kw.ac.kr:501/department_office/lecture.php) | [http://feeds.feedburner.com/KWUcsNotice](http://feeds.feedburner.com/KWUcsNotice)
[광운대학교 총학생회 페이스북](https://www.facebook.com/KWUStudentCouncil) | [http://feeds.feedburner.com/KWUStudentCouncil](http://feeds.feedburner.com/KWUStudentCouncil)

## 파일 설명
### KWUNotice.php
광운대학교 공지사항 게시판의 글을 RSS로 만들어주는 파일

### KWUNotice_simple.php
위에서 만든 RSS가 용량 문제로 피드버너에 등록이 안 돼서 공지사항 글에서 텍스트만 뽑아내도록 수정한 파일

### KWUcsNotice.php
광운대학교 소프트웨어학부 공지사항 게시판의 글을 RSS로 만들어주는 파일

### KWUStudentCouncil.php
광운대학교 총학생회 페이스북의 포스트를 RSS로 만들어주는 파일  
페이스북은 내용 긁어 오기가 까다로워서 [RSS-Bridge](https://github.com/RSS-Bridge/rss-bridge)를 사용했습니다. [releases 페이지](https://github.com/RSS-Bridge/rss-bridge/releases)에서 받아다가 `bridge` 디렉터리 안에 압축을 풀어 설치하면 됩니다.

## 사용 라이브러리
* [PHP Simple HTML DOM Parser](https://simplehtmldom.sourceforge.io/) - HTML 파싱을 위한 라이브러리
* [RollingCurlX](https://github.com/marcushat/RollingCurlX) - 동시에 curl 요청을 보내는 라이브러리. 글 내용을 가져오기 위해 수많은 링크를 긁어오는데 너무 느려서 속도를 개선하려고 사용

## 테스트 환경
* PHP 7.4

`RollingCurlX` 라이브러리가 PHP 8을 지원하지 않아서 PHP 8에서는 작동하지 않습니다.

<?php
$URL = 'bridge/?action=display&bridge=Facebook&context=User&u=KWUStudentCouncil&media_type=all&limit=-1&format=Atom';

header('Content-Type: text/xml; charset=UTF-8');
date_default_timezone_set('Asia/Seoul'); // 기본 시간대 설정

header('Location: ' . $URL);

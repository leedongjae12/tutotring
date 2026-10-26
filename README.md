# tutotring — PHP 8.4 / Rocky Linux 9.6

## 서버 설치

저장소 전체를 서버에 내려받고, 해당 디렉터리에서 애플리케이션 소유자 계정으로 실행합니다.
`install.sh` 파일 하나만 내려받으면 애플리케이션 설치가 완료되지 않습니다.

```bash
git clone https://github.com/leedongjae12/tutotring.git
cd tutotring
bash install.sh
```

스크립트는 sudo 권한으로 EPEL, CRB, Remi 저장소를 준비하고 PHP 모듈을 `remi-8.4`로 선택합니다.
기존 서버의 다른 PHP 애플리케이션에도 영향을 줄 수 있으므로 전용 서버 또는 검증 환경에서 실행하세요.
패키지 서명 및 HTTPS 검증을 유지하며, Composer는 일반 사용자 권한으로 실행합니다.
웹서버 설정·서비스 재시작·DB 변경은 자동 실행하지 않습니다.
Rocky Linux에서의 패키지 설치는 아직 실제 검증하지 않았습니다.

이미 PHP 8.4와 의존성이 설치되었다면 변경 없이 검사할 수 있습니다.

```bash
bash install.sh --check
```

## DB 및 웹서버 설정

`DB_HOST`, `DB_USER`, `DB_PASSWORD`, `DB_NAME`, 선택적으로 `DB_PORT`(기본 3306)를
PHP 실행 프로세스에 전달하세요. 기존 코드에 있던 DB 접속 정보는 제거했습니다.
`.env` 파일은 자동으로 읽지 않습니다. PHP-FPM의 환경 변수 전달 설정을 따로 확인하세요.
접속 정보가 없거나 DB 연결에 실패하면 자격 증명 노출 없이 HTTP 503을 반환합니다.
DB 스키마, 데이터, 사용자 정의 DB 함수는 별도로 준비해야 합니다.

PHP-FPM과 Apache/Nginx의 문서 루트는 저장소 디렉터리로 설정하고,
`include`, `vendor`, `tests`, `.git` 디렉터리와 `install.sh`, Composer 파일의 외부 접근을 차단하세요.
정상 HTTPS 설정이 필요하며, 기존 앱의 HTTPS 리디렉션을 유지했습니다.
모든 PHP 짧은 태그를 표준 태그로 변경했으므로 `short_open_tag=Off`로 실행할 수 있습니다.

개발 환경의 내부 확인 예시:

```bash
php -S 127.0.0.1:8080 -t .
```

DB 환경 변수가 없을 때 `/sinchon/index.php`의 예상 응답은 HTTP 503입니다.
런타임 검사 통과는 로그인·DB 업무 흐름의 정상 동작을 의미하지 않습니다.
`css`, `js`, `images` 등 누락된 정적 파일과 대학 SSO/API 접근도 별도 준비가 필요합니다.

## 호환성 변경

- 제거된 `get_magic_quotes_gpc()` 호출 제거 및 배열 입력 처리.
- 암복호화 함수가 전달받은 키를 사용하도록 수정. 기존 잘못된 키로 생성된 세션은 재로그인이 필요합니다.
  기존 CBC 암호문 형식은 유지하며, 인증 암호화로의 전환은 별도 작업입니다.
- PHP 8.1 이후의 mysqli 예외를 연결 단계에서 처리하고, 기존 쿼리의 `false` 검사 동작은 유지.
- PhpSpreadsheet를 Composer로 선언하고 잠금 파일에 따라 설치.

```bash
php tests/php84_smoke.php
```

입력 처리, 암복호화 왕복, 실제 XLSX 저장·읽기를 검사합니다. 운영 DB에는 접속하지 않습니다.

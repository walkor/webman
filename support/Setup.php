<?php

declare(strict_types=1);

namespace support;

use Composer\IO\IOInterface;
use Composer\Script\Event;
use Symfony\Component\Console\Cursor;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Terminal;

/**
 * create-project setup wizard: interactive locale, timezone and optional components selection, then runs composer require.
 */
class Setup
{
    // --- Optional component package names ---

    private const PACKAGE_CONSOLE           = 'webman/console';
    private const PACKAGE_DATABASE          = 'webman/database';
    private const PACKAGE_THINK_ORM         = 'webman/think-orm';
    private const PACKAGE_REDIS             = 'webman/redis';
    private const PACKAGE_ILLUMINATE_EVENTS = 'illuminate/events';

    private const SETUP_TITLE = 'Webman Setup';

    // --- Timezone regions ---

    private const TIMEZONE_REGIONS = [
        'Asia'       => \DateTimeZone::ASIA,
        'Europe'     => \DateTimeZone::EUROPE,
        'America'    => \DateTimeZone::AMERICA,
        'Africa'     => \DateTimeZone::AFRICA,
        'Australia'  => \DateTimeZone::AUSTRALIA,
        'Pacific'    => \DateTimeZone::PACIFIC,
        'Atlantic'   => \DateTimeZone::ATLANTIC,
        'Indian'     => \DateTimeZone::INDIAN,
        'Antarctica' => \DateTimeZone::ANTARCTICA,
        'Arctic'     => \DateTimeZone::ARCTIC,
        'UTC'        => \DateTimeZone::UTC,
    ];

    // --- Locale => default timezone ---

    private const LOCALE_DEFAULT_TIMEZONES = [
        'zh_CN' => 'Asia/Shanghai',
        'zh_TW' => 'Asia/Taipei',
        'en'    => 'UTC',
        'ja'    => 'Asia/Tokyo',
        'ko'    => 'Asia/Seoul',
        'fr'    => 'Europe/Paris',
        'de'    => 'Europe/Berlin',
        'es'    => 'Europe/Madrid',
        'pt_BR' => 'America/Sao_Paulo',
        'ru'    => 'Europe/Moscow',
        'vi'    => 'Asia/Ho_Chi_Minh',
        'tr'    => 'Europe/Istanbul',
        'id'    => 'Asia/Jakarta',
        'th'    => 'Asia/Bangkok',
    ];

    // --- Locale options (localized display names) ---

    private const LOCALE_LABELS = [
        'zh_CN' => '简体中文',
        'zh_TW' => '繁體中文',
        'en'    => 'English',
        'ja'    => '日本語',
        'ko'    => '한국어',
        'fr'    => 'Français',
        'de'    => 'Deutsch',
        'es'    => 'Español',
        'pt_BR' => 'Português (Brasil)',
        'ru'    => 'Русский',
        'vi'    => 'Tiếng Việt',
        'tr'    => 'Türkçe',
        'id'    => 'Bahasa Indonesia',
        'th'    => 'ไทย',
    ];

    // --- Multilingual messages (%s = placeholder) ---

    private const MESSAGES = [
        'zh_CN' => [
            'skip'             => '非交互模式，跳过安装向导。',
            'default_choice'   => ' (默认 %s)',
            'timezone_prompt'  => '时区 (默认 %s，输入可联想补全): ',
            'timezone_title'   => '时区设置 (默认 %s)',
            'timezone_help'    => '输入关键字Tab自动补全，可↑↓下选择:',
            'timezone_region'  => '选择时区区域',
            'timezone_city'    => '选择时区',
            'timezone_invalid' => '无效的时区，已使用默认值 %s',
            'console_question' => '安装命令行组件 webman/console',
            'db_question'      => '数据库组件',
            'db_none'          => '不安装',
            'db_invalid'       => '请输入有效选项',
            'redis_question'   => '安装 Redis 组件 webman/redis',
            'events_note'      => '  (Redis 依赖 illuminate/events，已自动包含)',
            'no_components'    => '未选择额外组件。',
            'installing'       => '即将安装：',
            'running'          => '执行：',
            'error_install'    => '安装可选组件时出错，请手动执行：composer require %s',
            'done'             => '可选组件安装完成。',
            'summary_locale'   => '语言：%s',
            'summary_timezone' => '时区：%s',
        ],
        'zh_TW' => [
            'skip'             => '非交互模式，跳過安裝嚮導。',
            'default_choice'   => ' (預設 %s)',
            'timezone_prompt'  => '時區 (預設 %s，輸入可聯想補全): ',
            'timezone_title'   => '時區設定 (預設 %s)',
            'timezone_help'    => '輸入關鍵字Tab自動補全，可↑↓上下選擇:',
            'timezone_region'  => '選擇時區區域',
            'timezone_city'    => '選擇時區',
            'timezone_invalid' => '無效的時區，已使用預設值 %s',
            'console_question' => '安裝命令列組件 webman/console',
            'db_question'      => '資料庫組件',
            'db_none'          => '不安裝',
            'db_invalid'       => '請輸入有效選項',
            'redis_question'   => '安裝 Redis 組件 webman/redis',
            'events_note'      => '  (Redis 依賴 illuminate/events，已自動包含)',
            'no_components'    => '未選擇額外組件。',
            'installing'       => '即將安裝：',
            'running'          => '執行：',
            'error_install'    => '安裝可選組件時出錯，請手動執行：composer require %s',
            'done'             => '可選組件安裝完成。',
            'summary_locale'   => '語言：%s',
            'summary_timezone' => '時區：%s',
        ],
        'en' => [
            'skip'             => 'Non-interactive mode, skipping setup wizard.',
            'default_choice'   => ' (default %s)',
            'timezone_prompt'  => 'Timezone (default=%s, type to autocomplete): ',
            'timezone_title'   => 'Timezone (default=%s)',
            'timezone_help'    => 'Type keyword then press Tab to autocomplete, use ↑↓ to choose:',
            'timezone_region'  => 'Select timezone region',
            'timezone_city'    => 'Select timezone',
            'timezone_invalid' => 'Invalid timezone, using default %s',
            'console_question' => 'Install console component webman/console',
            'db_question'      => 'Database component',
            'db_none'          => 'None',
            'db_invalid'       => 'Please enter a valid option',
            'redis_question'   => 'Install Redis component webman/redis',
            'events_note'      => '  (Redis requires illuminate/events, automatically included)',
            'no_components'    => 'No optional components selected.',
            'installing'       => 'Installing:',
            'running'          => 'Running:',
            'error_install'    => 'Failed to install. Try manually: composer require %s',
            'done'             => 'Optional components installed.',
            'summary_locale'   => 'Language: %s',
            'summary_timezone' => 'Timezone: %s',
        ],
        'ja' => [
            'skip'             => '非対話モードのため、セットアップウィザードをスキップします。',
            'default_choice'   => ' (デフォルト %s)',
            'timezone_prompt'  => 'タイムゾーン (デフォルト=%s、入力で補完): ',
            'timezone_title'   => 'タイムゾーン (デフォルト=%s)',
            'timezone_help'    => 'キーワード入力→Tabで補完、↑↓で選択:',
            'timezone_region'  => 'タイムゾーンの地域を選択',
            'timezone_city'    => 'タイムゾーンを選択',
            'timezone_invalid' => '無効なタイムゾーンです。デフォルト %s を使用します',
            'console_question' => 'コンソールコンポーネント webman/console をインストール',
            'db_question'      => 'データベースコンポーネント',
            'db_none'          => 'インストールしない',
            'db_invalid'       => '有効なオプションを入力してください',
            'redis_question'   => 'Redis コンポーネント webman/redis をインストール',
            'events_note'      => '  (Redis は illuminate/events が必要です。自動的に含まれます)',
            'no_components'    => 'オプションコンポーネントが選択されていません。',
            'installing'       => 'インストール中：',
            'running'          => '実行中：',
            'error_install'    => 'インストールに失敗しました。手動で実行してください：composer require %s',
            'done'             => 'オプションコンポーネントのインストールが完了しました。',
            'summary_locale'   => '言語：%s',
            'summary_timezone' => 'タイムゾーン：%s',
        ],
        'ko' => [
            'skip'             => '비대화형 모드입니다. 설치 마법사를 건너뜁니다.',
            'default_choice'   => ' (기본값 %s)',
            'timezone_prompt'  => '시간대 (기본값=%s, 입력하여 자동완성): ',
            'timezone_title'   => '시간대 (기본값=%s)',
            'timezone_help'    => '키워드 입력 후 Tab 자동완성, ↑↓로 선택:',
            'timezone_region'  => '시간대 지역 선택',
            'timezone_city'    => '시간대 선택',
            'timezone_invalid' => '잘못된 시간대입니다. 기본값 %s 을(를) 사용합니다',
            'console_question' => '콘솔 컴포넌트 webman/console 설치',
            'db_question'      => '데이터베이스 컴포넌트',
            'db_none'          => '설치 안 함',
            'db_invalid'       => '유효한 옵션을 입력하세요',
            'redis_question'   => 'Redis 컴포넌트 webman/redis 설치',
            'events_note'      => '  (Redis는 illuminate/events가 필요합니다. 자동으로 포함됩니다)',
            'no_components'    => '선택된 추가 컴포넌트가 없습니다.',
            'installing'       => '설치 예정：',
            'running'          => '실행 중：',
            'error_install'    => '설치에 실패했습니다. 수동으로 실행하세요: composer require %s',
            'done'             => '선택 컴포넌트 설치가 완료되었습니다.',
            'summary_locale'   => '언어: %s',
            'summary_timezone' => '시간대: %s',
        ],
        'fr' => [
            'skip'             => 'Mode non interactif, assistant d\'installation ignoré.',
            'default_choice'   => ' (défaut %s)',
            'timezone_prompt'  => 'Fuseau horaire (défaut=%s, tapez pour compléter) : ',
            'timezone_title'   => 'Fuseau horaire (défaut=%s)',
            'timezone_help'    => 'Tapez un mot-clé, Tab pour compléter, ↑↓ pour choisir :',
            'timezone_region'  => 'Sélectionnez la région du fuseau horaire',
            'timezone_city'    => 'Sélectionnez le fuseau horaire',
            'timezone_invalid' => 'Fuseau horaire invalide, utilisation de %s par défaut',
            'console_question' => 'Installer le composant console webman/console',
            'db_question'      => 'Composant base de données',
            'db_none'          => 'Aucun',
            'db_invalid'       => 'Veuillez entrer une option valide',
            'redis_question'   => 'Installer le composant Redis webman/redis',
            'events_note'      => '  (Redis nécessite illuminate/events, inclus automatiquement)',
            'no_components'    => 'Aucun composant optionnel sélectionné.',
            'installing'       => 'Installation en cours :',
            'running'          => 'Exécution :',
            'error_install'    => 'Échec de l\'installation. Essayez manuellement : composer require %s',
            'done'             => 'Composants optionnels installés.',
            'summary_locale'   => 'Langue : %s',
            'summary_timezone' => 'Fuseau horaire : %s',
        ],
        'de' => [
            'skip'             => 'Nicht-interaktiver Modus, Einrichtungsassistent übersprungen.',
            'default_choice'   => ' (Standard %s)',
            'timezone_prompt'  => 'Zeitzone (Standard=%s, Eingabe zur Vervollständigung): ',
            'timezone_title'   => 'Zeitzone (Standard=%s)',
            'timezone_help'    => 'Stichwort tippen, Tab ergänzt, ↑↓ auswählen:',
            'timezone_region'  => 'Zeitzone Region auswählen',
            'timezone_city'    => 'Zeitzone auswählen',
            'timezone_invalid' => 'Ungültige Zeitzone, Standardwert %s wird verwendet',
            'console_question' => 'Konsolen-Komponente webman/console installieren',
            'db_question'      => 'Datenbank-Komponente',
            'db_none'          => 'Keine',
            'db_invalid'       => 'Bitte geben Sie eine gültige Option ein',
            'redis_question'   => 'Redis-Komponente webman/redis installieren',
            'events_note'      => '  (Redis benötigt illuminate/events, automatisch eingeschlossen)',
            'no_components'    => 'Keine optionalen Komponenten ausgewählt.',
            'installing'       => 'Installation:',
            'running'          => 'Ausführung:',
            'error_install'    => 'Installation fehlgeschlagen. Manuell ausführen: composer require %s',
            'done'             => 'Optionale Komponenten installiert.',
            'summary_locale'   => 'Sprache: %s',
            'summary_timezone' => 'Zeitzone: %s',
        ],
        'es' => [
            'skip'             => 'Modo no interactivo, asistente de instalación omitido.',
            'default_choice'   => ' (predeterminado %s)',
            'timezone_prompt'  => 'Zona horaria (predeterminado=%s, escriba para autocompletar): ',
            'timezone_title'   => 'Zona horaria (predeterminado=%s)',
            'timezone_help'    => 'Escriba una palabra clave, Tab autocompleta, use ↑↓ para elegir:',
            'timezone_region'  => 'Seleccione la región de zona horaria',
            'timezone_city'    => 'Seleccione la zona horaria',
            'timezone_invalid' => 'Zona horaria inválida, usando valor predeterminado %s',
            'console_question' => 'Instalar componente de consola webman/console',
            'db_question'      => 'Componente de base de datos',
            'db_none'          => 'Ninguno',
            'db_invalid'       => 'Por favor ingrese una opción válida',
            'redis_question'   => 'Instalar componente Redis webman/redis',
            'events_note'      => '  (Redis requiere illuminate/events, incluido automáticamente)',
            'no_components'    => 'No se seleccionaron componentes opcionales.',
            'installing'       => 'Instalando:',
            'running'          => 'Ejecutando:',
            'error_install'    => 'Error en la instalación. Intente manualmente: composer require %s',
            'done'             => 'Componentes opcionales instalados.',
            'summary_locale'   => 'Idioma: %s',
            'summary_timezone' => 'Zona horaria: %s',
        ],
        'pt_BR' => [
            'skip'             => 'Modo não interativo, assistente de instalação ignorado.',
            'default_choice'   => ' (padrão %s)',
            'timezone_prompt'  => 'Fuso horário (padrão=%s, digite para autocompletar): ',
            'timezone_title'   => 'Fuso horário (padrão=%s)',
            'timezone_help'    => 'Digite uma palavra-chave, Tab autocompleta, use ↑↓ para escolher:',
            'timezone_region'  => 'Selecione a região do fuso horário',
            'timezone_city'    => 'Selecione o fuso horário',
            'timezone_invalid' => 'Fuso horário inválido, usando padrão %s',
            'console_question' => 'Instalar componente de console webman/console',
            'db_question'      => 'Componente de banco de dados',
            'db_none'          => 'Nenhum',
            'db_invalid'       => 'Por favor, digite uma opção válida',
            'redis_question'   => 'Instalar componente Redis webman/redis',
            'events_note'      => '  (Redis requer illuminate/events, incluído automaticamente)',
            'no_components'    => 'Nenhum componente opcional selecionado.',
            'installing'       => 'Instalando:',
            'running'          => 'Executando:',
            'error_install'    => 'Falha na instalação. Tente manualmente: composer require %s',
            'done'             => 'Componentes opcionais instalados.',
            'summary_locale'   => 'Idioma: %s',
            'summary_timezone' => 'Fuso horário: %s',
        ],
        'ru' => [
            'skip'             => 'Неинтерактивный режим, мастер установки пропущен.',
            'default_choice'   => ' (по умолчанию %s)',
            'timezone_prompt'  => 'Часовой пояс (по умолчанию=%s, введите для автодополнения): ',
            'timezone_title'   => 'Часовой пояс (по умолчанию=%s)',
            'timezone_help'    => 'Введите ключевое слово, Tab для автодополнения, ↑↓ для выбора:',
            'timezone_region'  => 'Выберите регион часового пояса',
            'timezone_city'    => 'Выберите часовой пояс',
            'timezone_invalid' => 'Неверный часовой пояс, используется значение по умолчанию %s',
            'console_question' => 'Установить консольный компонент webman/console',
            'db_question'      => 'Компонент базы данных',
            'db_none'          => 'Не устанавливать',
            'db_invalid'       => 'Пожалуйста, введите допустимый вариант',
            'redis_question'   => 'Установить компонент Redis webman/redis',
            'events_note'      => '  (Redis требует illuminate/events, автоматически включён)',
            'no_components'    => 'Дополнительные компоненты не выбраны.',
            'installing'       => 'Установка:',
            'running'          => 'Выполнение:',
            'error_install'    => 'Ошибка установки. Выполните вручную: composer require %s',
            'done'             => 'Дополнительные компоненты установлены.',
            'summary_locale'   => 'Язык: %s',
            'summary_timezone' => 'Часовой пояс: %s',
        ],
        'vi' => [
            'skip'             => 'Chế độ không tương tác, bỏ qua trình hướng dẫn cài đặt.',
            'default_choice'   => ' (mặc định %s)',
            'timezone_prompt'  => 'Múi giờ (mặc định=%s, nhập để tự động hoàn thành): ',
            'timezone_title'   => 'Múi giờ (mặc định=%s)',
            'timezone_help'    => 'Nhập từ khóa, Tab để tự hoàn thành, dùng ↑↓ để chọn:',
            'timezone_region'  => 'Chọn khu vực múi giờ',
            'timezone_city'    => 'Chọn múi giờ',
            'timezone_invalid' => 'Múi giờ không hợp lệ, sử dụng mặc định %s',
            'console_question' => 'Cài đặt thành phần console webman/console',
            'db_question'      => 'Thành phần cơ sở dữ liệu',
            'db_none'          => 'Không cài đặt',
            'db_invalid'       => 'Vui lòng nhập tùy chọn hợp lệ',
            'redis_question'   => 'Cài đặt thành phần Redis webman/redis',
            'events_note'      => '  (Redis cần illuminate/events, đã tự động bao gồm)',
            'no_components'    => 'Không có thành phần tùy chọn nào được chọn.',
            'installing'       => 'Đang cài đặt:',
            'running'          => 'Đang thực thi:',
            'error_install'    => 'Cài đặt thất bại. Thử thủ công: composer require %s',
            'done'             => 'Các thành phần tùy chọn đã được cài đặt.',
            'summary_locale'   => 'Ngôn ngữ: %s',
            'summary_timezone' => 'Múi giờ: %s',
        ],
        'tr' => [
            'skip'             => 'Etkileşimsiz mod, kurulum sihirbazı atlanıyor.',
            'default_choice'   => ' (varsayılan %s)',
            'timezone_prompt'  => 'Saat dilimi (varsayılan=%s, otomatik tamamlama için yazın): ',
            'timezone_title'   => 'Saat dilimi (varsayılan=%s)',
            'timezone_help'    => 'Anahtar kelime yazın, Tab tamamlar, ↑↓ ile seçin:',
            'timezone_region'  => 'Saat dilimi bölgesini seçin',
            'timezone_city'    => 'Saat dilimini seçin',
            'timezone_invalid' => 'Geçersiz saat dilimi, varsayılan %s kullanılıyor',
            'console_question' => 'Konsol bileşeni webman/console yüklensin mi',
            'db_question'      => 'Veritabanı bileşeni',
            'db_none'          => 'Yok',
            'db_invalid'       => 'Lütfen geçerli bir seçenek girin',
            'redis_question'   => 'Redis bileşeni webman/redis yüklensin mi',
            'events_note'      => '  (Redis, illuminate/events gerektirir, otomatik olarak dahil edildi)',
            'no_components'    => 'İsteğe bağlı bileşen seçilmedi.',
            'installing'       => 'Yükleniyor:',
            'running'          => 'Çalıştırılıyor:',
            'error_install'    => 'Yükleme başarısız. Manuel olarak deneyin: composer require %s',
            'done'             => 'İsteğe bağlı bileşenler yüklendi.',
            'summary_locale'   => 'Dil: %s',
            'summary_timezone' => 'Saat dilimi: %s',
        ],
        'id' => [
            'skip'             => 'Mode non-interaktif, melewati wizard instalasi.',
            'default_choice'   => ' (default %s)',
            'timezone_prompt'  => 'Zona waktu (default=%s, ketik untuk melengkapi): ',
            'timezone_title'   => 'Zona waktu (default=%s)',
            'timezone_help'    => 'Ketik kata kunci, Tab untuk melengkapi, gunakan ↑↓ untuk memilih:',
            'timezone_region'  => 'Pilih wilayah zona waktu',
            'timezone_city'    => 'Pilih zona waktu',
            'timezone_invalid' => 'Zona waktu tidak valid, menggunakan default %s',
            'console_question' => 'Instal komponen konsol webman/console',
            'db_question'      => 'Komponen database',
            'db_none'          => 'Tidak ada',
            'db_invalid'       => 'Silakan masukkan opsi yang valid',
            'redis_question'   => 'Instal komponen Redis webman/redis',
            'events_note'      => '  (Redis memerlukan illuminate/events, otomatis disertakan)',
            'no_components'    => 'Tidak ada komponen opsional yang dipilih.',
            'installing'       => 'Menginstal:',
            'running'          => 'Menjalankan:',
            'error_install'    => 'Instalasi gagal. Coba manual: composer require %s',
            'done'             => 'Komponen opsional terinstal.',
            'summary_locale'   => 'Bahasa: %s',
            'summary_timezone' => 'Zona waktu: %s',
        ],
        'th' => [
            'skip'             => 'โหมดไม่โต้ตอบ ข้ามตัวช่วยติดตั้ง',
            'default_choice'   => ' (ค่าเริ่มต้น %s)',
            'timezone_prompt'  => 'เขตเวลา (ค่าเริ่มต้น=%s พิมพ์เพื่อเติมอัตโนมัติ): ',
            'timezone_title'   => 'เขตเวลา (ค่าเริ่มต้น=%s)',
            'timezone_help'    => 'พิมพ์คีย์เวิร์ดแล้วกด Tab เพื่อเติมอัตโนมัติ ใช้ ↑↓ เพื่อเลือก:',
            'timezone_region'  => 'เลือกภูมิภาคเขตเวลา',
            'timezone_city'    => 'เลือกเขตเวลา',
            'timezone_invalid' => 'เขตเวลาไม่ถูกต้อง ใช้ค่าเริ่มต้น %s',
            'console_question' => 'ติดตั้งคอมโพเนนต์คอนโซล webman/console',
            'db_question'      => 'คอมโพเนนต์ฐานข้อมูล',
            'db_none'          => 'ไม่ติดตั้ง',
            'db_invalid'       => 'กรุณาป้อนตัวเลือกที่ถูกต้อง',
            'redis_question'   => 'ติดตั้งคอมโพเนนต์ Redis webman/redis',
            'events_note'      => '  (Redis ต้องการ illuminate/events รวมไว้โดยอัตโนมัติ)',
            'no_components'    => 'ไม่ได้เลือกคอมโพเนนต์เสริม',
            'installing'       => 'กำลังติดตั้ง:',
            'running'          => 'กำลังดำเนินการ:',
            'error_install'    => 'ติดตั้งล้มเหลว ลองด้วยตนเอง: composer require %s',
            'done'             => 'คอมโพเนนต์เสริมติดตั้งเรียบร้อยแล้ว',
            'summary_locale'   => 'ภาษา: %s',
            'summary_timezone' => 'เขตเวลา: %s',
        ],
    ];

    // --- Interrupt message (Ctrl+C) ---

    private const INTERRUPTED_MESSAGES = [
        'zh_CN' => '安装中断，可运行 composer setup-webman 可重新设置。',
        'zh_TW' => '安裝中斷，可運行 composer setup-webman 重新設置。',
        'en'    => 'Setup interrupted. Run "composer setup-webman" to restart setup.',
        'ja'    => 'セットアップが中断されました。composer setup-webman を実行して再設定できます。',
        'ko'    => '설치가 중단되었습니다. composer setup-webman 을 실행하여 다시 설정할 수 있습니다.',
        'fr'    => 'Installation interrompue. Exécutez « composer setup-webman » pour recommencer.',
        'de'    => 'Einrichtung abgebrochen. Führen Sie "composer setup-webman" aus, um neu zu starten.',
        'es'    => 'Instalación interrumpida. Ejecute "composer setup-webman" para reiniciar.',
        'pt_BR' => 'Instalação interrompida. Execute "composer setup-webman" para reiniciar.',
        'ru'    => 'Установка прервана. Выполните «composer setup-webman» для повторной настройки.',
        'vi'    => 'Cài đặt bị gián đoạn. Chạy "composer setup-webman" để cài đặt lại.',
        'tr'    => 'Kurulum kesildi. Yeniden kurmak için "composer setup-webman" komutunu çalıştırın.',
        'id'    => 'Instalasi terganggu. Jalankan "composer setup-webman" untuk mengatur ulang.',
        'th'    => 'การติดตั้งถูกขัดจังหวะ เรียกใช้ "composer setup-webman" เพื่อตั้งค่าใหม่',
    ];

    // --- Signal handling state ---

    /** @var string|null Saved stty mode for terminal restoration on interrupt */
    private static ?string $sttyMode = null;

    /** @var string Current locale for interrupt message */
    private static string $interruptLocale = 'en';

    // ═══════════════════════════════════════════════════════════════
    // Entry
    // ═══════════════════════════════════════════════════════════════

    public static function run(Event $event): void
    {
        $io = $event->getIO();

        // Non-interactive mode: use English for skip message
        if (!$io->isInteractive()) {
            $io->write('<comment>' . self::MESSAGES['en']['skip'] . '</comment>');
            return;
        }

        $io->write('');

        // Register Ctrl+C handler
        self::registerInterruptHandler();

        // Banner title (must be before locale selection)
        self::renderTitle();

        // 1. Locale selection
        $locale = self::askLocale($io);
        self::$interruptLocale = $locale;
        $defaultTimezone = self::LOCALE_DEFAULT_TIMEZONES[$locale] ?? 'UTC';
        $msg = fn(string $key, string ...$args): string =>
        empty($args) ? self::MESSAGES[$locale][$key] : sprintf(self::MESSAGES[$locale][$key], ...$args);

        // Write locale config (update when not default)
        if ($locale !== 'zh_CN') {
            self::updateConfig($event, 'config/translation.php', "'locale'", $locale);
        }

        $io->write('');
        $io->write('');

        // 2. Timezone selection (default by locale)
        $timezone = self::askTimezone($io, $msg, $defaultTimezone);
        if ($timezone !== 'Asia/Shanghai') {
            self::updateConfig($event, 'config/app.php', "'default_timezone'", $timezone);
        }

        // 3. Optional components
        $packages = self::askComponents($io, $msg);

        // 4. Summary
        $io->write('');
        $io->write('─────────────────────────────────────');
        $io->write('<info>' . $msg('summary_locale', self::LOCALE_LABELS[$locale]) . '</info>');
        $io->write('<info>' . $msg('summary_timezone', $timezone) . '</info>');

        if ($packages === []) {
            $io->write('<info>' . $msg('no_components') . '</info>');
            return;
        }

        $io->write('<info>' . $msg('installing') . '</info> ' . implode(', ', $packages));
        $io->write('');

        self::runComposerRequire($packages, $io, $msg);
    }

    private static function renderTitle(): void
    {
        $output = new ConsoleOutput();
        $terminalWidth = (new Terminal())->getWidth();
        if ($terminalWidth <= 0) {
            $terminalWidth = 80;
        }

        $text = ' ' . self::SETUP_TITLE . ' ';
        $minBoxWidth = 44;
        $maxBoxWidth = min($terminalWidth, 96);
        $boxWidth = min($maxBoxWidth, max($minBoxWidth, mb_strwidth($text) + 10));

        $innerWidth = $boxWidth - 2;
        $textWidth = mb_strwidth($text);
        $pad = max(0, $innerWidth - $textWidth);
        $left = intdiv($pad, 2);
        $right = $pad - $left;
        $line2 = '│' . str_repeat(' ', $left) . $text . str_repeat(' ', $right) . '│';
        $line1 = '┌' . str_repeat('─', $innerWidth) . '┐';
        $line3 = '└' . str_repeat('─', $innerWidth) . '┘';

        $output->writeln('');
        $output->writeln('<fg=bright-blue>' . $line1 . '</>');
        $output->writeln('<fg=bright-blue>' . $line2 . '</>');
        $output->writeln('<fg=bright-blue>' . $line3 . '</>');
        $output->writeln('');
    }

    // ═══════════════════════════════════════════════════════════════
    // Signal handling (Ctrl+C)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Register Ctrl+C (SIGINT) handler to show a friendly message on interrupt.
     * Gracefully skipped when the required extensions are unavailable.
     */
    private static function registerInterruptHandler(): void
    {
        // Unix/Linux/Mac: pcntl extension with async signals for immediate delivery
        /*if (function_exists('pcntl_async_signals') && function_exists('pcntl_signal')) {
            pcntl_async_signals(true);
            pcntl_signal(\SIGINT, [self::class, 'handleInterrupt']);
            return;
        }*/

        // Windows: sapi ctrl handler (PHP >= 7.4)
        if (function_exists('sapi_windows_set_ctrl_handler')) {
            sapi_windows_set_ctrl_handler(static function (int $event) {
                if ($event === \PHP_WINDOWS_EVENT_CTRL_C) {
                    self::handleInterrupt();
                }
            });
        }
    }

    /**
     * Handle Ctrl+C: restore terminal, show tip, then exit.
     */
    private static function handleInterrupt(): void
    {
        // Restore terminal if in raw mode
        if (self::$sttyMode !== null) {
            @shell_exec('stty ' . self::$sttyMode);
            self::$sttyMode = null;
        }

        $output = new ConsoleOutput();
        $output->writeln('');
        $output->writeln('<comment>' . (self::INTERRUPTED_MESSAGES[self::$interruptLocale] ?? self::INTERRUPTED_MESSAGES['en']) . '</comment>');
        exit(1);
    }

    // ═══════════════════════════════════════════════════════════════
    // Interactive Menu System
    // ═══════════════════════════════════════════════════════════════

    /**
     * Check if terminal supports interactive features (arrow keys, ANSI colors).
     */
    private static function supportsInteractive(): bool
    {
        return Terminal::hasSttyAvailable();
    }

    /**
     * Display a selection menu with arrow key navigation (if supported) or text input fallback.
     *
     * @param IOInterface $io   Composer IO
     * @param string      $title Menu title
     * @param array       $items Indexed array of ['tag' => string, 'label' => string]
     * @param int         $default Default selected index (0-based)
     * @return int Selected index
     */
    private static function selectMenu(IOInterface $io, string $title, array $items, int $default = 0): int
    {
        // Append localized "default" hint to avoid ambiguity
        // (Template should contain a single %s placeholder for the default tag.)
        $defaultHintTemplate = null;
        if (isset(self::MESSAGES[self::$interruptLocale]['default_choice'])) {
            $defaultHintTemplate = self::MESSAGES[self::$interruptLocale]['default_choice'];
        }

        $defaultTag = $items[$default]['tag'] ?? '';
        if ($defaultHintTemplate && $defaultTag !== '') {
            $title .= sprintf($defaultHintTemplate, $defaultTag);
        } elseif ($defaultTag !== '') {
            // Fallback for early menus (e.g. locale selection) before locale is chosen.
            $title .= sprintf(' (default %s)', $defaultTag);
        }

        if (self::supportsInteractive()) {
            return self::arrowKeySelect($title, $items, $default);
        }

        return self::fallbackSelect($io, $title, $items, $default);
    }

    /**
     * Display a yes/no confirmation as a selection menu.
     *
     * @param IOInterface $io    Composer IO
     * @param string      $title Menu title
     * @param bool        $default Default value (true = yes)
     * @return bool User's choice
     */
    private static function confirmMenu(IOInterface $io, string $title, bool $default = true): bool
    {
        $items = $default
            ? [['tag' => 'Y', 'label' => 'yes'], ['tag' => 'n', 'label' => 'no']]
            : [['tag' => 'y', 'label' => 'yes'], ['tag' => 'N', 'label' => 'no']];
        $defaultIndex = $default ? 0 : 1;

        return self::selectMenu($io, $title, $items, $defaultIndex) === 0;
    }

    /**
     * Interactive select with arrow key navigation, manual input and ANSI reverse-video highlighting.
     * Input area and option list highlighting are bidirectionally linked.
     * Requires stty (Unix-like terminals).
     */
    private static function arrowKeySelect(string $title, array $items, int $default): int
    {
        $output = new ConsoleOutput();
        $count = count($items);
        $selected = $default;

        $maxTagWidth = max(array_map(fn(array $item) => mb_strlen($item['tag']), $items));
        $defaultTag = $items[$default]['tag'];
        $input = $defaultTag;

        // Print title and initial options
        $output->writeln('');
        $output->writeln('<fg=bright-blue>' . $title . '</>');
        self::drawMenuItems($output, $items, $selected, $maxTagWidth);
        $output->write('> ' . $input);

        // Enter raw mode
        self::$sttyMode = shell_exec('stty -g');
        shell_exec('stty -icanon -echo');

        try {
            while (!feof(STDIN)) {
                $c = fread(STDIN, 1);

                if (false === $c || '' === $c) {
                    break;
                }

                // ── Backspace ──
                if ("\177" === $c || "\010" === $c) {
                    if ('' !== $input) {
                        $input = mb_substr($input, 0, -1);
                    }
                    $selected = self::findItemByTag($items, $input);
                    $output->write("\033[{$count}A");
                    self::drawMenuItems($output, $items, $selected, $maxTagWidth);
                    $output->write("\033[2K\r> " . $input);
                    continue;
                }

                // ── Escape sequences (arrow keys) ──
                if ("\033" === $c) {
                    $seq = fread(STDIN, 2);
                    if (isset($seq[1])) {
                        $changed = false;
                        if ('A' === $seq[1]) { // Up
                            $selected = ($selected <= 0 ? $count : $selected) - 1;
                            $changed = true;
                        } elseif ('B' === $seq[1]) { // Down
                            $selected = ($selected + 1) % $count;
                            $changed = true;
                        }
                        if ($changed) {
                            // Sync input with selected item's tag
                            $input = $items[$selected]['tag'];
                            $output->write("\033[{$count}A");
                            self::drawMenuItems($output, $items, $selected, $maxTagWidth);
                            $output->write("\033[2K\r> " . $input);
                        }
                    }
                    continue;
                }

                // ── Enter: confirm selection ──
                if ("\n" === $c || "\r" === $c) {
                    if ($selected < 0) {
                        $selected = $default;
                    }
                    $output->write("\033[2K\r> <comment>" . $items[$selected]['tag'] . '</comment>');
                    $output->writeln('');
                    break;
                }

                // ── Ignore other control characters ──
                if (ord($c) < 32) {
                    continue;
                }

                // ── Printable character (with UTF-8 multi-byte support) ──
                if ("\x80" <= $c) {
                    $extra = ["\xC0" => 1, "\xD0" => 1, "\xE0" => 2, "\xF0" => 3];
                    $c .= fread(STDIN, $extra[$c & "\xF0"] ?? 0);
                }
                $input .= $c;
                $selected = self::findItemByTag($items, $input);
                $output->write("\033[{$count}A");
                self::drawMenuItems($output, $items, $selected, $maxTagWidth);
                $output->write("\033[2K\r> " . $input);
            }
        } finally {
            if (self::$sttyMode !== null) {
                shell_exec('stty ' . self::$sttyMode);
                self::$sttyMode = null;
            }
        }

        return $selected < 0 ? $default : $selected;
    }

    /**
     * Fallback select for terminals without stty support. Uses plain text input.
     */
    private static function fallbackSelect(IOInterface $io, string $title, array $items, int $default): int
    {
        $maxTagWidth = max(array_map(fn(array $item) => mb_strlen($item['tag']), $items));
        $defaultTag = $items[$default]['tag'];

        $io->write('');
        $io->write('<fg=bright-blue>' . $title . '</>');
        foreach ($items as $item) {
            $tag = str_pad($item['tag'], $maxTagWidth);
            $io->write("  [$tag] " . $item['label']);
        }

        while (true) {
            $io->write('> ', false);
            $line = fgets(STDIN);
            if ($line === false) {
                return $default;
            }
            $answer = trim($line);

            if ($answer === '') {
                return $default;
            }

            // Match by tag (case-insensitive)
            foreach ($items as $i => $item) {
                if (strcasecmp($item['tag'], $answer) === 0) {
                    return $i;
                }
            }
        }
    }

    /**
     * Render menu items with optional ANSI reverse-video highlighting for the selected item.
     * When $selected is -1, no item is highlighted.
     */
    private static function drawMenuItems(ConsoleOutput $output, array $items, int $selected, int $maxTagWidth): void
    {
        foreach ($items as $i => $item) {
            $tag = str_pad($item['tag'], $maxTagWidth);
            $line = "  [$tag] " . $item['label'];
            if ($i === $selected) {
                $output->writeln("\033[2K\r\033[7m" . $line . "\033[0m");
            } else {
                $output->writeln("\033[2K\r" . $line);
            }
        }
    }

    /**
     * Find item index by tag (case-insensitive exact match).
     * Returns -1 if no match found or input is empty.
     */
    private static function findItemByTag(array $items, string $input): int
    {
        if ($input === '') {
            return -1;
        }
        foreach ($items as $i => $item) {
            if (strcasecmp($item['tag'], $input) === 0) {
                return $i;
            }
        }
        return -1;
    }

    // ═══════════════════════════════════════════════════════════════
    // Locale selection
    // ═══════════════════════════════════════════════════════════════

    private static function askLocale(IOInterface $io): string
    {
        $locales = array_keys(self::LOCALE_LABELS);
        $items = [];
        foreach ($locales as $i => $code) {
            $items[] = ['tag' => (string) $i, 'label' => self::LOCALE_LABELS[$code] . " ($code)"];
        }

        $selected = self::selectMenu(
            $io,
            '语言 / Language / 言語 / 언어',
            $items,
            0
        );

        return $locales[$selected];
    }

    // ═══════════════════════════════════════════════════════════════
    // Timezone selection
    // ═══════════════════════════════════════════════════════════════

    private static function askTimezone(IOInterface $io, callable $msg, string $default): string
    {
        if (Terminal::hasSttyAvailable()) {
            return self::askTimezoneAutocomplete($msg, $default);
        }

        return self::askTimezoneSelect($io, $msg, $default);
    }

    /**
     * Option A: when stty is available, custom character-by-character autocomplete
     * (case-insensitive, substring match). Interaction: type to filter, hint on right;
     * ↑↓ change candidate, Tab accept, Enter confirm; empty input = use default.
     */
    private static function askTimezoneAutocomplete(callable $msg, string $default): string
    {
        $allTimezones = \DateTimeZone::listIdentifiers();
        $output = new ConsoleOutput();
        $cursor = new Cursor($output);

        $output->writeln('');
        $output->writeln('<fg=bright-blue>' . $msg('timezone_title', $default) . '</>');
        $output->writeln($msg('timezone_help'));
        $output->write('> ');

        self::$sttyMode = shell_exec('stty -g');
        shell_exec('stty -icanon -echo');

        // Auto-fill default timezone in the input area; user can edit it directly.
        $input = $default;
        $output->write($input);

        $ofs = 0;
        $matches = self::filterTimezones($allTimezones, $input);
        if (!empty($matches)) {
            $hint = $matches[$ofs % count($matches)];
            // Avoid duplicating hint when input already fully matches the only candidate.
            if (!(count($matches) === 1 && $hint === $input)) {
                $cursor->clearLineAfter();
                $cursor->savePosition();
                $output->write('  <fg=bright-blue>' . $hint . '</>');
                if (count($matches) > 1) {
                    $output->write('  <info>(' . count($matches) . ' matches, ↑↓)</info>');
                }
                $cursor->restorePosition();
            }
        }

        try {
            while (!feof(STDIN)) {
                $c = fread(STDIN, 1);

                if (false === $c || '' === $c) {
                    break;
                }

                // ── Backspace ──
                if ("\177" === $c || "\010" === $c) {
                    if ('' !== $input) {
                        $lastChar = mb_substr($input, -1);
                        $input = mb_substr($input, 0, -1);
                        $cursor->moveLeft(max(1, mb_strwidth($lastChar)));
                    }
                    $ofs = 0;

                    // ── Escape sequences (arrows) ──
                } elseif ("\033" === $c) {
                    $seq = fread(STDIN, 2);
                    if (isset($seq[1]) && !empty($matches)) {
                        if ('A' === $seq[1]) {
                            $ofs = ($ofs - 1 + count($matches)) % count($matches);
                        } elseif ('B' === $seq[1]) {
                            $ofs = ($ofs + 1) % count($matches);
                        }
                    }

                    // ── Tab: accept current match ──
                } elseif ("\t" === $c) {
                    if (isset($matches[$ofs])) {
                        self::replaceInput($output, $cursor, $input, $matches[$ofs]);
                        $input   = $matches[$ofs];
                        $matches = [];
                    }
                    $cursor->clearLineAfter();
                    continue;

                    // ── Enter: confirm ──
                } elseif ("\n" === $c || "\r" === $c) {
                    if (isset($matches[$ofs])) {
                        self::replaceInput($output, $cursor, $input, $matches[$ofs]);
                        $input = $matches[$ofs];
                    }
                    // Re-render user input with <comment> style
                    $cursor->moveToColumn(1);
                    $cursor->clearLine();
                    $output->write('> <comment>' . $input . '</comment>');
                    $output->writeln('');
                    break;

                    // ── Other control chars: ignore ──
                } elseif (ord($c) < 32) {
                    continue;

                    // ── Printable character ──
                } else {
                    if ("\x80" <= $c) {
                        $extra = ["\xC0" => 1, "\xD0" => 1, "\xE0" => 2, "\xF0" => 3];
                        $c .= fread(STDIN, $extra[$c & "\xF0"] ?? 0);
                    }
                    $output->write($c);
                    $input .= $c;
                    $ofs = 0;
                }

                // Update match list
                $matches = self::filterTimezones($allTimezones, $input);

                // Show autocomplete hint
                $cursor->clearLineAfter();
                if (!empty($matches)) {
                    $hint = $matches[$ofs % count($matches)];
                    $cursor->savePosition();
                    $output->write('  <fg=bright-blue>' . $hint . '</>');
                    if (count($matches) > 1) {
                        $output->write('  <info>(' . count($matches) . ' matches, ↑↓)</info>');
                    }
                    $cursor->restorePosition();
                }
            }
        } finally {
            if (self::$sttyMode !== null) {
                shell_exec('stty ' . self::$sttyMode);
                self::$sttyMode = null;
            }
        }

        $result = '' === $input ? $default : $input;

        if (!in_array($result, $allTimezones, true)) {
            $output->writeln('<comment>' . $msg('timezone_invalid', $default) . '</comment>');
            return $default;
        }

        return $result;
    }

    /**
     * Clear current input and replace with new text.
     */
    private static function replaceInput(ConsoleOutput $output, Cursor $cursor, string $oldInput, string $newInput): void
    {
        if ('' !== $oldInput) {
            $cursor->moveLeft(mb_strwidth($oldInput));
        }
        $cursor->clearLineAfter();
        $output->write($newInput);
    }

    /**
     * Case-insensitive substring match for timezones.
     */
    private static function filterTimezones(array $timezones, string $input): array
    {
        if ('' === $input) {
            return [];
        }
        $lower = mb_strtolower($input);
        return array_values(array_filter(
            $timezones,
            fn(string $tz) => str_contains(mb_strtolower($tz), $lower)
        ));
    }

    /**
     * Option B: when stty is not available (e.g. Windows), two-step select: region then city.
     */
    private static function askTimezoneSelect(IOInterface $io, callable $msg, string $default): string
    {
        // Step 1: Select region
        $regionNames = array_keys(self::TIMEZONE_REGIONS);
        $defaultRegion = explode('/', $default)[0];

        $regionItems = [];
        $defaultRegionIndex = 0;
        foreach ($regionNames as $i => $name) {
            $regionItems[] = ['tag' => (string) $i, 'label' => $name];
            if ($name === $defaultRegion) {
                $defaultRegionIndex = $i;
            }
        }

        $regionIndex = self::selectMenu($io, $msg('timezone_region'), $regionItems, $defaultRegionIndex);

        $selectedRegion = $regionNames[$regionIndex];
        $regionConst = self::TIMEZONE_REGIONS[$selectedRegion];

        // Step 2: Select timezone
        $timezones = \DateTimeZone::listIdentifiers($regionConst);

        $tzItems = [];
        $defaultTzIndex = 0;
        foreach ($timezones as $i => $tz) {
            $tzItems[] = ['tag' => (string) $i, 'label' => $tz];
            if ($tz === $default) {
                $defaultTzIndex = $i;
            }
        }

        $tzIndex = self::selectMenu($io, $msg('timezone_city'), $tzItems, $defaultTzIndex);

        return $timezones[$tzIndex];
    }

    // ═══════════════════════════════════════════════════════════════
    // Optional component selection
    // ═══════════════════════════════════════════════════════════════

    private static function askComponents(IOInterface $io, callable $msg): array
    {
        $packages = [];

        // Console (default: yes)
        if (self::confirmMenu($io, $msg('console_question'), true)) {
            $packages[] = self::PACKAGE_CONSOLE;
        }

        // Database
        $dbItems = [
            ['tag' => '0', 'label' => $msg('db_none')],
            ['tag' => '1', 'label' => 'webman/database'],
            ['tag' => '2', 'label' => 'webman/think-orm'],
        ];
        $dbChoice = self::selectMenu($io, $msg('db_question'), $dbItems, 0);
        if ($dbChoice === 1) {
            $packages[] = self::PACKAGE_DATABASE;
        } elseif ($dbChoice === 2) {
            $packages[] = self::PACKAGE_THINK_ORM;
        }

        // Redis (default: no)
        if (self::confirmMenu($io, $msg('redis_question'), false)) {
            $packages[] = self::PACKAGE_REDIS;
            $packages[] = self::PACKAGE_ILLUMINATE_EVENTS;
            $io->write('<comment>' . $msg('events_note') . '</comment>');
        }

        return $packages;
    }

    // ═══════════════════════════════════════════════════════════════
    // Config file update
    // ═══════════════════════════════════════════════════════════════

    /**
     * Update a config value like 'key' => 'old_value' in the given file.
     */
    private static function updateConfig(Event $event, string $relativePath, string $key, string $newValue): void
    {
        $root = dirname($event->getComposer()->getConfig()->get('vendor-dir'));
        $file = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        if (!is_readable($file)) {
            return;
        }
        $content = file_get_contents($file);
        if ($content === false) {
            return;
        }
        $pattern = '/' . preg_quote($key, '/') . "\s*=>\s*'[^']*'/";
        $replacement = $key . " => '" . $newValue . "'";
        $newContent = preg_replace($pattern, $replacement, $content);
        if ($newContent !== null && $newContent !== $content) {
            file_put_contents($file, $newContent);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // Composer require
    // ═══════════════════════════════════════════════════════════════

    private static function runComposerRequire(array $packages, IOInterface $io, callable $msg): void
    {
        $escaped = array_map('escapeshellarg', $packages);
        $cmd = 'composer require ' . implode(' ', $escaped) . ' --no-interaction';
        $io->write('<comment>' . $msg('running') . '</comment> ' . $cmd);
        $io->write('');

        $code = 0;
        passthru($cmd, $code);

        if ($code !== 0) {
            $io->writeError('<error>' . $msg('error_install', implode(' ', $packages)) . '</error>');
        } else {
            $io->write('<info>' . $msg('done') . '</info>');
        }
    }
}

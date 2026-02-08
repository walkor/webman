<?php

declare(strict_types=1);

namespace support;

use Composer\IO\IOInterface;
use Composer\Script\Event;
use Symfony\Component\Console\Cursor;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Terminal;

/**
 * create-project 安装向导：交互选择语言、时区与可选组件，并执行 composer require
 */
class InstallWizard
{
    // ─── 可选组件包名 ───────────────────────────────────────────

    private const PACKAGE_CONSOLE           = 'webman/console';
    private const PACKAGE_DATABASE          = 'webman/database';
    private const PACKAGE_THINK_ORM         = 'webman/think-orm';
    private const PACKAGE_REDIS             = 'webman/redis';
    private const PACKAGE_ILLUMINATE_EVENTS = 'illuminate/events';

    // ─── 时区区域 ───────────────────────────────────────────────

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

    // ─── 语言 → 推荐默认时区 ────────────────────────────────────

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

    // ─── 语言选项（本地化显示名） ───────────────────────────────

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

    // ─── 多语言消息（%s 为动态占位符） ──────────────────────────

    private const MESSAGES = [
        'zh_CN' => [
            'skip'             => '非交互模式，跳过安装向导。',
            'title'            => '===== Webman 安装向导 =====',
            'timezone_prompt'  => '时区 (回车=%s，输入可联想补全): ',
            'timezone_region'  => '选择时区区域',
            'timezone_city'    => '选择时区',
            'timezone_invalid' => '无效的时区，已使用默认值 %s',
            'console_question' => '安装命令行组件 webman/console [Y/n] (回车=Y): ',
            'db_question'      => '数据库组件',
            'db_none'          => '不安装',
            'db_invalid'       => '请输入有效选项',
            'redis_question'   => '安装 Redis 组件 webman/redis [y/N] (回车=N): ',
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
            'title'            => '===== Webman 安裝嚮導 =====',
            'timezone_prompt'  => '時區 (回車=%s，輸入可聯想補全): ',
            'timezone_region'  => '選擇時區區域',
            'timezone_city'    => '選擇時區',
            'timezone_invalid' => '無效的時區，已使用預設值 %s',
            'console_question' => '安裝命令列組件 webman/console [Y/n] (回車=Y): ',
            'db_question'      => '資料庫組件',
            'db_none'          => '不安裝',
            'db_invalid'       => '請輸入有效選項',
            'redis_question'   => '安裝 Redis 組件 webman/redis [y/N] (回車=N): ',
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
            'title'            => '===== Webman Setup Wizard =====',
            'timezone_prompt'  => 'Timezone (default=%s, type to autocomplete): ',
            'timezone_region'  => 'Select timezone region',
            'timezone_city'    => 'Select timezone',
            'timezone_invalid' => 'Invalid timezone, using default %s',
            'console_question' => 'Install console component webman/console [Y/n] (default=Y): ',
            'db_question'      => 'Database component',
            'db_none'          => 'None',
            'db_invalid'       => 'Please enter a valid option',
            'redis_question'   => 'Install Redis component webman/redis [y/N] (default=N): ',
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
            'title'            => '===== Webman セットアップウィザード =====',
            'timezone_prompt'  => 'タイムゾーン (デフォルト=%s、入力で補完): ',
            'timezone_region'  => 'タイムゾーンの地域を選択',
            'timezone_city'    => 'タイムゾーンを選択',
            'timezone_invalid' => '無効なタイムゾーンです。デフォルト %s を使用します',
            'console_question' => 'コンソールコンポーネント webman/console をインストール [Y/n] (デフォルト=Y): ',
            'db_question'      => 'データベースコンポーネント',
            'db_none'          => 'インストールしない',
            'db_invalid'       => '有効なオプションを入力してください',
            'redis_question'   => 'Redis コンポーネント webman/redis をインストール [y/N] (デフォルト=N): ',
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
            'title'            => '===== Webman 설치 마법사 =====',
            'timezone_prompt'  => '시간대 (기본값=%s, 입력하여 자동완성): ',
            'timezone_region'  => '시간대 지역 선택',
            'timezone_city'    => '시간대 선택',
            'timezone_invalid' => '잘못된 시간대입니다. 기본값 %s 을(를) 사용합니다',
            'console_question' => '콘솔 컴포넌트 webman/console 설치 [Y/n] (기본값=Y): ',
            'db_question'      => '데이터베이스 컴포넌트',
            'db_none'          => '설치 안 함',
            'db_invalid'       => '유효한 옵션을 입력하세요',
            'redis_question'   => 'Redis 컴포넌트 webman/redis 설치 [y/N] (기본값=N): ',
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
            'title'            => '===== Assistant d\'installation Webman =====',
            'timezone_prompt'  => 'Fuseau horaire (défaut=%s, tapez pour compléter) : ',
            'timezone_region'  => 'Sélectionnez la région du fuseau horaire',
            'timezone_city'    => 'Sélectionnez le fuseau horaire',
            'timezone_invalid' => 'Fuseau horaire invalide, utilisation de %s par défaut',
            'console_question' => 'Installer le composant console webman/console [Y/n] (défaut=Y) : ',
            'db_question'      => 'Composant base de données',
            'db_none'          => 'Aucun',
            'db_invalid'       => 'Veuillez entrer une option valide',
            'redis_question'   => 'Installer le composant Redis webman/redis [y/N] (défaut=N) : ',
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
            'title'            => '===== Webman Einrichtungsassistent =====',
            'timezone_prompt'  => 'Zeitzone (Standard=%s, Eingabe zur Vervollständigung): ',
            'timezone_region'  => 'Zeitzone Region auswählen',
            'timezone_city'    => 'Zeitzone auswählen',
            'timezone_invalid' => 'Ungültige Zeitzone, Standardwert %s wird verwendet',
            'console_question' => 'Konsolen-Komponente webman/console installieren [Y/n] (Standard=Y): ',
            'db_question'      => 'Datenbank-Komponente',
            'db_none'          => 'Keine',
            'db_invalid'       => 'Bitte geben Sie eine gültige Option ein',
            'redis_question'   => 'Redis-Komponente webman/redis installieren [y/N] (Standard=N): ',
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
            'title'            => '===== Asistente de instalación de Webman =====',
            'timezone_prompt'  => 'Zona horaria (predeterminado=%s, escriba para autocompletar): ',
            'timezone_region'  => 'Seleccione la región de zona horaria',
            'timezone_city'    => 'Seleccione la zona horaria',
            'timezone_invalid' => 'Zona horaria inválida, usando valor predeterminado %s',
            'console_question' => 'Instalar componente de consola webman/console [Y/n] (predeterminado=Y): ',
            'db_question'      => 'Componente de base de datos',
            'db_none'          => 'Ninguno',
            'db_invalid'       => 'Por favor ingrese una opción válida',
            'redis_question'   => 'Instalar componente Redis webman/redis [y/N] (predeterminado=N): ',
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
            'title'            => '===== Assistente de Instalação Webman =====',
            'timezone_prompt'  => 'Fuso horário (padrão=%s, digite para autocompletar): ',
            'timezone_region'  => 'Selecione a região do fuso horário',
            'timezone_city'    => 'Selecione o fuso horário',
            'timezone_invalid' => 'Fuso horário inválido, usando padrão %s',
            'console_question' => 'Instalar componente de console webman/console [Y/n] (padrão=Y): ',
            'db_question'      => 'Componente de banco de dados',
            'db_none'          => 'Nenhum',
            'db_invalid'       => 'Por favor, digite uma opção válida',
            'redis_question'   => 'Instalar componente Redis webman/redis [y/N] (padrão=N): ',
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
            'title'            => '===== Мастер установки Webman =====',
            'timezone_prompt'  => 'Часовой пояс (по умолчанию=%s, введите для автодополнения): ',
            'timezone_region'  => 'Выберите регион часового пояса',
            'timezone_city'    => 'Выберите часовой пояс',
            'timezone_invalid' => 'Неверный часовой пояс, используется значение по умолчанию %s',
            'console_question' => 'Установить консольный компонент webman/console [Y/n] (по умолчанию=Y): ',
            'db_question'      => 'Компонент базы данных',
            'db_none'          => 'Не устанавливать',
            'db_invalid'       => 'Пожалуйста, введите допустимый вариант',
            'redis_question'   => 'Установить компонент Redis webman/redis [y/N] (по умолчанию=N): ',
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
            'title'            => '===== Trình hướng dẫn cài đặt Webman =====',
            'timezone_prompt'  => 'Múi giờ (mặc định=%s, nhập để tự động hoàn thành): ',
            'timezone_region'  => 'Chọn khu vực múi giờ',
            'timezone_city'    => 'Chọn múi giờ',
            'timezone_invalid' => 'Múi giờ không hợp lệ, sử dụng mặc định %s',
            'console_question' => 'Cài đặt thành phần console webman/console [Y/n] (mặc định=Y): ',
            'db_question'      => 'Thành phần cơ sở dữ liệu',
            'db_none'          => 'Không cài đặt',
            'db_invalid'       => 'Vui lòng nhập tùy chọn hợp lệ',
            'redis_question'   => 'Cài đặt thành phần Redis webman/redis [y/N] (mặc định=N): ',
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
            'title'            => '===== Webman Kurulum Sihirbazı =====',
            'timezone_prompt'  => 'Saat dilimi (varsayılan=%s, otomatik tamamlama için yazın): ',
            'timezone_region'  => 'Saat dilimi bölgesini seçin',
            'timezone_city'    => 'Saat dilimini seçin',
            'timezone_invalid' => 'Geçersiz saat dilimi, varsayılan %s kullanılıyor',
            'console_question' => 'Konsol bileşeni webman/console yüklensin mi [Y/n] (varsayılan=Y): ',
            'db_question'      => 'Veritabanı bileşeni',
            'db_none'          => 'Yok',
            'db_invalid'       => 'Lütfen geçerli bir seçenek girin',
            'redis_question'   => 'Redis bileşeni webman/redis yüklensin mi [y/N] (varsayılan=N): ',
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
            'title'            => '===== Wizard Instalasi Webman =====',
            'timezone_prompt'  => 'Zona waktu (default=%s, ketik untuk melengkapi): ',
            'timezone_region'  => 'Pilih wilayah zona waktu',
            'timezone_city'    => 'Pilih zona waktu',
            'timezone_invalid' => 'Zona waktu tidak valid, menggunakan default %s',
            'console_question' => 'Instal komponen konsol webman/console [Y/n] (default=Y): ',
            'db_question'      => 'Komponen database',
            'db_none'          => 'Tidak ada',
            'db_invalid'       => 'Silakan masukkan opsi yang valid',
            'redis_question'   => 'Instal komponen Redis webman/redis [y/N] (default=N): ',
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
            'title'            => '===== ตัวช่วยติดตั้ง Webman =====',
            'timezone_prompt'  => 'เขตเวลา (ค่าเริ่มต้น=%s พิมพ์เพื่อเติมอัตโนมัติ): ',
            'timezone_region'  => 'เลือกภูมิภาคเขตเวลา',
            'timezone_city'    => 'เลือกเขตเวลา',
            'timezone_invalid' => 'เขตเวลาไม่ถูกต้อง ใช้ค่าเริ่มต้น %s',
            'console_question' => 'ติดตั้งคอมโพเนนต์คอนโซล webman/console [Y/n] (ค่าเริ่มต้น=Y): ',
            'db_question'      => 'คอมโพเนนต์ฐานข้อมูล',
            'db_none'          => 'ไม่ติดตั้ง',
            'db_invalid'       => 'กรุณาป้อนตัวเลือกที่ถูกต้อง',
            'redis_question'   => 'ติดตั้งคอมโพเนนต์ Redis webman/redis [y/N] (ค่าเริ่มต้น=N): ',
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

    // ═══════════════════════════════════════════════════════════════
    // 入口
    // ═══════════════════════════════════════════════════════════════

    public static function run(Event $event): void
    {
        $io = $event->getIO();

        // 非交互模式：此时尚未选择语言，统一使用英文提示
        if (!$io->isInteractive()) {
            $io->write('<comment>' . self::MESSAGES['en']['skip'] . '</comment>');
            return;
        }

        $io->write('');

        // 1. 语言选择
        $locale = self::askLocale($io);
        $defaultTimezone = self::LOCALE_DEFAULT_TIMEZONES[$locale] ?? 'UTC';
        $msg = fn(string $key, string ...$args): string =>
            empty($args) ? self::MESSAGES[$locale][$key] : sprintf(self::MESSAGES[$locale][$key], ...$args);

        // 写入语言配置（非默认语言时更新）
        if ($locale !== 'zh_CN') {
            self::updateConfig($event, 'config/translation.php', "'locale'", $locale);
        }

        $io->write('');
        $io->write('<info>' . $msg('title') . '</info>');
        $io->write('');

        // 2. 时区选择（根据语言推荐默认时区）
        $timezone = self::askTimezone($io, $msg, $defaultTimezone);
        if ($timezone !== 'Asia/Shanghai') {
            self::updateConfig($event, 'config/app.php', "'default_timezone'", $timezone);
        }

        // 3. 可选组件
        $packages = self::askComponents($io, $msg);

        // 4. 摘要
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

    // ─── 语言选择 ────────────────────────────────────────────────

    private static function askLocale(IOInterface $io): string
    {
        $locales = array_keys(self::LOCALE_LABELS);
        $choices = [];
        foreach ($locales as $i => $code) {
            $choices[(string) $i] = self::LOCALE_LABELS[$code] . " ($code)";
        }

        $selected = $io->select(
            '<question>语言 / Language / 言語 / 언어</question>',
            $choices,
            '0',
            false,
            'Invalid selection / 无效选择',
            false
        );

        return $locales[(int) $selected];
    }

    // ─── 时区选择 ────────────────────────────────────────────────

    private static function askTimezone(IOInterface $io, callable $msg, string $default): string
    {
        if (Terminal::hasSttyAvailable()) {
            return self::askTimezoneAutocomplete($msg, $default);
        }

        return self::askTimezoneSelect($io, $msg, $default);
    }

    /**
     * 方案 A：有 stty 时，自定义逐字符联想（不区分大小写、支持任意子串匹配）
     *
     * 交互方式：
     *   - 输入任意字符实时过滤，光标右侧显示最佳匹配（黄色提示）
     *   - ↑↓ 切换候选  Tab 接受当前候选  Enter 确认
     *   - 直接回车 = 使用默认值
     */
    private static function askTimezoneAutocomplete(callable $msg, string $default): string
    {
        $allTimezones = \DateTimeZone::listIdentifiers();
        $output = new ConsoleOutput();
        $cursor = new Cursor($output);

        $output->write('<question>' . $msg('timezone_prompt', $default) . '</question>');

        $sttyMode = shell_exec('stty -g');
        shell_exec('stty -icanon -echo');

        $input   = '';
        $ofs     = 0;
        $matches = [];

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
                        $cursor->moveLeft(1);
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

                // ── 更新匹配列表 ──
                $matches = self::filterTimezones($allTimezones, $input);

                // ── 显示联想提示 ──
                $cursor->clearLineAfter();
                if (!empty($matches)) {
                    $hint = $matches[$ofs % count($matches)];
                    $cursor->savePosition();
                    $output->write('  <comment>' . $hint . '</comment>');
                    if (count($matches) > 1) {
                        $output->write('  <info>(' . count($matches) . ' matches, ↑↓)</info>');
                    }
                    $cursor->restorePosition();
                }
            }
        } finally {
            shell_exec('stty ' . $sttyMode);
        }

        $result = '' === $input ? $default : $input;

        if (!in_array($result, $allTimezones, true)) {
            $output->writeln('<comment>' . $msg('timezone_invalid', $default) . '</comment>');
            return $default;
        }

        return $result;
    }

    /**
     * 清除已输入文字并替换为新文字
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
     * 不区分大小写的子串匹配
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
     * 方案 B：无 stty 时（Windows 原生终端），两步 select：区域 → 城市
     */
    private static function askTimezoneSelect(IOInterface $io, callable $msg, string $default): string
    {
        // Step 1: 选区域
        $regionNames = array_keys(self::TIMEZONE_REGIONS);
        $defaultRegion = explode('/', $default)[0];

        $regionChoices = [];
        $defaultRegionIndex = '0';
        foreach ($regionNames as $i => $name) {
            $regionChoices[(string) $i] = $name;
            if ($name === $defaultRegion) {
                $defaultRegionIndex = (string) $i;
            }
        }

        $regionIndex = $io->select(
            '<question>' . $msg('timezone_region') . '</question>',
            $regionChoices,
            $defaultRegionIndex,
            false,
            $msg('timezone_invalid', $default),
            false
        );

        $selectedRegion = $regionNames[(int) $regionIndex];
        $regionConst = self::TIMEZONE_REGIONS[$selectedRegion];

        // Step 2: 选具体时区
        $timezones = \DateTimeZone::listIdentifiers($regionConst);

        $tzChoices = [];
        $defaultTzIndex = '0';
        foreach ($timezones as $i => $tz) {
            $tzChoices[(string) $i] = $tz;
            if ($tz === $default) {
                $defaultTzIndex = (string) $i;
            }
        }

        $tzIndex = $io->select(
            '<question>' . $msg('timezone_city') . '</question>',
            $tzChoices,
            $defaultTzIndex,
            false,
            $msg('timezone_invalid', $default),
            false
        );

        return $timezones[(int) $tzIndex];
    }

    // ─── 可选组件选择 ────────────────────────────────────────────

    private static function askComponents(IOInterface $io, callable $msg): array
    {
        $packages = [];

        // Console
        if ($io->askConfirmation('<question>' . $msg('console_question') . '</question>', true)) {
            $packages[] = self::PACKAGE_CONSOLE;
        }

        // Database
        $dbChoice = $io->select(
            '<question>' . $msg('db_question') . '</question>',
            ['0' => $msg('db_none'), '1' => 'webman/database', '2' => 'webman/think-orm'],
            '0',
            false,
            $msg('db_invalid'),
            false
        );
        if ($dbChoice === '1') {
            $packages[] = self::PACKAGE_DATABASE;
        } elseif ($dbChoice === '2') {
            $packages[] = self::PACKAGE_THINK_ORM;
        }

        // Redis
        if ($io->askConfirmation('<question>' . $msg('redis_question') . '</question>', false)) {
            $packages[] = self::PACKAGE_REDIS;
            $packages[] = self::PACKAGE_ILLUMINATE_EVENTS;
            $io->write('<comment>' . $msg('events_note') . '</comment>');
        }

        return $packages;
    }

    // ─── 配置文件修改 ────────────────────────────────────────────

    /**
     * 修改配置文件中形如 'key' => 'old_value' 的值
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

    // ─── Composer require ────────────────────────────────────────

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

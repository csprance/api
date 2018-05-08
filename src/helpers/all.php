<?php

require __DIR__ . '/constants.php';
require __DIR__ . '/app.php';
require __DIR__ . '/arrays.php';
require __DIR__ . '/cors.php';
require __DIR__ . '/extensions.php';
require __DIR__ . '/file.php';
require __DIR__ . '/items.php';
require __DIR__ . '/mail.php';
require __DIR__ . '/settings.php';
require __DIR__ . '/sorting.php';
require __DIR__ . '/url.php';

if (!function_exists('uc_convert')) {
    /**
     * Converts a string to title
     *
     * @param string $text The string to convert.
     *
     * @return string Formatted string.
     */
    function uc_convert($text)
    {
        $phrase = preg_replace('!\s+!', ' ', trim(ucwords(strtolower(str_replace('_', ' ', $text)))));
        $specialCaps = [
            'Ids' => 'IDs',
            'Ssn' => 'SSN',
            'Ein' => 'EIN',
            'Nda' => 'NDA',
            'Api' => 'API',
            'Youtube' => 'YouTube',
            'Faq' => 'FAQ',
            'Iphone' => 'iPhone',
            'Ipad' => 'iPad',
            'Ipod' => 'iPod',
            'Pdf' => 'PDF',
            'Pdfs' => 'PDFs',
            'Ui' => 'UI',
            'Url' => 'URL',
            'Urls' => 'URLs',
            'Ip' => 'IP',
            'Ftp' => 'FTP',
            'Db' => 'DB',
            'Cv' => 'CV',
            'Id' => 'ID',
            'Ph' => 'pH',
            'Php' => 'PHP',
            'Html' => 'HTML',
            'Js' => 'JS',
            'Json' => 'JSON',
            'Css' => 'CSS',
            'Csv' => 'CSV',
            'Ios' => 'iOS',
            'Iso' => 'ISO',
            'Rngr' => 'RNGR'
        ];

        $searchPattern = array_keys($specialCaps);
        $replaceValues = array_values($specialCaps);
        foreach ($searchPattern as $key => $value) {
            $searchPattern[$key] = ("/\b" . $value . "\b/");
        }

        return preg_replace($searchPattern, $replaceValues, $phrase);
    }
}

if (!function_exists('get_directus_path')) {
    /**
     * Gets the Directus path (subdirectory based on the host)
     *
     * @param string $subPath
     *
     * @return string
     */
    function get_directus_path($subPath = '')
    {
        $path = create_uri_from_global()->getBasePath();

        $path = trim($path, '/');
        $subPath = ltrim($subPath, '/');

        return (empty($path) ? '/' : sprintf('/%s/', $path)) . $subPath;
    }
}

if (!function_exists('normalize_path')) {
    /**
    * Normalize a filesystem path.
    *
    * On windows systems, replaces backslashes with forward slashes.
    * Ensures that no duplicate slashes exist.
    *
    * from WordPress source code
    *
    * @param string $path Path to normalize.
    *
    * @return string Normalized path.
    */
    function normalize_path($path)
    {
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('|/+|','/', $path);

        return $path;
    }
}

if (!function_exists('get_url')) {
    /**
     * Get Directus URL
     *
     * @param $path - Extra path to add to the url
     *
     * @return string
     */
    function get_url($path = '')
    {
        return create_uri_from_global()->getBaseUrl() . '/' . ltrim($path, '/');
    }
}


if (!function_exists('create_uri_from_global'))
{
    /**
     * Creates a uri object based on $_SERVER
     *
     * Snippet copied from Slim URI class
     *
     * @return \Slim\Http\Uri
     */
    function create_uri_from_global()
    {
        // Scheme
        $env = $_SERVER;
        $isSecure = \Directus\Util\ArrayUtils::get($env, 'HTTPS');
        $scheme = (empty($isSecure) || $isSecure === 'off') ? 'http' : 'https';

        // Authority: Username and password
        $username = \Directus\Util\ArrayUtils::get($env, 'PHP_AUTH_USER', '');
        $password = \Directus\Util\ArrayUtils::get($env, 'PHP_AUTH_PW', '');

        // Authority: Host
        if (\Directus\Util\ArrayUtils::has($env, 'HTTP_HOST')) {
            $host = \Directus\Util\ArrayUtils::get($env, 'HTTP_HOST');
        } else {
            $host = \Directus\Util\ArrayUtils::get($env, 'SERVER_NAME');
        }

        // Authority: Port
        $port = (int)\Directus\Util\ArrayUtils::get($env, 'SERVER_PORT', 80);
        if (preg_match('/^(\[[a-fA-F0-9:.]+\])(:\d+)?\z/', $host, $matches)) {
            $host = $matches[1];

            if (isset($matches[2])) {
                $port = (int)substr($matches[2], 1);
            }
        } else {
            $pos = strpos($host, ':');
            if ($pos !== false) {
                $port = (int)substr($host, $pos + 1);
                $host = strstr($host, ':', true);
            }
        }

        // Path
        $requestScriptName = parse_url(\Directus\Util\ArrayUtils::get($env, 'SCRIPT_NAME'), PHP_URL_PATH);
        $requestScriptDir = dirname($requestScriptName);

        // parse_url() requires a full URL. As we don't extract the domain name or scheme,
        // we use a stand-in.
        $requestUri = parse_url('http://example.com' . \Directus\Util\ArrayUtils::get($env, 'REQUEST_URI'), PHP_URL_PATH);

        $basePath = '';
        $virtualPath = $requestUri;
        if ($requestUri == $requestScriptName) {
            $basePath = $requestScriptDir;
        } elseif (stripos($requestUri, $requestScriptName) === 0) {
            $basePath = $requestScriptName;
        } elseif ($requestScriptDir !== '/' && stripos($requestUri, $requestScriptDir) === 0) {
            $basePath = $requestScriptDir;
        }

        if ($basePath) {
            $virtualPath = ltrim(substr($requestUri, strlen($basePath)), '/');
        }

        // Query string
        $queryString = \Directus\Util\ArrayUtils::get($env, 'QUERY_STRING', '');
        if ($queryString === '') {
            $queryString = parse_url('http://example.com' . \Directus\Util\ArrayUtils::get($env, 'REQUEST_URI'), PHP_URL_QUERY);
        }

        // Fragment
        $fragment = '';

        // Build Uri
        $uri = new \Slim\Http\Uri($scheme, $host, $port, $virtualPath, $queryString, $fragment, $username, $password);
        if ($basePath) {
            $uri = $uri->withBasePath($basePath);
        }

        return $uri;
    }
}

if (!function_exists('get_virtual_path')) {
    /**
     * Gets the virtual request path
     *
     * @return string
     */
    function get_virtual_path()
    {
        return create_uri_from_global()->getPath();
    }
}

if (!function_exists('get_api_env_from_request')) {
    /**
     * Gets the env from the request uri
     *
     * @return string
     */
    function get_api_env_from_request()
    {
        $path = trim(get_virtual_path(), '/');
        $parts = explode('/', $path);

        return isset($parts[0]) ? $parts[0] : '_';
    }
}

if (!function_exists('get_file_info')) {
    /**
     * Get info about a file, return extensive information about images (more to come)
     *
     * @return array File info
     */
    function get_file_info($file)
    {
        $finfo = new finfo(FILEINFO_MIME);
        $type = explode('; charset=', $finfo->file($file));
        $info = ['type' => $type[0], 'charset' => $type[1]];

        $type_str = explode('/', $info['type']);

        if ($type_str[0] == 'image') {
            $size = getimagesize($file, $meta);
            $info['width'] = $size[0];
            $info['height'] = $size[1];

            if (isset($meta['APP13'])) {
                $iptc = iptcparse($meta['APP13']);
                $info['caption'] = $iptc['2#120'][0];
                $info['title'] = $iptc['2#005'][0];
                $info['tags'] = implode($iptc['2#025'], ',');
            }
        }

        return $info;
    }
}

if (!function_exists('template')) {
    /**
     * Renders a single line. Looks for {{ var }}
     *
     * @param string $string
     * @param array $parameters
     *
     * @return string
     */
    function template($string, array $parameters)
    {
        $replacer = function ($match) use ($parameters) {
            return isset($parameters[$match[1]]) ? $parameters[$match[1]] : $match[0];
        };

        return preg_replace_callback('/{{\s*(.+?)\s*}}/', $replacer, $string);
    }
}

if (!function_exists('to_name_value')) {
    function to_name_value($array, $keys = null)
    {
        $data = [];
        foreach ($array as $name => $value) {
            $row = ['name' => $name, 'value' => $value];
            if (isset($keys)) $row = array_merge($row, $keys);
            array_push($data, $row);
        }

        return $data;
    }
}

if (!function_exists('find')) {
    function find($array, $key, $value)
    {
        foreach ($array as $item) {
            if (isset($item[$key]) && ($item[$key] == $value)) return $item;
        }
    }
}

if (!function_exists('is_numeric_array')) {
    // http://stackoverflow.com/questions/902857/php-getting-array-type
    function is_numeric_array($array)
    {
        return ($array == array_values($array));
    }
}

if (!function_exists('is_numeric_keys_array')) {
    function is_numeric_keys_array($array)
    {
        return \Directus\Util\ArrayUtils::isNumericKeys($array);
    }
}

if (!function_exists('debug')) {
    function debug($data, $title = null)
    {
        echo '<div style="padding:10px;">';
        echo "<b>$title</b>";
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        echo '</div>';
    }
}

if (!function_exists('register_global_hooks')) {
    /**
     * Register all the hooks from the configuration file
     *
     * @param \Directus\Application\Application $app
     */
    function register_global_hooks(\Directus\Application\Application $app)
    {
        $config = $app->getConfig();
        register_hooks_list($app, [$config->get('hooks')]);
    }
}

if (!function_exists('register_extensions_hooks')) {
    /**
     * Register all extensions hooks
     *
     * @param \Directus\Application\Application $app
     */
    function register_extensions_hooks(\Directus\Application\Application $app)
    {
        register_hooks_list(
            $app,
            get_custom_hooks('public/extensions/custom/hooks')
        );

        register_hooks_list(
            $app,
            get_custom_hooks('public/extensions/core/pages', true)
        );

        register_hooks_list(
            $app,
            get_custom_hooks('public/extensions/core/interfaces', true)
        );
    }
}

if (!function_exists('register_hooks_list')) {
    /**
     * Register an array of hooks (containing a list of actions and filters)
     *
     * @param \Directus\Application\Application $app
     * @param array $hooksList
     */
    function register_hooks_list(\Directus\Application\Application $app, array $hooksList)
    {
        foreach ($hooksList as $hooks) {
            register_hooks($app, array_get($hooks, 'actions', []), false);
            register_hooks($app, array_get($hooks, 'filters', []), true);
        }
    }
}

if (!function_exists('register_hooks')) {
    /**
     * Load one or multiple listeners
     *
     * @param \Directus\Application\Application $app
     * @param array|Closure $listeners
     * @param bool $areFilters
     */
    function register_hooks(\Directus\Application\Application $app, $listeners, $areFilters = false)
    {
        $hookEmitter = $app->getContainer()->get('hook_emitter');

        if (!is_array($listeners)) {
            $listeners = [$listeners];
        }

        foreach ($listeners as $event => $handlers) {
            if (!is_array($handlers)) {
                $handlers = [$handlers];
            }

            foreach ($handlers as $handler) {
                register_hook($hookEmitter, $event, $handler, null, $areFilters);
            }
        }
    }
}

if (!function_exists('register_hook')) {
    /**
     * Register a hook listeners
     *
     * @param \Directus\Hook\Emitter $emitter
     * @param string $name
     * @param callable $listener
     * @param int|null $priority
     * @param bool $areFilters
     */
    function register_hook(\Directus\Hook\Emitter $emitter, $name, $listener, $priority = null, $areFilters = false)
    {
        if (!$areFilters) {
            register_action_hook($emitter, $name, $listener, $priority);
        } else {
            register_filter_hook($emitter, $name, $listener, $priority);
        }
    }
}

if (!function_exists('register_action_hook')) {
    /**
     * Register a hook action
     *
     * @param \Directus\Hook\Emitter $emitter
     * @param string $name
     * @param callable $listener
     * @param int|null $priority
     */
    function register_action_hook(\Directus\Hook\Emitter $emitter, $name, $listener, $priority = null)
    {
        $emitter->addAction($name, $listener, $priority);
    }
}

if (!function_exists('register_hook_filter')) {
    /**
     * Register a hook action
     *
     * @param \Directus\Hook\Emitter $emitter
     * @param string $name
     * @param callable $listener
     * @param int|null $priority
     */
    function register_filter_hook(\Directus\Hook\Emitter $emitter, $name, $listener, $priority = null)
    {
        $emitter->addFilter($name, $listener, $priority);
    }
}

if (!function_exists('get_user_timezone')) {
    function get_user_timezone()
    {
        $userTimeZone = get_auth_timezone();

        if (!$userTimeZone) {
            $userTimeZone = date_default_timezone_get();
        }

        return $userTimeZone;
    }
}

if (!function_exists('get_auth_info')) {
    function get_auth_info($attribute)
    {
        $app = \Directus\Application\Application::getInstance();
        try {
            /** @var \Directus\Authentication\Provider $authentication */
            $authentication = $app->getContainer()->get('auth');
        } catch (\Exception $e) {
            return null;
        }

        return $authentication->getUserAttributes($attribute);
    }
}

if (!function_exists('get_auth_timezone')) {
    function get_auth_timezone()
    {
        return get_auth_info('timezone');
    }
}

if (!function_exists('base_path')) {
    function base_path($suffix = '')
    {
        $app = \Directus\Application\Application::getInstance();

        $path = $app ? $app->getContainer()->get('path_base') : realpath(__DIR__ . '/../../');

        if (!is_string($suffix)) {
            throw new \Directus\Exception\Exception('suffix must be a string');
        }

        if ($suffix) {
            $path = rtrim($path, '/') . '/' . ltrim($suffix, '/');
        }

        return $path;
    }
}

if (!function_exists('get_fake_timezones')) {
    /**
     * Gets the list of fake timezone map to an real one
     *
     * @return array
     */
    function get_fake_timezones()
    {
        return [
            'America/Mexico/La_Paz' => 'America/Chihuahua',
            'America/Guadalajara' => 'America/Mexico_City',
            'America/Quito' => 'America/Bogota',
            'America/Argentina/GeorgeTown' => 'America/Argentina/Buenos_Aires',
            'Europe/Edinburgh' => 'Europe/London',
            'Europe/Bern' => 'Europe/Berlin',
            'Europe/Kyiv' => 'Europe/Helsinki',
            'Asia/Abu_Dhabi' => 'Asia/Muscat',
            'Europe/St_Petersburg' => 'Europe/Moscow',
            'Asia/Islamabad' => 'Asia/Karachi',
            'Asia/Mumbai' => 'Asia/Calcutta',
            'Asia/New_Delhi' => 'Asia/Calcutta',
            'Asia/Sri_Jayawardenepura' => 'Asia/Calcutta',
            'Asia/Astana' => 'Asia/Dhaka',
            'Asia/Hanoi' => 'Asia/Bangkok',
            'Asia/Beijing' => 'Asia/Hong_Kong',
            'Asia/Sapporo' => 'Asia/Tokyo',
            'Asia/Osaka' => 'Asia/Tokyo',
            'Pacific/Marshall_Is' => 'Pacific/Fiji',
            'Asia/Solomon_Is' => 'Asia/Magadan',
            'Asia/New_Caledonia' => 'Asia/Magadan',
            'Pacific/Wellington' => 'Pacific/Auckland'
        ];
    }
}

if (!function_exists('get_real_timezone')) {
    /**
     * Gets the real name of the timezone name
     *
     * we have fake it until php makes it
     *
     * @param $name
     *
     * @return string
     */
    function get_real_timezone($name)
    {
        $fakes = get_fake_timezones();
        return isset($fakes[$name]) ? $fakes[$name] : $name;
    }
}

if (!function_exists('get_timezone_list')) {
    /**
     * @return array
     */
    function get_timezone_list()
    {
        // List from: https://github.com/tamaspap/timezones
        return [
            'Pacific/Midway' => '(UTC-11:00) Midway Island',
            'Pacific/Samoa' => '(UTC-11:00) Samoa',
            'Pacific/Honolulu' => '(UTC-10:00) Hawaii',
            'US/Alaska' => '(UTC-09:00) Alaska',
            'America/Los_Angeles' => '(UTC-08:00) Pacific Time (US & Canada)',
            'America/Tijuana' => '(UTC-08:00) Tijuana',
            'US/Arizona' => '(UTC-07:00) Arizona',
            'America/Chihuahua' => '(UTC-07:00) Chihuahua',
            'America/Mexico/La_Paz' => '(UTC-07:00) La Paz',
            'America/Mazatlan' => '(UTC-07:00) Mazatlan',
            'US/Mountain' => '(UTC-07:00) Mountain Time (US & Canada)',
            'America/Managua' => '(UTC-06:00) Central America',
            'US/Central' => '(UTC-06:00) Central Time (US & Canada)',
            'America/Guadalajara' => '(UTC-06:00) Guadalajara',
            'America/Mexico_City' => '(UTC-06:00) Mexico City',
            'America/Monterrey' => '(UTC-06:00) Monterrey',
            'Canada/Saskatchewan' => '(UTC-06:00) Saskatchewan',
            'America/Bogota' => '(UTC-05:00) Bogota',
            'US/Eastern' => '(UTC-05:00) Eastern Time (US & Canada)',
            'US/East-Indiana' => '(UTC-05:00) Indiana (East)',
            'America/Lima' => '(UTC-05:00) Lima',
            'America/Quito' => '(UTC-05:00) Quito',
            'Canada/Atlantic' => '(UTC-04:00) Atlantic Time (Canada)',
            'America/New_York' => '(UTC-04:00) New York',
            'America/Caracas' => '(UTC-04:30) Caracas',
            'America/La_Paz' => '(UTC-04:00) La Paz',
            'America/Santiago' => '(UTC-04:00) Santiago',
            'America/Santo_Domingo' => '(UTC-04:00) Santo Domingo',
            'Canada/Newfoundland' => '(UTC-03:30) Newfoundland',
            'America/Sao_Paulo' => '(UTC-03:00) Brasilia',
            'America/Argentina/Buenos_Aires' => '(UTC-03:00) Buenos Aires',
            'America/Argentina/GeorgeTown' => '(UTC-03:00) Georgetown',
            'America/Godthab' => '(UTC-03:00) Greenland',
            'America/Noronha' => '(UTC-02:00) Mid-Atlantic',
            'Atlantic/Azores' => '(UTC-01:00) Azores',
            'Atlantic/Cape_Verde' => '(UTC-01:00) Cape Verde Is.',
            'Africa/Casablanca' => '(UTC+00:00) Casablanca',
            'Europe/Edinburgh' => '(UTC+00:00) Edinburgh',
            'Etc/Greenwich' => '(UTC+00:00) Greenwich Mean Time : Dublin',
            'Europe/Lisbon' => '(UTC+00:00) Lisbon',
            'Europe/London' => '(UTC+00:00) London',
            'Africa/Monrovia' => '(UTC+00:00) Monrovia',
            'UTC' => '(UTC+00:00) UTC',
            'Europe/Amsterdam' => '(UTC+01:00) Amsterdam',
            'Europe/Belgrade' => '(UTC+01:00) Belgrade',
            'Europe/Berlin' => '(UTC+01:00) Berlin',
            'Europe/Bern' => '(UTC+01:00) Bern',
            'Europe/Bratislava' => '(UTC+01:00) Bratislava',
            'Europe/Brussels' => '(UTC+01:00) Brussels',
            'Europe/Budapest' => '(UTC+01:00) Budapest',
            'Europe/Copenhagen' => '(UTC+01:00) Copenhagen',
            'Europe/Ljubljana' => '(UTC+01:00) Ljubljana',
            'Europe/Madrid' => '(UTC+01:00) Madrid',
            'Europe/Paris' => '(UTC+01:00) Paris',
            'Europe/Prague' => '(UTC+01:00) Prague',
            'Europe/Rome' => '(UTC+01:00) Rome',
            'Europe/Sarajevo' => '(UTC+01:00) Sarajevo',
            'Europe/Skopje' => '(UTC+01:00) Skopje',
            'Europe/Stockholm' => '(UTC+01:00) Stockholm',
            'Europe/Vienna' => '(UTC+01:00) Vienna',
            'Europe/Warsaw' => '(UTC+01:00) Warsaw',
            'Africa/Lagos' => '(UTC+01:00) West Central Africa',
            'Europe/Zagreb' => '(UTC+01:00) Zagreb',
            'Europe/Athens' => '(UTC+02:00) Athens',
            'Europe/Bucharest' => '(UTC+02:00) Bucharest',
            'Africa/Cairo' => '(UTC+02:00) Cairo',
            'Africa/Harare' => '(UTC+02:00) Harare',
            'Europe/Helsinki' => '(UTC+02:00) Helsinki',
            'Europe/Istanbul' => '(UTC+02:00) Istanbul',
            'Asia/Jerusalem' => '(UTC+02:00) Jerusalem',
            'Europe/Kyiv' => '(UTC+02:00) Kyiv',
            'Africa/Johannesburg' => '(UTC+02:00) Pretoria',
            'Europe/Riga' => '(UTC+02:00) Riga',
            'Europe/Sofia' => '(UTC+02:00) Sofia',
            'Europe/Tallinn' => '(UTC+02:00) Tallinn',
            'Europe/Vilnius' => '(UTC+02:00) Vilnius',
            'Asia/Baghdad' => '(UTC+03:00) Baghdad',
            'Asia/Kuwait' => '(UTC+03:00) Kuwait',
            'Europe/Minsk' => '(UTC+03:00) Minsk',
            'Africa/Nairobi' => '(UTC+03:00) Nairobi',
            'Asia/Riyadh' => '(UTC+03:00) Riyadh',
            'Europe/Volgograd' => '(UTC+03:00) Volgograd',
            'Asia/Tehran' => '(UTC+03:30) Tehran',
            'Asia/Abu_Dhabi' => '(UTC+04:00) Abu Dhabi',
            'Asia/Baku' => '(UTC+04:00) Baku',
            'Europe/Moscow' => '(UTC+04:00) Moscow',
            'Asia/Muscat' => '(UTC+04:00) Muscat',
            'Europe/St_Petersburg' => '(UTC+04:00) St. Petersburg',
            'Asia/Tbilisi' => '(UTC+04:00) Tbilisi',
            'Asia/Yerevan' => '(UTC+04:00) Yerevan',
            'Asia/Kabul' => '(UTC+04:30) Kabul',
            'Asia/Islamabad' => '(UTC+05:00) Islamabad',
            'Asia/Karachi' => '(UTC+05:00) Karachi',
            'Asia/Tashkent' => '(UTC+05:00) Tashkent',
            'Asia/Calcutta' => '(UTC+05:30) Chennai',
            'Asia/Kolkata' => '(UTC+05:30) Kolkata',
            'Asia/Mumbai' => '(UTC+05:30) Mumbai',
            'Asia/New_Delhi' => '(UTC+05:30) New Delhi',
            'Asia/Sri_Jayawardenepura' => '(UTC+05:30) Sri Jayawardenepura',
            'Asia/Katmandu' => '(UTC+05:45) Kathmandu',
            'Asia/Almaty' => '(UTC+06:00) Almaty',
            'Asia/Astana' => '(UTC+06:00) Astana',
            'Asia/Dhaka' => '(UTC+06:00) Dhaka',
            'Asia/Yekaterinburg' => '(UTC+06:00) Ekaterinburg',
            'Asia/Rangoon' => '(UTC+06:30) Rangoon',
            'Asia/Bangkok' => '(UTC+07:00) Bangkok',
            'Asia/Hanoi' => '(UTC+07:00) Hanoi',
            'Asia/Jakarta' => '(UTC+07:00) Jakarta',
            'Asia/Novosibirsk' => '(UTC+07:00) Novosibirsk',
            'Asia/Beijing' => '(UTC+08:00) Beijing',
            'Asia/Chongqing' => '(UTC+08:00) Chongqing',
            'Asia/Hong_Kong' => '(UTC+08:00) Hong Kong',
            'Asia/Krasnoyarsk' => '(UTC+08:00) Krasnoyarsk',
            'Asia/Kuala_Lumpur' => '(UTC+08:00) Kuala Lumpur',
            'Australia/Perth' => '(UTC+08:00) Perth',
            'Asia/Singapore' => '(UTC+08:00) Singapore',
            'Asia/Taipei' => '(UTC+08:00) Taipei',
            'Asia/Ulan_Bator' => '(UTC+08:00) Ulaan Bataar',
            'Asia/Urumqi' => '(UTC+08:00) Urumqi',
            'Asia/Irkutsk' => '(UTC+09:00) Irkutsk',
            'Asia/Osaka' => '(UTC+09:00) Osaka',
            'Asia/Sapporo' => '(UTC+09:00) Sapporo',
            'Asia/Seoul' => '(UTC+09:00) Seoul',
            'Asia/Tokyo' => '(UTC+09:00) Tokyo',
            'Australia/Adelaide' => '(UTC+09:30) Adelaide',
            'Australia/Darwin' => '(UTC+09:30) Darwin',
            'Australia/Brisbane' => '(UTC+10:00) Brisbane',
            'Australia/Canberra' => '(UTC+10:00) Canberra',
            'Pacific/Guam' => '(UTC+10:00) Guam',
            'Australia/Hobart' => '(UTC+10:00) Hobart',
            'Australia/Melbourne' => '(UTC+10:00) Melbourne',
            'Pacific/Port_Moresby' => '(UTC+10:00) Port Moresby',
            'Australia/Sydney' => '(UTC+10:00) Sydney',
            'Asia/Yakutsk' => '(UTC+10:00) Yakutsk',
            'Asia/Vladivostok' => '(UTC+11:00) Vladivostok',
            'Pacific/Auckland' => '(UTC+12:00) Auckland',
            'Pacific/Fiji' => '(UTC+12:00) Fiji',
            'Pacific/Kwajalein' => '(UTC+12:00) International Date Line West',
            'Asia/Kamchatka' => '(UTC+12:00) Kamchatka',
            'Asia/Magadan' => '(UTC+12:00) Magadan',
            'Pacific/Marshall_Is' => '(UTC+12:00) Marshall Is.',
            'Asia/New_Caledonia' => '(UTC+12:00) New Caledonia',
            'Asia/Solomon_Is' => '(UTC+12:00) Solomon Is.',
            'Pacific/Wellington' => '(UTC+12:00) Wellington',
            'Pacific/Tongatapu' => '(UTC+13:00) Nuku\'alofa',
        ];
    }
}

if (!function_exists('get_country_list')) {
    /**
     * @return array
     */
    function get_country_list()
    {
        return [
            'AF' => 'Afghanistan',
            'AL' => 'Albania',
            'DZ' => 'Algeria',
            'AS' => 'American Samoa',
            'AD' => 'Andorra',
            'AO' => 'Angola',
            'AI' => 'Anguilla',
            'AQ' => 'Antarctica',
            'AG' => 'Antigua and Barbuda',
            'AR' => 'Argentina',
            'AM' => 'Armenia',
            'AW' => 'Aruba',
            'AU' => 'Australia',
            'AT' => 'Austria',
            'AZ' => 'Azerbaijan',
            'BS' => 'Bahamas',
            'BH' => 'Bahrain',
            'BD' => 'Bangladesh',
            'BB' => 'Barbados',
            'BY' => 'Belarus',
            'BE' => 'Belgium',
            'BZ' => 'Belize',
            'BJ' => 'Benin',
            'BM' => 'Bermuda',
            'BT' => 'Bhutan',
            'BO' => 'Bolivia',
            'BA' => 'Bosnia and Herzegovina',
            'BW' => 'Botswana',
            'BV' => 'Bouvet Island',
            'BR' => 'Brazil',
            'BQ' => 'British Antarctic Territory',
            'IO' => 'British Indian Ocean Territory',
            'VG' => 'British Virgin Islands',
            'BN' => 'Brunei',
            'BG' => 'Bulgaria',
            'BF' => 'Burkina Faso',
            'BI' => 'Burundi',
            'KH' => 'Cambodia',
            'CM' => 'Cameroon',
            'CA' => 'Canada',
            'CT' => 'Canton and Enderbury Islands',
            'CV' => 'Cape Verde',
            'KY' => 'Cayman Islands',
            'CF' => 'Central African Republic',
            'TD' => 'Chad',
            'CL' => 'Chile',
            'CN' => 'China',
            'CX' => 'Christmas Island',
            'CC' => 'Cocos [Keeling] Islands',
            'CO' => 'Colombia',
            'KM' => 'Comoros',
            'CG' => 'Congo - Brazzaville',
            'CD' => 'Congo - Kinshasa',
            'CK' => 'Cook Islands',
            'CR' => 'Costa Rica',
            'HR' => 'Croatia',
            'CU' => 'Cuba',
            'CY' => 'Cyprus',
            'CZ' => 'Czech Republic',
            'CI' => 'Côte d’Ivoire',
            'DK' => 'Denmark',
            'DJ' => 'Djibouti',
            'DM' => 'Dominica',
            'DO' => 'Dominican Republic',
            'NQ' => 'Dronning Maud Land',
            'EC' => 'Ecuador',
            'EG' => 'Egypt',
            'SV' => 'El Salvador',
            'GQ' => 'Equatorial Guinea',
            'ER' => 'Eritrea',
            'EE' => 'Estonia',
            'ET' => 'Ethiopia',
            'FK' => 'Falkland Islands',
            'FO' => 'Faroe Islands',
            'FJ' => 'Fiji',
            'FI' => 'Finland',
            'FR' => 'France',
            'GF' => 'French Guiana',
            'PF' => 'French Polynesia',
            'TF' => 'French Southern Territories',
            'FQ' => 'French Southern and Antarctic Territories',
            'GA' => 'Gabon',
            'GM' => 'Gambia',
            'GE' => 'Georgia',
            'DE' => 'Germany',
            'GH' => 'Ghana',
            'GI' => 'Gibraltar',
            'GR' => 'Greece',
            'GL' => 'Greenland',
            'GD' => 'Grenada',
            'GP' => 'Guadeloupe',
            'GU' => 'Guam',
            'GT' => 'Guatemala',
            'GG' => 'Guernsey',
            'GN' => 'Guinea',
            'GW' => 'Guinea-Bissau',
            'GY' => 'Guyana',
            'HT' => 'Haiti',
            'HM' => 'Heard Island and McDonald Islands',
            'HN' => 'Honduras',
            'HK' => 'Hong Kong SAR China',
            'HU' => 'Hungary',
            'IS' => 'Iceland',
            'IN' => 'India',
            'ID' => 'Indonesia',
            'IR' => 'Iran',
            'IQ' => 'Iraq',
            'IE' => 'Ireland',
            'IM' => 'Isle of Man',
            'IL' => 'Israel',
            'IT' => 'Italy',
            'JM' => 'Jamaica',
            'JP' => 'Japan',
            'JE' => 'Jersey',
            'JT' => 'Johnston Island',
            'JO' => 'Jordan',
            'KZ' => 'Kazakhstan',
            'KE' => 'Kenya',
            'KI' => 'Kiribati',
            'KW' => 'Kuwait',
            'KG' => 'Kyrgyzstan',
            'LA' => 'Laos',
            'LV' => 'Latvia',
            'LB' => 'Lebanon',
            'LS' => 'Lesotho',
            'LR' => 'Liberia',
            'LY' => 'Libya',
            'LI' => 'Liechtenstein',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'MO' => 'Macau SAR China',
            'MK' => 'Macedonia',
            'MG' => 'Madagascar',
            'MW' => 'Malawi',
            'MY' => 'Malaysia',
            'MV' => 'Maldives',
            'ML' => 'Mali',
            'MT' => 'Malta',
            'MH' => 'Marshall Islands',
            'MQ' => 'Martinique',
            'MR' => 'Mauritania',
            'MU' => 'Mauritius',
            'YT' => 'Mayotte',
            'FX' => 'Metropolitan France',
            'MX' => 'Mexico',
            'FM' => 'Micronesia',
            'MI' => 'Midway Islands',
            'MD' => 'Moldova',
            'MC' => 'Monaco',
            'MN' => 'Mongolia',
            'ME' => 'Montenegro',
            'MS' => 'Montserrat',
            'MA' => 'Morocco',
            'MZ' => 'Mozambique',
            'MM' => 'Myanmar [Burma]',
            'NA' => 'Namibia',
            'NR' => 'Nauru',
            'NP' => 'Nepal',
            'NL' => 'Netherlands',
            'AN' => 'Netherlands Antilles',
            'NT' => 'Neutral Zone',
            'NC' => 'New Caledonia',
            'NZ' => 'New Zealand',
            'NI' => 'Nicaragua',
            'NE' => 'Niger',
            'NG' => 'Nigeria',
            'NU' => 'Niue',
            'NF' => 'Norfolk Island',
            'KP' => 'North Korea',
            'VD' => 'North Vietnam',
            'MP' => 'Northern Mariana Islands',
            'NO' => 'Norway',
            'OM' => 'Oman',
            'PC' => 'Pacific Islands Trust Territory',
            'PK' => 'Pakistan',
            'PW' => 'Palau',
            'PS' => 'Palestinian Territories',
            'PA' => 'Panama',
            'PZ' => 'Panama Canal Zone',
            'PG' => 'Papua New Guinea',
            'PY' => 'Paraguay',
            'YD' => 'People\'s Democratic Republic of Yemen',
            'PE' => 'Peru',
            'PH' => 'Philippines',
            'PN' => 'Pitcairn Islands',
            'PL' => 'Poland',
            'PT' => 'Portugal',
            'PR' => 'Puerto Rico',
            'QA' => 'Qatar',
            'RO' => 'Romania',
            'RU' => 'Russia',
            'RW' => 'Rwanda',
            'RE' => 'Réunion',
            'BL' => 'Saint Barthélemy',
            'SH' => 'Saint Helena',
            'KN' => 'Saint Kitts and Nevis',
            'LC' => 'Saint Lucia',
            'MF' => 'Saint Martin',
            'PM' => 'Saint Pierre and Miquelon',
            'VC' => 'Saint Vincent and the Grenadines',
            'WS' => 'Samoa',
            'SM' => 'San Marino',
            'SA' => 'Saudi Arabia',
            'SN' => 'Senegal',
            'RS' => 'Serbia',
            'CS' => 'Serbia and Montenegro',
            'SC' => 'Seychelles',
            'SL' => 'Sierra Leone',
            'SG' => 'Singapore',
            'SK' => 'Slovakia',
            'SI' => 'Slovenia',
            'SB' => 'Solomon Islands',
            'SO' => 'Somalia',
            'ZA' => 'South Africa',
            'GS' => 'South Georgia and the South Sandwich Islands',
            'KR' => 'South Korea',
            'ES' => 'Spain',
            'LK' => 'Sri Lanka',
            'SD' => 'Sudan',
            'SR' => 'Suriname',
            'SJ' => 'Svalbard and Jan Mayen',
            'SZ' => 'Swaziland',
            'SE' => 'Sweden',
            'CH' => 'Switzerland',
            'SY' => 'Syria',
            'ST' => 'São Tomé and Príncipe',
            'TW' => 'Taiwan',
            'TJ' => 'Tajikistan',
            'TZ' => 'Tanzania',
            'TH' => 'Thailand',
            'TL' => 'Timor-Leste',
            'TG' => 'Togo',
            'TK' => 'Tokelau',
            'TO' => 'Tonga',
            'TT' => 'Trinidad and Tobago',
            'TN' => 'Tunisia',
            'TR' => 'Turkey',
            'TM' => 'Turkmenistan',
            'TC' => 'Turks and Caicos Islands',
            'TV' => 'Tuvalu',
            'UM' => 'U.S. Minor Outlying Islands',
            'PU' => 'U.S. Miscellaneous Pacific Islands',
            'VI' => 'U.S. Virgin Islands',
            'UG' => 'Uganda',
            'UA' => 'Ukraine',
            'SU' => 'Union of Soviet Socialist Republics',
            'AE' => 'United Arab Emirates',
            'GB' => 'United Kingdom',
            'US' => 'United States',
            'ZZ' => 'Unknown or Invalid Region',
            'UY' => 'Uruguay',
            'UZ' => 'Uzbekistan',
            'VU' => 'Vanuatu',
            'VA' => 'Vatican City',
            'VE' => 'Venezuela',
            'VN' => 'Vietnam',
            'WK' => 'Wake Island',
            'WF' => 'Wallis and Futuna',
            'EH' => 'Western Sahara',
            'YE' => 'Yemen',
            'ZM' => 'Zambia',
            'ZW' => 'Zimbabwe',
            'AX' => 'Åland Islands',
        ];
    }
}

if (!function_exists('convert_shorthand_size_to_bytes')) {
    /**
     * Convert shorthand size into bytes
     *
     * @param $size - shorthand size
     *
     * @return int
     */
    function convert_shorthand_size_to_bytes($size)
    {
        $size = strtolower($size);
        $bytes = (int)$size;

        if (strpos($size, 'k') !== false) {
            $bytes = intval($size) * KB_IN_BYTES;
        } elseif (strpos($size, 'm') !== false) {
            $bytes = intval($size) * MB_IN_BYTES;
        } elseif (strpos($size, 'g') !== false) {
            $bytes = intval($size) * GB_IN_BYTES;
        }

        return $bytes;
    }
}

if (!function_exists('get_max_upload_size')) {
    /**
     * Get the maximum upload size in bytes
     *
     * @return int
     */
    function get_max_upload_size()
    {
        $maxUploadSize = convert_shorthand_size_to_bytes(ini_get('upload_max_filesize'));
        $maxPostSize = convert_shorthand_size_to_bytes(ini_get('post_max_size'));

        return min($maxUploadSize, $maxPostSize);
    }
}

if (!function_exists('find_directories')) {
    /**
     * Gets directories inside the given path
     *
     * @param $path
     *
     * @return array
     */
    function find_directories($path)
    {
        return array_filter(glob(rtrim($path, '/') . '/*', GLOB_ONLYDIR), function ($path) {
            $name = basename($path);

            return $name[0] !== '_';
        });
    }
}

if (!function_exists('find_files')) {
    /**
     * Find files inside $paths, directories and file name starting with "_" will be ignored.
     *
     *
     * @param string $searchPaths
     * @param int $flags
     * @param string $pattern
     * @param bool|int $includeSubDirectories
     * @param callable - $ignore filter
     *
     * @return array
     */
    function find_files($searchPaths, $flags = 0, $pattern = '', $includeSubDirectories = false)
    {
        if (!is_array($searchPaths)) {
            $searchPaths = [$searchPaths];
        }

        $validPath = function ($path) {
            $filename = pathinfo($path, PATHINFO_FILENAME);

            return $filename[0] !== '_';
        };

        $filesPath = [];
        foreach ($searchPaths as $searchPath) {
            $searchPath = rtrim($searchPath, '/');
            $result = array_filter(glob($searchPath . '/' . rtrim($pattern, '/'), $flags), $validPath);
            $filesPath = array_merge($filesPath, $result);

            if ($includeSubDirectories === true || (int)$includeSubDirectories > 0) {
                if (is_numeric($includeSubDirectories)) {
                    $includeSubDirectories--;
                }

                foreach (glob($searchPath . '/*', GLOB_ONLYDIR) as $subDir) {
                    if ($validPath($subDir)) {
                        $result = find_files($subDir, $flags, $pattern, $includeSubDirectories);
                        $filesPath = array_merge($filesPath, $result);
                    }
                }
            }
        }

        return $filesPath;
    }
}

if (!function_exists('find_js_files')) {
    /**
     * Find JS files in the given path
     *
     * @param string $paths
     * @param bool|int $includeSubDirectories
     *
     * @return array
     */
    function find_js_files($paths, $includeSubDirectories = false)
    {
        return find_files($paths, 0, '*.js', $includeSubDirectories);
    }
}

if (!function_exists('find_json_files')) {
    /**
     * Find JSON files in the given path
     *
     * @param string $paths
     * @param bool|int $includeSubDirectories
     *
     * @return array
     */
    function find_json_files($paths, $includeSubDirectories = false)
    {
        return find_files($paths, 0, '*.json', $includeSubDirectories);
    }
}

if (!function_exists('find_php_files')) {
    /**
     * Find PHP files in the given path
     *
     * @param string $paths
     * @param bool|int $includeSubDirectories
     *
     * @return array
     */
    function find_php_files($paths, $includeSubDirectories = false)
    {
        return find_files($paths, 0, '*.php', $includeSubDirectories);
    }
}

if (!function_exists('find_html_files')) {
    /**
     * Find HTML files in the given path
     *
     * @param string $paths
     * @param bool|int $includeSubDirectories
     *
     * @return array
     */
    function find_html_files($paths, $includeSubDirectories = false)
    {
        return find_files($paths, 0, '*.html', $includeSubDirectories);
    }
}

if (!function_exists('find_twig_files')) {
    /**
     * Find Twig files in the given path
     *
     * @param string $paths
     * @param bool|int $includeSubDirectories
     *
     * @return array
     */
    function find_twig_files($paths, $includeSubDirectories = false)
    {
        return find_files($paths, 0, '*.twig', $includeSubDirectories);
    }
}

if (!function_exists('get_contents')) {
    /**
     * Get content from an URL
     *
     * @param $url
     * @param $headers
     *
     * @return mixed
     */
    function get_contents($url, $headers = [])
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);

        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}

if (!function_exists('get_json')) {
    /**
     * Get json from an url
     *
     * @param $url
     * @param array $headers
     *
     * @return mixed
     */
    function get_json($url, $headers = [])
    {
        $content = get_contents($url, array_merge(['Content-Type: application/json'], $headers));

        return json_decode($content, true);
    }
}

if (!function_exists('check_version')) {
    /**
     * Check Directus latest version
     *
     * @param bool $firstCheck
     *
     * @return array
     */
    function check_version($firstCheck = false)
    {
        $data = [
            'outdated' => false,
        ];

        $version = \Directus\Application\Application::DIRECTUS_VERSION;

        // =============================================================================
        // Getting the latest version, silently skip it if the server is no responsive.
        // =============================================================================
        try {
            $responseData = get_json('https://directus.io/check-version' . ($firstCheck ? '?first_check=1' : ''));

            if ($responseData && isset($responseData['success']) && $responseData['success'] == true) {
                $versionData = $responseData['data'];
                $data = array_merge($data, $versionData);
                $data['outdated'] = version_compare($version, $versionData['current_version'], '<');
            }
        } catch (\Exception $e) {
            // Do nothing
        }

        return $data;
    }
}

if (!function_exists('feedback_login_ping')) {
    /**
     * Sends a unique random token to help us understand approximately how many instances of Directus exist.
     * This can be disabled in your config file.
     *
     * @param string $key
     */
    function feedback_login_ping($key)
    {
        try {
            get_json('https://directus.io/feedback/ping/' . (string) $key);
        } catch (\Exception $e) {
            // Do nothing
        }
    }
}

if (!function_exists('get_request_ip')) {
    function get_request_ip()
    {
        if (isset($_SERVER['X_FORWARDED_FOR'])) {
            return $_SERVER['X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['CLIENT_IP'])) {
            return $_SERVER['CLIENT_IP'];
        }

        return $_SERVER['REMOTE_ADDR'];
    }
}

if (!function_exists('get_project_info')) {
    function get_project_info()
    {
        /** @var \Directus\Database\TableGateway\DirectusSettingsTableGateway $settingsTable */
        $settingsTable = \Directus\Database\TableGatewayFactory::create('directus_settings', [
            'acl' => null
        ]);
        $settings = $settingsTable->fetchCollection('global');

        $projectName = isset($settings['project_name']) ? $settings['project_name'] : 'Directus';
        $defaultProjectLogo = get_directus_path('/assets/imgs/directus-logo-flat.svg');
        if (isset($settings['cms_thumbnail_url']) && $settings['cms_thumbnail_url']) {
            $projectLogoURL = $settings['cms_thumbnail_url'];
            $filesTable = \Directus\Database\TableGatewayFactory::create('directus_files', [
                'acl' => null
            ]);
            $data = $filesTable->loadEntries([
                'id' => $projectLogoURL
            ]);

            $projectLogoURL = \Directus\Util\ArrayUtils::get($data, 'url', $defaultProjectLogo);
        } else {
            $projectLogoURL = $defaultProjectLogo;
        }

        return [
            'project_name' => $projectName,
            'project_logo_url' => $projectLogoURL
        ];
    }
}

if (!function_exists('get_missing_requirements')) {
    /**
     * Gets an array of errors message when there's a missing requirements
     *
     * @return array
     */
    function get_missing_requirements()
    {
        $errors = [];

        if (version_compare(PHP_VERSION, '5.5.0', '<')) {
            $errors[] = 'Your host needs to use PHP 5.5.0 or higher to run this version of Directus!';
        }

        if (!defined('PDO::ATTR_DRIVER_NAME')) {
            $errors[] = 'Your host needs to have PDO enabled to run this version of Directus!';
        }

        if (defined('PDO::ATTR_DRIVER_NAME') && !in_array('mysql', PDO::getAvailableDrivers())) {
            $errors[] = 'Your host needs to have PDO MySQL Driver enabled to run this version of Directus!';
        }

        if (!extension_loaded('gd') || !function_exists('gd_info')) {
            $errors[] = 'Your host needs to have GD Library enabled to run this version of Directus!';
        }

        if (!extension_loaded('fileinfo') || !class_exists('finfo')) {
            $errors[] = 'Your host needs to have File Information extension enabled to run this version of Directus!';
        }

        if (!extension_loaded('curl') || !function_exists('curl_init')) {
            $errors[] = 'Your host needs to have cURL extension enabled to run this version of Directus!';
        }

        if (!file_exists(base_path() . '/vendor/autoload.php')) {
            $errors[] = 'Composer dependencies must be installed first.';
        }

        return $errors;
    }
}

if (!function_exists('display_missing_requirements_html')) {
    /**
     * Display an html error page
     *
     * @param array $errors
     * @param \Directus\Application\Application $app
     */
    function display_missing_requirements_html($errors, $app)
    {
        $projectInfo = get_project_info();

        $data = array_merge($projectInfo, [
            'errors' => $errors
        ]);

        $app->response()->header('Content-Type', 'text/html; charset=utf-8');
        $app->render('errors/requirements.twig', $data);
    }
}

if (!function_exists('define_constant')) {
    /**
     * Define a constant if it does not exists
     *
     * @param string $name
     * @param mixed $value
     *
     * @return bool
     */
    function define_constant($name, $value)
    {
        $defined = true;

        if (!defined($name)) {
            define($name, $value);
            $defined = false;
        }

        return $defined;
    }
}

if (!function_exists('get_columns_flat_at')) {
    /**
     * Get all the columns name in the given level
     *
     * @param array $columns
     * @param int $level
     *
     * @return array
     */
    function get_columns_flat_at(array $columns, $level = 0)
    {
        $names = [];

        foreach ($columns as $column) {
            $parts = explode('.', $column);

            if (isset($parts[$level])) {
                $names[] = $parts[$level];
            }
        }

        return $names;
    }
}

if (!function_exists('get_csv_flat_columns')) {
    /**
     * Gets a CSV flat columns list from the given array
     *
     * @param array $columns
     * @param null $prefix
     *
     * @return string
     */
    function get_csv_flat_columns(array $columns, $prefix = null)
    {
        $flatColumns = [];
        $prefix = $prefix === null ? '' : $prefix . '.';

        foreach ($columns as $key => $value) {
            if (is_array($value)) {
                $value = get_csv_flat_columns($value, $prefix . $key);
            } else {
                $value = $prefix . $key;
            }

            $flatColumns[] = $value;
        }

        return implode(',', $flatColumns);
    }
}

if (!function_exists('get_array_flat_columns')) {
    /**
     * Gets an array flat columns list from the given array
     *
     * @param $columns
     *
     * @return array
     */
    function get_array_flat_columns($columns)
    {
        // TODO: make sure array is passed???
        if (empty($columns)) {
            return [];
        }

        return explode(',', get_csv_flat_columns($columns ?: []));
    }
}
if (!function_exists('get_unflat_columns')) {
    /**
     * Gets the unflat version of flat (dot-notated) column list
     *
     * @param string|array $columns
     *
     * @return array
     */
    function get_unflat_columns($columns)
    {
        $names = [];

        if (!is_array($columns)) {
            $columns = explode(',', $columns);
        }

        foreach ($columns as $column) {
            $parts = explode('.', $column, 2);

            if (isset($parts[0])) {
                if (!isset($names[$parts[0]])) {
                    $names[$parts[0]] = null;
                }

                if (isset($parts[1])) {
                    if ($names[$parts[0]] === null) {
                        $names[$parts[0]] = [];
                    }

                    $child = get_unflat_columns($parts[1]);
                    $names[$parts[0]][key($child)] = current($child);
                };
            }
        }

        return $names;
    }
}

if (!function_exists('column_identifier_reverse')) {
    /**
     * Reverse a dot notation column identifier
     *
     * Ex: posts.comments.author.email => email.author.comments.posts
     *
     * @param string $identifier
     *
     * @return string
     */
    function column_identifier_reverse($identifier)
    {
        if (strpos($identifier, '.') === false) {
            return $identifier;
        }

        $parts = array_reverse(explode('.', $identifier));

        return implode('.', $parts);
    }
}

if (!function_exists('compact_sort_to_array')) {
    /**
     * Converts compact sorting column to array
     *
     * Example: -<field> to [field => 'DESC']
     *
     * @param $field
     *
     * @return array
     *
     * @throws \Directus\Exception\Exception
     */
    function compact_sort_to_array($field)
    {
        if (!is_string($field)) {
            throw new \Directus\Exception\Exception(sprintf('field is expected to be string, %s given.', gettype($field)));
        }

        $order = 'ASC';
        if (substr($field, 0, 1) === '-') {
            $order = 'DESC';
            $field = substr($field, 1);
        }

        return [
            $field => $order
        ];
    }
}

if (!function_exists('convert_param_columns')) {
    function convert_param_columns($columns)
    {
        if (is_array($columns)) {
            return $columns;
        }

        if (is_string($columns)) {
            // remove all 'falsy' columns name
            $columns = array_filter(\Directus\Util\StringUtils::csv($columns, true));
        } else {
            $columns = [];
        }

        return $columns;
    }
}

if (!function_exists('slugify')) {
    /**
     * Converts a string to a valid slug
     *
     * @param string $string
     *
     * @return string
     */
    function slugify($string)
    {
        $string = trim($string);
        $search = ['Á', 'Ä', 'Â', 'À', 'Ã', 'Å', 'Č', 'Ç', 'Ć', 'Ď', 'É', 'Ě', 'Ë', 'È', 'Ê', 'Ẽ', 'Ĕ', 'Ȇ', 'Í', 'Ì', 'Î', 'Ï', 'Ň', 'Ñ', 'Ó', 'Ö', 'Ò', 'Ô', 'Õ', 'Ø', 'Ř', 'Ŕ', 'Š', 'Ť', 'Ú', 'Ů', 'Ü', 'Ù', 'Û', 'Ý', 'Ÿ', 'Ž', 'á', 'ä', 'â', 'à', 'ã', 'å', 'č', 'ç', 'ć', 'ď', 'é', 'ě', 'ë', 'è', 'ê', 'ẽ', 'ĕ', 'ȇ', 'í', 'ì', 'î', 'ï', 'ň', 'ñ', 'ó', 'ö', 'ò', 'ô', 'õ', 'ø', 'ð', 'ř', 'ŕ', 'š', 'ť', 'ú', 'ů', 'ü', 'ù', 'û', 'ý', 'ÿ', 'ž', 'þ', 'Þ', 'Đ', 'đ', 'ß', 'Æ', 'a', '·', '/', '_', ',', ':', ';'];
        $replace = ['A', 'A', 'A', 'A', 'A', 'A', 'C', 'C', 'C', 'D', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'N', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'R', 'R', 'S', 'T', 'U', 'U', 'U', 'U', 'U', 'Y', 'Y', 'Z', 'a', 'a', 'a', 'a', 'a', 'a', 'c', 'c', 'c', 'd', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'r', 'r', 's', 't', 'u', 'u', 'u', 'u', 'u', 'y', 'y', 'z', 'b', 'B', 'D', 'd', 'B', 'A', 'a', '-', '-', '-', '-', '-', '-'];

        $string = str_replace($search, $replace, $string);

        $patterns = [
            '/[^A-Za-z0-9 -]/', // remove invalid chars
            '/\s+/', // Collapse whitespace and replace by -
            '/\-+/' // Collapse dashes
        ];
        $replacements = [
            '',
            '-',
            '-'
        ];

        // NOTE: using lowercase because the interface also always return lowercase
        return mb_strtolower(preg_replace($patterns, $replacements, $string));
    }
}

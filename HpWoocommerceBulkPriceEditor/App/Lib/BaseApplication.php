<?php

namespace HpWoocommerceBulkPriceEditor\App\Lib;

// If this file is called directly, abort.
if (!defined('HP_EXEC')) {
    die;
}

class BaseApplication
{

    protected ?string $namespace = null;
    protected ?string $pluginRoot = null;
    protected ?string $pluginFile = null;
    protected ?string $pluginFunctionPrefix = null;
    protected ?string $pluginMenuName = null;
    public ?string $pluginName = null;
    protected ?array $pluginData = null;
    protected ?string $env = null;
    protected ?array $assets = null;

    public function __construct($namespace, $root)
    {
        $this->namespace = $namespace;
        $this->pluginRoot = $root;
        $this->pluginFile = "{$this->pluginRoot}/index.php";
        $exp = explode('/', $this->pluginRoot);
        $this->pluginName = $exp[count($exp) - 1];
        $this->pluginFunctionPrefix = str_replace('-', '_', $this->pluginName);


        $this->env = require("{$this->pluginRoot}/{$this->namespace}/App/config/env.php");

        if (!function_exists('get_plugin_data')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        $this->pluginData = get_plugin_data($this->pluginFile, true, false);
        if (!$this->pluginMenuName) {
            $this->pluginMenuName = $this->pluginData['Name'];
        }


        if ((defined(WP_DEBUG) && WP_DEBUG) || $this->env === 'development') {
            $functionsDevPath = "{$this->pluginRoot}/{$this->namespace}/App/Lib/dev/functions.php";
            $testFile = "{$this->pluginRoot}/{$this->namespace}/App/Lib/dev/test.php";
            if (Helper::file_exists($functionsDevPath) && Helper::file_exists($testFile)) {
                require_once $functionsDevPath;
                require_once $testFile;
            }

            @ini_set('display_errors', 1);
        }

        //dd(WC_VERSION);
        $this->assets = require("{$this->pluginRoot}/{$this->namespace}/App/config/assets.php");
    }

    public function run(): void
    {
        $this->hooks();
    }

    protected function hooks(): void
    {
        // On activation
        register_activation_hook($this->pluginFile, [$this, 'onActivation']);

        // On deactivation
        register_deactivation_hook($this->pluginFile, [$this, 'onDeactivation']);

        // Add custom menu item to the admin dashboard
        add_action('admin_menu', [$this, 'admin_menu']);

        // Admin ajax
        add_action("wp_ajax_{$this->pluginFunctionPrefix}_action", [$this, 'my_ajax_action_function']);

        // Custom CORS
        add_action('init', [$this, 'my_custom_cors_headers']);

        add_action('admin_init', [$this, 'admin_init']);
        add_action('admin_head', [$this, 'admin_head']);

        // Init rest routes
        $rest = require("{$this->pluginRoot}/{$this->namespace}/App/routes/rest.php");

        add_filter('script_loader_tag', function ($tag, $handle) {
            if ($handle === "{$this->pluginName}-admin-module") {
                $tag = str_replace(
                    '<script ',
                    '<script type="module" crossorigin ',
                    $tag
                );
            }
            return $tag;
        }, 10, 2);

        // Rest routes
        add_action('rest_api_init', function () use ($rest) {
            foreach ($rest as $route => $data) {
                register_rest_route("{$this->pluginName}/api/v1", $route, [
                    'methods' => $data['method'],
                    'callback' => [$this, 'my_rest_action_function'],
                    'permission_callback' => '__return_true',
                ]);
            }
        });
    }

    public function admin_init(): void
    {

    }

    public function my_custom_cors_headers(): void
    {
        // Check if the request is for the admin-ajax.php endpoint
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            // Set Access-Control-Allow-Origin to the domain you're making requests from
            header("Access-Control-Allow-Origin: " . esc_url($_SERVER['HTTP_ORIGIN']));
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

            // If it's an OPTIONS request (preflight), exit early with success response
            if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
                status_header(200);
                //wp_die();
            }
        }
    }

    public function my_ajax_action_function()
    {
        if (!current_user_can('manage_woocommerce')) {
            return;
        }

        if (!$task = isset($_GET['task']) ? sanitize_text_field(wp_unslash($_GET['task'])) : null) {
            $task = isset($_POST['task']) ? sanitize_text_field(wp_unslash($_POST['task'])) : null;
        }

        if ($task) {
            $REQUEST_METHOD = $_SERVER['REQUEST_METHOD'];
            $admin = require("{$this->pluginRoot}/{$this->namespace}/App/routes/admin.php");

            if (isset($admin[$task]) && $REQUEST_METHOD === $admin[$task]['method']) {
                $controller_class = $admin[$task]['callback'][0];
                $task_name = $admin[$task]['callback'][1];

                $controller = new $controller_class();
                if (method_exists($controller, $task_name)) {
                    $request = new Request();
                    $controller->{$task_name}($request);
                } else {
                    wp_die(esc_html(esc_js("{$controller_class}->{$task_name} method not found!")));
                }
            }
        } else {
            wp_send_json_error(['message' => 'Not found!'], 404);
        }
    }

    public function my_rest_action_function($request): void
    {
        $rest = require("{$this->pluginRoot}/{$this->namespace}/App/routes/rest.php");
        $rest_route = str_replace(["/{$this->pluginName}/api/v1"], [''], $request->get_param('rest_route'));

        if (isset($rest[$rest_route])) {
            $controller_class = $rest[$rest_route]['callback'][0];
            $task_name = $rest[$rest_route]['callback'][1];

            $controller = new $controller_class();
            if (!method_exists($controller, $task_name)) {
                wp_die(esc_html(esc_js("{$controller_class}->{$task_name} method not found!")));
            }

            $request = new Request($request);
            $controller->{$task_name}($request);
        } else {
            wp_die(esc_html(esc_js("/{$this->pluginName}/api/v1{$rest_route} not found!")));
        }
    }

    public function admin_menu(): void
    {
        // Add a new top-level menu item to the admin dashboard
        add_menu_page(
            $this->pluginData['Title'],             // Page title
            $this->pluginMenuName,              // Menu title in the sidebar
            'manage_woocommerce',                   // Capability required to access this menu
            $this->pluginName,                      // Menu slug (unique identifier)
            [$this, 'display_admin_page'],          // Function to display the page content
            home_url('/') . "wp-content/plugins/{$this->pluginName}/ui/images/{$this->pluginName}.png",      // Icon URL or Dashicon class
            98                               // Position in the menu (lower numbers appear higher)
        );
    }

    public function display_admin_page(): void
    {
        // enqueue WordPress media manager scripts
        wp_enqueue_media();

        $home = home_url();

        $src = $home . "/wp-content/plugins/{$this->pluginName}/ui";
        $assets = $this->assets;

        $env = $this->env;

        $i18n = $this->loadAdminTranslations();
        $rtl = is_rtl();

        if ('development' === $env) {
            $iframeSrc = "http://wordpress-local.com:9000";
            $iframeSrc = $iframeSrc . '#' . urlencode(json_encode(['HP' => ['baseURL' => $home, 'i18n' => $i18n, 'rtl' => $rtl]]));
            $origin = 'http://wordpress-local.com:9000';
            require_once "{$this->pluginRoot}/{$this->namespace}/App/Lib/dev/home.php";
        } else {
            wp_enqueue_script("{$this->pluginName}-admin-module", "{$src}/assets/{$assets['js']}", [], null, true);
            wp_enqueue_style("{$this->pluginName}-admin-style", "{$src}/assets/{$assets['css']}", [], null);
            require_once "{$this->pluginRoot}/{$this->namespace}/App/templates/home.php";
        }
    }

    public function loadAdminTranslations(): array
    {
        $locale = get_user_locale();

        $po_file = "{$this->pluginRoot}{$this->pluginData['DomainPath']}/{$this->pluginData['TextDomain']}-{$locale}.po";
        $translations = [];
        if (!file_exists($po_file)) {
            return $translations;
        }

        $lines = file($po_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $msgid = '';
        foreach ($lines as $line) {
            $line = trim($line);

            // msgid
            if (str_starts_with($line, 'msgid ')) {
                $msgid = trim(substr($line, 6), " \"");
            } // msgstr
            elseif (str_starts_with($line, 'msgstr ')) {
                $msgstr = trim(substr($line, 7), " \"");
                if ($msgid !== '') {
                    $translations[$msgid] = $msgstr;
                    $msgid = '';
                }
            }
        }

        return $translations;
    }

    public function onActivation()
    {

    }

    public function onDeactivation()
    {

    }

    public function admin_head(): void
    {
        // only run on plugin's admin page
        $screen = get_current_screen();

        if (!is_object($screen) || $screen->id !== "toplevel_page_{$this->pluginName}") {
            return;
        }

        // Remove all registered admin notices
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
    }

}
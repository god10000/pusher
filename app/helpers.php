<?php
/**
 * 助手函数
 *
 * @author mybsdc <mybsdc@gmail.com>
 * @date 2019/3/3
 * @time 16:34
 */

use Luolongfei\Lib\Argv;
use Luolongfei\Lib\Config;
use Luolongfei\Lib\Log;
use Luolongfei\Lib\Env;

if (!function_exists('config')) {
    /**
     * 获取配置
     *
     * @param string $key 键，支持点式访问
     *
     * @return array|mixed
     */
    function config($key = '')
    {
        return Config::instance()->get($key);
    }
}

if (!function_exists('system_log')) {
    /**
     * 写日志
     *
     * @param $content
     * @param array $response
     * @param string $fileName
     */
    function system_log($content, array $response = [], $fileName = '')
    {
        try {
            $path = sprintf('%s/logs/system_log/%s/', ROOT_PATH, date('Y-m'));
            $file = $path . ($fileName ?: date('d')) . '.log';

            if (!is_dir($path)) {
                mkdir($path, 0777, true);
                chmod($path, 0777);
            }

            $handle = fopen($file, 'a'); // 追加而非覆盖

            if (!filesize($file)) {
                chmod($file, 0666);
            }

            fwrite($handle, sprintf(
                    "[%s] %s %s\n",
                    date('Y-m-d H:i:s'),
                    is_string($content) ? $content : json_encode($content),
                    $response ? json_encode($response, JSON_UNESCAPED_UNICODE) : '')
            );

            fclose($handle);
        } catch (\Exception $e) {
            // DO NOTHING
        }
    }
}

if (!function_exists('is_locked')) {
    /**
     * 检查任务是否已被锁定
     *
     * @param string $taskName
     * @param bool $always 是否被永久锁定
     *
     * @return bool
     * @throws Exception
     */
    function is_locked($taskName = '', $always = false)
    {
        try {
            $lock = sprintf(
                '%s/num_limit/%s/%s.lock',
                APP_PATH,
                $always ? 'always' : date('Y-m-d'),
                $taskName
            );

            return file_exists($lock);
        } catch (\Exception $e) {
            system_log(sprintf('检查任务%s是否锁定时出错，错误原因：%s', $taskName, $e->getMessage()));
        }

        return false;
    }
}

if (!function_exists('lock_task')) {
    /**
     * 锁定任务
     *
     * 防止重复执行
     *
     * @param string $taskName
     * @param bool $always 是否永久锁定
     *
     * @return bool
     */
    function lock_task($taskName = '', $always = false)
    {
        try {
            $lock = sprintf(
                '%s/num_limit/%s/%s.lock',
                APP_PATH,
                $always ? 'always' : date('Y-m-d'),
                $taskName
            );

            $path = dirname($lock);
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
                chmod($path, 0777);
            }

            if (file_exists($lock)) {
                return true;
            }

            $handle = fopen($lock, 'a'); // 追加而非覆盖

            if (!filesize($lock)) {
                chmod($lock, 0666);
            }

            fwrite($handle, sprintf(
                    "Locked at %s.\n",
                    date('Y-m-d H:i:s')
                )
            );

            fclose($handle);

            Log::info(sprintf('%s已被锁定，此任务%s已不会再执行，请知悉', $taskName, $always ? '' : '今天内'));
        } catch (\Exception $e) {
            system_log(sprintf('创建锁定任务文件%s时出错，错误原因：%s', $lock, $e->getMessage()));

            return false;
        }

        return true;
    }
}

if (!function_exists('env')) {
    /**
     * 获取环境变量值
     *
     * @param $key
     * @param null $default
     *
     * @return array|bool|false|null|string
     */
    function env($key, $default = null)
    {
        Env::instance()->load();

        $value = getenv($key);

        if ($value === false) {
            return $default;
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }

        if (($valueLength = strlen($value)) > 1 && $value[0] === '"' && $value[$valueLength - 1] === '"') { // 去除双引号
            return substr($value, 1, -1);
        }

        return $value;
    }
}

if (!function_exists('get_argv')) {
    /**
     * 获取命令行传参
     *
     * @param string $name
     * @param string $defaults
     *
     * @return mixed|string
     */
    function get_argv(string $name, string $defaults = '')
    {
        return Argv::instance()->get($name, $defaults);
    }
}
<?php
/**
 * 命令行参数
 *
 * @author mybsdc <mybsdc@gmail.com>
 * @date 2020/1/3
 * @time 16:32
 */

namespace Luolongfei\Lib;

class Argv
{
    /**
     * @var Argv
     */
    protected static $instance;

    /**
     * @var array 所有命令行传参
     */
    public $allArgvs = [];

    /**
     * @return Argv
     */
    public static function instance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * 获取命令行参数
     *
     * @param string $name
     * @param string $defaults
     *
     * @return mixed|string
     */
    public function get(string $name, string $defaults = '')
    {
        if (!$this->allArgvs) {
            $this->setAllArgvs();
        }

        return $this->allArgvs[$name] ?? $defaults;
    }

    /**
     * 设置命令行所有参数
     *
     * @return array
     */
    public function setAllArgvs()
    {
        global $argv;

        foreach ($argv as $a) { // Windows默认命令行无法正确传入使用引号括住的带空格参数，换个命令行终端就好，Linux不受影响
            if (preg_match('/^-{1,2}(?P<name>\w+)(?:=([\'"]|)(?P<val>[^\n\t\v\f\r\'"]+)\2)?$/i', $a, $m)) {
                $this->allArgvs[$m['name']] = $m['val'] ?? true;
            }
        }

        return $this->allArgvs;
    }
}
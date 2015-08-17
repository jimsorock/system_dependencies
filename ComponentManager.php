<?php

namespace ProgA\SystemDependecies;

require 'Component.php';

class ComponentManager {

    private $components;
    private static $componentManager;

    function __construct()
    {
        $this->setComponents(array('TELNET', 'TCPIP','NETCARD', 'DNS', 'foo', 'HTML', 'BROWSER'));
    }

    public static function getInstance()
    {
        if (self::$componentManager === NULL) {
            self::$componentManager = new ComponentManager();
        }

        return self::$componentManager;
    }


    /**
     * @return mixed
     */
    public function getComponents()
    {
        return $this->components;
    }

    /**
     * @param $componentName
     * @return Component
     */
    public function getComponent($componentName)
    {
        return $this->components[$componentName];
    }

    /**
     * @param $name
     * @param $components
     * @return $this
     */
    public function setComponent($name, $components)
    {
        $this->components[$name] = new Component($name, $components);
        return $this;
    }

    public function setComponents($componentsList)
    {
        foreach($componentsList as $component) {
            $this->components[$component] = new Component($component, array());
        }
    }

    public function listInstalledComponents()
    {
        /** @var Component $component */
        foreach($this->getComponents() as $component) {
            if($component->isInstalled()) {
                echo "  {$component->getName()}\n";
            }
        }
    }

    public function main(ComponentManager $componentManager = null) {
        if($componentManager == null) {
            $componentManager = $this->getInstance();
        }
        $handle = fopen($_SERVER['argv'][1], "r");
        if ($handle) {
            while (($buffer = fgets($handle, 80)) !== false) {
                echo $buffer;
                $keywords = preg_split('/\s+/', $buffer, -1, PREG_SPLIT_NO_EMPTY);
                switch($keywords[0]) {
                    case 'DEPEND':
                        //setup component dependencies
                        $componentManager->setComponent($keywords[1], array_slice($keywords, 2));
                        break;
                    case 'INSTALL':
                        //install component
                        $componentManager->getComponent($keywords[1])->install(true);
                        break;
                    case 'REMOVE':
                        //remove component
                        $componentManager->getComponent($keywords[1])->remove();
                        break;
                    case 'LIST':
                        //list installed components
                        $componentManager->listInstalledComponents();
                        break;
                    case 'END':
                        //end of input
                        break;
                }

            }
            if (!feof($handle)) {
                echo "Error: unexpected fgets() fail\n";
            }
            echo "\n";
            fclose($handle);
            //var_dump($componentManager->getComponents());
        }
    }

}

if (file_exists($_SERVER['argv'][1])) {
    ComponentManager::getInstance()->main();
} else {
    echo "The file {$_SERVER['argv'][1]} does not exist\n";
}